<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Arsip, Kirim_Document, Undangan, Memo, Risalah, Notifikasi};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use PhpParser\Node\Expr\AssignOp\Concat;

class DashboardApiController extends Controller
{

    public function index()
    {
        $user = Auth::user();
        $archivedMemo = Arsip::where('user_id', $user->id)
            ->where('jenis_document', 'App\Models\Memo')
            ->pluck('document_id')->toArray();

        $memoCount = Kirim_Document::where('jenis_document', 'memo')
            ->where(function ($query) use ($user) {
                $query->where('id_pengirim', $user->id)
                    ->orWhere('id_penerima', $user->id);
            })
            ->whereNotIn('id_document', $archivedMemo)
            ->select('id_document')
            ->groupBy('id_document')
            ->get()
            ->count();

        $archivedUndangan = Arsip::where('user_id', $user->id)
            ->where('jenis_document', 'App\Models\Undangan')
            ->pluck('document_id')->toArray();

        $undanganCount = Kirim_Document::where('jenis_document', 'undangan')
            ->where(function ($query) use ($user) {
                $query->where('id_pengirim', $user->id)
                    ->orWhere('id_penerima', $user->id);
            })
            ->whereNotIn('id_document', $archivedUndangan)
            ->select('id_document')
            ->groupBy('id_document')
            ->get()
            ->count();

        $archivedRisalah = Arsip::where('user_id', $user->id)
            ->where('jenis_document', 'App\Models\Risalah')
            ->pluck('document_id')->toArray();

        $risalahCount = Kirim_Document::where('jenis_document', 'risalah')
            ->where(function ($query) use ($user) {
                $query->where('id_pengirim', $user->id)
                    ->orWhere('id_penerima', $user->id);
            })
            ->whereNotIn('id_document', $archivedRisalah)
            ->select('id_document')
            ->groupBy('id_document')
            ->get()
            ->count();

        $now = Carbon::now();

        $ownedUndangan = Kirim_Document::where('id_penerima', $user->id)
            ->orWhere('id_pengirim', $user->id)
            ->where('jenis_document', 'undangan')
            ->pluck('id_document');

        $undangan = Undangan::whereIn('id_undangan', $ownedUndangan)
            ->where('status', 'approve')
            ->whereDate('tgl_rapat', '>=', $now)
            ->selectRaw('*, DATEDIFF(tgl_rapat, ?) as selisih_hari', [$now])
            ->orderByRaw('selisih_hari')
            ->limit(5)
            ->get();

        foreach ($undangan as $u) {
            $u->waktu = $u->waktu_mulai . ' - ' . $u->waktu_selesai;
        }


        $recentDocs = Kirim_Document::where('id_penerima', $user->id)
            ->limit(10)
            ->orderBy('id_kirim_document', 'desc')
            ->where('status', 'pending')
            ->get();

        foreach ($recentDocs as $d) {
            switch ($d->jenis_document) {
                case 'memo':
                    $doc = Memo::find($d->id_document);
                    $d->id = $doc ? $doc->id_memo : null;
                    $d->judul = $doc ? $doc->judul : 'Dokumen tidak ditemukan';
                    $d->tgl_dokumen = $doc ? ($doc->updated_at ?? $doc->tgl_dibuat) : null;
                    $d->tipe = 'memo';
                    break;
                case 'undangan':
                    $doc = Undangan::find($d->id_document);
                    $d->id = $doc ? $doc->id_undangan : null;
                    $d->judul = $doc ? $doc->judul : 'Dokumen tidak ditemukan';
                    $d->tgl_dokumen = $doc ? ($doc->tgl_rapat ?? $doc->tgl_dibuat) : null;
                    $d->tipe = 'undangan';
                    break;
                case 'risalah':
                    $doc = Risalah::find($d->id_document);
                    $d->id = $doc ? $doc->id_risalah : null;
                    $d->judul = $doc ? $doc->judul : 'Dokumen tidak ditemukan';
                    $d->tgl_dokumen = $doc ? ($doc->updated_at ?? $doc->tgl_dibuat) : null;
                    $d->tipe = 'risalah';
                    break;
                default:
                    $d->judul = 'Dokumen tidak ditemukan';
                    $d->tgl_dokumen = null;
                    break;
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'fullname' => $user->firstname,
                'memo_count' => $memoCount,
                'risalah_count' => $risalahCount,
                'undangan_count' => $undanganCount,
                'undangan' => $undangan,
                'recent_docs' => $recentDocs,
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
