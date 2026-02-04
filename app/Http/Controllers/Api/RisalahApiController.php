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
use App\Models\Kirim_Document;
use App\Services\QrCodeService;

class RisalahApiController extends Controller
{


    public function index(Request $request)
    {
        $user = Auth::user();

        $risalahDiarsipkan = Arsip::where('user_id', $user->id)
            ->where('jenis_document', 'App\Models\Risalah')
            ->pluck('document_id')->toArray();

        $ownedDocs = Kirim_Document::where('jenis_document', 'risalah')
            ->where(function ($q) use ($user) {
                $q->where('id_penerima', $user->id)
                    ->orWhere('id_pengirim', $user->id);
            })
            ->pluck('id_document')->unique()->toArray();

        // eager load user
        $query = Risalah::with('user')
            //->where('nama_pemimpin_acara', $user->fullname)
            ->whereNotIn('id_risalah', $risalahDiarsipkan)
            ->whereIn('id_risalah', $ownedDocs)
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('kode') && $request->kode !== 'pilih') {
            $query->where('kode', $request->kode);
        }
        if ($request->filled('approval')) {
            $query->where('status', 'pending');
            $query->where('nama_pemimpin_acara', $user->fullname);
        }

        $risalahs = $query->get();

        return RisalahResource::collection($risalahs)->additional([
            'status' => 'success',
            'message' => $risalahs->isEmpty() ? 'Belum ada risalah' : 'Daftar risalah ditemukan',
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
            $risalah = Risalah::findOrFail($id);
            $user = Auth::user();

            if ($request->status === 'approve') {
                $request->validate([
                    'status' => 'required|in:approve,reject,correction',
                ]);
            } else {
                $request->validate([
                    'status' => 'required|in:approve,reject,correction',
                    'catatan' => 'required|string',
                ]);
            }

            switch ($request->status) {
                case 'approve':
                    $risalah->status = 'approve';

                    $risalah->tgl_disahkan = now();

                    $undangan = Undangan::where('judul', $risalah->judul)->first();

                    $qrText = 'Pemimpin Acara: ' . Auth::user()->firstname . ' ' . Auth::user()->lastname
                        . "\nNomor Risalah: " . ($risalah->nomor_risalah ?? '-')
                        . "\nTanggal: " . $risalah->tgl_disahkan->translatedFormat('l, d F Y H:i:s')
                        . "\nDikeluarkan oleh Website SIPO PT Rekaindo Global Jasa";
                    $qrService = new QrCodeService;

                    $qrBase64 = $qrService->generateWithLogo($qrText);

                    $risalah->qr_pemimpin_acara = $qrBase64;
                    $tujuanArray = explode(';', $risalah->tujuan);
                    foreach ($tujuanArray as $tujuanId) {
                        Kirim_Document::create([
                            'id_document' => $risalah->id_risalah,
                            'jenis_document' => 'risalah',
                            'id_pengirim' => $risalah->pembuat,
                            'id_penerima' => $tujuanId,
                            'status' => 'approve',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        Notifikasi::create([
                            'judul' => 'Risalah Masuk',
                            'judul_document' => $risalah->judul,
                            'id_user' => $tujuanId,
                            'updated_at' => now(),
                        ]);
                        $push->sendToUser(
                            $tujuanId,
                            'Risalah Masuk',
                            $risalah->judul
                        );
                    }

                    Notifikasi::create([
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
                    break;
                case 'reject':
                    $risalah->status = 'reject';
                    $risalah->tgl_disahkan = now();
                    Notifikasi::create([
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
                    break;
                case 'correction':
                    $risalah->status = 'correction';
                    Notifikasi::create([
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
                    break;
            }
            Kirim_Document::where('id_document', $risalah->id_risalah)
                ->where('jenis_document', 'risalah')
                ->where('id_penerima', $user->id)
                ->update(['status' => $request->status ?? null]);


            $risalah->catatan = $request->catatan ?? null;
            $risalah->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Status dokumen berhasil diperbarui'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan : ' . $e->getMessage()
            ], 500);
        }
    }
}
