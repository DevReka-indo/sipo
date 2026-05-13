<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\NotifApiController;
use App\Http\Resources\RisalahResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\Risalah;
use App\Models\Undangan;
use App\Models\Arsip;
use App\Models\Notifikasi;
use App\Models\BagianKerja;
use App\Models\Kirim_Document;
use App\Services\QrCodeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\CounterNomorSurat;
use App\Models\User;

class RisalahApiController extends Controller
{

    public function index(Request $request)
    {
        $user = Auth::user();
        $userId = (int) $user->id;
        $uid = (string) $userId;

        $risalahDiarsipkan = Arsip::where('user_id', $userId)
            ->where('jenis_document', 'App\Models\Risalah')
            ->pluck('document_id')
            ->toArray();

        $allowedSortColumns = [
            'created_at',
            'tgl_disahkan',
            'tgl_dibuat',
            'nomor_risalah',
            'judul',
        ];

        $sortBy = in_array($request->get('sort_by'), $allowedSortColumns)
            ? $request->get('sort_by')
            : 'created_at';

        $sortDirection = $request->get('sort_direction', 'desc') === 'asc'
            ? 'asc'
            : 'desc';

        $query = Risalah::with('user')
            ->whereNotIn('id_risalah', $risalahDiarsipkan)
            ->where(function ($q) use ($userId, $uid) {
                $q->where(function ($sub) use ($userId) {
                    $sub->where('status', '!=', 'approve')
                        ->where(function ($x) use ($userId) {
                            $x->where('pembuat', $userId)
                                ->orWhere('pemimpin_acara_user_id', $userId)
                                ->orWhere('notulis_acara_user_id', $userId);
                        });
                })
                ->orWhere(function ($sub) use ($userId, $uid) {
                    $sub->where('status', 'approve')
                        ->where(function ($x) use ($userId, $uid) {
                            $x->where('pembuat', $userId)
                                ->orWhere('pemimpin_acara_user_id', $userId)
                                ->orWhere('notulis_acara_user_id', $userId)
                                ->orWhereRaw(
                                    "FIND_IN_SET(?, REPLACE(COALESCE(tujuan, ''), ';', ','))",
                                    [$uid]
                                );
                        });
                });
            });

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('tgl_dibuat_awal') && $request->filled('tgl_dibuat_akhir')) {
            $query->whereBetween('tgl_dibuat', [
                $request->tgl_dibuat_awal,
                $request->tgl_dibuat_akhir,
            ]);
        } elseif ($request->filled('tgl_dibuat_awal')) {
            $query->whereDate('tgl_dibuat', '>=', $request->tgl_dibuat_awal);
        } elseif ($request->filled('tgl_dibuat_akhir')) {
            $query->whereDate('tgl_dibuat', '<=', $request->tgl_dibuat_akhir);
        }

        if ($request->filled('kode') && $request->kode !== 'pilih') {
            $query->where('kode', $request->kode);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('judul', 'like', '%' . $request->search . '%')
                    ->orWhere('nomor_risalah', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('approval')) {
            $query->where('status', 'pending')
                ->where(function ($q) use ($userId) {
                    $q->where('pemimpin_acara_user_id', $userId)
                        ->orWhere('notulis_acara_user_id', $userId);
                });
        }

        $perPage = $request->get('per_page');

        if ($perPage) {
            $risalahs = $query
                ->orderBy($sortBy, $sortDirection)
                ->paginate($perPage);
        } else {
            $risalahs = $query
                ->orderBy($sortBy, $sortDirection)
                ->get();
        }

        return $this->apiResponse(
            RisalahResource::collection($risalahs),
            $risalahs->isEmpty() ? 'Belum ada risalah' : 'Daftar risalah ditemukan'
        );

        // return RisalahResource::collection($risalahs)->additional([
        //     'status' => 'success',
        //     'message' => $risalahs->isEmpty() ? 'Belum ada risalah' : 'Daftar risalah ditemukan',
        // ]);
    }

    public function risalahKeluar(){

    }


    public function kodeFilter()
    {
        $kode = BagianKerja::query()
            ->select('kode_bagian')
            ->whereNotNull('kode_bagian')
            ->distinct()
            ->orderBy('kode_bagian')
            ->get()
            ->map(fn ($item) => [
                'label' => $item->kode_bagian,
                'value' => $item->kode_bagian,
            ])
            ->values();

        return response()->json([
            'status' => 'success',
            'data' => $kode,
        ], 200);
    }

    public function getAll()
    {
        $risalahs = Risalah::with('user')->latest()->get();

        return RisalahResource::collection($risalahs)->additional([
            'status' => 'success',
            'message' => $risalahs->isEmpty() ? 'Belum ada risalah' : 'Daftar risalah ditemukan',
        ]);
    }

    public function show($id)
    {

        $risalah = Risalah::with('user')->findOrFail($id);
        if ($risalah->nama_pemimpin_acara === Auth::user()->fullname) {
            $owner = true;
        } else {
            $owner = false;
        }
        if (!$risalah) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Risalah tidak ditemukan',
                ],
                404,
            );
        }
        //return new RisalahResource($risalah);
        return response()->json([
            'owner' => $owner,
            'data' => new RisalahResource($risalah),
        ]);
    }


    // endpoint GET /api/risalahs/{id}/lampiran
    public function lampiran($id)
    {
        $risalah = Risalah::findOrFail($id);

        if (!$risalah->lampiran) {
            abort(404, 'Lampiran tidak ditemukan');
        }

        $lampiran = $risalah->lampiran;

        // 1️⃣ Coba decode JSON → untuk kasus multiple file
        $decoded = json_decode($lampiran, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            // Kalau ternyata array, kembalikan daftar URL
            $urls = [];
            foreach ($decoded as $index => $fileBase64) {
                $urls[] = route('api.risalah.lampiran.single', [
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
        $risalah = Risalah::findOrFail($id);
        $decoded = json_decode($risalah->lampiran, true);


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
            $user = Auth::user();

            // Validasi
            $rules = [
                'status' => 'required|in:approve,reject,correction',
            ];

            // catatan wajib kalau reject/correction
            if (in_array($request->status, ['reject', 'correction'])) {
                $rules['catatan'] = 'required|string';
            } else {
                $rules['catatan'] = 'nullable|string';
            }

            $validated = $request->validate($rules);

            $result = DB::transaction(function () use ($id, $user, $request, $push) {
                // Lock baris risalah biar aman dari double approve bersamaan
                $risalah = Risalah::where('id_risalah', $id)->lockForUpdate()->firstOrFail();

                // Ambil kirim_document yang sedang diproses oleh user ini (biasanya pemimpin acara)
                $currentKirim = Kirim_Document::where('id_document', $risalah->id_risalah)
                    ->where('jenis_document', 'risalah')
                    ->where('id_penerima', $user->id)
                    ->lockForUpdate()
                    ->first();

                // Helper: update status kirim untuk user yang action
                if ($currentKirim) {
                    $currentKirim->status = $request->status;
                    $currentKirim->updated_at = now();
                    $currentKirim->save();
                }

                // Default set catatan (kalau approve biasanya boleh null)
                // Kalau kamu mau "catatan lama tidak terhapus saat approve", ganti jadi:
                // if ($request->filled('catatan')) { $risalah->catatan = $request->catatan; }
                $risalah->catatan = $request->catatan ?? null;

                // =========================
                // APPROVE
                // =========================
                if ($request->status === 'approve') {

                    // Kalau sudah approve sebelumnya, jangan bikin kirim_document & notif dobel
                    // Tapi tetap pastikan status tersimpan konsisten.
                    if ($risalah->status === 'approve' && !empty($risalah->nomor_risalah)) {
                        // tetap update status kirim user yang action
                        Kirim_Document::where('id_document', $risalah->id_risalah)
                            ->where('jenis_document', 'risalah')
                            ->where('id_penerima', $user->id)
                            ->update(['status' => 'approve', 'updated_at' => now()]);

                        return [
                            'status' => 'success',
                            'message' => 'Risalah sudah disetujui sebelumnya.',
                        ];
                    }

                    // Generate nomor surat saat approve (kalau belum ada)
                    if (empty($risalah->nomor_risalah)) {
                        $maxAttempts = 10;
                        $attempt = 0;
                        $nomorRisalah = null;
                        $counterNomorSurat = null;

                        while ($attempt < $maxAttempts) {
                            try {
                                $bulanRomawi = CounterNomorSurat::getBulanRomawi(now()->month);
                                $tahun = now()->year;
                                $kodeBagian = $risalah->kode_bagian;

                                $lastSeriTahun = CounterNomorSurat::getLastSeriTahun(
                                    $tahun,
                                    'RIS',
                                    $kodeBagian
                                );

                                $nextSeriTahun = $lastSeriTahun + 1;
                                $seriTahunanPadded = str_pad($nextSeriTahun, 2, '0', STR_PAD_LEFT);

                                $nomorRisalah = sprintf(
                                    "RIS-%s/REKA/%s/%s/%d",
                                    $seriTahunanPadded,
                                    strtoupper($kodeBagian),
                                    $bulanRomawi,
                                    $tahun
                                );

                                $counterNomorSurat = CounterNomorSurat::create([
                                    'tanggal_permintaan' => now(),
                                    'seri_tahun' => $seriTahunanPadded,
                                    'seri_bulan' => '00', // risalah tidak pakai seri bulan
                                    'perusahaan' => 'REKA',
                                    'kode_tipe_surat' => 'RIS',
                                    'divisi' => $kodeBagian,
                                    'bulan' => $bulanRomawi,
                                    'tahun' => $tahun,
                                    'pic_peminta' => $user->fullname,
                                    'jenis' => 'Risalah',
                                    'perihal' => $risalah->judul,
                                    'nomor_surat_generated' => $nomorRisalah,
                                    'is_used' => true,
                                ]);

                                $risalah->nomor_risalah = $nomorRisalah;
                                $risalah->seri_surat = $nextSeriTahun;

                                break; // sukses
                            } catch (\Illuminate\Database\QueryException $e) {
                                // Duplicate key
                                if ((string) $e->getCode() === '23000') {
                                    if ($counterNomorSurat) {
                                        $counterNomorSurat->delete();
                                    }
                                    $attempt++;
                                    usleep(100000);
                                    continue;
                                }
                                throw $e;
                            }
                        }

                        if (!$nomorRisalah) {
                            throw new \Exception('Gagal generate nomor risalah. Silakan coba lagi.');
                        }
                    }

                    // Set approve + tanggal disahkan
                    $risalah->status = 'approve';
                    $risalah->tgl_disahkan = now();

                    $qrService = new QrCodeService();

                    // QR pemimpin acara (user yang approve)
                    $qrTextPemimpin = "Pemimpin Acara: " . $user->firstname . ' ' . $user->lastname
                        . "\nNomor Risalah: " . ($risalah->nomor_risalah ?? '-')
                        . "\nTanggal Pengesahan: " . $risalah->tgl_disahkan->translatedFormat('l, d F Y H:i:s')
                        . "\nDikeluarkan oleh website SIPO PT Rekaindo Global Jasa";

                    $risalah->qr_pemimpin_acara = $qrService->generateWithLogo($qrTextPemimpin);

                    // QR notulis (update supaya nomor risalahnya masuk)
                    $qrTextNotulis = "Notulis Acara: " . ($risalah->nama_notulis_acara ?? '-')
                        . "\nNomor Risalah: " . ($risalah->nomor_risalah ?? '-')
                        . "\nTanggal: " . now()->translatedFormat('l, d F Y H:i:s')
                        . "\nDikeluarkan oleh website SIPO PT Rekaindo Global Jasa";

                    $risalah->qr_notulis_acara = $qrService->generateWithLogo($qrTextNotulis);

                    // Kirim ke semua tujuan
                    $tujuanArray = array_filter(array_map('trim', explode(';', (string) $risalah->tujuan)));

                    foreach ($tujuanArray as $tujuanId) {
                        $targetUser = User::find($tujuanId);
                        if (!$targetUser) continue;

                        // Hindari duplikasi
                        Kirim_Document::firstOrCreate([
                            'id_document' => $risalah->id_risalah,
                            'jenis_document' => 'risalah',
                            'id_pengirim' => $currentKirim?->id_pengirim ?? $risalah->pembuat,
                            'id_penerima' => $targetUser->id,
                        ], [
                            'status' => 'approve',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        Notifikasi::create([
                            'id_document'    => $risalah->id_risalah,
                            'jenis_document' => 'risalah',
                            'judul' => 'Risalah Masuk',
                            'judul_document' => $risalah->judul,
                            'id_user' => $targetUser->id,
                            'updated_at' => now(),
                        ]);

                        $push->sendToUser(
                            $targetUser->id,
                            'Risalah Masuk',
                            $risalah->judul
                        );
                    }

                    // Notifikasi ke pembuat
                    Notifikasi::create([
                        'id_document'    => $risalah->id_risalah,
                        'jenis_document' => 'risalah',
                        'judul' => 'Risalah Disetujui',
                        'judul_document' => $risalah->judul,
                        'id_user' => $risalah->pembuat,
                        'updated_at' => now(),
                    ]);

                    $push->sendToUser(
                        $risalah->pembuat,
                        'Risalah Disetujui',
                        $risalah->judul
                    );

                    // Update status kirim untuk user approver (kalau record-nya ada)
                    Kirim_Document::where('id_document', $risalah->id_risalah)
                        ->where('jenis_document', 'risalah')
                        ->where('id_penerima', $user->id)
                        ->update(['status' => 'approve', 'updated_at' => now()]);
                }

                // =========================
                // REJECT / CORRECTION
                // =========================
                if ($request->status === 'reject') {
                    $risalah->status = 'reject';
                    $risalah->tgl_disahkan = now();

                    Notifikasi::create([
                        'id_document'    => $risalah->id_risalah,
                        'jenis_document' => 'risalah',
                        'judul' => 'Risalah Ditolak',
                        'judul_document' => $risalah->judul,
                        'id_user' => $risalah->pembuat,
                        'updated_at' => now(),
                    ]);

                    $push->sendToUser(
                        $risalah->pembuat,
                        'Risalah Ditolak',
                        $risalah->judul
                    );
                }

                if ($request->status === 'correction') {
                    $risalah->status = 'correction';
                    $risalah->tgl_disahkan = now();

                    Notifikasi::create([
                        'id_document'    => $risalah->id_risalah,
                        'jenis_document' => 'risalah',
                        'judul' => 'Risalah Perlu Revisi',
                        'judul_document' => $risalah->judul,
                        'id_user' => $risalah->pembuat,
                        'updated_at' => now(),
                    ]);

                    $push->sendToUser(
                        $risalah->pembuat,
                        'Risalah Perlu Revisi',
                        $risalah->judul
                    );
                }

                // Simpan final
                $risalah->save();

                return [
                    'status' => 'success',
                    'message' => 'Status dokumen berhasil diperbarui',
                ];
            });

            return response()->json($result, 200);

        } catch (\Throwable $e) {
            Log::error('RisalahApiController@updateStatus error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan : ' . $e->getMessage()
            ], 500);
        }
    }

    //current standard response format
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
