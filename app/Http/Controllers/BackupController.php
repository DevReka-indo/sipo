<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Memo;
use App\Models\Undangan;
use App\Models\Risalah;
use App\Models\Divisi;
use App\Models\Backup_Document;
use App\Models\kategori_barang;
use App\Models\Kirim_Document;
use Illuminate\Support\Facades\Auth;


class BackupController extends Controller
{
    public function memo(Request $request)
    {
        $userId = Auth::id();
        $kode = Memo::withTrashed()
            ->whereNotNull('kode')
            ->pluck('kode')
            ->filter()
            ->unique()
            ->values();


        $query = Memo::onlyTrashed();
        // Filter berdasarkan status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan tanggal dibuat
        if ($request->filled('tgl_dibuat_awal') && $request->filled('tgl_dibuat_akhir')) {
            $query->whereBetween('tgl_dibuat', [$request->tgl_dibuat_awal, $request->tgl_dibuat_akhir]);
        } elseif ($request->filled('tgl_dibuat_awal')) {
            $query->whereDate('tgl_dibuat', '>=', $request->tgl_dibuat_awal);
        } elseif ($request->filled('tgl_dibuat_akhir')) {
            $query->whereDate('tgl_dibuat', '<=', $request->tgl_dibuat_akhir);
        }

        // Urutan data
        $sortDirection = $request->get('sort_direction', 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy('created_at', $sortDirection);

        // Pencarian berdasarkan judul atau nomor
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('judul', 'like', '%' . $request->search . '%')
                    ->orWhere('nomor_memo', 'like', '%' . $request->search . '%');
            });
        }

        // Filter berdasarkan divisi
        if ($request->filled('kode') && $request->kode != 'pilih') {
            $query->where('kode', $request->kode);
        }

        // Ambil hasil paginate
        $perPage = $request->get('per_page', 10);
        $memos = $query->paginate($perPage);
        return view('superadmin.backup.memo', compact('memos', 'sortDirection', 'kode'));
    }



    public function undangan(Request $request)
    {
        $userId = Auth::id();

        $kode = Undangan::withTrashed()
            ->whereNotNull('kode')
            ->pluck('kode')
            ->filter()
            ->unique()
            ->values();

        $query = Undangan::onlyTrashed();
        // Filter berdasarkan status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan tanggal dibuat
        if ($request->filled('tgl_dibuat_awal') && $request->filled('tgl_dibuat_akhir')) {
            $query->whereBetween('tgl_dibuat', [$request->tgl_dibuat_awal, $request->tgl_dibuat_akhir]);
        } elseif ($request->filled('tgl_dibuat_awal')) {
            $query->whereDate('tgl_dibuat', '>=', $request->tgl_dibuat_awal);
        } elseif ($request->filled('tgl_dibuat_akhir')) {
            $query->whereDate('tgl_dibuat', '<=', $request->tgl_dibuat_akhir);
        }

        // Urutan data
        $sortDirection = $request->get('sort_direction', 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy('created_at', $sortDirection);

        // Pencarian berdasarkan judul atau nomor
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('judul', 'like', '%' . $request->search . '%')
                    ->orWhere('nomor_undangan', 'like', '%' . $request->search . '%');
            });
        }

        // Filter berdasarkan divisi
        if ($request->filled('kode') && $request->kode != 'pilih') {
            $query->where('kode', $request->kode);
        }
        // Ambil hasil paginate
        $perPage = $request->get('per_page', 10); // Default ke 10 jika tidak ada input
        $undangans = $query->paginate($perPage);

        return view('superadmin.backup.undangan', compact('undangans', 'sortDirection', 'kode'));
    }


    public function RestoreMemo($id)
    {
        $memo = Memo::withTrashed()
            ->where('id_memo', $id)
            ->first();
        $kirim_documents = Kirim_Document::withTrashed()->where('id_document', $id)->where('jenis_document', 'memo')->get();
        if ($memo) {
            $memo->restore();
            foreach ($kirim_documents as $kirim_memo) {
                $kirim_memo->restore();
            }
        } else {
            return redirect()->route('memo.backup')->with('failure', 'Memo tidak ditemukan.');
        }
        return redirect()->route('memo.backup')->with('success', 'Memo terpilih berhasil dipulihkan.');
    }
    public function bulkRestoreMemo(Request $request)
    {

        $ids = $request->input('selected_ids', []);
        Memo::onlyTrashed()->whereIn('id_memo', $ids)->restore();
        Kirim_Document::onlyTrashed()->whereIn('id_document', $ids)->where('jenis_document', 'memo')->restore();
        return response()->json(['success' => true, 'message' => 'Memo berhasil dipulihkan.']);
    }
    public function forceDeleteMemo($id)
    {

        $memo = Memo::onlyTrashed()->findOrFail($id);
        $kirim_document = Kirim_Document::onlyTrashed()->where('id_document', $id)->where('jenis_document', 'memo')->get();
        $barang = kategori_barang::where('memo_id_memo', $id)->get();
        if ($memo) {
            $memo->forceDelete();
            foreach ($kirim_document as $kirim_memo) {
                $kirim_memo->forceDelete();
            }

            foreach ($barang as $b) {
                $b->delete();
            }
        } else {
            return response()->json(['message' => 'Memo tidak ditemukan.'], 404);
        }
        return response()->json(['success' => true, 'message' => 'Memo berhasil dihapus permanen.']);
    }
    public function bulkForceDeleteMemo(Request $request)
    {
        $ids = $request->input('selected_ids', []);

        Memo::onlyTrashed()->whereIn('id_memo', $ids)->forceDelete();
        Kirim_Document::onlyTrashed()->whereIn('id_document', $ids)->where('jenis_document', 'memo')->forceDelete();
        kategori_barang::whereIn('memo_id_memo', $ids)->delete();
        return response()->json(['success' => true, 'message' => 'Memo terpilih berhasil dihapus permanen.']);
    }


    public function RestoreUndangan($id)
    {
        $undangan = Undangan::withTrashed()
            ->where('id_undangan', $id)
            ->first();

        $kirim_documents = Kirim_Document::withTrashed()
            ->where('id_document', $id)
            ->where('jenis_document', 'undangan')
            ->get();

        if ($undangan) {
            $undangan->restore();
            foreach ($kirim_documents as $kirim_undangan) {
                $kirim_undangan->restore();
            }
        } else {
            dd($undangan);
        }

        return redirect()->route('undangan.backup')->with('success', 'Pemulihan Undangan Berhasil.');
    }

    public function forceDelete($id)
    {
        $undangan = Undangan::withTrashed()->findOrFail($id);
        $kirim_documents = Kirim_Document::withTrashed()
            ->where('id_document', $id)
            ->where('jenis_document', 'undangan')
            ->get();

        if ($undangan) {
            $undangan->forceDelete();
            foreach ($kirim_documents as $kirim_undangan) {
                $kirim_undangan->forceDelete();
            }
        } else {
            return redirect()->route('undangan.backup')->with('failure', 'Undangan tidak ditemukan.');
        }

        return redirect()->route('undangan.backup')->with('success', 'Undangan berhasil dihapus permanen.');
    }

    public function bulkRestore(Request $request)
    {
        try {
            $ids = $request->input('selected_ids', []);

            if (empty($ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada undangan yang dipilih'
                ], 400);
            }

            // Restore undangan
            $restoredUndangan = Undangan::onlyTrashed()->whereIn('id_undangan', $ids)->restore();

            // Restore related kirim documents
            $restoredKirimDocs = Kirim_Document::onlyTrashed()
                ->whereIn('id_document', $ids)
                ->where('jenis_document', 'undangan')
                ->restore();

            return response()->json([
                'success' => true,
                'message' => 'Undangan berhasil dipulihkan',
                'data' => [
                    'restored_undangan' => $restoredUndangan,
                    'restored_kirim_docs' => $restoredKirimDocs
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memulihkan undangan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bulkForceDelete(Request $request)
    {
        try {
            $ids = $request->input('selected_ids', []);

            if (empty($ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada undangan yang dipilih'
                ], 400);
            }

            // Force delete undangan
            $deletedUndangan = Undangan::onlyTrashed()->whereIn('id_undangan', $ids)->forceDelete();

            // Force delete related kirim documents
            $deletedKirimDocs = Kirim_Document::onlyTrashed()
                ->whereIn('id_document', $ids)
                ->where('jenis_document', 'undangan')
                ->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'Undangan berhasil dihapus permanen',
                'data' => [
                    'deleted_undangan' => $deletedUndangan,
                    'deleted_kirim_docs' => $deletedKirimDocs
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus undangan: ' . $e->getMessage()
            ], 500);
        }
    }
    public function bulkRestoreRisalah(Request $request)
    {
        try {
            $ids = $request->input('selected_ids', []);

            if (empty($ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada risalah yang dipilih'
                ], 400);
            }

            // Restore risalah
            $restoredRisalah = Risalah::onlyTrashed()->whereIn('id_risalah', $ids)->restore();

            // Restore related kirim documents
            $restoredKirimDocs = Kirim_Document::onlyTrashed()
                ->whereIn('id_document', $ids)
                ->where('jenis_document', 'risalah')
                ->restore();

            return response()->json([
                'success' => true,
                'message' => 'Risalah berhasil dipulihkan',
                'data' => [
                    'restored_risalah' => $restoredRisalah,
                    'restored_kirim_docs' => $restoredKirimDocs
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memulihkan risalah: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bulkForceDeleteRisalah(Request $request)
    {
        try {
            $ids = $request->input('selected_ids', []);

            if (empty($ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada risalah yang dipilih'
                ], 400);
            }

            // Cek apakah risalah yang akan dihapus ada
            $risalah = Risalah::onlyTrashed()->whereIn('id_risalah', $ids)->get();

            if ($risalah->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Risalah tidak ditemukan atau sudah dihapus permanen'
                ], 404);
            }

            // Force delete risalah
            $deletedRisalah = Risalah::onlyTrashed()->whereIn('id_risalah', $ids)->forceDelete();

            // Force delete related kirim documents
            $deletedKirimDocs = Kirim_Document::onlyTrashed()
                ->whereIn('id_document', $ids)
                ->where('jenis_document', 'risalah')
                ->forceDelete();

            return response()->json([
                'success' => true,
                'message' => count($ids) . ' risalah berhasil dihapus permanen',
                'data' => [
                    'deleted_risalah' => $deletedRisalah,
                    'deleted_kirim_docs' => $deletedKirimDocs
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function forceDeleteRisalah($id, Request $request)
    {
        // Pastikan model ditemukan
        $risalah = Risalah::find($id);

        if (!$risalah) {
            return response()->json(['success' => false, 'message' => 'Dokumen tidak ditemukan']);
        }

        // Hapus risalah
        $risalah->delete();

        // Hapus juga relasi di kirim_document
        Kirim_Document::where('id_document', $id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Dokumen berhasil dihapus.'
        ]);
    }
}
