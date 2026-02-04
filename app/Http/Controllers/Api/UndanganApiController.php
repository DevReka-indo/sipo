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

    public function kodeFilter()
    {
        $kode = Undangan::whereNotNull('kode')
            ->pluck('kode')
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
            $user = Auth::user();

            if ($request->status === 'approve') {
                $request->validate([
                    'status' => 'required',
                ]);
            } else {
                $request->validate([
                    'status' => 'required',
                    'catatan' => 'required',
                ]);
            }

            switch ($request->status) {
                case 'approve':
                    $undangan->status = 'approve';
                    $undangan->tgl_disahkan = now();

                    $qrText = 'Disetujui oleh: ' . Auth::user()->firstname . ' ' . Auth::user()->lastname
                        . "\nNomor Undangan: " . ($undangan->nomor_undangan ?? '-')
                        . "\nTanggal: " . $undangan->tgl_disahkan->translatedFormat('l, d F Y H:i:s')
                        . "\nDikeluarkan oleh Website SIPO PT Rekaindo Global Jasa";
                    $qrService = new QrCodeService;

                    $qrBase64 = $qrService->generateWithLogo($qrText);

                    $undangan->qr_approved_by = $qrBase64;

                    $tujuanArray = explode(';', $undangan->tujuan);
                    foreach ($tujuanArray as $tujuanId) {
                        Kirim_Document::create([
                            'id_document' => $undangan->id_undangan,
                            'jenis_document' => 'undangan',
                            'id_pengirim' => $undangan->pembuat,
                            'id_penerima' => $tujuanId,
                            'status' => 'approve',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        Notifikasi::create([
                            'judul' => 'Undangan Masuk',
                            'judul_document' => $undangan->judul,
                            'id_user' => $tujuanId,
                            'updated_at' => now(),
                        ]);
                        $push->sendToUser(
                            $tujuanId,
                            'Undangan Masuk',
                            $undangan->judul
                        );
                    }

                    Notifikasi::create([
                        'judul' => 'Undangan Disetujui',
                        'judul_document' => $undangan->judul,
                        'id_user' => $undangan->pembuat,
                        'updated_at' => now(),
                    ]);
                    $push->sendToUser(
                        $undangan->pembuat,
                        'Undangan Disetujui',
                        $undangan->judul
                    );
                    break;
                case 'reject':
                    $undangan->status = 'reject';
                    $undangan->tgl_disahkan = now();
                    Notifikasi::create([
                        'judul' => 'Undangan Ditolak',
                        'judul_document' => $undangan->judul,
                        'id_user' => $undangan->pembuat,
                        'updated_at' => now(),
                    ]);
                    $push->sendToUser(
                        $undangan->pembuat,
                        'Undangan Ditolak',
                        $undangan->judul
                    );
                    break;
                case 'correction':
                    $undangan->status = 'correction';
                    Notifikasi::create([
                        'judul' => 'Undangan Perlu Revisi',
                        'judul_document' => $undangan->judul,
                        'id_user' => $undangan->pembuat,
                        'updated_at' => now(),
                    ]);
                    $push->sendToUser(
                        $undangan->pembuat,
                        'Undangan Perlu Revisi',
                        $undangan->judul
                    );
                    break;
            }

            Kirim_Document::where('id_document', $undangan->id_undangan)
                ->where('jenis_document', 'undangan')
                ->where('id_penerima', $user->id)
                ->update(['status' => $request->status ?? null]);

            $undangan->catatan = $request->catatan ?? null;
            $undangan->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Status dokumen berhasil diperbarui'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
