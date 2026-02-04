<?php

namespace App\Http\Controllers;

use App\Models\Memo;
use App\Models\Risalah;
use App\Models\Undangan;
use App\Models\Kirim_Document;
use App\Models\Arsip;
use App\Models\Divisi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $isSuperadmin = $user->role->nm_role === 'superadmin';

        if ($isSuperadmin) {
            return $this->superadminDashboard();
        }

        // === Untuk user biasa ===
        $userId = Auth::id();

        // Ambil ID dokumen yang diarsipkan (per user)
        $memoDiarsipkan = Arsip::where('user_id', $userId)->where('jenis_document', Memo::class)->pluck('document_id');

        $undanganDiarsipkan = Arsip::where('user_id', $userId)->where('jenis_document', Undangan::class)->pluck('document_id');

        $risalahDiarsipkan = Arsip::where('user_id', $userId)->where('jenis_document', Risalah::class)->pluck('document_id');

        // Hitung dokumen berdasarkan Kirim_Document
        $jumlahMemoKeluar = Kirim_Document::where('jenis_document', 'memo')->where('id_pengirim', $userId)->whereNotIn('id_document', $memoDiarsipkan)->distinct('id_document')->count();

        $jumlahMemoMasuk = Kirim_Document::where('jenis_document', 'memo')->where('id_penerima', $userId)->whereNotIn('id_document', $memoDiarsipkan)->distinct('id_document')->count();

        $jumlahUndanganKeluar = Kirim_Document::where('jenis_document', 'undangan')->where('id_pengirim', $userId)->whereNotIn('id_document', $undanganDiarsipkan)->distinct('id_document')->count();

        $jumlahUndanganMasuk = Kirim_Document::where('jenis_document', 'undangan')->where('id_penerima', $userId)->whereNotIn('id_document', $undanganDiarsipkan)->distinct('id_document')->count();

        $jumlahRisalah = Kirim_Document::where('jenis_document', 'risalah')
            ->where(function ($query) use ($userId) {
                $query->where('id_pengirim', $userId)->orWhere('id_penerima', $userId);
            })
            ->whereNotIn('id_document', $risalahDiarsipkan)
            ->distinct('id_document')
            ->count();

        // Notifikasi
        $notifikasi = DB::table('notifikasi')->where('id_user', $userId)->orderBy('updated_at', 'desc')->limit(10)->get();

        $notifikasiByDate = $notifikasi->groupBy(function ($item) {
            return Carbon::parse($item->updated_at)->locale('id')->translatedFormat('l, d F');
        });

        // Kirim ke view role-specific (tanpa chartData)
        return view($user->role->nm_role . '.dashboard', compact('jumlahMemoKeluar', 'jumlahMemoMasuk', 'jumlahUndanganKeluar', 'jumlahUndanganMasuk', 'jumlahRisalah', 'notifikasiByDate'));
    }

    /**
     * Dashboard khusus untuk Superadmin – menampilkan data agregat SELURUH SISTEM
     */
    private function superadminDashboard()
    {
        $userId = Auth::id();

        // Total dokumen seluruh sistem
        $jumlahMemoKeluar = Memo::count();
        $jumlahUndanganKeluar = Undangan::count();
        $jumlahRisalah = Risalah::count();

        // Variabel dummy untuk konsistensi (tidak digunakan di view superadmin)
        $jumlahMemoMasuk = 0;
        $jumlahUndanganMasuk = 0;

        // Notifikasi (milik superadmin)
        $notifikasi = DB::table('notifikasi')->where('id_user', $userId)->orderBy('updated_at', 'desc')->limit(10)->get();

        $notifikasiByDate = $notifikasi->groupBy(function ($item) {
            return Carbon::parse($item->updated_at)->locale('id')->translatedFormat('l, d F');
        });

        // Ambil data chart — pastikan aman
        $chartData = $this->getChartData();

        return view('superadmin.dashboard', compact('jumlahMemoKeluar', 'jumlahMemoMasuk', 'jumlahUndanganKeluar', 'jumlahUndanganMasuk', 'jumlahRisalah', 'notifikasiByDate', 'chartData'));
    }

    /**
     * Ambil data aktivitas dokumen per bulan (6 bulan terakhir) untuk chart
     */
    private function getChartData()
    {
        $labels = [];
        $memoData = [];
        $undanganData = [];
        $risalahData = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $year = $date->year;
            $month = $date->month;
            $label = $date->locale('id')->translatedFormat('M Y');

            $labels[] = $label;

            // Gunakan try-catch untuk proteksi ekstra (opsional)
            try {
                $memoCount = Memo::whereYear('created_at', $year)->whereMonth('created_at', $month)->count();

                $undanganCount = Undangan::whereYear('created_at', $year)->whereMonth('created_at', $month)->count();

                $risalahCount = Risalah::whereYear('created_at', $year)->whereMonth('created_at', $month)->count();
            } catch (\Exception $e) {
                // Jika error, isi dengan 0
                $memoCount = $undanganCount = $risalahCount = 0;
            }

            $memoData[] = (int) $memoCount;
            $undanganData[] = (int) $undanganCount;
            $risalahData[] = (int) $risalahCount;
        }

        return [
            'labels' => $labels,
            'memo' => $memoData,
            'undangan' => $undanganData,
            'risalah' => $risalahData,
        ];
    }

    /**
     * Get user's division/department code
     */
    private function getDivDeptKode($user)
    {
        if ($user->divisi_id) {
            $divisi = Divisi::find($user->divisi_id);
            return $divisi?->kode_divisi;
        }

        if ($user->dept_id) {
            $departemen = DB::table('departemen')->where('id_departemen', $user->dept_id)->first();
            return $departemen?->kode_departemen;
        }

        return null;
    }
}
