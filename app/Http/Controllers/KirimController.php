<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Arsip;
use App\Models\Notifikasi;
use App\Models\Memo;
use App\Models\Divisi;
use App\Models\SeriRisalah;
use App\Models\Director;
use App\Models\Department;
use App\Models\Position;
use App\Models\User;
use App\Models\Undangan;
use App\Models\Kirim_Document;
use App\Models\Risalah;
use App\Models\RisalahDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;
use App\Http\Controllers\MemoController;
use App\Http\Controllers\Api\NotifApiController;
use App\Http\Controllers\UndanganController;
use App\Services\QrCodeService;
use App\Models\BagianKerja;

class KirimController extends Controller
{
    public function index($id)
    {
        $memo = Memo::find($id);
        $undangan = Undangan::find($id);
        $risalah = Risalah::find($id);

        if (!$memo && !$undangan && !$risalah) {
            return redirect()->back()->with('error', 'Dokumen tidak ditemukan.');
        }

        $divisi = Divisi::all();
        $position = Position::all();
        $user = User::whereIn('role_id_role', ['2', '3'])->get();
        $userId = Auth::id();

        if ($memo) {
            if ($memo->divisi_id_divisi === Auth::user()->divisi_id_divisi) {
                $memo->final_status = $memo->status;
            } else {
                $statusKirim = Kirim_Document::where('id_document', $memo->id_memo)
                    ->where('jenis_document', 'memo')
                    ->where('id_penerima', $userId)
                    ->first();
                $memo->final_status = $statusKirim ? $statusKirim->status : '-';
            }
            return view('admin.memo.kirim-memoAdmin', compact('user', 'divisi', 'memo', 'position'));
        } elseif ($undangan) {
            if ($undangan->divisi_id_divisi === Auth::user()->divisi_id_divisi) {
                $undangan->final_status = $undangan->status;
            } else {
                $statusKirim = Kirim_Document::where('id_document', $undangan->id_undangan)
                    ->where('jenis_document', 'undangan')
                    ->where('id_penerima', $userId)
                    ->first();
                $undangan->final_status = $statusKirim ? $statusKirim->status : '-';
            }
            return view('admin.undangan.kirim-undanganAdmin', compact('user', 'divisi', 'undangan', 'position'));
        } elseif ($risalah) {
            if ($risalah->divisi_id_divisi === Auth::user()->divisi_id_divisi) {
                $risalah->final_status = $risalah->status;
            } else {
                $statusKirim = Kirim_Document::where('id_document', $risalah->id_risalah)
                    ->where('jenis_document', 'risalah')
                    ->where(function ($query) use ($userId) {
                        $query->where('id_penerima', $userId)
                            ->orWhere('id_pengirim', $userId);
                    })
                    ->first();

                $risalah->final_status = $statusKirim ? $statusKirim->status : '-';
            }
            return view('admin.risalah.kirim-risalahAdmin', compact('user', 'divisi', 'risalah', 'position'));
        }

        // Bisa tambahkan elseif risalah di sini jika ada
    }

    public function viewManager($id)
    {
        // Cek apakah ID ini milik Memo, Undangan, atau Risalah
        $undangan = Undangan::find($id);

        // Pastikan minimal satu dokumen ditemukan
        if (!$undangan) {
            return redirect()->back()->with('error', 'Dokumen tidak ditemukan.');
        }

        // Ambil data divisi dan user
        $divisi = Divisi::all();
        $position = Position::all();
        $user = User::whereIn('role_id_role', ['2', '3'])->get();

        return view('manager.undangan.persetujuan-undangan', compact('user', 'divisi', 'undangan', 'position'));
    }


    public function sendDocument(Request $request)
    {

        // dd($request->all());
        $request->validate([
            'id_document' => 'required',
            'posisi_penerima' => 'required|exists:position,id_position', // Validasi posisi
            'divisi_penerima' => 'required|exists:divisi,id_divisi', // Pastikan divisi ada
        ]);


        $documentid = $request->id_document;
        $posisiPenerima = $request->posisi_penerima;
        $divisiPenerima = $request->divisi_penerima;

        // Cari semua user dengan posisi dan divisi yang dipilih
        $penerimaUsers = User::where('position_id_position', $posisiPenerima)
            ->where('divisi_id_divisi', $divisiPenerima)
            ->get();

        if ($penerimaUsers->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada user yang sesuai dengan kriteria penerima.');
        }

        $filePath = null;
        if ($request->hasFile('lampiran')) {
            $file = $request->file('lampiran');
            $fileData = base64_encode(file_get_contents($file->getRealPath()));
            // Simpan file base64 ke tabel sesuai jenis dokumen
            if ($request->jenis_document == 'memo') {
                $memo = Memo::findOrFail($documentid);
                $memo->lampiran = $fileData;
                $memo->save();
            } elseif ($request->jenis_document == 'undangan') {
                $undangan = Undangan::findOrFail($documentid);
                $undangan->lampiran = $fileData;
                $undangan->save();
            } elseif ($request->jenis_document == 'risalah') {
                $risalah = Risalah::findOrFail($documentid);
                $risalah->lampiran = $fileData;
                $risalah->save();
            }
        }




        // Simpan pengiriman memo ke setiap penerima
        foreach ($penerimaUsers as $user) {
            Kirim_Document::create([
                'id_document' => $documentid,
                'jenis_document' => $request->jenis_document,
                'id_pengirim' => Auth::id(),
                'id_penerima' => $user->id,
                'status' => 'pending',
            ]);
        }
        $previousUrl = session('previous_url', route('memo.diterima'));
        session()->forget('previous_url');
        if (Auth::user()->role->nm_role == 'manager') {
            return redirect($previousUrl)->with('success', 'Dokumen berhasil dikirim.');
        } else {

            return redirect()->back()->with('success', 'Dokumen berhasil dikirim.');
        }
    }

    public function memoTerkirim(Request $request)
    {
        $userId = Auth::id();
        $memoController = new MemoController();
        $userKode = $memoController->getDivDeptKode(Auth::user());
        $sortBy = $request->get('sort_by', 'kirim_document.id_kirim_document');
        $sortDirection = $request->get('sort_direction', 'desc');

        $allowedSorts = [
            'kirim_document.id_kirim_document',
            'memo.tgl_dibuat',
            'memo.tgl_disahkan',
            'memo.judul',
            'memo.nomor_memo'
        ];

        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'kirim_document.id_kirim_document';
        }

        // Get archived memo document IDs for this user
        $memoDiarsipkan = Arsip::where('user_id', $userId)
            ->where('jenis_document', 'App\\Models\\Memo')
            ->pluck('document_id')
            ->toArray();

        $memoTerkirim = Kirim_Document::query()
            ->where('jenis_document', 'memo')
            ->where(function ($q) use ($userId) {
                $q->where('id_pengirim', $userId)
                    //->orWhere('id_penerima', $userId)
                    ->orWhere(function ($q2) {
                        $q2->where('jenis_document', 'memo')
                            // ->where('status', '!=', 'approve')
                        ;
                    });
            })
            ->whereNotIn('id_document', $memoDiarsipkan)
            ->whereHas('memo', function ($query) use ($request, $userKode) {

                $query->where(function ($q) use ($userKode) {
                    $q->where('kode', $userKode)
                        ->orWhere('nama_bertandatangan', Auth::user()->fullname);
                });


                if ($request->filled('tgl_dibuat_awal') && $request->filled('tgl_dibuat_akhir')) {
                    $tgl_awal = $request->date('tgl_dibuat_awal') ?? null;
                    $tgl_akhir = $request->date('tgl_dibuat_akhir') ?? null;
                    $query->whereBetween('tgl_dibuat', [$tgl_awal, $tgl_akhir]);
                } elseif ($request->filled('tgl_dibuat_awal')) {
                    $query->whereDate('tgl_dibuat', '>=', $request->tgl_dibuat_awal);
                } elseif ($request->filled('tgl_dibuat_akhir')) {
                    $query->whereDate('tgl_dibuat', '<=', $request->tgl_dibuat_akhir);
                }

                if ($request->filled('search')) {
                    $query->where(function ($q) use ($request) {
                        $q->where('judul', 'like', '%' . $request->search . '%')
                            ->orWhere('nomor_memo', 'like', '%' . $request->search . '%');
                    });
                }

                if ($request->filled('status')) {
                    $query->where('memo.status', $request->status);
                }
                //dd($query->toSql(), $query->getBindings());
            })
            ->whereIn('id_kirim_document', function ($subQuery) {
                $subQuery->selectRaw('MIN(id_kirim_document)')
                    ->from('kirim_document')
                    ->groupBy('id_document');
            })
            ->with('memo');

        if (Str::startsWith($sortBy, 'memo.')) {
            $memoColumn = Str::after($sortBy, 'memo.');
            $memoTerkirim->join('memo', 'kirim_document.id_document', '=', 'memo.id_memo')
                ->orderBy("memo.$memoColumn", $sortDirection)
                ->select('kirim_document.*'); // agar tetap menghasilkan Kirim_Document model
        } else {
            $memoTerkirim->orderBy($sortBy, $sortDirection);
        }

        $perPage = $request->get('per_page', 10);
        $memoTerkirim = $memoTerkirim->paginate($perPage);

        return view('manager.memo.memo-terkirim', compact('memoTerkirim', 'sortBy', 'sortDirection'));
    }

    public function memoDiterima(Request $request)
    {
        //dd($request->all());
        $userId = auth()->id();
        $memoController = new MemoController();
        $userKode = $memoController->getDivDeptKode(Auth::user());
        session(['previous_url' => url()->previous()]);
        $sortBy = $request->get('sort_by', 'kirim_document.id_kirim_document');
        $sortDirection = $request->get('sort_direction', 'desc');
        $kode = Memo::whereNotNull('kode')
            ->pluck('kode')
            ->filter()
            ->unique()
            ->values();
        $allowedSorts = [
            'kirim_document.id_kirim_document',
            'memo.tgl_dibuat',
            'memo.tgl_disahkan',
            'memo.judul',
            'memo.nomor_memo'
        ];

        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'kirim_document.id_kirim_document';
        }
        // Get archived memo document IDs for this user
        $memoDiarsipkan = Arsip::where('user_id', $userId)
            ->where('jenis_document', 'App\\Models\\Memo')
            ->pluck('document_id')
            ->toArray();

        $memoDiterima = Kirim_Document::where('jenis_document', 'memo')
            ->where('id_penerima', $userId)
            ->whereNotIn('id_document', $memoDiarsipkan) // exclude archived
            ->whereIn('kirim_document.status', ['pending', 'approve'])
            ->whereHas('memo', function ($query) use ($request, $userKode) {
                $query->where('nama_bertandatangan', '!=', Auth::user()->fullname);

                if ($request->filled('tgl_dibuat_awal') && $request->filled('tgl_dibuat_akhir')) {
                    $query->whereBetween('tgl_dibuat', [$request->tgl_dibuat_awal, $request->tgl_dibuat_akhir]);
                } elseif ($request->filled('tgl_dibuat_awal')) {
                    $query->whereDate('tgl_dibuat', '>=', $request->tgl_dibuat_awal);
                } elseif ($request->filled('tgl_dibuat_akhir')) {
                    $query->whereDate('tgl_dibuat', '<=', $request->tgl_dibuat_akhir);
                }

                if ($request->filled('search')) {
                    $query->where(function ($q2) use ($request) {
                        $q2->where('judul', 'like', '%' . $request->search . '%')
                            ->orWhere('nomor_memo', 'like', '%' . $request->search . '%');
                    });
                }

                if ($request->filled('kode')) {
                    $query->where('kode', $request->kode);
                }
            })
            ->whereIn('id_kirim_document', function ($subQuery) use ($userId) {
                $subQuery->selectRaw('MIN(id_kirim_document)')
                    ->from('kirim_document')
                    ->where('jenis_document', 'memo')
                    ->where('id_penerima', $userId)
                    ->groupBy('id_document');
            })
            ->with('memo');

        if (Str::startsWith($sortBy, 'memo.')) {
            $memoColumn = Str::after($sortBy, 'memo.');
            $memoDiterima->join('memo', 'kirim_document.id_document', '=', 'memo.id_memo')
                ->orderBy("memo.$memoColumn", $sortDirection)
                ->select('kirim_document.*');
        } else {
            $memoDiterima->orderBy($sortBy, $sortDirection);
        }

        $perPage = $request->get('per_page', 10);
        $memoDiterima = $memoDiterima->paginate($perPage);

        return view('manager.memo.memo-diterima', compact('memoDiterima', 'sortBy', 'sortDirection', 'kode'));
    }

    public function undangan(Request $request)
    {
        $userId =  auth()->id();
        $filterType = $request->get('userid_filter', 'both');
        $sortBy = $request->get('sort_by', 'tgl_rapat_diff');
        $sortDirection = $request->get('sort_direction', 'asc') === 'asc' ? 'asc' : 'desc';
        $kode = Undangan::whereNotNull('kode')
            ->pluck('kode')
            ->filter()
            ->unique()
            ->values();
        $allowedSorts = [
            'kirim_document.id_kirim_document',
            'undangan.tgl_dibuat',
            'undangan.tgl_disahkan',
            'undangan.judul',
            'undangan.nomor_undangan',
            'tgl_rapat_diff'
        ];

        if (!in_array($sortBy, $allowedSorts)) $sortBy = 'tgl_rapat_diff';

        // Ambil id_kirim_document terkecil tiap id_document
        $subQuery = Kirim_Document::where('jenis_document', 'undangan')
            ->where(function ($q) use ($userId, $filterType) {
                if ($filterType === 'own') {
                    $q->where('id_pengirim', $userId);
                } elseif ($filterType === 'other') {
                    $q->where('id_penerima', $userId);
                } else {
                    $q->where(function ($q2) use ($userId) {
                        $q2->where('id_pengirim', $userId)->orWhere('id_penerima', $userId);
                    });
                }
            });

        if ($request->filled('status')) $subQuery->where('status', $request->status);

        $subQuery->whereHas('undangan', function ($q) use ($request) {
            if ($request->filled('tgl_dibuat_awal') && $request->filled('tgl_dibuat_akhir')) {
                $q->whereBetween('tgl_dibuat', [$request->tgl_dibuat_awal, $request->tgl_dibuat_akhir]);
            } elseif ($request->filled('tgl_dibuat_awal')) {
                $q->whereDate('tgl_dibuat', '>=', $request->tgl_dibuat_awal);
            } elseif ($request->filled('tgl_dibuat_akhir')) {
                $q->whereDate('tgl_dibuat', '<=', $request->tgl_dibuat_akhir);
            }

            if ($request->filled('search')) {
                $q->where(function ($q2) use ($request) {
                    $q2->where('judul', 'like', '%' . $request->search . '%')
                        ->orWhere('nomor_undangan', 'like', '%' . $request->search . '%');
                });
            }
            if ($request->filled('kode')) {
                $q->where('kode', $request->kode);
            }
        });

        $idKirimList = $subQuery->selectRaw('MIN(id_kirim_document) as id_kirim_document')
            ->groupBy('id_document')
            ->pluck('id_kirim_document');

        // 🔹 Ambil semua undangan yang sudah diarsipkan user ini
        $undanganDiarsipkan = Arsip::where('user_id', $userId)
            ->where('jenis_document', 'App\\Models\\Undangan')
            ->pluck('document_id')
            ->toArray();

        // Query utama kirim_document + undangan
        $undangans = Kirim_Document::whereIn('id_kirim_document', $idKirimList)
            ->whereNotIn('id_document', $undanganDiarsipkan) // ⬅ filter arsip disini
            ->with('undangan');

        // Sorting
        if ($sortBy == 'tgl_rapat_diff') {
            $undangans->join('undangan', 'kirim_document.id_document', '=', 'undangan.id_undangan')
                ->orderByRaw("
                CASE WHEN DATEDIFF(tgl_rapat, CURDATE()) < 0 THEN 1 ELSE 0 END ASC
            ")
                ->orderByRaw("
                ABS(DATEDIFF(tgl_rapat, CURDATE())) {$sortDirection}
            ")
                ->select('kirim_document.*');
        } elseif (Str::startsWith($sortBy, 'undangan.')) {
            $field = Str::after($sortBy, 'undangan.');
            $undangans->join('undangan', 'kirim_document.id_document', '=', 'undangan.id_undangan')
                ->orderBy("undangan.$field", $sortDirection)
                ->select('kirim_document.*');
        } else {
            $undangans->orderBy($sortBy, $sortDirection);
        }

        $perPage = $request->get('per_page', 10);
        $undangans = $undangans->paginate($perPage);

        $undangans->getCollection()->transform(function ($undangan) use ($userId) {
            // Ambil ID pembuat dari relasi undangan
            $creator = $undangan->undangan->pembuat;

            if ($creator == $userId) {
                // Undangan dibuat oleh user (keluar)
                $undangan->final_status = $undangan->status;
                $undangan->jenis = 'keluar';
            } else {
                // Undangan masuk ke user
                $statusKirim = Kirim_Document::where('id_document', $undangan->id_document)
                    ->where('jenis_document', 'undangan')
                    ->where('id_penerima', $userId)
                    ->first();

                $undangan->final_status = $statusKirim ? $statusKirim->status : '-';
                $undangan->jenis = 'masuk';
            }

            return $undangan;
        });


        return view('manager.undangan.undangan', compact('undangans', 'sortBy', 'sortDirection', 'kode'));
    }
    public function undanganDiterima(Request $request)
    {
        $userId = auth()->id();
        $undanganController = new UndanganController();
        $userKode = $undanganController->getDivDeptKode(Auth::user());
        $filterType = $request->get('userid_filter', 'both'); // dipertahankan agar variabel sama persis
        $sortBy = $request->get('sort_by', 'tgl_rapat_diff');
        $sortDirection = $request->get('sort_direction', 'asc') === 'asc' ? 'asc' : 'desc';

        $kode = Undangan::whereNotNull('kode')
            ->pluck('kode')
            ->filter()
            ->unique()
            ->values();

        $allowedSorts = [
            'kirim_document.id_kirim_document',
            'undangan.tgl_dibuat',
            'undangan.tgl_disahkan',
            'undangan.judul',
            'undangan.nomor_undangan',
            'tgl_rapat_diff'
        ];
        if (!in_array($sortBy, $allowedSorts)) $sortBy = 'tgl_rapat_diff';

        // HANYA BERBEDA DI SINI: fix ke id_penerima = user login
        $subQuery = Kirim_Document::where('jenis_document', 'undangan')
            ->where(function ($q) use ($userId) {
                $q->where('id_penerima', $userId);
            });

        if ($request->filled('status')) $subQuery->where('status', $request->status);

        $subQuery->whereHas('undangan', function ($q) use ($request, $userKode) {
            // filter by user's div/dept code: only undangan not from same kode (received)
            $q->where('kode', '!=', $userKode);
            if ($request->filled('tgl_dibuat_awal') && $request->filled('tgl_dibuat_akhir')) {
                $q->whereBetween('tgl_dibuat', [$request->tgl_dibuat_awal, $request->tgl_dibuat_akhir]);
            } elseif ($request->filled('tgl_dibuat_awal')) {
                $q->whereDate('tgl_dibuat', '>=', $request->tgl_dibuat_awal);
            } elseif ($request->filled('tgl_dibuat_akhir')) {
                $q->whereDate('tgl_dibuat', '<=', $request->tgl_dibuat_akhir);
            }

            if ($request->filled('search')) {
                $q->where(function ($q2) use ($request) {
                    $q2->where('judul', 'like', '%' . $request->search . '%')
                        ->orWhere('nomor_undangan', 'like', '%' . $request->search . '%');
                });
            }
            if ($request->filled('kode')) {
                $q->where('kode', $request->kode);
            }
        });

        $idKirimList = $subQuery->selectRaw('MIN(id_kirim_document) as id_kirim_document')
            ->groupBy('id_document')
            ->pluck('id_kirim_document');

        $undanganDiarsipkan = Arsip::where('user_id', $userId)
            ->where('jenis_document', 'App\\Models\\Undangan')
            ->pluck('document_id')
            ->toArray();

        $undangans = Kirim_Document::whereIn('id_kirim_document', $idKirimList)
            ->whereNotIn('id_document', $undanganDiarsipkan)
            ->with('undangan');

        if ($sortBy == 'tgl_rapat_diff') {
            $undangans->join('undangan', 'kirim_document.id_document', '=', 'undangan.id_undangan')
                ->orderByRaw("CASE WHEN DATEDIFF(tgl_rapat, CURDATE()) < 0 THEN 1 ELSE 0 END ASC")
                ->orderByRaw("ABS(DATEDIFF(tgl_rapat, CURDATE())) {$sortDirection}")
                ->select('kirim_document.*');
        } elseif (Str::startsWith($sortBy, 'undangan.')) {
            $field = Str::after($sortBy, 'undangan.');
            $undangans->join('undangan', 'kirim_document.id_document', '=', 'undangan.id_undangan')
                ->orderBy("undangan.$field", $sortDirection)
                ->select('kirim_document.*');
        } else {
            $undangans->orderBy($sortBy, $sortDirection);
        }

        $perPage = $request->get('per_page', 10);
        $undangans = $undangans->paginate($perPage);

        return view('manager.undangan.undangan-diterima', compact('undangans', 'sortBy', 'sortDirection', 'kode'));
    }

    public function memo(Request $request)
    {
        $userId = Auth::user()->id;
        $filterType = $request->get('userid_filter', 'both'); // own, other, both
        $sortBy = $request->get('sort_by', 'memo.tgl_dibuat');
        $sortDirection = $request->get('sort_direction', 'desc') === 'asc' ? 'asc' : 'desc';

        $memoController = new MemoController();
        $userKode = $memoController->getDivDeptKode(Auth::user());

        // Ambil kode departemen dari semua memo
        $kode = Memo::whereNotNull('kode')->pluck('kode')->unique()->values();

        $allowedSorts = [
            'kirim_document.id_kirim_document',
            'memo.tgl_dibuat',
            'memo.tgl_disahkan',
            'memo.judul',
            'memo.nomor_memo'
        ];

        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'memo.tgl_dibuat';
        }

        // Ambil semua memo yang sudah diarsipkan user ini
        $memoDiarsipkan = Arsip::where('user_id', $userId)
            ->where('jenis_document', 'App\\Models\\Memo')
            ->pluck('document_id')
            ->toArray();

        // 🔹 Subquery untuk ambil id_kirim terkecil tiap dokumen
        $subQuery = Kirim_Document::where('jenis_document', 'memo')->where(function ($q) use ($userId, $filterType) {
            if ($filterType === 'own') {
                $q->where('id_pengirim', $userId);
            } elseif ($filterType === 'other') {
                $q->where('id_penerima', $userId);
            } else {
                $q->where(function ($q2) use ($userId) {
                    $q2->where('id_pengirim', $userId)->orWhere('id_penerima', $userId);
                });
            }
        });

        if ($request->filled('status')) $subQuery->where('kirim_document.status', $request->status);

        $subQuery->whereHas('memo', function ($q) use ($request) {
            if ($request->filled('tgl_dibuat_awal') && $request->filled('tgl_dibuat_akhir')) {
                $q->whereBetween('tgl_dibuat', [$request->tgl_dibuat_awal, $request->tgl_dibuat_akhir]);
            } elseif ($request->filled('tgl_dibuat_awal')) {
                $q->whereDate('tgl_dibuat', '>=', $request->tgl_dibuat_awal);
            } elseif ($request->filled('tgl_dibuat_akhir')) {
                $q->whereDate('tgl_dibuat', '<=', $request->tgl_dibuat_akhir);
            }

            if ($request->filled('search')) {
                $q->where(function ($q2) use ($request) {
                    $q2->where('judul', 'like', '%' . $request->search . '%')
                        ->orWhere('nomor_memo', 'like', '%' . $request->search . '%');
                });
            }

            if ($request->filled('kode')) $q->where('kode', $request->kode);
        });

        $idKirimList = $subQuery->selectRaw('MIN(id_kirim_document) as id_kirim_document')
            ->groupBy('id_document')
            ->pluck('id_kirim_document');

        // 🔹 Query utama
        $memos = Kirim_Document::whereIn('id_kirim_document', $idKirimList)
            ->whereNotIn('id_document', $memoDiarsipkan)
            ->with('memo');

        // 🔹 Sorting
        if (Str::startsWith($sortBy, 'memo.')) {
            $field = Str::after($sortBy, 'memo.');
            $memos->join('memo', 'kirim_document.id_document', '=', 'memo.id_memo')
                ->orderBy("memo.$field", $sortDirection)
                ->select('kirim_document.*');
        } else {
            $memos->orderBy($sortBy, $sortDirection);
        }

        // 🔹 Pagination
        $perPage = $request->get('per_page', 10);
        $memos = $memos->paginate($perPage);

        // 🔹 Tandai memo masuk / keluar
        $memos->getCollection()->transform(function ($memo) use ($userId) {
            $creator = $memo->memo->pembuat; // ← pakai 'pembuat' (data asli dari tabel)

            if ($creator == $userId || $memo->memo->status != 'approve' || $memo->memo->nama_bertandatangan == Auth::user()->fullname) {
                // Memo dibuat oleh user → keluar
                $memo->final_status = $memo->status;
                $memo->jenis = 'keluar';
            } else {
                // Memo masuk ke user
                $statusKirim = Kirim_Document::where('id_document', $memo->id_document)
                    ->where('jenis_document', 'memo')
                    ->where('id_penerima', $userId)
                    ->first();

                $memo->final_status = $statusKirim ? $statusKirim->status : '-';
                $memo->jenis = 'masuk';
            }
            return $memo;
        });

        return view('manager.memo.memo', compact('memos', 'sortBy', 'sortDirection', 'kode'));
    }


    public function undanganTerkirim(Request $request)
    {
        $userId = Auth::user()->id;
        $undanganController = new UndanganController();
        $userKode = $undanganController->getDivDeptKode(Auth::user());

        $sortBy = $request->get('sort_by', 'tgl_rapat_diff');
        $sortDirection = $request->get('sort_direction', 'asc') === 'asc' ? 'asc' : 'desc';

        $kode = Undangan::whereNotNull('kode')->pluck('kode')->filter()->unique()->values();
        $allowedSorts = [
            'kirim_document.id_kirim_document',
            'undangan.tgl_dibuat',
            'undangan.tgl_disahkan',
            'undangan.judul',
            'undangan.nomor_undangan',
            'tgl_rapat_diff',
        ];
        if (!in_array($sortBy, $allowedSorts)) $sortBy = 'tgl_rapat_diff';

        // === MIRROR memoTerkirim(): ambil semua kirim_document tipe 'undangan' dari kode yang sama ===
        $subQuery = Kirim_Document::where('jenis_document', 'undangan');

        // (Biarkan filter status opsional saja; sama seperti memoTerkirim yang tidak memaksa)
        if ($request->filled('status')) {
            $subQuery->where('status', $request->status);
        }

        $subQuery->whereHas('undangan', function ($q) use ($request, $userKode) {
            $q->where('kode', $userKode); // terkirim = dari kode kita sendiri

            if ($request->filled('tgl_dibuat_awal') && $request->filled('tgl_dibuat_akhir')) {
                $q->whereBetween('tgl_dibuat', [$request->tgl_dibuat_awal, $request->tgl_dibuat_akhir]);
            } elseif ($request->filled('tgl_dibuat_awal')) {
                $q->whereDate('tgl_dibuat', '>=', $request->tgl_dibuat_awal);
            } elseif ($request->filled('tgl_dibuat_akhir')) {
                $q->whereDate('tgl_dibuat', '<=', $request->tgl_dibuat_akhir);
            }

            if ($request->filled('search')) {
                $q->where(function ($q2) use ($request) {
                    $q2->where('judul', 'like', '%' . $request->search . '%')
                        ->orWhere('nomor_undangan', 'like', '%' . $request->search . '%');
                });
            }
            if ($request->filled('kode')) {
                $q->where('kode', $request->kode);
            }
        });

        // Ambil kiriman pertama per dokumen UNTUK user ini (mirror memoTerkirim)
        $idKirimList = $subQuery->selectRaw('MIN(id_kirim_document) as id_kirim_document')
            ->groupBy('id_document')
            ->pluck('id_kirim_document');

        // Exclude arsip (mirror memo)
        $undanganDiarsipkan = Arsip::where('user_id', $userId)
            ->where('jenis_document', 'App\\Models\\Undangan')
            ->pluck('document_id')
            ->toArray();

        $undangans = Kirim_Document::whereIn('id_kirim_document', $idKirimList)
            ->whereNotIn('id_document', $undanganDiarsipkan)
            ->with('undangan');

        // Sorting (tetap pakai fitur tgl_rapat_diff milik undangan)
        if ($sortBy == 'tgl_rapat_diff') {
            $undangans->join('undangan', 'kirim_document.id_document', '=', 'undangan.id_undangan')
                ->orderByRaw("CASE WHEN DATEDIFF(tgl_rapat, CURDATE()) < 0 THEN 1 ELSE 0 END ASC")
                ->orderByRaw("ABS(DATEDIFF(tgl_rapat, CURDATE())) {$sortDirection}")
                ->select('kirim_document.*');
        } elseif (Str::startsWith($sortBy, 'undangan.')) {
            $field = Str::after($sortBy, 'undangan.');
            $undangans->join('undangan', 'kirim_document.id_document', '=', 'undangan.id_undangan')
                ->orderBy("undangan.$field", $sortDirection)
                ->select('kirim_document.*');
        } else {
            $undangans->orderBy($sortBy, $sortDirection);
        }

        $perPage = $request->get('per_page', 10);
        $undangans = $undangans->paginate($perPage);

        return view('manager.undangan.undangan-terkirim', compact('undangans', 'sortBy', 'sortDirection', 'kode'));
    }


    public function risalah(Request $request)
    {
        $userId = Auth::id();
        $kode = DB::table('risalah')
            ->whereNotNull('kode')        // pastikan hanya yang ada kodenya
            ->distinct()
            ->pluck('kode');
        // --- Ambil risalah yang sudah diarsipkan user ini ---
        $risalahDiarsipkan = Arsip::where('user_id', $userId)
            ->where('jenis_document', 'App\\Models\\Risalah')
            ->pluck('document_id')
            ->toArray();

        // --- Allowed sorting columns ---
        $allowedSortColumns = ['created_at', 'tgl_disahkan', 'tgl_dibuat', 'nomor_risalah', 'judul'];
        $sortBy = in_array($request->get('sort_by'), $allowedSortColumns) ? $request->get('sort_by') : 'created_at';
        $sortDirection = $request->get('sort_direction', 'desc') === 'desc' ? 'desc' : 'asc';

        // --- Query awal: risalah yg user terlibat & belum diarsipkan ---
        $query = Risalah::query()
            ->whereNotIn('id_risalah', $risalahDiarsipkan)
            ->where(function ($q) use ($userId) {
                $q->whereHas('kirimDocument', function ($sub) use ($userId) {
                    $sub->where('jenis_document', 'risalah')
                        ->where(function ($inner) use ($userId) {
                            $inner->where('id_pengirim', $userId)
                                ->orWhere('id_penerima', $userId);
                        });
                });
            });

        // --- Filter status risalah ---
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // --- Filter kode risalah ---
        if ($request->filled('kode')) {
            $query->where('kode', $request->kode);
        }

        // --- Filter tanggal dibuat ---
        if ($request->filled('tgl_dibuat_awal') && $request->filled('tgl_dibuat_akhir')) {
            $query->whereBetween('tgl_dibuat', [$request->tgl_dibuat_awal, $request->tgl_dibuat_akhir]);
        } elseif ($request->filled('tgl_dibuat_awal')) {
            $query->whereDate('tgl_dibuat', '>=', $request->tgl_dibuat_awal);
        } elseif ($request->filled('tgl_dibuat_akhir')) {
            $query->whereDate('tgl_dibuat', '<=', $request->tgl_dibuat_akhir);
        }

        // --- Filter search (judul / nomor risalah) ---
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                    ->orWhere('nomor_risalah', 'like', "%{$search}%");
            });
        }

        // --- Sorting & pagination ---
        $perPage = $request->get('per_page', 10);
        $risalahs = $query->with('kirimDocument')
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage);

        // --- Tambahin final_status (status kirim untuk user ini) ---
        $risalahs->getCollection()->transform(function ($risalah) use ($userId) {
            $statusKirim = Kirim_Document::where('id_document', $risalah->id_risalah)
                ->where('jenis_document', 'risalah')
                ->where(function ($q) use ($userId) {
                    $q->where('id_pengirim', $userId)
                        ->orWhere('id_penerima', $userId);
                })
                ->orderBy('id_kirim_document') // ambil yg paling awal
                ->first();

            $risalah->final_status = $statusKirim ? $statusKirim->status : '-';
            return $risalah;
        });

        return view('manager.risalah.risalah', compact('risalahs', 'kode'));
    }


    public function create()
    {
        $idUser = Auth::user();
        $user = User::where('id', $idUser->id)->first();

        if ($user->position_id_position == 1) {
            $idDirektur = Director::where('id_director', $user->director_id_director)->first();
            $kodeDirektur = $idDirektur->kode_director;
        } else {
            $kodeDirektur = '';
        }
        // dd($user);
        if ($user->department_id_department != NULL) {
            $divisiName = Department::where('id_department', $user->department_id_department)->first();
            if ($divisiName->kode_department != NULL) {
                $divisiName = $divisiName->kode_department;
            } else if ($divisiName->kode_department == NULL) {
                if ($user->divisi_id_divisi == NULL) {
                    $divisiName = $divisiName->name_department;
                } else {
                    $divisiName = Divisi::where('id_divisi', $user->divisi_id_divisi)->first();
                    if ($divisiName->kode_divisi != NULL) {
                        $divisiName = $divisiName->kode_divisi;
                    } else if ($divisiName->kode_divisi == NULL) {
                        $divisiName = $divisiName->nm_divisi;
                    }
                }
            }
        } else if ($user->divisi_id_divisi != NULL) {
            $divisiName = Divisi::where('id_divisi', $user->divisi_id_divisi)->first();
            if ($divisiName->kode_divisi != NULL) {
                $divisiName = $divisiName->kode_divisi;
            } else if ($divisiName->kode_divisi == NULL) {
                $divisiName = $divisiName->nm_divisi;
            }
        } else if ($user->director_id_director != NULL) {
            $divisiName = Director::where('id_director', $user->director_id_director)->first();
            $divisiName = $divisiName->kode_director;
        }

        $user = Auth::user();

        $unitId       = $user->unit_id_unit;
        $sectionId    = $user->section_id_section;
        $departmentId = $user->department_id_department;
        $divisiId     = $user->divisi_id_divisi;
        $directorId   = $user->id_director;

        if (is_null($unitId) && is_null($sectionId) && is_null($departmentId) && is_null($divisiId) && is_null($directorId)) {
            return [];
        }

        $listUndangan = Kirim_Document::where('jenis_document', 'undangan')
            ->where('status', 'approve')
            ->where('id_pengirim', $user->id)
            ->orWhere('id_penerima', $user->id)
            ->pluck('id_document')
            ->unique();

        $undangan = Undangan::whereIn('id_undangan', $listUndangan)
            ->get()
            ->map(function ($item) {
                return (object) $item->toArray();
            });

        $risalah = new Risalah(); // atau ambil dari data risalah terakhir, terserah kebutuhanmu

        // Ambil nomor seri berikutnya
        $nextSeri = SeriRisalah::getNextSeri(false);
        // Konversi bulan ke angka Romawi
        $bulanRomawi = $this->convertToRoman(now()->month);

        // Daftar bagian kerja
        $bagianKerja = BagianKerja::orderBy('kode_bagian')->get();

        // Format nomor dokumen sesuai contoh pada gambar
        $nomorDokumen = sprintf(
            "RIS-%02d/REKA%s/%s/%s/%d",
            $nextSeri['seri_tahunan'],
            strtoupper($kodeDirektur),
            strtoupper($divisiName),
            $bulanRomawi,
            now()->year
        );
        $users = User::select()
            ->orderBy('firstname')
            ->get();

        return view(Auth::user()->role->nm_role . '.risalah.add-risalah', [
            'risalah' => $risalah,
            'nomorSeriTahunan' => $nextSeri['seri_tahunan'], // Tambahkan nomor seri tahunan
            'nomorDokumen' => $nomorDokumen,
            'kode_bagian' => $bagianKerja,
            'users' => $users,
            'self' => Auth::user(),
            'undangan' => $undangan
        ], compact('undangan', 'bagianKerja'));
    }


    public function createCustom()
    {
        $idUser = Auth::user();
        $user = User::where('id', $idUser->id)->first();

        if ($user->position_id_position == 1) {
            $idDirektur = Director::where('id_director', $user->director_id_director)->first();
            $kodeDirektur = $idDirektur->kode_director;
        } else {
            $kodeDirektur = '';
        }
        // dd($user);
        if ($user->department_id_department != NULL) {
            $divisiName = Department::where('id_department', $user->department_id_department)->first();
            if ($divisiName->kode_department != NULL) {
                $divisiName = $divisiName->kode_department;
            } else if ($divisiName->kode_department == NULL) {
                if ($user->divisi_id_divisi == NULL) {
                    $divisiName = $divisiName->name_department;
                } else {
                    $divisiName = Divisi::where('id_divisi', $user->divisi_id_divisi)->first();
                    if ($divisiName->kode_divisi != NULL) {
                        $divisiName = $divisiName->kode_divisi;
                    } else if ($divisiName->kode_divisi == NULL) {
                        $divisiName = $divisiName->nm_divisi;
                    }
                }
            }
        } else if ($user->divisi_id_divisi != NULL) {
            $divisiName = Divisi::where('id_divisi', $user->divisi_id_divisi)->first();
            if ($divisiName->kode_divisi != NULL) {
                $divisiName = $divisiName->kode_divisi;
            } else if ($divisiName->kode_divisi == NULL) {
                $divisiName = $divisiName->nm_divisi;
            }
        } else if ($user->director_id_director != NULL) {
            $divisiName = Director::where('id_director', $user->director_id_director)->first();
            $divisiName = $divisiName->kode_director;
        }

        $user = Auth::user();

        $unitId       = $user->unit_id_unit;
        $sectionId    = $user->section_id_section;
        $departmentId = $user->department_id_department;
        $divisiId     = $user->divisi_id_divisi;
        $directorId   = $user->id_director;

        if (is_null($unitId) && is_null($sectionId) && is_null($departmentId) && is_null($divisiId) && is_null($directorId)) {
            return [];
        }

        $risalah = new Risalah(); // atau ambil dari data risalah terakhir, terserah kebutuhanmu

        // Kode bagian kerja
        $bagianKerja = BagianKerja::orderBy('kode_bagian')->get();

        // Ambil nomor seri berikutnya
        $nextSeri = SeriRisalah::getNextSeri(false);
        // Konversi bulan ke angka Romawi
        $bulanRomawi = $this->convertToRoman(now()->month);

        $users = User::select()
            ->orderBy('firstname')
            ->get();

        $risalahController = new RisalahController();
        $orgTree = $risalahController->getOrgTreeWithUsers();
        $jsTreeData = $risalahController->convertToJsTree($orgTree);

        $mainDirector = $orgTree[0] ?? null;
        return view(Auth::user()->role->nm_role . '.risalah.add-custom', [
            'risalah' => $risalah,
            'nomorSeriTahunan' => $nextSeri['seri_tahunan'], // Tambahkan nomor seri tahunan
            'kode_bagian' => $bagianKerja,
            'users' => $users,
            'orgTree' => $orgTree,
            'jsTreeData' => $jsTreeData,
            'mainDirector' => $mainDirector
        ], compact('bagianKerja'));
    }


    public function nextSeri()
    {
        $memoController = new MemoController();
        $user = Auth::user();
        if ($user->position_id_position == 1) {
            $idDirektur = Director::where('id_director', $user->director_id_director)->first();
            $kodeDirektur = $idDirektur->kode_director;
        } else {
            $kodeDirektur = '';
        }
        // dd($user);

        $divDeptKode = $memoController->getDivDeptKode(Auth::user());

        // Ambil nomor seri berikutnya
        $nextSeri = SeriRisalah::getNextSeri(false);
        // Konversi bulan ke angka Romawi
        $bulanRomawi = $this->convertToRoman(now()->month);
        // Format nomor dokumen
        $nomorDokumen = sprintf(
            "RIS-%02d/REKA%s/%s/%s/%d",
            $nextSeri['seri_tahunan'],
            strtoupper($kodeDirektur),
            strtoupper($divDeptKode),
            $bulanRomawi,
            now()->year
        );
        return $nomorDokumen;
    }

    public function store(Request $request)
    {
        $memoController = new MemoController();

        $request->validate([
            'tgl_dibuat' => 'required|date',
            'nomor_risalah' => 'nullable|string',
            'kode_bagian' => 'required|string',
            'agenda' => 'required|string',
            'tempat' => 'required|string',
            'waktu_mulai' => 'required|string',
            'waktu_selesai' => 'required|string',
            'judul' => 'required|string',
            'tujuan' => 'required_without:with_undangan',
            'pembuat' => 'required|string',
            'nomor' => 'nullable|array',
            'topik' => 'nullable|array',
            'pembahasan' => 'nullable|array',
            'tindak_lanjut' => 'nullable|array',
            'target' => 'nullable|array',
            'pic' => 'nullable|array',
            'lampiran'   => 'nullable',
            'lampiran.*' => 'file|mimes:pdf,jpg,jpeg,png|max:2048',
        ], [
            'lampiran.*' => 'Lampiran gagal diunggan, pastikan format dan ukuran file sesuai ketentuan.',
            'tujuan.required_without' => 'Minimal satu peserta acara harus dipilih.',
            'lampiran.*.mimes' => 'File harus berupa PDF, JPG, atau PNG.',
            'lampiran.*.max'   => 'Ukuran tiap file tidak boleh lebih dari 2 MB.',
        ]);
        // Proses file lampiran (jika ada)
        $lampiranPath = null;
        // Handle multiple file uploads
        if ($request->hasFile('lampiran')) {

            $newFiles = [];
            foreach ($request->file('lampiran') as $file) {
                if ($file->isValid()) {
                    // Generate unique filename
                    $ext = strtolower($file->getClientOriginalExtension());

                    // Determine folder
                    if ($ext === 'pdf') {
                        $folder = 'lampiran/risalah/pdf';
                    } elseif (in_array($ext, ['png', 'jpg', 'jpeg'])) {
                        $folder = 'lampiran/risalah/image';
                    } else {
                        // fallback folder (optional)
                        $folder = 'lampiran/risalah/other';
                    }

                    $filename = time() . '_' . $file->getClientOriginalName();
                    $filePath = $file->storeAs($folder, $filename, 'public');

                    $newFiles[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $filePath,
                        'size' => $file->getSize(),
                        'uploaded_at' => now()->toDateTimeString()
                    ];
                }
            }
            $allFiles = array_merge($newFiles);
            $lampiranPath = !empty($allFiles) ? json_encode($allFiles) : null;
        }

// Ambil kode bagian dari form
        $kodeBagian = $request->input('kode_bagian');

        $divDeptKode = $memoController->getDivDeptKode(Auth::user());

        $pemimpin = User::where('id', $request->pemimpin_acara)->first();
        $notulis = User::where('id', $request->notulis_acara)->first();

        $namaPemimpinAcara = $pemimpin?->fullname;

        $namaNotulisAcara = $notulis?->fullname;

        $qrService = new QRCodeService();

        $qrTextNotulis = "Notulis Acara: " . $namaNotulisAcara
            . "\nNomor Risalah: " . ($request->nomor_risalah ?? '-')
            . "\nTanggal: " . now()->translatedFormat('l, d F Y H:i:s')
            . "\nDikeluarkan oleh website SIPO PT Rekaindo Global Jasa";
        $qrNotulisAcara = $qrService->generateWithLogo($qrTextNotulis);
        $tujuan = [];

        if ($request->with_undangan) {
            $undangan = Undangan::where('id_undangan', $request->with_undangan)->first();
            if ($undangan) {
                $tujuan = explode(';', $undangan->tujuan);
            }
        } else {
            $tujuan = $request->tujuan;
        }
        // Simpan risalah utama
        $risalah = null;

        while (true) {
            try {
                $risalah = Risalah::create([
                    'tgl_dibuat' => $request->tgl_dibuat,
                    'seri_surat' => $request->seri_surat,
                    'nomor_risalah' => $request->nomor_risalah,
                    'kode_bagian' => $kodeBagian,
                    'agenda' => $request->agenda,
                    'tempat' => $request->tempat,
                    'kode' => $divDeptKode,
                    'waktu_mulai' => $request->waktu_mulai,
                    'waktu_selesai' => $request->waktu_selesai,
                    'status' => 'pending',
                    'judul' => $request->judul,
                    'pembuat' => $request->pembuat,
                    'lampiran' => $lampiranPath,
                    'nama_pemimpin_acara' => $namaPemimpinAcara,
                    'nama_notulis_acara'    => $namaNotulisAcara,
                    'qr_notulis_acara' => $qrNotulisAcara,
                    'risalah_id_risalah' => $request->id_risalah,
                    'with_undangan' => $request->with_undangan,
                    'tujuan' => implode(';', $tujuan)
                ]);
                break; // sukses → keluar loop
            } catch (\Illuminate\Database\QueryException $e) {
                if ($e->getCode() == '23000') { // duplicate entry error
                    // Generate nomor baru pakai seri risalah
                    $newNomor = SeriRisalah::getNextSeri(true);
                    $request->merge(['nomor_risalah' => $newNomor]);
                    continue; // coba lagi simpan
                }
                throw $e; // error lain tetap dilempar
            }
        }


        if ($request->has('nomor') && is_array($request->nomor)) {
            foreach ($request->nomor as $index => $no) {
                RisalahDetail::create([
                    'risalah_id_risalah' => $risalah->id_risalah,
                    'nomor' => $no,
                    'topik' => $request->topik[$index] ?? '',
                    'pembahasan' => $request->pembahasan[$index] ?? '',
                    'tindak_lanjut' => $request->tindak_lanjut[$index] ?? '',
                    'target' => $request->target[$index] ?? '',
                    'pic' => $request->pic[$index] ?? '',
                ]);
            }
        }

        $push = new Api\NotifApiController;

        $penerima = $pemimpin;
        if (!$penerima) {
            return back()->withErrors(['nama_bertandatangan' => 'Nama penerima tidak ditemukan.']);
        }

        $sudahDikirim = \App\Models\Kirim_Document::where('id_document', $risalah->id_risalah)
            ->where('jenis_document', 'risalah')
            ->where('id_pengirim', Auth::id())
            ->where('id_penerima', $penerima->id)
            ->exists();
        $push = new NotifApiController();
        if (!$sudahDikirim) {
            \App\Models\Kirim_Document::firstOrCreate([
                'id_document' => $risalah->id_risalah,
                'jenis_document' => 'risalah',
                'id_pengirim' => Auth::id(),
                'id_penerima' => $penerima->id,
                'updated_at' => now(),
            ], [
                'status' => 'pending'
            ]);

            // Kirim notifikasi
            Notifikasi::create([
                'judul' => "Risalah Menunggu Persetujuan",
                'judul_document' => $risalah->judul,
                'id_user' => $penerima->id,
                'updated_at' => now()
            ]);
            $push->sendToUser(
                $penerima->id,
                "Risalah Menunggu Persetujuan",
                $risalah->judul
            );
        }
        $risalah->save();

        return redirect()->route('risalah.' . Auth::user()->role->nm_role)->with('success', 'Risalah berhasil ditambahkan');
    }

    private function convertToRoman($number)
    {
        $map = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII'
        ];
        return $map[$number] ?? '';
    }

    // Daftar dokumen yang dikirim
    public function sentDocuments()
    {
        $documents = Kirim_Document::where('id_pengirim', Auth::id())->get();
        return view('manager.memo.memo-terkirim', compact('documents'));
    }

    // Daftar dokumen yang diterima


    public function viewRisalah($id)
    {
        // Cek apakah ID ini milik Risalah
        $risalah = Risalah::find($id);

        if (!$risalah) {
            return redirect()->back()->with('error', 'Dokumen tidak ditemukan.');
        }

        // Ambil data referensi
        $divisi = Divisi::all();
        $position = Position::all();
        $user = User::whereIn('role_id_role', ['2', '3'])->get();

        // Ambil undangan berdasarkan judul risalah
        $undangan = Undangan::where('judul', $risalah->judul)->first();

        // Cek apakah undangan dan tujuannya ada
        if ($undangan && $undangan->tujuan) {
            $userIds = explode(';', $undangan->tujuan);
            $pdfController = new \App\Http\Controllers\CetakPDFController();
            $listNama = User::with(['position', 'director', 'divisi', 'department', 'section', 'unit'])
                ->whereIn('id', $userIds)
                ->get()
                ->map(function ($user, $key) use ($pdfController) {
                    $level = $pdfController->detectLevel($user);
                    $user->level_kerja = $level;
                    $user->bagian_text = $pdfController->getBagianText($user, $level);
                    return $user;
                })
                ->sortBy(function ($user) {
                    return optional($user->position)->id_position;
                })
                ->values();

            $tujuanUsernames = $listNama->map(function ($user, $index) {
                return ($index + 1) . '. '
                    . $user->position->nm_position . ' '
                    . $user->bagian_text . ' '
                    . '(' . $user->firstname . ' ' . $user->lastname . ')';
            })->implode("\n");
        } else {
            $tujuanUsernames = null;
        }

        $lampiranData = [];
        if ($risalah->lampiran) {
            // Coba decode sebagai JSON dulu (untuk data baru)
            $jsonData = json_decode($risalah->lampiran, true);
            if ($jsonData !== null && is_array($jsonData)) {
                $lampiranData = $jsonData;
            } else {
                // Jika bukan JSON, ini kemungkinan data BLOB lama - skip untuk sekarang
                // atau bisa dikasih placeholder jika memang ada file
                $lampiranData = $jsonData;
            }
        }

        return view('manager.risalah.persetujuan-risalah', compact('user', 'divisi', 'risalah', 'position', 'undangan', 'tujuanUsernames', 'lampiranData'));
    }
}
