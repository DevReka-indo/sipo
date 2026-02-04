<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\NotifApiController;
use App\Models\{Memo, Arsip,};
use App\Http\Resources\MemoResource;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\Notifikasi;
use App\Models\Kirim_Document;
use Illuminate\Support\Facades\Auth;
use App\Services\QrCodeService;

class MemoApiController extends Controller
{
    // GET /api/memos
    public function index(Request $request)
    {
        // eager load user
        $user = Auth::user();

        $memoDiarsipkan = Arsip::where('user_id', $user->id)
            ->where('jenis_document', 'App\Models\Memo')
            ->pluck('document_id')->toArray();

        $ownedDocs = Kirim_Document::where('jenis_document', 'memo')
            ->where(function ($q) use ($user) {
                $q->where('id_penerima', $user->id)
                    ->orWhere('id_pengirim', $user->id);
            })
            ->pluck('id_document')->unique()->toArray();

        // eager load user
        $query = Memo::with('user')->whereNotIn('id_memo', $memoDiarsipkan)->whereIn('id_memo', $ownedDocs)->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('kode') && $request->kode !== 'pilih') {
            $query->where('kode', $request->kode);
        }

        $memos = $query->get();

        return MemoResource::collection($memos)->additional([
            'status' => 'success',
            'message' => $memos->isEmpty() ? 'Belum ada memo' : 'Daftar memo ditemukan',
        ]);
    }
    public function kodeFilter()
    {
        $kode = Memo::whereNotNull('kode')
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
        $memos = Memo::with('user')->latest()->get();

        return MemoResource::collection($memos)->additional([
            'status' => 'success',
            'message' => $memos->isEmpty() ? 'Belum ada memo' : 'Daftar memo ditemukan',
        ]);
    }
    // GET /api/memos/{id}
    public function show($id)
    {
        // $memo = Memo::find($id);
        $memo = Memo::with('user')->findOrFail($id);

        if (!$memo) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Memo tidak ditemukan',
                ],
                404,
            );
        }

        return new MemoResource($memo);
    }

    public function updateStatus(Request $request, $id)
    {
        $push = new NotifApiController();
        try {
            $memo = Memo::findOrFail($id);
            $user = Auth::user();
            $request->validate([
                'status' => 'required|in:approve,reject,correction',
                'catatan' => $request->status !== 'approve' ? 'required|string' : 'nullable|string',
            ]);

            switch ($request->status) {
                case 'approve':
                    $memo->status = 'approve';

                    $memo->tgl_disahkan = now();
                    $qrText = 'Disetujui oleh: ' . Auth::user()->firstname . ' ' . Auth::user()->lastname
                        . "\nNomor Memo: " . ($memo->nomor_memo ?? '-')
                        . "\nTanggal: " . $memo->tgl_disahkan->translatedFormat('l, d F Y H:i:s')
                        . "\nDikeluarkan oleh Website SIPO PT Rekaindo Global Jasa";
                    $qrService = new QrCodeService;

                    $qrBase64 = $qrService->generateWithLogo($qrText);

                    $memo->qr_approved_by = $qrBase64;

                    $tujuanArray = explode(';', $memo->tujuan);
                    foreach ($tujuanArray as $tujuanId) {
                        Kirim_Document::create([
                            'id_document' => $memo->id_memo,
                            'jenis_document' => 'memo',
                            'id_pengirim' => $memo->pembuat,
                            'id_penerima' => $tujuanId,
                            'status' => 'approve',
                        ]);

                        Notifikasi::create([
                            'judul' => 'Memo Masuk',
                            'judul_document' => $memo->judul,
                            'id_user' => $tujuanId,
                        ]);

                        $push->sendToUser(
                            $tujuanId,
                            'Memo Masuk',
                            $memo->judul
                        );
                    }

                    Notifikasi::create([
                        'judul' => 'Memo Disetujui',
                        'judul_document' => $memo->judul,
                        'id_user' => $memo->pembuat,
                    ]);

                    $push->sendToUser(
                        $memo->pembuat,
                        'Memo Disetujui',
                        $memo->judul
                    );
                    break;


                case 'reject':
                    $memo->status = 'reject';
                    $memo->tgl_disahkan = now();
                    Notifikasi::create([
                        'judul' => 'Memo Ditolak',
                        'judul_document' => $memo->judul,
                        'id_user' => $memo->pembuat,
                    ]);
                    $push->sendToUser(
                        $memo->pembuat,
                        'Memo Ditolak',
                        $memo->judul
                    );

                    break;
                case 'correction':
                    $memo->status = 'correction';
                    Notifikasi::create([
                        'judul' => 'Memo Perlu Revisi',
                        'judul_document' => $memo->judul,
                        'id_user' => $memo->pembuat,
                    ]);
                    $push->sendToUser(
                        $memo->pembuat,
                        'Memo Perlu Revisi',
                        $memo->judul
                    );
                    break;
            }
            Kirim_Document::where('id_document', $memo->id_memo)
                ->where('jenis_document', 'memo')
                ->where('id_penerima', $user->id)
                ->update(['status' => $request->status ?? null]);


            $memo->catatan = $request->catatan ?? null;
            $memo->save();

            return response()->json(
                [
                    'status' => 'success',
                    'message' => 'Status dokumen berhasil diperbarui',
                ],
                200,
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }
}
