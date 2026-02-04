<?php

namespace App\Http\Controllers;

use App\Models\Memo;
use App\Models\Risalah;
use App\Models\Undangan;
use App\Models\Kirim_Document;
use App\Models\Arsip;
use App\Models\Divisi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Menghitung jumlah memo yang sudah dibuat

        $kirimDocuments = Kirim_Document::where('id_penerima', Auth::user()->id)->get();

        // $jumlahMemo = Kirim_Document::where('jenis_document', 'memo')
        //     ->where(function ($query) {
        //         $query->where('id_pengirim', Auth::user()->id)
        //             ->orWhere('id_penerima', Auth::user()->id);
        //     })
        //     ->select('id_document')
        //     ->groupBy('id_document')
        //     ->get()
        //     ->count();

        $memoDiarsipkan = Arsip::where('user_id', Auth::id())
            ->where('jenis_document', Memo::class)
            ->pluck('document_id');

        $risalahDiarsipkan = Arsip::where('user_id', Auth::id())
            ->where('jenis_document', Risalah::class)
            ->pluck('document_id');

        $undanganDiarsipkan = Arsip::where('user_id', Auth::id())
            ->where('jenis_document', Undangan::class)
            ->pluck('document_id');

        $jumlahMemo = Kirim_Document::where('jenis_document', 'memo')
            ->when(Auth::user()->role->nm_role !== 'superadmin', function ($query){
                $query->where(function ($q) {
                $q->where('id_pengirim', Auth::id())
                ->orWhere('id_penerima', Auth::id());
                });
            })
            ->whereNotIn('id_document', $memoDiarsipkan)
            ->distinct('id_document')
            ->count();


        $jumlahRisalah = Kirim_Document::where('jenis_document', 'risalah')
            ->when(Auth::user()->role->nm_role !== 'superadmin', function ($query){
                $query->where(function ($q) {
                $q->where('id_pengirim', Auth::id())
                ->orWhere('id_penerima', Auth::id());
                });
            })
            ->whereNotIn('id_document', $risalahDiarsipkan)
            ->distinct('id_document')
            ->count();


        $jumlahUndangan = Kirim_Document::where('jenis_document', 'undangan')
            ->when(Auth::user()->role->nm_role !== 'superadmin', function ($query){
                $query->where(function ($q) {
                $q->where('id_pengirim', Auth::id())
                ->orWhere('id_penerima', Auth::id());
                });
            })
            ->whereNotIn('id_document', $undanganDiarsipkan)
            ->distinct('id_document')
            ->count();

        $Memo = Memo::all()->count();
        $Undangan = Undangan::all()->count();
        $Risalah = Risalah::all()->count();

        // Jumlah total semua
        $Memo = Memo::all()->count();
        $Undangan = Undangan::all()->count();
        $Risalah = Risalah::all()->count();

        // === ⬇ Tambahkan bagian notifikasi di sini ⬇ ===
        $notifikasi = DB::table('notifikasi')
            ->where('id_user', Auth::id())
            ->orderBy('id_notifikasi', 'desc')
            ->limit(5)
            ->get();

        $notifikasiByDate = $notifikasi->groupBy(function ($item) {
            return Carbon::parse($item->updated_at)->translatedFormat('l, d F');
        });
        // === ⬆ END Notifikasi Section ⬆ ===

        return view(Auth::user()->role->nm_role . '.dashboard', compact(
            'jumlahMemo',
            'jumlahRisalah',
            'jumlahUndangan',
            'Memo',
            'Undangan',
            'Risalah',
            'notifikasiByDate'
        ));
    }
}
