<?php

namespace App\Http\Controllers;

use App\Models\kategori_barang;
use Illuminate\Http\Request;
use App\Models\Document;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    public function index(Request $request) {
        $status = $request->input('status');
        $documents = Document::where('status', $status)->paginate(10);

        return view('documents.index', compact('documents'));
    }

    public function create()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;
        $divisiId =  Auth::auth()->user()->divisi_id_divisi;
        $divisiName =  Auth::auth()->user()->divisi->nm_divisi;



        // Hitung nomor seri bulanan
        $nomorSeriBulanan = Document::where('bulan', $currentMonth)
            ->where('tahun', $currentYear)
            ->where('divisi_id_divisi', $divisiId)
            ->count() + 1;

        // Hitung nomor seri tahunan
        $nomorSeriTahunan = Document::where('tahun', $currentYear)
            ->where('divisi_id_divisi', $divisiId)
            ->count() + 1;

        // Konversi bulan ke angka Romawi
        $bulanRomawi = $this->convertToRoman($currentMonth);

        // Format nomor dokumen
        $nomorDokumen = sprintf(
            "%d.%d/REKA/GEN/%s/%s/%d",
            $nomorSeriBulanan,
            $nomorSeriTahunan,
            strtoupper($divisiName),
            $bulanRomawi,
            $currentYear
        );

        $managers = User::where('divisi_id_divisi', $divisiId)
        ->where('position_id_position', '2')
        ->get(['id', 'firstname', 'lastname']);

        // Kirim data ke view
        return view('superadmin.memo.add-memo', [
            'nomorSeriBulanan' => $nomorSeriBulanan,
            'nomorSeriTahunan' => $nomorSeriTahunan,
            'nomorDokumen' => $nomorDokumen,
            'managers' => $managers
        ]);


    }
    public function store(Request $request) {


        $request->validate([
            'jenis_document' => 'required|string|max:70',
            'judul' => 'required|string|max:70',
            'isi_document' => 'required|string',
            'tujuan' => 'required|string|max:255',
            'seri_bulanan' => 'required|numeric',
            'seri_tahunan' => 'required|numeric',
            'nomor_document' => 'required|string|max:255',
            'nama_pimpinan' => 'required|string|max:255',
            'tgl_dibuat' => 'required|date',
            'tgl_disahkan' => 'nullable|date',
            'divisi_id_divisi' => 'required|exists:divisi,id_divisi',
            'bulan' => 'required|numeric|min:1|max:12',
            'tahun' => 'required|numeric|min:2021',
            'tanda_identitas' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ],[
            'tanda_identitas.mimes' => 'File harus berupa PDF, JPG, atau PNG.',
            'tanda_identitas.max' => 'Ukuran file tidak boleh lebih dari 2 MB.',
        ]);




        $fileContent = null;
        if ($request->hasFile('tanda_identitas')) {
            $file = $request->file('tanda_identitas');
            $fileContent = file_get_contents($file->getRealPath()); // Membaca file sebagai binary
        }

        // Simpan dokumen
        $document = Document::create([
            'divisi_id_divisi' => $request->input('divisi_id_divisi'),
            'jenis_document' => $request->input('jenis_document'),
            'judul' => $request->input('judul'),
            'tujuan' => $request->input('tujuan'),
            'isi_document' => $request->input('isi_document'),
            'seri_bulanan' => $request->input('seri_bulanan'),
            'seri_tahunan' => $request->input('seri_tahunan'),
            'nomor_document' => $request->input('nomor_document'),
            'tgl_dibuat' => $request->input('tgl_dibuat'),
            'tgl_disahkan' => $request->input('tgl_disahkan'),
            'bulan' => $request->input('bulan'),
            'tahun' => $request->input('tahun'),
            'status' => 'pending',
            'nama_pimpinan' => $request->input('nama_pimpinan'),
            'tanda_identitas' => $fileContent,

        ]);
        if ($request->has('jumlah_kolom')) {
            for ($i = 0; $i < $request->jumlah_kolom; $i++) {
                kategori_barang::create([
                    'document_id_document' => $document->id_document,
                    'nomor' => $request->input('nomor_' . $i),
                    'barang' => $request->input('barang_' . $i),
                    'qty' => $request->input('qty_' . $i),
                    'satuan' => $request->input('satuan_' . $i),

                ]);
            }
        }

        return redirect()->route('memo.superadmin')->with('success', 'Dokumen berhasil dibuat.');
    }

    private function convertToRoman($number) {
        $map = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];
        return $map[$number];
    }
    public function updateDocumentStatus(Document $document) {
        $recipients = $document->recipients;

        if ($recipients->every(fn($recipient) => $recipient->status === 'approve')) {
            $document->update(['status' => 'approve']);
        } elseif ($recipients->contains(fn($recipient) => $recipient->status === 'reject')) {
            $document->update(['status' => 'reject']);
        } else {
            $document->update(['status' => 'pending']);
        }
    }

    public function updateDocumentApprovalDate(Document $document) {
        if ($document->status !== 'pending') {
            $document->update(['tanggal_disahkan' => now()]);
        }
    }
    public function approve(Document $document) {
        $document->update([
            'status' => 'approve',
            'tanggal_disahkan' => now() // Set tanggal disahkan
        ]);

        return redirect()->back()->with('success', 'Dokumen berhasil disetujui.');
    }
    public function reject(Document $document) {
        $document->update([
            'status' => 'reject',
            'tanggal_disahkan' => now() // Set tanggal disahkan
        ]);

        return redirect()->back()->with('error', 'Dokumen ditolak.');
    }



}
