<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UndanganResource;
use App\Http\Controllers\Api\NotifApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\{Undangan, Seri, User, Divisi, Arsip, Notifikasi, Kirim_Document, Backup_Document, Department, Director};
use Clegginabox\PDFMerger\PDFMerger;
use Barryvdh\DomPDF\Facade\PDF;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Services\QrCodeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\CounterNomorSurat;
use App\Models\BagianKerja;

class UndanganApiController extends Controller
{
    public function index(Request $request)
    {

        $user = Auth::user();

        $undanganDiarsipkan = Arsip::where('user_id', $user->id)
            ->where('jenis_document', 'App\Models\Undangan')
            ->pluck('document_id')->toArray();

        $ownedDocs = Kirim_Document::where('jenis_document', 'undangan')
            ->where(function ($q) use ($user) {
                $q->where('id_penerima', $user->id)
                    ->orWhere('id_pengirim', $user->id);
            })
            ->pluck('id_document')->unique()->toArray();

        // eager load user
        $query = Undangan::with('user')->whereNotIn('id_undangan', $undanganDiarsipkan)->whereIn('id_undangan', $ownedDocs)->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('kode') && $request->kode !== 'pilih') {
            $query->where('kode', $request->kode);
        }

        $undangans = $query->get();

        return UndanganResource::collection($undangans)->additional([
            'status' => 'success',
            'message' => $undangans->isEmpty() ? 'Belum ada undangan' : 'Daftar undangan ditemukan',
        ]);
    }

    public function undanganKeluar()
    {
        $user = Auth::user();
        $userId = (int) $user->id;

        $undanganDiarsipkan = Arsip::query()
            ->where('user_id', $userId)
            ->where('jenis_document', 'App\\Models\\Undangan')
            ->pluck('document_id')
            ->toArray();

        $kodeBagianUser = collect(explode(';', (string) $user->kode_bagian))
            ->map(fn ($kode) => trim($kode))
            ->filter()
            ->unique()
            ->values();

        $query = Undangan::query()
            ->with('user')
            ->whereNotIn('id_undangan', $undanganDiarsipkan)
            ->where(function ($q) use ($kodeBagianUser) {
                foreach ($kodeBagianUser as $kodeBagian) {
                    $q->orWhereRaw(
                        "FIND_IN_SET(?, REPLACE(COALESCE(kode_bagian, ''), ';', ','))",
                        [$kodeBagian]
                    );
                }
            });

        if ((int) $user->role_id_role === 2) {
            $query->where('pembuat', $userId);
        }

        $undangans = $query
            ->latest()
            ->get();

        $undangans->transform(function ($undangan) {
            $undangan->final_status = $undangan->status;
            $undangan->jenis = 'keluar';
            return $undangan;
        });

        return $this->apiResponse(
            UndanganResource::collection($undangans),
            $undangans->isEmpty()
                ? 'Belum ada undangan keluar'
                : 'Daftar undangan keluar ditemukan'
        );
    }

    public function undanganMasuk()
    {
        $user = Auth::user();
        $uid = (string) $user->id;

        $undanganDiarsipkan = Arsip::where('user_id', $user->id)
            ->where('jenis_document', 'App\Models\Undangan')
            ->pluck('document_id')
            ->toArray();

        $undangans = Undangan::with('user')
            ->where('status', 'approve')
            ->where(function ($q) use ($uid) {
                $q->whereRaw("FIND_IN_SET(?, REPLACE(COALESCE(tujuan, ''), ';', ','))", [$uid])
                ->orWhereRaw("FIND_IN_SET(?, REPLACE(COALESCE(tembusan, ''), ';', ','))", [$uid])
                ->orWhereRaw("FIND_IN_SET(?, REPLACE(COALESCE(bcc, ''), ';', ','))", [$uid]);
            })
            ->whereNotIn('id_undangan', $undanganDiarsipkan)
            ->latest()
            ->get();

        return $this->apiResponse(
            UndanganResource::collection($undangans),
            $undangans->isEmpty() ? 'Belum ada undangan diterima' : 'Daftar undangan masuk ditemukan'
        );
    }

    public function kodeFilter()
    {
        $kode = BagianKerja::query()
            ->whereNotNull('kode_bagian')
            ->pluck('kode_bagian')
            ->filter()
            ->unique()
            ->values();

        return response()->json([
            'status' => 'success',
            'data' => $kode,
        ], 200);
    }

    public function getAll()
    {
        $undangans = Undangan::with('user')->latest()->get();

        return UndanganResource::collection($undangans)->additional([
            'status' => 'success',
            'message' => $undangans->isEmpty() ? 'Belum ada undangan' : 'Daftar undangan ditemukan',
        ]);
    }

    public function show($id)
    {

        $undangan = Undangan::with('user')->findOrFail($id);

        if (!$undangan) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Undangan tidak ditemukan',
                ],
                404,
            );
        }

        return new UndanganResource($undangan);
    }

    public function viewPDF($id)
    {
        $undangan = Undangan::findOrFail($id);

        if (!$undangan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Undangan tidak ditemukan',
            ], 404);
        }

        $pdf = PDF::loadView('pdf.undangan', compact('undangan'));
        return $pdf->stream("undangan_{$undangan->id_undangan}.pdf");
    }

    public function lampiran($id)
    {
        $undangan = Undangan::findOrFail($id);
        if (!$undangan->lampiran) {
            abort(404, 'Lampiran tidak ditemukan');
        }

        $lampiran = $undangan->lampiran;

        // 1️⃣ Coba decode JSON → untuk kasus multiple file
        $decoded = json_decode($lampiran, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            // Kalau ternyata array, kembalikan daftar URL
            $urls = [];
            foreach ($decoded as $index => $fileBase64) {
                $urls[] = route('api.undangan.lampiran.single', [
                    'id' => $id,
                    'index' => $index,
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Multiple lampiran ditemukan',
                'data' => $urls,
            ]);
        }

        // 2️⃣ Kalau single file
        $fileData = base64_decode($lampiran);

        if (!$fileData) {
            abort(404, 'Lampiran tidak valid');
        }

        // Deteksi mime type
        $finfo = finfo_open();
        $mimeType = finfo_buffer($finfo, $fileData, FILEINFO_MIME_TYPE);
        finfo_close($finfo);

        $extension = explode('/', $mimeType)[1] ?? 'bin';
        $fileName = "lampiran_{$id}." . $extension;

        return response($fileData, 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="' . $fileName . '"');
    }

    public function lampiranSingle($id, $index)
    {
        $undangan = Undangan::findOrFail($id);
        $decoded = json_decode($undangan->lampiran, true);

        if (json_last_error() !== JSON_ERROR_NONE || !isset($decoded[$index])) {
            abort(404, 'Lampiran tidak ditemukan');
        }

        $fileData = base64_decode($decoded[$index]);

        $finfo = finfo_open();
        $mimeType = finfo_buffer($finfo, $fileData, FILEINFO_MIME_TYPE);
        finfo_close($finfo);

        $extension = explode('/', $mimeType)[1] ?? 'bin';
        $fileName = "lampiran_{$id}_{$index}." . $extension;

        return response($fileData, 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="' . $fileName . '"');
    }

    public function updateStatus(Request $request, $id)
    {
        $push = new NotifApiController();

        try {
            $undangan = Undangan::findOrFail($id);
            $userId   = Auth::id();

            // =========================
            // Validasi dinamis
            // =========================
            $rules = [
                'status' => 'required|in:approve,reject,correction',
                'catatan' => 'nullable|string',
            ];

            if (in_array($request->status, ['reject', 'correction'])) {
                $rules['catatan'] = 'required|string';
            }

            $validated = $request->validate($rules);

            DB::transaction(function () use ($request, $undangan, $userId, $push) {

                // =========================
                // Update status undangan
                // =========================
                $undangan->status = $request->status;

                // Update kirim_document milik approver (yang sedang login)
                $currentKirim = Kirim_Document::where('id_document', $undangan->id_undangan)
                    ->where('jenis_document', 'undangan')
                    ->where('id_penerima', $userId)
                    ->first();

                if ($currentKirim) {
                    $currentKirim->status     = $request->status;
                    $currentKirim->updated_at = now();
                    $currentKirim->save();
                }

                // =========================
                // APPROVE
                // =========================
                if ($request->status === 'approve') {
                    $tglDisahkan = now();
                    $undangan->tgl_disahkan = $tglDisahkan;

                    // 1) Generate nomor surat (jika masih kosong)
                    if (empty($undangan->nomor_undangan)) {
                        $bulanRomawi = CounterNomorSurat::getBulanRomawi($tglDisahkan->month);

                        // divisi/kode bagian untuk counter (sesuai web controller)
                        $divisiCounter = $undangan->kode_bagian;

                        // Kalau kode_bagian kosong, fallback minimal (hindari error)
                        // (web controller fallback ke getDivDeptKode(Auth::user()))
                        if (empty($divisiCounter)) {
                            // Fallback sederhana: pakai kode yang ada di field kode, atau 'GEN'
                            $divisiCounter = $undangan->kode ?: 'GEN';
                        }

                        $counter = CounterNomorSurat::createNomorSurat([
                            'tanggal_permintaan' => $tglDisahkan,
                            'perusahaan' => 'REKA',
                            'kode_tipe_surat' => 'GEN',
                            'divisi' => $divisiCounter,
                            'bulan' => $bulanRomawi,
                            'tahun' => $tglDisahkan->year,
                            'pic_peminta' => Auth::user()->fullname,
                            'jenis' => 'Undangan',
                            'perihal' => $undangan->judul,
                        ]);

                        $undangan->nomor_undangan = $counter->nomor_surat_generated;
                    }

                    // 2) Generate QR approved_by (pakai nomor undangan final)
                    $qrText = "Disetujui oleh: " . Auth::user()->firstname . ' ' . Auth::user()->lastname
                        . "\nNomor Undangan: " . ($undangan->nomor_undangan ?? '-')
                        . "\nTanggal: " . $tglDisahkan->translatedFormat('l, d F Y H:i:s')
                        . "\nDikeluarkan oleh website SIPO PT Rekaindo Global Jasa";

                    $qrService = new QrCodeService();
                    $undangan->qr_approved_by = $qrService->generateWithLogo($qrText);

                    // 3) Kirim ke tujuan (anti dobel)
                    $tujuanUserIds = is_array($undangan->tujuan)
                        ? $undangan->tujuan
                        : explode(';', (string) $undangan->tujuan);

                    foreach ($tujuanUserIds as $tujuanId) {
                        $tujuanId = trim((string) $tujuanId);
                        if ($tujuanId === '') continue;

                        // skip pembuat (sesuai web controller)
                        if ((int)$tujuanId === (int)$undangan->pembuat) continue;

                        // anti duplikat kirim_document approve
                        $exists = Kirim_Document::where([
                            ['id_document', '=', $undangan->id_undangan],
                            ['jenis_document', '=', 'undangan'],
                            ['id_pengirim', '=', $undangan->pembuat],
                            ['id_penerima', '=', $tujuanId],
                            ['status', '=', 'approve'],
                        ])->exists();

                        if (!$exists) {
                            Kirim_Document::create([
                                'id_document' => $undangan->id_undangan,
                                'jenis_document' => 'undangan',
                                'id_pengirim' => $undangan->pembuat,
                                'id_penerima' => $tujuanId,
                                'status' => 'approve',
                                'updated_at' => now(),
                            ]);
                        }

                        Notifikasi::create([
                            'id_document'    => $undangan->id_undangan,
                            'jenis_document' => 'undangan',
                            'judul' => "Undangan Masuk",
                            'judul_document' => $undangan->judul,
                            'id_user' => $tujuanId,
                            'updated_at' => now(),
                        ]);

                        // $push->sendToUser($tujuanId, 'Undangan Masuk', $undangan->judul);
                    }

                    // notif ke pembuat (sesuai web controller)
                    Notifikasi::create([
                        'id_document'    => $undangan->id_undangan,
                        'jenis_document' => 'undangan',
                        'judul' => "Undangan Disetujui dan Telah Terkirim",
                        'judul_document' => $undangan->judul,
                        'id_user' => $undangan->pembuat,
                        'updated_at' => now(),
                    ]);
                    // $push->sendToUser($undangan->pembuat, 'Undangan Disetujui dan Telah Terkirim', $undangan->judul);

                // =========================
                // REJECT
                // =========================
                } elseif ($request->status === 'reject') {
                    $undangan->tgl_disahkan = now();

                    Notifikasi::create([
                        'id_document'    => $undangan->id_undangan,
                        'jenis_document' => 'undangan',
                        'judul' => "Undangan Ditolak",
                        'judul_document' => $undangan->judul,
                        'id_user' => $undangan->pembuat,
                        'updated_at' => now(),
                    ]);
                    // $push->sendToUser($undangan->pembuat, 'Undangan Ditolak', $undangan->judul);

                // =========================
                // CORRECTION
                // =========================
                } elseif ($request->status === 'correction') {
                    $undangan->tgl_disahkan = now();

                    Notifikasi::create([
                        'id_document'    => $undangan->id_undangan,
                        'jenis_document' => 'undangan',
                        'judul' => "Undangan Perlu Dikoreksi",
                        'judul_document' => $undangan->judul,
                        'id_user' => $undangan->pembuat,
                        'updated_at' => now(),
                    ]);
                    // $push->sendToUser($undangan->pembuat, 'Undangan Perlu Dikoreksi', $undangan->judul);
                }

                // Simpan catatan
                $undangan->catatan = $request->catatan ?? null;

                // Save undangan
                $undangan->save();
            });

            return response()->json([
                'status'  => 'success',
                'message' => 'Status undangan berhasil diperbarui.',
                'data' => [
                    'id_undangan' => $undangan->id_undangan,
                    'status_doc' => $undangan->status,
                    'nomor_undangan' => $undangan->nomor_undangan,
                    'tgl_disahkan' => $undangan->tgl_disahkan,
                ]
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Error updateStatus Undangan API: ' . $e->getMessage(), [
                'id_undangan' => $id,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    function apiResponse($data, $message = '', $status = 'success')
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data_count' => is_countable($data) ? count($data) : 1,
            'data' => $data,
        ]);
    }
}
