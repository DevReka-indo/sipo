<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Perusahaan;
use Illuminate\Support\Facades\Storage;

class PerusahaanController extends Controller
{
    // Tampilkan data perusahaan
    public function index()
    {
        $perusahaan = Perusahaan::first(); // Ambil data pertama
        return view('data-perusahaan', compact('perusahaan'));
    }

    // Simpan atau update data perusahaan
    public function update(Request $request)
    {
        try {
            $perusahaan = Perusahaan::firstOrNew([]);

            $request->validate([
                'nama_instansi' => 'required',
                'alamat_web' => 'required',
                'telepon' => 'required',
                'email' => 'required|email',
                'alamat' => 'required',
                'logo' => 'nullable|image|max:2048',
            ]);

            if ($request->hasFile('logo')) {
                $logoFile = $request->file('logo');
                $logoContent = file_get_contents($logoFile->getRealPath());
                $perusahaan->logo = base64_encode($logoContent);
            }

            $perusahaan->fill([
                'nama_instansi' => $request->nama_instansi,
                'alamat_web' => $request->alamat_web,
                'telepon' => $request->telepon,
                'email' => $request->email,
                'alamat' => $request->alamat,
            ])->save();

            return redirect()->route('data-perusahaan')->with('success', 'Data perusahaan berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->route('data-perusahaan')->with('error', 'Terjadi kesalahan saat memperbarui data perusahaan: ' . $e->getMessage());
        }
    }
}
