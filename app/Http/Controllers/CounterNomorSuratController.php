<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CounterNomorSurat;

class CounterNomorSuratController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // 1️⃣ CEK KODE BAGIAN USER
        if (empty($user->kode_bagian)) {
            return view('counter-nomor-surat.index', [
                'authorized' => false,
                'message' => 'Hubungi tim IT untuk dapat mengakses halaman ini',
                'data' => collect(),
                'kodeBagianList' => [],
                'tahunList' => [],
                'kodeTipeSuratList' => [],
            ]);
        }

        // 2️⃣ PARSE KODE BAGIAN (pisah berdasarkan titik koma)
        $kodeBagianArray = array_map('trim', explode(';', $user->kode_bagian));

        // 3️⃣ AMBIL FILTER DARI REQUEST
        $filterKode = $request->input('kode_bagian');
        $filterTahun = $request->input('tahun');
        $filterTipeSurat = $request->input('kode_tipe_surat'); // ← FILTER BARU

        // 4️⃣ CEK ROLE: SUPERADMIN LIHAT SEMUA
        if (in_array($user->role->nm_role, ['superadmin'])) {
            $query = CounterNomorSurat::query();

            // Filter tahun untuk superadmin
            if ($filterTahun) {
                $query->where('tahun', $filterTahun);
            }

            // Filter kode tipe surat untuk superadmin
            if ($filterTipeSurat) {
                $query->where('kode_tipe_surat', $filterTipeSurat);
            }

            // $data = $query->orderBy('created_at', 'desc')->get();
            $data = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

            // Ambil daftar tahun untuk dropdown
            $tahunList = CounterNomorSurat::selectRaw('DISTINCT tahun')->orderBy('tahun', 'desc')->pluck('tahun');

            // Ambil daftar kode tipe surat untuk dropdown
            $kodeTipeSuratList = CounterNomorSurat::selectRaw('DISTINCT kode_tipe_surat')->whereNotNull('kode_tipe_surat')->orderBy('kode_tipe_surat', 'asc')->pluck('kode_tipe_surat');

            return view('counter-nomor-surat.index', [
                'authorized' => true,
                'message' => null,
                'data' => $data,
                'kodeBagian' => 'SEMUA',
                'kodeBagianList' => [],
                'tahunList' => $tahunList,
                'kodeTipeSuratList' => $kodeTipeSuratList,
                'filterKode' => $filterKode,
                'filterTahun' => $filterTahun,
                'filterTipeSurat' => $filterTipeSurat,
            ]);
        }

        // 5️⃣ QUERY UNTUK USER BIASA (FILTER BERDASARKAN KODE BAGIAN)
        $query = CounterNomorSurat::query()->whereIn('divisi', $kodeBagianArray);

        // Filter kode bagian spesifik (jika dipilih)
        if ($filterKode && in_array($filterKode, $kodeBagianArray)) {
            $query->where('divisi', $filterKode);
        }

        // Filter tahun
        if ($filterTahun) {
            $query->where('tahun', $filterTahun);
        }

        // Filter kode tipe surat
        if ($filterTipeSurat) {
            $query->where('kode_tipe_surat', $filterTipeSurat);
        }

        // $data = $query->orderBy('created_at', 'desc')->get();
        $data = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        // 6️⃣ AMBIL DAFTAR TAHUN UNTUK DROPDOWN (SESUAI KODE BAGIAN USER)
        $tahunList = CounterNomorSurat::query()->whereIn('divisi', $kodeBagianArray)->selectRaw('DISTINCT tahun')->orderBy('tahun', 'desc')->pluck('tahun');

        // 7️⃣ AMBIL DAFTAR KODE TIPE SURAT UNTUK DROPDOWN (SESUAI KODE BAGIAN USER)
        $kodeTipeSuratList = CounterNomorSurat::query()->whereIn('divisi', $kodeBagianArray)->selectRaw('DISTINCT kode_tipe_surat')->whereNotNull('kode_tipe_surat')->orderBy('kode_tipe_surat', 'asc')->pluck('kode_tipe_surat');

        return view('counter-nomor-surat.index', [
            'authorized' => true,
            'message' => null,
            'data' => $data,
            'kodeBagian' => implode(', ', $kodeBagianArray),
            'kodeBagianList' => $kodeBagianArray,
            'tahunList' => $tahunList,
            'kodeTipeSuratList' => $kodeTipeSuratList,
            'filterKode' => $filterKode,
            'filterTahun' => $filterTahun,
            'filterTipeSurat' => $filterTipeSurat,
        ]);
    }
}
