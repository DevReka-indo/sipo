<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BagianKerja;

class KodeBagianController extends Controller
{
    /* =========================
     * INDEX
     * ========================= */
    public function index(Request $request)
    {
        $filterKategori = $request->kategori;
        $filterStatus = $request->status;

        $query = BagianKerja::query()->withTrashed();

        if ($filterKategori) {
            $query->where('kategori', $filterKategori);
        }

        if ($filterStatus === '1') {
            $query->whereNull('deleted_at')->where('is_active', true);
        }

        if ($filterStatus === '0') {
            $query->onlyTrashed();
        }

        $data = $query->orderBy('kode_bagian')->paginate(10)->withQueryString();

        return view('superadmin.kode_bagian.index', [
            'data' => $data,
            'filterKategori' => $filterKategori,
            'filterStatus' => $filterStatus,
            'kategoriList' => BagianKerja::getKategoriList(),
        ]);
    }

    /* ======================
     * CREATE
     * ====================== */
    public function create()
    {
        return view('superadmin.kode_bagian.create');
    }

    /* =========================
     * STORE (ADD)
     * ========================= */
    public function store(Request $request)
    {
        $request->validate([
            'kode_bagian' => 'required|string|max:20|unique:bagian_kerja,kode_bagian',
            'nama_bagian' => 'required|string|max:255',
            'kategori' => 'nullable|string|max:100',
        ]);

        BagianKerja::create([
            'kode_bagian' => strtoupper($request->kode_bagian),
            'nama_bagian' => $request->nama_bagian,
            'kategori' => $request->kategori,
            'is_active' => true,
        ]);

        return redirect()->route('kode-bagian.index')->with('success', 'Kode bagian kerja berhasil ditambahkan');
    }

    /* =========================
     * EDIT (FORM)
     * ========================= */
    public function edit($id)
    {
        $bagian = BagianKerja::findOrFail($id);

        return view('superadmin.kode_bagian.edit', [
            'bagian' => $bagian,
        ]);
    }

    /* =========================
     * UPDATE
     * ========================= */
    public function update(Request $request, $id)
    {
        $bagian = BagianKerja::findOrFail($id);

        $request->validate([
            'kode_bagian' => 'required|string|max:20|unique:bagian_kerja,kode_bagian,' . $bagian->id,
            'nama_bagian' => 'required|string|max:255',
            'kategori' => 'nullable|string|max:100',
            'is_active' => 'required|boolean',
        ]);

        $bagian->update([
            'kode_bagian' => strtoupper($request->kode_bagian),
            'nama_bagian' => $request->nama_bagian,
            'kategori' => $request->kategori,
            'is_active' => $request->is_active,
        ]);

        return redirect()->route('kode-bagian.index')->with('success', 'Kode bagian kerja berhasil diperbarui');
    }

    /* =========================
     * DELETE (NONAKTIF)
     * ========================= */
    public function destroy($id)
    {
        $bagian = BagianKerja::findOrFail($id);

        $bagian->update([
            'is_active' => false,
        ]);

        $bagian->delete(); // soft delete (isi deleted_at)

        return redirect()->route('kode-bagian.index')->with('success', 'Kode bagian kerja berhasil dihapus');
    }

    /* =========================
     * RESTORE (AKTIFKAN KEMBALI)
     * ========================= */
    public function restore($id)
    {
        $bagian = BagianKerja::withTrashed()->findOrFail($id);

        $bagian->restore(); // hapus deleted_at

        $bagian->update([
            'is_active' => true,
        ]);

        return redirect()->route('kode-bagian.index')->with('success', 'Kode bagian kerja berhasil dipulihkan');
    }
}
