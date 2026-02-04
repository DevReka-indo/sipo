<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Divisi;
use App\Models\Risalah;
use App\Models\BackupRisalah;
use App\Models\Kirim_Document;

class BackupRisalahController extends Controller
{
    public function risalah(Request $request)
    {
        $divisi = Divisi::all();
        $risalahs = Risalah::onlyTrashed();
        $kode = Risalah::withTrashed()
            ->whereNotNull('kode')
            ->pluck('kode')
            ->filter()
            ->unique()
            ->values();

        // Filter berdasarkan status
        if ($request->filled('status')) {
            $risalahs->where('status', $request->status);
        }

        // Filter berdasarkan tanggal dibuat
        if ($request->filled('tgl_dibuat_awal') && $request->filled('tgl_dibuat_akhir')) {
            $risalahs->whereBetween('tgl_dibuat', [$request->tgl_dibuat_awal, $request->tgl_dibuat_akhir]);
        } elseif ($request->filled('tgl_dibuat_awal')) {
            $risalahs->whereDate('tgl_dibuat', '>=', $request->tgl_dibuat_awal);
        } elseif ($request->filled('tgl_dibuat_akhir')) {
            $risalahs->whereDate('tgl_dibuat', '<=', $request->tgl_dibuat_akhir);
        }

        $sortDirection = $request->get('sort_direction', 'desc') === 'asc' ? 'asc' : 'desc';
        $risalahs->orderBy('created_at', $sortDirection);

        // Pencarian berdasarkan nama dokumen atau nomor memo
        if ($request->filled('search')) {
            $risalahs->where(function ($q) use ($request) {
                $q->where('judul', 'like', '%' . $request->search . '%')
                    ->orWhere('nomor_risalah', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->filled('kode') && $request->kode != 'pilih') {
            $risalahs->where('kode', $request->kode);
        }

        // Ambil hasil paginate
        $risalahs = $risalahs->paginate(6);

        return view('superadmin.backup.risalah', compact('risalahs', 'divisi', 'sortDirection', 'kode'));
    }

    public function RestoreRisalah($id)
    {
        dd($id);
        Risalah::withTrashed()->find($id)->restore();
        Kirim_Document::withTrashed()->where('id_document', $id)->restore();

        return response()->json(['success' => true]);
    }
}
