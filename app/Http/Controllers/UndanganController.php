<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Seri;
use App\Models\User;
use App\Models\Divisi;
use App\Models\Arsip;
use App\Models\Notifikasi;
use App\Models\Undangan;
use App\Models\Department;
use App\Models\Director;
use App\Models\Backup_Document;
use App\Models\Kirim_Document;
use App\Models\Memo;
use App\Models\Section;
use App\Models\Unit;
use App\Models\Disposisi;
use App\Support\PositionOrder;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\CetakPDFController;
use Illuminate\Support\Facades\Validator;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use App\Services\QrCodeService;
use App\Models\BagianKerja;
use App\Services\NotifService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UndanganController extends Controller
{

    //protected $qrCodeService;

    //public function __construct(QrCodeService $qrCodeService)
    //{
    //    $this->qrCodeService = $qrCodeService;
    //}

    public function index(Request $request)
    {
        $seri = Seri::all();
        $userId = Auth::id();

        // Ambil semua document_id undangan yang sudah diarsipkan user ini
        $undanganDiarsipkan = Arsip::where('user_id', $userId)
            ->where('jenis_document', 'App\\Models\\Undangan') // Filter hanya undangan
            ->pluck('document_id')
            ->toArray();

        // $sortBy = $request->get('sort_by', 'tgl_rapat_diff');
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'asc') === 'asc' ? 'asc' : 'desc';

        $allowedSortColumns = ['created_at', 'tgl_disahkan', 'tgl_dibuat', 'nomor_undangan', 'judul', 'tgl_rapat_diff'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'tgl_rapat_diff';
        }

        // Query undangan yang belum diarsipkan user ini
        $query = Undangan::whereNotIn('id_undangan', $undanganDiarsipkan)
            ->whereHas('kirimDocument', function ($q) use ($userId) {
                $q->where('jenis_document', 'undangan')
                    ->where(function ($sub) use ($userId) {
                        $sub->where('id_pengirim', $userId)
                            ->orWhere('id_penerima', $userId);
                    });
            });

        if ($sortBy === 'tgl_rapat_diff') {
            $query
                ->whereNotNull('tgl_rapat')
                ->orderByRaw("
                CASE
                    WHEN DATEDIFF(tgl_rapat, CURDATE()) < 0 THEN 1
                    ELSE 0
                END ASC
            ")
                ->orderByRaw("
                ABS(DATEDIFF(tgl_rapat, CURDATE())) $sortDirection
            ");
        } elseif (in_array($sortBy, ['created_at', 'tgl_disahkan', 'tgl_dibuat', 'nomor_undangan', 'judul'])) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('tgl_dibuat', 'desc');
        }

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $filterType = $request->get('userid_filter', 'both');
        if ($filterType === 'own') {
            $query->whereHas('kirimDocument', function ($q) use ($userId) {
                $q->where('jenis_document', 'undangan')
                    ->where('id_pengirim', $userId);
            });
        } elseif ($filterType === 'other') {
            $query->whereHas('kirimDocument', function ($q) use ($userId) {
                $q->where('jenis_document', 'undangan')
                    ->where('id_penerima', $userId);
            });
        }

        if ($request->filled('tgl_dibuat_awal') && $request->filled('tgl_dibuat_akhir')) {
            $query->whereBetween('tgl_rapat', [$request->tgl_dibuat_awal, $request->tgl_dibuat_akhir]);
        } elseif ($request->filled('tgl_dibuat_awal')) {
            $query->whereDate('tgl_rapat', '>=', $request->tgl_dibuat_awal);
        } elseif ($request->filled('tgl_dibuat_akhir')) {
            $query->whereDate('tgl_rapat', '<=', $request->tgl_dibuat_akhir);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('judul', 'like', '%' . $request->search . '%')
                    ->orWhere('nomor_undangan', 'like', '%' . $request->search . '%');
            });
        }
        $kode = $query->whereNotNull('kode')
            ->pluck('kode')
            ->filter()
            ->unique()
            ->values();

        $perPage = $request->get('per_page', 10);
        $undangans = $query->paginate($perPage);

        // $undangans->getCollection()->transform(function ($undangan) use ($userId) {
        //     $statusKirim = Kirim_Document::where('id_document', $undangan->id_undangan)
        //         ->where('jenis_document', 'undangan')
        //         ->where('id_penerima', $userId)
        //         ->first();

        //     $undangan->final_status = $statusKirim ? $statusKirim->status : '-';
        //     return $undangan;
        // });

        $undangans->getCollection()->transform(function ($undangan) use ($userId) {
            $creator = $undangan->pembuat;
            if ($creator == $userId) {
                $undangan->final_status = $undangan->status;
                $undangan->jenis = 'keluar';
            } else {
                $statusKirim = Kirim_Document::where('id_document', $undangan->id_undangan)->where('jenis_document', 'undangan')->where('id_penerima', $userId)->first();
                $undangan->final_status = $statusKirim ? $statusKirim->status : '-';
                $undangan->jenis = 'masuk';
            }
            return $undangan;
        });

        $kirimDocuments = Kirim_Document::where('jenis_document', 'undangan')
            ->where(function ($query) use ($userId) {
                $query->where('id_pengirim', $userId)
                    ->orWhere('id_penerima', $userId);
            })->get();

        return view('admin.undangan.index', compact('undangans', 'kode', 'seri', 'sortDirection', 'kirimDocuments'));
    }

    /**
     * Undangan Terkirim for logged in user (hanya undangan di mana id_pengirim == auth user)
     */
    public function undanganTerkirim(Request $request)
    {
        $seri = Seri::all();
        $user = Auth::user();
        $userId = (int) $user->id;

        $undanganDiarsipkan = Arsip::where('user_id', $userId)
            ->where('jenis_document', 'App\\Models\\Undangan')
            ->pluck('document_id')
            ->toArray();

        $sortBy = $request->get('sort_by', 'tgl_rapat_diff');
        $sortDirection = $request->get('sort_direction', 'asc') === 'asc' ? 'asc' : 'desc';

        $allowedSortColumns = ['created_at', 'tgl_disahkan', 'tgl_dibuat', 'nomor_undangan', 'judul', 'tgl_rapat_diff'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'tgl_rapat_diff';
        }

        $kodeBagianUser = collect(explode(';', (string) $user->kode_bagian))
            ->map(fn ($kode) => trim($kode))
            ->filter()
            ->unique()
            ->values();

        $query = Undangan::whereNotIn('id_undangan', $undanganDiarsipkan)
            ->where(function ($q) use ($kodeBagianUser) {
                foreach ($kodeBagianUser as $kodeBagian) {
                    $q->orWhereRaw(
                        "FIND_IN_SET(?, REPLACE(COALESCE(kode_bagian, ''), ';', ','))",
                        [$kodeBagian]
                    );
                }
            });

        if ((int) $user->role_id_role === 2) {
            $query->where('pembuat', $userId);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('tgl_dibuat_awal') && $request->filled('tgl_dibuat_akhir')) {
            $query->whereBetween('tgl_rapat', [$request->tgl_dibuat_awal, $request->tgl_dibuat_akhir]);
        } elseif ($request->filled('tgl_dibuat_awal')) {
            $query->whereDate('tgl_rapat', '>=', $request->tgl_dibuat_awal);
        } elseif ($request->filled('tgl_dibuat_akhir')) {
            $query->whereDate('tgl_rapat', '<=', $request->tgl_dibuat_akhir);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('judul', 'like', '%' . $request->search . '%')
                    ->orWhere('nomor_undangan', 'like', '%' . $request->search . '%');
            });
        }

        $kode = (clone $query)
            ->whereNotNull('kode')
            ->pluck('kode')
            ->filter()
            ->unique()
            ->values();

        if ($sortBy === 'tgl_rapat_diff') {
            $query->whereNotNull('tgl_rapat')
                ->orderByRaw("
                    CASE
                        WHEN DATEDIFF(tgl_rapat, CURDATE()) < 0 THEN 1
                        ELSE 0
                    END ASC
                ")
                ->orderByRaw("ABS(DATEDIFF(tgl_rapat, CURDATE())) $sortDirection");
        } else {
            $query->orderBy($sortBy, $sortDirection);
        }

        $perPage = $request->get('per_page', 10);
        $undangans = $query->paginate($perPage);

        $undangans->getCollection()->transform(function ($undangan) {
            $undangan->final_status = $undangan->status;
            $undangan->jenis = 'keluar';
            return $undangan;
        });

        return view('undangan.undangan-terkirim', compact(
            'undangans',
            'seri',
            'sortDirection',
            'kode'
        ));
    }

    /**
     *
     */
    public function undanganDiterima(Request $request)
    {
        $seri = Seri::all();
        $user = Auth::user();
        $userId = (int) $user->id;
        $uid = (string) $userId;

        $undanganDiarsipkan = Arsip::where('user_id', $userId)
            ->where('jenis_document', 'App\\Models\\Undangan')
            ->pluck('document_id')
            ->toArray();

        $sortBy = $request->get('sort_by', 'tgl_rapat_diff');
        $sortDirection = $request->get('sort_direction', 'asc') === 'asc' ? 'asc' : 'desc';

        $allowedSortColumns = ['created_at', 'tgl_disahkan', 'tgl_dibuat', 'nomor_undangan', 'judul', 'tgl_rapat_diff'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'tgl_rapat_diff';
        }

        $query = Undangan::whereNotIn('id_undangan', $undanganDiarsipkan)
            ->where('status', 'approve')
            ->where(function ($q) use ($uid) {
                $q->whereRaw("FIND_IN_SET(?, REPLACE(COALESCE(tujuan, ''), ';', ','))", [$uid])
                    ->orWhereRaw("FIND_IN_SET(?, REPLACE(COALESCE(tembusan, ''), ';', ','))", [$uid])
                    ->orWhereRaw("FIND_IN_SET(?, REPLACE(COALESCE(bcc, ''), ';', ','))", [$uid]);
            });

        if ($request->filled('tgl_dibuat_awal') && $request->filled('tgl_dibuat_akhir')) {
            $query->whereBetween('tgl_rapat', [$request->tgl_dibuat_awal, $request->tgl_dibuat_akhir]);
        } elseif ($request->filled('tgl_dibuat_awal')) {
            $query->whereDate('tgl_rapat', '>=', $request->tgl_dibuat_awal);
        } elseif ($request->filled('tgl_dibuat_akhir')) {
            $query->whereDate('tgl_rapat', '<=', $request->tgl_dibuat_akhir);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('judul', 'like', '%' . $request->search . '%')
                    ->orWhere('nomor_undangan', 'like', '%' . $request->search . '%');
            });
        }

        $kode = (clone $query)
            ->whereNotNull('kode')
            ->pluck('kode')
            ->filter()
            ->unique()
            ->values();

        if ($sortBy === 'tgl_rapat_diff') {
            $query->whereNotNull('tgl_rapat')
                ->orderByRaw("
                    CASE
                        WHEN DATEDIFF(tgl_rapat, CURDATE()) < 0 THEN 1
                        ELSE 0
                    END ASC
                ")
                ->orderByRaw("ABS(DATEDIFF(tgl_rapat, CURDATE())) $sortDirection");
        } else {
            $query->orderBy($sortBy, $sortDirection);
        }

        $perPage = $request->get('per_page', 10);
        $undangans = $query->paginate($perPage);

        $undangans->getCollection()->transform(function ($undangan) use ($userId) {
            $undangan->final_status = $undangan->status;
            $undangan->jenis = 'masuk';

            $isTembusan = collect(explode(';', (string) $undangan->tembusan))
                ->map(fn ($item) => trim($item))
                ->filter(fn ($item) => $item !== '')
                ->contains((string) $userId);

            $isBcc = collect(explode(';', (string) $undangan->bcc))
                ->map(fn ($item) => trim($item))
                ->filter(fn ($item) => $item !== '')
                ->contains((string) $userId);

            if ($isTembusan) {
                $undangan->sumber_diterima = 'tembusan';
            } elseif ($isBcc) {
                $undangan->sumber_diterima = 'bcc';
            } else {
                $undangan->sumber_diterima = 'tujuan';
            }

            return $undangan;
        });

        return view('undangan.undangan-diterima', compact(
            'undangans',
            'seri',
            'sortDirection',
            'kode'
        ));
    }



    public function superadmin(Request $request)
    {   //dd('Superadmin Undangan');
        $divisi = Divisi::all();
        $kode = Undangan::whereNotNull('kode')
            ->pluck('kode')
            ->unique();

        $seri = Seri::all();
        $userId = Auth::id();


        $undanganDiarsipkan = Arsip::where('user_id', Auth::id())->where('jenis_document', 'App\Models\Undangan')->pluck('document_id')->toArray();
        $sortBy = $request->get('sort_by', 'created_at'); // default ke created_at
        $sortDirection = $request->get('sort_direction', 'desc') === 'asc' ? 'asc' : 'desc';

        $allowedSortColumns = ['created_at', 'tgl_disahkan', 'tgl_dibuat', 'nomor_undangan', 'judul'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'created_at'; // fallback default
        }

        $query = Undangan::query()
            ->whereNotIn('id_undangan', $undanganDiarsipkan)
            ->orderBy($sortBy, $sortDirection);

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

        // Ambil semua arsip undangan berdasarkan user login
        $arsipUndanganQuery = Arsip::where('user_id', $userId)
            ->where('jenis_document', 'undangan')
            ->with('document');

        $sortDirection = $request->get('sort_direction', 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy('created_at', $sortDirection);

        if ($request->filled('kode') && $request->kode != 'pilih') {
            $query->where('kode', $request->kode);
        }

        // Pencarian berdasarkan nama dokumen atau nomor memo
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('judul', 'like', '%' . $request->search . '%')
                    ->orWhere('nomor_undangan', 'like', '%' . $request->search . '%');
            });
        }
        //dd($request->all(), $query->toSql(), $query->getBindings());
        $perPage = $request->get('per_page', 10); // Default ke 10 jika tidak ada input
        $undangans = $query->paginate($perPage);


        return view('superadmin.undangan.index', compact('undangans', 'kode', 'seri', 'sortDirection'));
    }


    // public function nextSeri()
    // {
    //     $user = Auth::user();
    //     if ($user->position_id_position == 1) {
    //         $idDirektur = Director::where('id_director', $user->director_id_director)->first();
    //         $kodeDirektur = $idDirektur->kode_director;
    //     } else {
    //         $kodeDirektur = '';
    //     }
    //     // dd($user);

    //     $divDeptKode = $this->getDivDeptKode($user);

    //     // Ambil nomor seri berikutnya
    //     $nextSeri = Seri::getNextSeri(false);
    //     // Konversi bulan ke angka Romawi
    //     $bulanRomawi = $this->convertToRoman(now()->month);
    //     // Format nomor dokumen
    //     $nomorDokumen = sprintf(
    //         "%02d.%02d/REKA%s/GEN/%s/%s/%d",
    //         $nextSeri['seri_tahunan'],
    //         $nextSeri['seri_bulanan'],
    //         strtoupper($kodeDirektur),
    //         strtoupper($divDeptKode),
    //         $bulanRomawi,
    //         now()->year
    //     );

    //     return $nomorDokumen;
    // }

    public function nextSeri(Request $request)
    {
        // Validasi input seri dan nomor surat manual
        $request->validate([
            'seri_surat' => 'required|string|max:50',
            'nomor_surat' => 'required|string|max:100',
        ], [
            'seri_surat.required' => 'Seri surat wajib diisi.',
            'nomor_surat.required' => 'Nomor surat wajib diisi.',
        ]);

        // Ambil input dari user
        $seriSurat = $request->input('seri_surat');
        $nomorMemo = $request->input('nomor_Memo');

        // Kembalikan hasilnya agar bisa dipakai di tempat lain
        return [
            'seri_surat' => $seriSurat,
            'nomor_Memo' => $nomorMemo,
        ];
    }


    public function create()
    {

        $divisiList = Divisi::all();

        $idUser = Auth::user();
        $user = User::where('id', $idUser->id)->first();

        if ($user->position_id_position == 1) {
            $idDirektur = Director::where('id_director', $user->director_id_director)->first();
            $kodeDirektur = $idDirektur->kode_director;
        } else {
            $kodeDirektur = '';
        }
        // dd($user);

        $divDeptKode = $this->getDivDeptKode($user);

        // Daftar bagian kerja
        $bagianKerja = BagianKerja::orderBy('kode_bagian')->get();

        // Ambil nomor seri berikutnya
        $nextSeri = Seri::getNextSeri(false);
        // Konversi bulan ke angka Romawi
        $bulanRomawi = $this->convertToRoman(now()->month);
        // Format nomor dokumen
        $nomorDokumen = sprintf(
            "%02d.%02d/REKA/GEN/%s/%s/%d",
            $nextSeri['seri_tahunan'],
            $nextSeri['seri_bulanan'],
            strtoupper($divDeptKode),
            $bulanRomawi,
            now()->year
        );

        //Mengambil data manager yang bertanda tangan nanti
        $user = Auth::user();

        //ini di cek berdasarkan role manager kemudian dicari yang cocok dengan yang login dan ditampilkan di dropdown
        if ($user->position_id_position !== 1) {
            $managers = User::with('position:id_position,nm_position')
                ->where('role_id_role', 3)
                // ->where('position_id_position', '!=', 9)
                ->where(function ($q) use ($user) {
                    $q->where(function ($q2) use ($user) {
                        $q2->whereNotNull('divisi_id_divisi')
                            ->where('divisi_id_divisi', $user->divisi_id_divisi);
                    })->orWhere(function ($q2) use ($user) {
                        $q2->whereNotNull('department_id_department')
                            ->where('department_id_department', $user->department_id_department);
                    })->orWhere(function ($q2) use ($user) {
                        $q2->whereNotNull('section_id_section')
                            ->where('section_id_section', $user->section_id_section);
                    })->orWhere(function ($q2) use ($user) {
                        $q2->whereNotNull('unit_id_unit')
                            ->where('unit_id_unit', $user->unit_id_unit);
                    });
                })
                ->get(['id', 'firstname', 'lastname', 'position_id_position']);
        } else
            $managers = collect([
                User::with('position:id_position,nm_position')
                    ->find($user->id, ['id', 'firstname', 'lastname', 'position_id_position'])
            ]);

        // Ambil seluruh user dan struktur organisasi (untuk dropdown tree)
        $users = User::select('id', 'firstname', 'lastname', 'divisi_id_divisi', 'department_id_department', 'section_id_section', 'unit_id_unit')->get();
        // Struktur organisasi tree (harus dibuat di backend, contoh dummy di bawah)
        $orgTree = $this->getOrgTreeWithUsers();
        $jsTreeData = $this->convertToJsTree($orgTree);

        $mainDirector = $orgTree[0] ?? null; // assuming the first node is the main director
        // dd($orgTree, $jsTreeData);

        return view('undangan.add-coba', [
            'nomorSeriTahunan' => $nextSeri['seri_tahunan'],
            'nomorDokumen' => $nomorDokumen,
            'kode' => $divDeptKode,
            'kode_bagian' => $bagianKerja,
            'managers' => $managers,
            'divisiList' => $divisiList,
            'users' => $users,
            'orgTree' => $orgTree,
            'jsTreeData' => $jsTreeData,
            'mainDirector' => $mainDirector
        ], compact('bagianKerja'));
    }

    //PRIVATE FUNCTIONS UNTUK BUAT TREE TUJUAN[]
    private function getOrgTreeWithUsers()
    {
        $directors = Director::with([
            'users.position',
            'divisi.users.position',
            'divisi.department.users.position',
            'divisi.department.section.users.position',
            'divisi.department.section.unit.users.position',
            'department.users.position',
            'department.section.users.position',
            'department.section.unit.users.position'
        ])->get();


        $tree = [];

        foreach ($directors as $director) {
            $dir = $director->toArray();
            $dir['users'] = $director->users->toArray();
            $tree[] = $dir;
        }
        return $tree;
    }
    private function filterUsersAtLevel($users, $level)
    {
        return array_values(array_filter($users, function ($user) use ($level) {
            return (
                ($level === 'director' && is_null($user['divisi_id_divisi']) && is_null($user['department_id_department']) && is_null($user['section_id_section']) && is_null($user['unit_id_unit'])) ||
                ($level === 'divisi' && !is_null($user['divisi_id_divisi']) && is_null($user['department_id_department']) && is_null($user['section_id_section']) && is_null($user['unit_id_unit'])) ||
                ($level === 'department' && !is_null($user['department_id_department']) && is_null($user['section_id_section']) && is_null($user['unit_id_unit'])) ||
                ($level === 'section' && !is_null($user['section_id_section']) && is_null($user['unit_id_unit'])) ||
                ($level === 'unit' && !is_null($user['unit_id_unit']))
            );
        }));
    }

    private function getUserText($user, $context)
    {
        $rawPosition = isset($user['position']['nm_position']) ? $user['position']['nm_position'] : '-';

        // Format position - remove parentheses and create abbreviations
        if ($rawPosition !== '-') {
            // Remove parentheses and content inside them, then clean up extra spaces
            $position = preg_replace('/\s*\([^)]*\)\s*/', ' ', $rawPosition);
            $position = trim(preg_replace('/\s+/', ' ', $position));

            // Create abbreviations for common positions
            if (!in_array($position, ['Staff', 'Direktur'])) {
                $abbreviations = [
                    'Penanggung Jawab Senior Manager' => 'PJ SM',
                    'Penanggung Jawab Manager' => 'PJ M',
                    'Penanggung Jawab Supervisor' => 'PJ SPV',
                    'Senior Manager' => 'SM',
                    'General Manager' => 'GM',
                    'Manager' => 'M',
                    'Supervisor' => 'SPV'
                ];

                foreach ($abbreviations as $full => $abbrev) {
                    if (strpos($position, $full) !== false) {
                        $position = str_replace($full, $abbrev, $position);
                        break;
                    }
                }
            }
        } else {
            $position = '-';
        }

        $hierarki = collect([
            $context['unit'] ?? null,
            $context['section'] ?? null,
            $context['department'] ?? null,
            $context['divisi'] ?? null,
            $context['director'] ?? null
        ])->filter()->first() ?? '-';

        $firstname = $user['firstname'] ?? ($user['nm_user'] ?? '-');
        $lastname = $user['lastname'] ?? '';

        return "$position $hierarki ($firstname $lastname)";
    }

    private function convertToJsTree($tree)
    {
        $result = [];


        foreach ($tree as $director) {
            $dirNode = [
                'id' => 'director-' . ($director['id_director'] ?? ''),
                'text' => $director['name_director'] ?? 'Director',
                'children' => []
            ];

            // users at director
            $usersAtDirector = $this->filterUsersAtLevel($director['users'] ?? [], 'director');
            foreach ($usersAtDirector as $user) {
                $dirNode['children'][] = [
                    'id' => 'user-' . $user['id'],
                    'text' => $this->getUserText($user, ['director' => $dirNode['text']]),
                    'icon' => 'fa fa-user'
                ];
            }
            $addedDepartments = [];

            foreach ($director['divisi'] ?? [] as $divisi) {
                $divName = $divisi['nm_divisi'] ?? ($divisi['name_divisi'] ?? 'Divisi');
                $divNode = [
                    'id' => 'divisi-' . ($divisi['id_divisi'] ?? ''),
                    'text' => $divName,
                    'children' => []
                ];

                // divisi users
                $usersAtDivisi = $this->filterUsersAtLevel($divisi['users'] ?? [], 'divisi');
                foreach ($usersAtDivisi as $user) {
                    $divNode['children'][] = [
                        'id' => 'user-' . $user['id'],
                        'text' => $this->getUserText($user, [
                            'director' => $dirNode['text'],
                            'divisi' => $divName
                        ]),
                        'icon' => 'fa fa-user'
                    ];
                }

                // departments inside divisi
                foreach ($divisi['department'] ?? [] as $dept) {
                    $deptId = $dept['id_department'] ?? null;
                    if (!$deptId || in_array($deptId, $addedDepartments)) {
                        continue; // skip duplicates
                    }

                    $divNode['children'][] = $this->buildDeptNode($dept, [
                        'director' => $dirNode['text'],
                        'divisi' => $divName
                    ]);
                    $addedDepartments[] = $deptId;
                }

                $dirNode['children'][] = $divNode;
            }

            // 1) Always add departments directly under the director (if any)
            foreach ($director['department'] ?? [] as $dept) {
                $deptId = $dept['id_department'] ?? null;
                if (!$deptId || in_array($deptId, $addedDepartments)) {
                    continue; // skip duplicates
                }
                $dirNode['children'][] = $this->buildDeptNode($dept, [
                    'director' => $dirNode['text']
                ]);
                $addedDepartments[] = $deptId;
            }

            // 2) Then add divisions (if any) and their departments


            $result[] = $dirNode;
        }

        return json_encode($result);
    }

    /**
     * Helper: build department -> sections -> units -> users
     * $ctx is an array containing names to include in user text (director/divisi/etc).
     */
    private function buildDeptNode(array $dept, array $ctx = [])
    {
        $deptName = $dept['name_department'] ?? ($dept['nm_department'] ?? 'Department');
        $deptNode = [
            'id' => 'dept-' . ($dept['id_department'] ?? ''),
            'text' => $deptName,
            'children' => []
        ];

        // users at department
        $usersAtDepartment = $this->filterUsersAtLevel($dept['users'] ?? [], 'department');
        foreach ($usersAtDepartment as $user) {
            $deptNode['children'][] = [
                'id' => 'user-' . $user['id'],
                'text' => $this->getUserText($user, array_merge($ctx, ['department' => $deptName])),
                'icon' => 'fa fa-user'
            ];
        }

        // sections -> units
        foreach ($dept['section'] ?? [] as $section) {
            $sectionName = $section['name_section'] ?? 'Section';
            $sectionNode = [
                'id' => 'section-' . ($section['id_section'] ?? ''),
                'text' => $sectionName,
                'children' => []
            ];

            $usersAtSection = $this->filterUsersAtLevel($section['users'] ?? [], 'section');
            foreach ($usersAtSection as $user) {
                $sectionNode['children'][] = [
                    'id' => 'user-' . $user['id'],
                    'text' => $this->getUserText($user, array_merge($ctx, [
                        'department' => $deptName,
                        'section' => $sectionName
                    ])),
                    'icon' => 'fa fa-user'
                ];
            }

            foreach ($section['unit'] ?? [] as $unit) {
                $unitName = $unit['name_unit'] ?? 'Unit';
                $unitNode = [
                    'id' => 'unit-' . ($unit['id_unit'] ?? ''),
                    'text' => $unitName,
                    'children' => []
                ];

                $usersAtUnit = $this->filterUsersAtLevel($unit['users'] ?? [], 'unit');
                foreach ($usersAtUnit as $user) {
                    $unitNode['children'][] = [
                        'id' => 'user-' . $user['id'],
                        'text' => $this->getUserText($user, array_merge($ctx, [
                            'department' => $deptName,
                            'section' => $sectionName,
                            'unit' => $unitName
                        ])),
                        'icon' => 'fa fa-user'
                    ];
                }

                $sectionNode['children'][] = $unitNode;
            }

            $deptNode['children'][] = $sectionNode;
        }

        return $deptNode;
    }

    public function getDivDeptKode($user)
    {
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

        return ($divisiName);
    }
    private function containsEmoji($text)
    {
        if (empty($text)) return false;

        // Regex pattern untuk detect emoji
        $emojiPattern = '/[\x{1F600}-\x{1F64F}]|[\x{1F300}-\x{1F5FF}]|[\x{1F680}-\x{1F6FF}]|[\x{1F1E0}-\x{1F1FF}]|[\x{2600}-\x{26FF}]|[\x{2700}-\x{27BF}]/u';

        // Pattern tambahan untuk emoji lainnya
        $additionalEmojiPattern = '/[\x{1F900}-\x{1F9FF}]|[\x{1FA70}-\x{1FAFF}]|[\x{1F780}-\x{1F7FF}]|[\x{1F800}-\x{1F8FF}]/u';

        return preg_match($emojiPattern, $text) || preg_match($additionalEmojiPattern, $text);
    }

    // Method helper untuk validasi emoji pada field tertentu
    private function validateNoEmoji($request)
    {
        $fieldsToCheck = ['judul', 'waktu_mulai', 'waktu_selesai', 'tempat'];
        $errors = [];

        foreach ($fieldsToCheck as $field) {
            if ($request->filled($field) && $this->containsEmoji($request->input($field))) {
                $fieldName = $this->getFieldDisplayName($field);
                $errors[$field] = "Field {$fieldName} tidak boleh mengandung emoji.";
            }
        }

        return $errors;
    }

    // Helper untuk nama field yang user-friendly sesuai label di form
    private function getFieldDisplayName($field)
    {
        $names = [
            'judul' => 'Judul',
            'waktu_mulai' => 'Waktu Mulai',
            'waktu_selesai' => 'Waktu Selesai',
            'tempat' => 'Tempat'
        ];

        return $names[$field] ?? ucfirst($field);
    }


    private function convertTujuanToUserId(array $rawTujuan)
    {
        $departments = [];
        $sections = [];
        $divisions = [];
        $units = [];
        $users = [];

        foreach ($rawTujuan as $item) {
            if (is_numeric($item)) {
                $users[] = (int) $item;
                continue;
            }

            if (Str::startsWith($item, 'dept-')) {
                $departments[] = (int) Str::after($item, 'dept-');
            } elseif (Str::startsWith($item, 'section-')) {
                $sections[] = (int) Str::after($item, 'section-');
            } elseif (Str::startsWith($item, 'divisi-')) {
                $divisions[] = (int) Str::after($item, 'divisi-');
            } elseif (Str::startsWith($item, 'unit-')) {
                $units[] = (int) Str::after($item, 'unit-');
            } elseif (Str::startsWith($item, 'user-')) {
                $users[] = (int) Str::after($item, 'user-');
            }
        }

        return User::where(function ($query) use ($departments, $sections, $divisions, $units, $users) {
            if (!empty($departments)) {
                $query->orWhereIn('department_id_department', $departments);
            }
            if (!empty($sections)) {
                $query->orWhereIn('section_id_section', $sections);
            }
            if (!empty($divisions)) {
                $query->orWhereIn('divisi_id_divisi', $divisions);
            }
            if (!empty($units)) {
                $query->orWhereIn('unit_id_unit', $units);
            }
            if (!empty($users)) {
                $query->orWhereIn('id', $users);
            }
        })->pluck('id')->toArray();
    }

    // private function parseRecipientUserIds(?string $raw): array
    // {
    //     if (!$raw) {
    //         return [];
    //     }

    //     return collect(explode(';', $raw))
    //         ->map(fn($v) => trim($v))
    //         ->filter(fn($v) => $v !== '' && is_numeric($v))
    //         ->map(fn($v) => (int) $v)
    //         ->unique()
    //         ->values()
    //         ->all();
    // }

    private function resolveApproverUserId(Undangan $undangan, ?Request $request = null): ?int
    {
        if ($request && $request->filled('manager_user_id')) {
            $candidate = (int) $request->manager_user_id;
            if ($candidate > 0) {
                return $candidate;
            }
        }

        if (!empty($undangan->manager_user_id)) {
            $candidate = (int) $undangan->manager_user_id;
            if ($candidate > 0) {
                return $candidate;
            }
        }

        if (!empty($undangan->nama_bertandatangan)) {
            $penandatangan = User::findByFullname((string) $undangan->nama_bertandatangan);
            if ($penandatangan) {
                return (int) $penandatangan->id;
            }
        }

        return null;
    }

    private function getWorkflowNotificationRecipients(Undangan $undangan, ?Request $request = null): array
    {
        return collect([
            (int) $undangan->pembuat,
            $this->resolveApproverUserId($undangan, $request),
        ])
            ->map(fn($v) => is_numeric($v) ? (int) $v : null)
            ->filter(fn($v) => !is_null($v) && $v > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function getFinalNotificationRecipients(Undangan $undangan, ?int $approverId = null): array
    {
        $tujuanIds = is_array($undangan->tujuan)
            ? $undangan->tujuan
            : explode(';', (string) $undangan->tujuan);

        return collect(array_merge(
            [(int) $undangan->pembuat, $approverId],
            $tujuanIds,
            $this->parseRecipientUserIds($undangan->tembusan ?? null),
            $this->parseRecipientUserIds($undangan->bcc ?? null),
        ))
            ->map(fn($v) => is_numeric($v) ? (int) $v : null)
            ->filter(fn($v) => !is_null($v) && $v > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function dispatchUndanganNotification(int $userId, string $judul, Undangan $undangan): void
    {
        app(NotifService::class)->createAndPush(
            $userId,
            $judul,
            $undangan->judul,
            (int) $undangan->id_undangan,
            'undangan'
        );
    }

    private function dispatchUndanganNotifications(array $recipientIds, string $judul, Undangan $undangan): void
    {
        foreach (collect($recipientIds)
            ->map(fn($id) => is_numeric($id) ? (int) $id : null)
            ->filter(fn($id) => !is_null($id) && $id > 0)
            ->unique()
            ->values() as $recipientId) {
            $this->dispatchUndanganNotification((int) $recipientId, $judul, $undangan);
        }
    }

    // private function buildGroupedRecipientDisplayList(array $selectedUserIds): array
    // {
    //     if (empty($selectedUserIds)) {
    //         return [];
    //     }

    //     $selectedUsers = User::with(['position:id_position,nm_position'])
    //         ->whereIn('id', $selectedUserIds)
    //         ->get([
    //             'id',
    //             'firstname',
    //             'lastname',
    //             'position_id_position',
    //             'director_id_director',
    //             'divisi_id_divisi',
    //             'department_id_department',
    //             'section_id_section',
    //             'unit_id_unit',
    //         ]);

    //     if ($selectedUsers->isEmpty()) {
    //         return [];
    //     }

    //     $selectedIdSet = $selectedUsers->pluck('id')->flip();
    //     $remainingIds = $selectedUsers->pluck('id')->all();

    //     $directorMap = Director::pluck('name_director', 'id_director');
    //     $divisionMap = Divisi::pluck('nm_divisi', 'id_divisi');
    //     $departmentMap = Department::pluck('name_department', 'id_department');
    //     $sectionMap = Section::pluck('name_section', 'id_section');
    //     $unitMap = Unit::pluck('name_unit', 'id_unit');

    //     $result = [];
    //     $scopes = [
    //         ['col' => 'director_id_director', 'label' => 'Direktur', 'map' => $directorMap],
    //         ['col' => 'divisi_id_divisi', 'label' => 'Divisi', 'map' => $divisionMap],
    //         ['col' => 'department_id_department', 'label' => 'Departemen', 'map' => $departmentMap],
    //         ['col' => 'section_id_section', 'label' => 'Bagian', 'map' => $sectionMap],
    //         ['col' => 'unit_id_unit', 'label' => 'Unit', 'map' => $unitMap],
    //     ];

    //     foreach ($scopes as $scope) {
    //         $groupIds = $selectedUsers
    //             ->whereIn('id', $remainingIds)
    //             ->pluck($scope['col'])
    //             ->filter()
    //             ->unique()
    //             ->values();

    //         foreach ($groupIds as $groupId) {
    //             $allMemberIds = User::where($scope['col'], $groupId)->pluck('id');
    //             if ($allMemberIds->isEmpty()) {
    //                 continue;
    //             }

    //             $allSelected = $allMemberIds->every(fn($memberId) => $selectedIdSet->has($memberId));
    //             if ($allSelected) {
    //                 $scopeName = $scope['map'][$groupId] ?? ('ID ' . $groupId);
    //                 $result[] = $scope['label'] . ': ' . $scopeName;
    //                 $remainingIds = array_values(array_diff($remainingIds, $allMemberIds->all()));
    //             }
    //         }
    //     }

    //     foreach ($selectedUsers->whereIn('id', $remainingIds) as $user) {
    //         $fullName = trim(($user->firstname ?? '') . ' ' . ($user->lastname ?? ''));
    //         $positionName = $user->position->nm_position ?? '-';

    //         $bagianKerja = '-';
    //         if (!empty($user->unit_id_unit) && isset($unitMap[$user->unit_id_unit])) {
    //             $bagianKerja = $unitMap[$user->unit_id_unit];
    //         } elseif (!empty($user->section_id_section) && isset($sectionMap[$user->section_id_section])) {
    //             $bagianKerja = $sectionMap[$user->section_id_section];
    //         } elseif (!empty($user->department_id_department) && isset($departmentMap[$user->department_id_department])) {
    //             $bagianKerja = $departmentMap[$user->department_id_department];
    //         } elseif (!empty($user->divisi_id_divisi) && isset($divisionMap[$user->divisi_id_divisi])) {
    //             $bagianKerja = $divisionMap[$user->divisi_id_divisi];
    //         } elseif (!empty($user->director_id_director) && isset($directorMap[$user->director_id_director])) {
    //             $bagianKerja = $directorMap[$user->director_id_director];
    //         }

    //         $positionClean = preg_replace('/^\s*\([^)]*\)\s*/', '', $positionName) ?: $positionName;
    //         $result[] = $fullName . ' - ' . $bagianKerja . ' (' . $positionClean . ')';
    //     }

    //     return array_values(array_filter($result));
    // }

    public function store(Request $request)
    {
        $emojiErrors = $this->validateNoEmoji($request);
        if (!empty($emojiErrors)) {
            return redirect()->back()
                ->withErrors($emojiErrors)
                ->withInput();
        }

        $validator = Validator::make($request->all(), [
            'judul' => 'required|string|max:255',
            'isi_undangan' => 'required|string',
            'tujuan' => 'required|array|min:1',
            'nomor_undangan' => 'nullable|string',
            'kode_bagian' => 'required|exists:bagian_kerja,kode_bagian',
            'manager_user_id' => 'required|exists:users,id',
            'pembuat' => 'required|string',
            'catatan' => 'nullable|string|max:255',
            'tgl_dibuat' => 'required|date',
            'tgl_disahkan' => 'nullable|date',
            'tgl_rapat' => 'required|date',
            'tempat' => 'required|string',
            'waktu_mulai' => 'required|string',
            'waktu_selesai' => 'required|string',
            'lampiran' => 'nullable',
            'lampiran.*' => 'file|mimes:pdf,jpg,jpeg,png|max:2048',
        ], [
            'tgl_dibuat.required' => 'Tanggal dibuat wajib diisi.',
            'tujuan.required' => 'Minimal satu tujuan harus dipilih.',
            'kode_bagian.required' => 'Bagian kerja wajib dipilih.',
            'kode_bagian.exists' => 'Bagian kerja tidak valid.',
            'lampiran.*' => 'Lampiran gagal diunggah. Pastikan format dan ukuran file sesuai ketentuan.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }

        // Proses lampiran
        $lampiranPath = null;
        if ($request->hasFile('lampiran')) {
            $newFiles = [];
            foreach ($request->file('lampiran') as $file) {
                if ($file->isValid()) {
                    $ext = strtolower($file->getClientOriginalExtension());
                    $folder = $ext === 'pdf' ? 'lampiran/undangan/pdf' :
                            (in_array($ext, ['png', 'jpg', 'jpeg']) ? 'lampiran/undangan/image' : 'lampiran/undangan/other');

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
            $lampiranPath = !empty($newFiles) ? json_encode($newFiles) : null;
        }

        $tujuanRaw = is_array($request->tujuan) ? $request->tujuan : explode(';', $request->tujuan);
        $tujuanIds = $this->convertTujuanToUserId($tujuanRaw);

        $tembusanIds = [];
        if ($request->has('tembusan') && is_array($request->tembusan)) {
            $tembusanIds = $this->convertTujuanToUserId($request->tembusan);
        }

        $bccIds = [];
        if ($request->has('bcc') && is_array($request->bcc)) {
            $bccIds = $this->convertTujuanToUserId($request->bcc);
        }

        $tujuanString = implode(';', $tujuanIds);

        $manager = User::findOrFail($request->input('manager_user_id'));

        // Create undangan
        $undangan = Undangan::create([
            'judul' => $request->input('judul'),
            'tujuan' => $tujuanString,
            'kode_bagian' => $request->input('kode_bagian'), // ✅ SIMPAN
            'isi_undangan' => $request->input('isi_undangan'),
            'seri_surat' => null, // ← Belum ada seri
            'nomor_undangan' => null, // ✅ KOSONG hingga approve
            'tgl_dibuat' => $request->input('tgl_dibuat'),
            'tgl_disahkan' => null, // ← Belum disahkan
            'pembuat' => $request->input('pembuat'),
            'catatan' => $request->input('catatan'),
            'kode' => $request->input('kode'),
            'status' => 'pending',
            'tgl_rapat' => $request->input('tgl_rapat'),
            'tempat' => $request->input('tempat'),
            'waktu_mulai' => $request->input('waktu_mulai'),
            'waktu_selesai' => $request->input('waktu_selesai'),
            'tembusan' => !empty($tembusanIds) ? implode(';', $tembusanIds) : null,
            'bcc' => !empty($bccIds) ? implode(';', $bccIds) : null,
            // 'nama_bertandatangan' => $manager->firstname . ' ' . $manager->lastname,
            'nama_bertandatangan' => trim($manager->firstname . ' ' . $manager->lastname),
            'manager_user_id' => (int) $manager->id,
            'lampiran' => $lampiranPath,
        ]);

        // Kirim ke manager untuk approval
        $creator = Auth::user();

        Kirim_Document::create([
            'id_document' => $undangan->id_undangan,
            'jenis_document' => 'undangan',
            'id_pengirim' => $creator->id,
            'id_penerima' => $manager->id,
            'status' => 'pending',
            'updated_at' => now(),
        ]);

        $approverId = $this->resolveApproverUserId($undangan, $request);
        $workflowRecipients = $this->getWorkflowNotificationRecipients($undangan, $request);
        foreach ($workflowRecipients as $recipientId) {
            $judulNotif = ((int) $recipientId === (int) $approverId)
                ? 'Undangan Menunggu Persetujuan'
                : 'Undangan Dalam Proses Persetujuan';
            $this->dispatchUndanganNotification((int) $recipientId, $judulNotif, $undangan);
        }

        if (Auth::user()->role_id_role == 3) {
            return redirect()->route('undangan.terkirim')
                ->with('success', 'Undangan berhasil dibuat dan menunggu persetujuan. Nomor surat akan diberikan setelah disetujui.');
        }

        return redirect()->route('undangan.terkirim')
            ->with('success', 'Undangan berhasil dibuat dan menunggu persetujuan. Nomor surat akan diberikan setelah disetujui.');
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
        return $map[$number];
    }

    public function updateDocumentStatus(Request $request, $id)
    {
        $undangan = Undangan::findOrFail($id);
        $userId = Auth::id();

        // Validasi input dinamis
        $rules = [
            'status' => 'required|in:approve,reject,correction',
            'catatan' => 'nullable|string',
        ];
        $messages = [];

        if (in_array($request->status, ['reject', 'correction'])) {
            $rules['catatan'] = 'required|string';
            $messages['catatan.required'] = 'Catatan wajib diisi jika undangan ditolak atau dikoreksi.';
        }

        $request->validate($rules, $messages);

        // Update status
        $undangan->status = $request->status;

        // Update kirim_document
        $currentKirim = Kirim_Document::where('id_document', $id)
            ->where('jenis_document', 'undangan')
            ->where('id_penerima', $userId)
            ->first();

        if ($currentKirim) {
            $currentKirim->status = $request->status;
            $currentKirim->updated_at = now();
            $currentKirim->save();
        }

        if ($request->status == 'approve') {
            $tglDisahkan = now();
            $undangan->tgl_disahkan = $tglDisahkan;

            // ===================================================================
            // 🎯 GENERATE NOMOR SURAT OTOMATIS MENGGUNAKAN CounterNomorSurat
            // ===================================================================
            // ✅ Cek apakah nomor masih kosong (NULL atau empty)
            if (empty($undangan->nomor_undangan)) {

                $bulanRomawi = \App\Models\CounterNomorSurat::getBulanRomawi(now()->month);

                try {
                    // Generate nomor surat otomatis
                    $counter = \App\Models\CounterNomorSurat::createNomorSurat([
                        'tanggal_permintaan' => $tglDisahkan,
                        'perusahaan' => 'REKA',
                        'kode_tipe_surat' => 'GEN',
                        'divisi' => $undangan->kode_bagian ?? $this->getDivDeptKode(Auth::user()),
                        'bulan' => $bulanRomawi,
                        'tahun' => $tglDisahkan->year,
                        'pic_peminta' => Auth::user()->fullname,
                        'jenis' => 'Undangan',
                        'perihal' => $undangan->judul,
                    ]);

                    // Set nomor surat yang sudah di-generate
                    $undangan->nomor_undangan = $counter->nomor_surat_generated;

                } catch (\Exception $e) {
                    // Log error dan gunakan fallback
                    \Illuminate\Support\Facades\Log::error('Generate nomor undangan gagal: ' . $e->getMessage());

                    // Fallback ke format lama
                    $divDeptKode = $this->getDivDeptKode(Auth::user());
                    $nextSeri = \App\Models\Seri::getNextSeri(false);
                    $undangan->nomor_undangan = sprintf(
                        '%02d.%02d/REKA/GEN/%s/%s/%d',
                        $nextSeri['seri_tahunan'],
                        $nextSeri['seri_bulanan'],
                        strtoupper($divDeptKode),
                        $bulanRomawi,
                        now()->year
                    );
                }
            }

            // Generate QR Code dengan nomor surat yang baru
            $qrText = "Disetujui oleh: " . Auth::user()->firstname . ' ' . Auth::user()->lastname
                . "\nNomor Undangan: " . ($undangan->nomor_undangan ?? '-')
                . "\nTanggal: " . $tglDisahkan->translatedFormat('l, d F Y H:i:s')
                . "\nDikeluarkan oleh website SIPO PT Rekaindo Global Jasa";

            $qrService = new QrCodeService();

            try {
                $qrBase64 = $qrService->generateWithLogo($qrText);
                $undangan->qr_approved_by = $qrBase64;
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Generate QR Code gagal: ' . $e->getMessage());
            }

            // Kirim ke seluruh penerima undangan (tujuan + tembusan + bcc)
            $approverId = $this->resolveApproverUserId($undangan);
            $finalRecipients = $this->getFinalNotificationRecipients($undangan, $approverId);
            $workflowRecipients = $this->getWorkflowNotificationRecipients($undangan);
            $distributionRecipients = collect($finalRecipients)
                ->reject(fn($recipientId) => in_array((int) $recipientId, $workflowRecipients, true))
                ->values();

            $tujuanUserId = is_array($undangan->tujuan) ? $undangan->tujuan : explode(';', (string) $undangan->tujuan);
            $tembusanUserId = $this->parseRecipientUserIds($undangan->tembusan ?? null);
            $bccUserId = $this->parseRecipientUserIds($undangan->bcc ?? null);
            $allRecipientIds = collect(array_merge($tujuanUserId, $tembusanUserId, $bccUserId))
                ->map(fn($v) => is_numeric($v) ? (int) $v : null)
                ->filter(fn($v) => !is_null($v) && $v > 0)
                ->unique()
                ->values();

            foreach ($allRecipientIds as $user) {
                if ((int) $user === (int) $undangan->pembuat) continue;

                $sudahDikirim = Kirim_Document::where([
                    ['id_document', $undangan->id_undangan],
                    ['jenis_document', 'undangan'],
                    ['id_pengirim', $undangan->pembuat],
                    ['id_penerima', $user],
                    ['status', 'approve'],
                ])->exists();

                if (!$sudahDikirim) {
                    Kirim_Document::create([
                        'id_document' => $undangan->id_undangan,
                        'jenis_document' => 'undangan',
                        'id_pengirim' => $undangan->pembuat,
                        'id_penerima' => $user,
                        'status' => 'approve',
                        'updated_at' => now(),
                    ]);
                }

                if ($distributionRecipients->contains((int) $user)) {
                    $this->dispatchUndanganNotification((int) $user, 'Undangan Masuk', $undangan);
                }
            }
            foreach ($workflowRecipients as $recipientId) {
                $this->dispatchUndanganNotification((int) $recipientId, 'Undangan Disetujui', $undangan);
            }

        } elseif ($request->status == 'reject') {
            $undangan->tgl_disahkan = now();
            $this->dispatchUndanganNotifications(
                $this->getWorkflowNotificationRecipients($undangan),
                'Undangan Ditolak',
                $undangan
            );

        } elseif ($request->status == 'correction') {
            $this->dispatchUndanganNotifications(
                $this->getWorkflowNotificationRecipients($undangan),
                'Undangan Perlu Dikoreksi',
                $undangan
            );

        } else {
            $undangan->tgl_disahkan = null;
        }

        $undangan->catatan = $request->catatan;
        $undangan->save();

        if (Auth::user()->role_id_role == 3) {
            return redirect()->route('undangan.terkirim')
                ->with('success', 'Status undangan berhasil diperbarui.');
        }

        return redirect()->route('undangan.' . Auth::user()->role->nm_role)
            ->with('success', 'Status undangan berhasil diperbarui.');
    }


    public function updateDocumentApprovalDate(Undangan $undangan)
    {
        if ($undangan->status !== 'pending') {
            $undangan->update(['tanggal_disahkan' => now()]);
        }
    }
    public function approve(Undangan $undangan)
    {
        $undangan->update([
            'status' => 'approve',
            'tanggal_disahkan' => now() // Set tanggal disahkan
        ]);

        return redirect()->back()->with('success', 'Dokumen berhasil disetujui.');
    }
    public function reject(Undangan $undangan)
    {
        $undangan->update([
            'status' => 'reject',
            'tanggal_disahkan' => now() // Set tanggal disahkan
        ]);

        return redirect()->back()->with('error', 'Dokumen ditolak.');
    }
    public function edit($id)
    {
        $undangan = Undangan::findOrFail($id);
        $divisi = Divisi::all();
        $seri = Seri::all();
        $user = Auth::user();
        $managers = User::where('role_id_role', 3)
            ->where(function ($q) use ($user) {
                $q->where('divisi_id_divisi', $user->divisi_id_divisi)
                    ->orWhere('department_id_department', $user->department_id_department);
                // ->orWhere('section_id_section', $user->section_id_section);
            })
            ->get(['id', 'firstname', 'lastname']);

        $orgTree = $this->getOrgTreeWithUsers();
        $jsTreeDataJson = $this->convertToJsTree($orgTree); // hasilnya string JSON
        $jsTreeData = json_decode($jsTreeDataJson, true);   // decode khusus edit()
        $mainDirector = $orgTree[0] ?? null;


        // kode_bagian
        $bagianKerja = BagianKerja::orderBy('kode_bagian')->get();

        $tujuanArray = [];
        if (!empty($undangan->tujuan)) {
            $tujuanArray = explode(';', $undangan->tujuan);
        }

        // Parse lampiran data jika ada
        $lampiranData = [];
        if ($undangan->lampiran) {
            // Coba decode sebagai JSON dulu (untuk data baru)
            $jsonData = json_decode($undangan->lampiran, true);
            if ($jsonData !== null && is_array($jsonData)) {
                $lampiranData = $jsonData;
            } else {
                // Jika bukan JSON, ini kemungkinan data BLOB lama - skip untuk sekarang
                // atau bisa dikasih placeholder jika memang ada file
                $lampiranData = [];
            }
        }

        $selectedTembusan = collect($this->parseRecipientUserIds($undangan->tembusan ?? null))
            ->map(fn($id) => 'user-' . $id)
            ->values()
            ->all();

        $selectedBcc = collect($this->parseRecipientUserIds($undangan->bcc ?? null))
            ->map(fn($id) => 'user-' . $id)
            ->values()
            ->all();

        $selectedManagerId = $this->resolveApproverUserId($undangan);

        return view('undangan.edit-baru', compact(
            'undangan',
            'divisi',
            'seri',
            'managers',
            'tujuanArray',
            'jsTreeData',
            'lampiranData',
            'bagianKerja',
            'selectedTembusan',
            'selectedBcc',
            'selectedManagerId'
        ));
    }

    public function update(Request $request, $id)
    {
        $undangan = Undangan::findOrFail($id);

        // =============================
        // VALIDASI EMOJI
        // =============================
        $rawIsiUndangan = $request->isi_undangan;

        $emojiErrors = $this->validateNoEmoji($request);
        if (!empty($emojiErrors)) {
            return redirect()->back()
                ->withErrors($emojiErrors)
                ->withInput();
        }

        // =============================
        // VALIDASI FORM
        // =============================
        $request->validate([
            'judul' => 'required|string|max:255',
            'isi_undangan' => 'required|string',
            'tujuan' => 'required|array|min:1',
            'nomor_undangan' => 'nullable|string|max:255',
            'kode_bagian' => 'required|string',
            'nama_bertandatangan' => 'required|string|max:255',
            'manager_user_id' => 'required|exists:users,id',
            'tgl_dibuat' => 'required|date',
            'tgl_disahkan' => 'nullable|date',
            'tgl_rapat' => 'required|date',
            'tempat' => 'required|string',
            'waktu_mulai' => 'required|string',
            'waktu_selesai' => 'required|string',
            'lampiran.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ], [
            'lampiran.*' => 'Lampiran gagal diunggah. Pastikan format dan ukuran file sesuai ketentuan.',
        ]);

        // =============================
        // UPDATE FIELD
        // =============================
        $undangan->judul = $request->judul;
        $undangan->isi_undangan = $rawIsiUndangan;

        if ($request->filled('tujuan')) {
            $tujuanIds = $this->convertTujuanToUserId($request->tujuan);

            $tembusanIds = [];
            if ($request->has('tembusan') && is_array($request->tembusan)) {
                $tembusanIds = $this->convertTujuanToUserId($request->tembusan);
                $undangan->tembusan = !empty($tembusanIds) ? implode(';', $tembusanIds) : null;
            } else {
                $undangan->tembusan = null;
            }

            $bccIds = [];
            if ($request->has('bcc') && is_array($request->bcc)) {
                $bccIds = $this->convertTujuanToUserId($request->bcc);
                $undangan->bcc = !empty($bccIds) ? implode(';', $bccIds) : null;
            } else {
                $undangan->bcc = null;
            }

            $undangan->tujuan = implode(';', $tujuanIds);
        }

        $undangan->status = 'pending';

        if ($request->filled('nomor_undangan')) {
            $undangan->nomor_undangan = $request->nomor_undangan;
        }

        $manager = User::findOrFail((int) $request->manager_user_id);
        $undangan->manager_user_id = (int) $manager->id;
        $undangan->nama_bertandatangan = trim($manager->firstname . ' ' . $manager->lastname);
        $undangan->tgl_dibuat = $request->tgl_dibuat;
        $undangan->tgl_disahkan = $request->tgl_disahkan;
        $undangan->tgl_rapat = $request->tgl_rapat;
        $undangan->tempat = $request->tempat;
        $undangan->waktu_mulai = $request->waktu_mulai;
        $undangan->waktu_selesai = $request->waktu_selesai;
        $undangan->kode_bagian = $request->kode_bagian;

        // =============================
        // HANDLE LAMPIRAN
        // =============================
        if ($request->hasFile('lampiran')) {

            $files = $request->file('lampiran');
            $newFiles = [];

            $folder = 'undangan/lampiran/' . $undangan->id_undangan;

            foreach ($files as $file) {
                if ($file->isValid()) {

                    $filename = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                    $filePath = $file->storeAs($folder, $filename, 'public');

                    $newFiles[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $filePath,
                        'size' => $file->getSize(),
                        'uploaded_at' => now()->toDateTimeString()
                    ];
                }
            }

            $existingFiles = [];

            if ($undangan->lampiran) {
                $jsonData = json_decode($undangan->lampiran, true);
                if ($jsonData !== null && is_array($jsonData)) {
                    $existingFiles = $jsonData;
                }
            }

            $allFiles = array_merge($existingFiles, $newFiles);

            $undangan->lampiran = !empty($allFiles)
                ? json_encode($allFiles)
                : null;
        }

        // =============================
        // SAVE
        // =============================
        $undangan->save();

        // =============================
        // RESET WORKFLOW APPROVAL
        // =============================
        \App\Models\Kirim_Document::where('id_document', $undangan->id_undangan)
            ->where('jenis_document', 'undangan')
            ->update([
                'status' => 'pending',
                'updated_at' => now()
            ]);

        // Sinkronkan approver terbaru ke kirim_document (workflow tetap gunakan tabel ini).
        \App\Models\Kirim_Document::updateOrCreate(
            [
                'id_document' => $undangan->id_undangan,
                'jenis_document' => 'undangan',
                'id_penerima' => (int) $undangan->manager_user_id,
            ],
            [
                'id_pengirim' => (int) $undangan->pembuat,
                'status' => 'pending',
                'updated_at' => now(),
            ]
        );

        // =============================
        // NOTIFIKASI RESUBMIT
        // =============================
        $approverId = $this->resolveApproverUserId($undangan, $request);
        foreach ($this->getWorkflowNotificationRecipients($undangan, $request) as $recipientId) {
            $judulNotif = ((int) $recipientId === (int) $approverId)
                ? 'Undangan Diedit, Menunggu Persetujuan'
                : 'Undangan Anda Berhasil Diupdate';
            $this->dispatchUndanganNotification((int) $recipientId, $judulNotif, $undangan);
        }

        // =============================
        // REDIRECT
        // =============================
        if (Auth::user()->role_id_role == 1) {

            return redirect()
                ->route('superadmin.undangan.index')
                ->with('success', 'Undangan berhasil diubah dan dikirim ulang untuk persetujuan.');
        }

        if (Auth::user()->role_id_role == 2) {

            return redirect()
                ->route('undangan.terkirim')
                ->with('success', 'Undangan berhasil diubah dan dikirim ulang untuk persetujuan.');
        }

        return redirect()
            ->route('undangan.terkirim')
            ->with('success', 'Undangan berhasil diubah dan dikirim ulang untuk persetujuan.');
    }

    public function destroy($id)
    {
        try {
            $undangan = Undangan::findOrFail($id);

            // Hapus kirim_document terkait
            $undangan->delete();
            Kirim_Document::where('id_document', $id)->where('jenis_document', 'undangan')->delete();

            // Check if request expects JSON response
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Undangan berhasil dihapus'
                ]);
            }

            return redirect()->route('undangan.terkirim')->with('success', 'Undangan deleted successfully.');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus undangan: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Gagal menghapus undangan: ' . $e->getMessage());
        }
    }

    public function deleteLampiranExisting($undanganId, $index)
    {
        try {
            $undangan = Undangan::findOrFail($undanganId);

            // Parse lampiran data
            $lampiranData = [];
            if ($undangan->lampiran) {
                $lampiranData = json_decode($undangan->lampiran, true) ?? [];
            }

            // Cek apakah index valid
            if (!isset($lampiranData[$index])) {
                return response()->json(['success' => false, 'message' => 'File tidak ditemukan'], 404);
            }

            // Hapus file fisik jika ada
            if (isset($lampiranData[$index]['path']) && \Illuminate\Support\Facades\Storage::disk('public')->exists($lampiranData[$index]['path'])) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($lampiranData[$index]['path']);
            }

            // Hapus dari array
            unset($lampiranData[$index]);
            // Reindex array
            $lampiranData = array_values($lampiranData);

            // Update undangan dengan lampiran data yang baru
            $undangan->lampiran = empty($lampiranData) ? null : json_encode($lampiranData);
            $undangan->save();

            return response()->json(['success' => true, 'message' => 'File berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat menghapus file'], 500);
        }
    }

    // public function view($id)
    // {
    //     $userId = Auth::id();

    //     $undangan = Undangan::where('id_undangan', $id)
    //         ->where(function ($query) use ($userId, $id) {
    //             $query
    //                 ->whereHas('kirimDocument', function ($sub) use ($userId) {
    //                     $sub->where('jenis_document', 'undangan')
    //                         ->where('id_penerima', $userId);
    //                 })
    //                 ->orWhere(function ($sub) use ($userId) {
    //                     $sub->where('tembusan', 'like', $userId . ';%')
    //                         ->orWhere('tembusan', 'like', '%;' . $userId . ';%')
    //                         ->orWhere('tembusan', 'like', '%;' . $userId)
    //                         ->orWhere('tembusan', '=', (string) $userId);
    //                 })
    //                 ->orWhere(function ($sub) use ($userId) {
    //                     $sub->where('bcc', 'like', $userId . ';%')
    //                         ->orWhere('bcc', 'like', '%;' . $userId . ';%')
    //                         ->orWhere('bcc', 'like', '%;' . $userId)
    //                         ->orWhere('bcc', '=', (string) $userId);
    //                 })
    //                 ->orWhere('pembuat', $userId)
    //                 ->orWhereExists(function ($sub) use ($userId, $id) {
    //                     $sub->selectRaw(1)
    //                         ->from('disposisi')
    //                         ->where('document_type', 'undangan')
    //                         ->where('document_id', $id)
    //                         ->whereRaw('JSON_CONTAINS(kepada_user_id, ?)', [json_encode((int) $userId)]);
    //                 });
    //         })
    //         ->firstOrFail();

    //     $divDeptKode = $this->getDivDeptKode(Auth::user());

    //     $idArray = is_array($undangan->tujuan)
    //         ? $undangan->tujuan
    //         : explode(';', $undangan->tujuan);

    //     $users = User::whereIn('id', $idArray)->with('position')->get();

    //     $pdfController = new CetakPDFController();

    //     $listNama = User::with(['position', 'director', 'divisi', 'department', 'section', 'unit'])
    //         ->whereIn('id', $idArray)
    //         ->get()
    //         ->map(function ($user) use ($pdfController) {
    //             $level = $pdfController->detectLevel($user);
    //             $user->level_kerja = $level;
    //             $user->bagian_text = $pdfController->getBagianText($user, $level);

    //             return $user;
    //         })
    //         ->sortBy(function ($user) {
    //             return optional($user->position)->id_position;
    //         })
    //         ->values();

    //     $undangan->tujuan = $listNama->map(function ($user, $index) {
    //         return ($index + 1) . '. '
    //             . $user->position->nm_position . ' '
    //             . $user->bagian_text . ' '
    //             . '(' . $user->firstname . ' ' . $user->lastname . ')';
    //     })->implode("\n");

    //     $undanganCollection = collect([$undangan]);

    //     $undanganCollection->transform(function ($undangan) use ($userId) {
    //         $isPenerimaDisposisi = Disposisi::where('document_type', 'undangan')
    //             ->where('document_id', $undangan->id_undangan)
    //             ->whereJsonContains('kepada_user_id', (int) $userId)
    //             ->exists();

    //         if ($isPenerimaDisposisi) {
    //             $undangan->final_status = 'disposisi';
    //         } elseif ($undangan->divisi_id_divisi === Auth::user()->divisi_id_divisi) {
    //             $undangan->final_status = $undangan->status;
    //         } else {
    //             $statusKirim = Kirim_Document::where('id_document', $undangan->id_undangan)
    //                 ->where('jenis_document', 'undangan')
    //                 ->where('id_penerima', $userId)
    //                 ->first();

    //             $undangan->final_status = $statusKirim ? $statusKirim->status : '-';
    //         }

    //         return $undangan;
    //     });

    //     $undangan = $undanganCollection->first();

    //     $lampiranData = [];
    //     if ($undangan->lampiran) {
    //         $jsonData = json_decode($undangan->lampiran, true);

    //         if ($jsonData !== null && is_array($jsonData)) {
    //             $lampiranData = $jsonData;
    //         }
    //     }

    //     $tembusanList = $this->buildGroupedRecipientDisplayList(
    //         $this->parseRecipientUserIds($undangan->tembusan ?? null)
    //     );

    //     $canViewBcc = Auth::id() === (int) $undangan->pembuat;
    //     $bccDisplayList = [];

    //     if ($canViewBcc) {
    //         $bccDisplayList = $this->buildGroupedRecipientDisplayList(
    //             $this->parseRecipientUserIds($undangan->bcc ?? null)
    //         );
    //     }

    //     return view('undangan.view-undangan', compact(
    //         'undangan',
    //         'lampiranData',
    //         'tembusanList',
    //         'canViewBcc',
    //         'bccDisplayList'
    //     ));
    // }

    public function view($id)
    {
        $userId = Auth::id();

        $undangan = Undangan::where('id_undangan', $id)
            ->where(function ($query) use ($userId, $id) {
                $query
                    ->whereHas('kirimDocument', function ($sub) use ($userId) {
                        $sub->where('jenis_document', 'undangan')
                            ->where('id_penerima', $userId);
                    })
                    ->orWhere(function ($sub) use ($userId) {
                        $sub->where('tembusan', 'like', $userId . ';%')
                            ->orWhere('tembusan', 'like', '%;' . $userId . ';%')
                            ->orWhere('tembusan', 'like', '%;' . $userId)
                            ->orWhere('tembusan', '=', (string) $userId);
                    })
                    ->orWhere(function ($sub) use ($userId) {
                        $sub->where('bcc', 'like', $userId . ';%')
                            ->orWhere('bcc', 'like', '%;' . $userId . ';%')
                            ->orWhere('bcc', 'like', '%;' . $userId)
                            ->orWhere('bcc', '=', (string) $userId);
                    })
                    ->orWhere('pembuat', $userId)
                    ->orWhereExists(function ($sub) use ($userId, $id) {
                        $sub->selectRaw(1)
                            ->from('disposisi')
                            ->where('document_type', 'undangan')
                            ->where('document_id', $id)
                            ->whereRaw('JSON_CONTAINS(kepada_user_id, ?)', [json_encode((int) $userId)]);
                    });
            })
            ->firstOrFail();

        $divDeptKode = $this->getDivDeptKode(Auth::user());

        $tujuanUserIds = $this->parseRecipientUserIds($undangan->tujuan);
        $tujuanLegacyNames = empty($tujuanUserIds)
            ? $this->parseLegacyRecipientNames($undangan->tujuan, $undangan->tujuan_string ?? null)
            : [];
        $tujuanDisplayList = $this->buildIndividualRecipientDisplayList($tujuanUserIds, $tujuanLegacyNames);

        $undanganCollection = collect([$undangan]);

        $undanganCollection->transform(function ($undangan) use ($userId) {
            $isPenerimaDisposisi = Disposisi::where('document_type', 'undangan')
                ->where('document_id', $undangan->id_undangan)
                ->whereJsonContains('kepada_user_id', (int) $userId)
                ->exists();

            if ($isPenerimaDisposisi) {
                $undangan->final_status = 'disposisi';
            } elseif ($undangan->divisi_id_divisi === Auth::user()->divisi_id_divisi) {
                $undangan->final_status = $undangan->status;
            } else {
                $statusKirim = Kirim_Document::where('id_document', $undangan->id_undangan)
                    ->where('jenis_document', 'undangan')
                    ->where('id_penerima', $userId)
                    ->first();

                $undangan->final_status = $statusKirim ? $statusKirim->status : '-';
            }

            return $undangan;
        });

        $undangan = $undanganCollection->first();

        $lampiranData = [];

        if ($undangan->lampiran) {
            $jsonData = json_decode($undangan->lampiran, true);

            if ($jsonData !== null && is_array($jsonData)) {
                $lampiranData = $jsonData;
            }
        }

        $tembusanUserIds = $this->parseRecipientUserIds($undangan->tembusan ?? null);
        $tembusanLegacyNames = empty($tembusanUserIds)
            ? $this->parseLegacyRecipientNames($undangan->tembusan ?? null)
            : [];
        $tembusanList = $this->buildIndividualRecipientDisplayList($tembusanUserIds, $tembusanLegacyNames);

        $canViewBcc = (int) Auth::id() === (int) $undangan->pembuat;
        $bccDisplayList = [];

        if ($canViewBcc) {
            $bccUserIds = $this->parseRecipientUserIds($undangan->bcc ?? null);
            $bccLegacyNames = empty($bccUserIds)
                ? $this->parseLegacyRecipientNames($undangan->bcc ?? null)
                : [];
            $bccDisplayList = $this->buildIndividualRecipientDisplayList($bccUserIds, $bccLegacyNames);
        }

        return view('undangan.view-undangan', compact(
            'undangan',
            'lampiranData',
            'tujuanDisplayList',
            'tembusanList',
            'canViewBcc',
            'bccDisplayList'
        ));
    }

    private function buildIndividualRecipientDisplayList(array $userIds, array $legacyNames = []): array
    {
        if (empty($userIds)) {
            return array_values(array_filter($legacyNames));
        }

        $users = User::with([
            'position:id_position,nm_position',
            'director:id_director,name_director',
            'divisi:id_divisi,nm_divisi',
            'department:id_department,name_department',
            'section:id_section,name_section',
            'unit:id_unit,name_unit',
        ])
            ->whereIn('id', $userIds)
            ->get([
                'id',
                'firstname',
                'lastname',
                'position_id_position',
                'director_id_director',
                'divisi_id_divisi',
                'department_id_department',
                'section_id_section',
                'unit_id_unit',
            ]);

        if ($users->isEmpty()) {
            return array_values(array_filter($legacyNames));
        }

        $users = PositionOrder::sortUsers($users);

        return $users
            ->map(function ($user) {
                $fullName = trim(($user->firstname ?? '') . ' ' . ($user->lastname ?? ''));
                $positionName = optional($user->position)->nm_position ?? '-';
                $positionClean = preg_replace('/^\s*\([^)]*\)\s*/', '', $positionName) ?: $positionName;

                $bagianKerja = $this->getRecipientWorkUnitLabel($user, [
                    'director' => collect([$user->director_id_director => optional($user->director)->name_director]),
                    'division' => collect([$user->divisi_id_divisi => optional($user->divisi)->nm_divisi]),
                    'department' => collect([$user->department_id_department => optional($user->department)->name_department]),
                    'section' => collect([$user->section_id_section => optional($user->section)->name_section]),
                    'unit' => collect([$user->unit_id_unit => optional($user->unit)->name_unit]),
                ]);

                return $fullName . ' - ' . $bagianKerja . ' (' . $positionClean . ')';
            })
            ->filter()
            ->values()
            ->all();
    }

    private function parseRecipientUserIds(?string $value): array
    {
        if (empty($value)) {
            return [];
        }

        return collect(explode(';', (string) $value))
            ->map(fn ($id) => trim($id))
            ->filter(fn ($id) => $id !== '' && is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    private function parseLegacyRecipientNames(?string $rawValue, ?string $legacyValue = null): array
    {
        $fromRaw = collect(explode(';', (string) $rawValue))
            ->map(fn ($item) => trim($item))
            ->filter(fn ($item) => $item !== '' && !is_numeric($item))
            ->values();

        $fromLegacy = collect(explode(';', (string) $legacyValue))
            ->map(fn ($item) => trim($item))
            ->filter(fn ($item) => $item !== '')
            ->values();

        return $fromRaw
            ->merge($fromLegacy)
            ->unique()
            ->values()
            ->all();
    }

    private function buildGroupedRecipientDisplayList(array $userIds, array $legacyNames = []): array
    {
        if (empty($userIds)) {
            return array_values(array_filter($legacyNames));
        }

        $selectedUsers = User::with([
            'position:id_position,nm_position',
            'department:id_department,name_department',
        ])
            ->whereIn('id', $userIds)
            ->get([
                'id',
                'firstname',
                'lastname',
                'position_id_position',
                'director_id_director',
                'divisi_id_divisi',
                'department_id_department',
                'section_id_section',
                'unit_id_unit',
            ]);

        if ($selectedUsers->isEmpty()) {
            return array_values(array_filter($legacyNames));
        }

        $displayList = [];

        $selectedIdSet = $selectedUsers
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->flip();

        $remainingIds = $selectedUsers
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $directorMap = Director::pluck('name_director', 'id_director');
        $divisionMap = Divisi::pluck('nm_divisi', 'id_divisi');
        $departmentMap = Department::pluck('name_department', 'id_department');
        $sectionMap = Section::pluck('name_section', 'id_section');
        $unitMap = Unit::pluck('name_unit', 'id_unit');

        $sectionToDepartmentMap = Section::pluck('department_id_department', 'id_section');
        $unitToSectionMap = Unit::pluck('section_id_section', 'id_unit');

        $groupedDepartmentIds = [];
        $groupedSectionIds = [];

        $scopes = [
            [
                'col' => 'director_id_director',
                'label' => 'Direktorat',
                'map' => $directorMap,
            ],
            [
                'col' => 'divisi_id_divisi',
                'label' => 'Divisi',
                'map' => $divisionMap,
            ],
            [
                'col' => 'department_id_department',
                'label' => 'Departemen',
                'map' => $departmentMap,
            ],
            [
                'col' => 'section_id_section',
                'label' => 'Bagian',
                'map' => $sectionMap,
            ],
            [
                'col' => 'unit_id_unit',
                'label' => 'Unit',
                'map' => $unitMap,
            ],
        ];

        foreach ($scopes as $scope) {
            $groupIds = $selectedUsers
                ->whereIn('id', $remainingIds)
                ->pluck($scope['col'])
                ->filter()
                ->unique()
                ->values();

            foreach ($groupIds as $groupId) {
                if ($scope['col'] === 'section_id_section') {
                    $parentDeptId = $sectionToDepartmentMap[$groupId] ?? null;

                    if (
                        !empty($parentDeptId) &&
                        in_array((int) $parentDeptId, $groupedDepartmentIds, true)
                    ) {
                        $coveredUserIds = $selectedUsers
                            ->where('section_id_section', $groupId)
                            ->pluck('id')
                            ->map(fn ($id) => (int) $id)
                            ->all();

                        $remainingIds = array_values(array_diff($remainingIds, $coveredUserIds));
                        continue;
                    }
                }

                if ($scope['col'] === 'unit_id_unit') {
                    $parentSectionId = $unitToSectionMap[$groupId] ?? null;
                    $parentDeptId = $parentSectionId
                        ? ($sectionToDepartmentMap[$parentSectionId] ?? null)
                        : null;

                    if (
                        (!empty($parentSectionId) && in_array((int) $parentSectionId, $groupedSectionIds, true)) ||
                        (!empty($parentDeptId) && in_array((int) $parentDeptId, $groupedDepartmentIds, true))
                    ) {
                        $coveredUserIds = $selectedUsers
                            ->where('unit_id_unit', $groupId)
                            ->pluck('id')
                            ->map(fn ($id) => (int) $id)
                            ->all();

                        $remainingIds = array_values(array_diff($remainingIds, $coveredUserIds));
                        continue;
                    }
                }

                $allMemberIds = User::where($scope['col'], $groupId)
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id);

                if ($allMemberIds->isEmpty()) {
                    continue;
                }

                $allSelected = $allMemberIds->every(
                    fn ($memberId) => $selectedIdSet->has((int) $memberId)
                );

                if ($allSelected) {
                    $scopeName = $scope['map'][$groupId] ?? 'ID ' . $groupId;

                    $displayList[] = $scope['label'] . ': ' . $scopeName;

                    if ($scope['col'] === 'department_id_department') {
                        $groupedDepartmentIds[] = (int) $groupId;
                    }

                    if ($scope['col'] === 'section_id_section') {
                        $groupedSectionIds[] = (int) $groupId;
                    }

                    $remainingIds = array_values(array_diff($remainingIds, $allMemberIds->all()));
                }
            }
        }

        $remainingUsers = PositionOrder::sortUsers(
            $selectedUsers->whereIn('id', $remainingIds)
        );

        foreach ($remainingUsers as $user) {
            $fullName = trim(($user->firstname ?? '') . ' ' . ($user->lastname ?? ''));
            $positionName = $user->position->nm_position ?? '-';
            $positionClean = preg_replace('/^\s*\([^)]*\)\s*/', '', $positionName) ?: $positionName;

            $bagianKerja = $this->getRecipientWorkUnitLabel($user, [
                'director' => $directorMap,
                'division' => $divisionMap,
                'department' => $departmentMap,
                'section' => $sectionMap,
                'unit' => $unitMap,
            ]);

            $displayList[] = $fullName . ' - ' . $bagianKerja . ' (' . $positionClean . ')';
        }

        return array_values(array_filter($displayList));
    }

    private function getRecipientWorkUnitLabel(User $user, array $maps): string
    {
        $positionName = $user->position->nm_position ?? '-';
        $positionLower = strtolower($positionName);

        $isStaff = str_contains($positionLower, 'staff') || str_contains($positionLower, 'staf');

        if ($isStaff) {
            if (!empty($user->unit_id_unit) && isset($maps['unit'][$user->unit_id_unit])) {
                return $maps['unit'][$user->unit_id_unit];
            }

            if (!empty($user->section_id_section) && isset($maps['section'][$user->section_id_section])) {
                return $maps['section'][$user->section_id_section];
            }

            if (!empty($user->department_id_department) && isset($maps['department'][$user->department_id_department])) {
                return $maps['department'][$user->department_id_department];
            }

            if (!empty($user->divisi_id_divisi) && isset($maps['division'][$user->divisi_id_divisi])) {
                return $maps['division'][$user->divisi_id_divisi];
            }

            if (!empty($user->director_id_director) && isset($maps['director'][$user->director_id_director])) {
                return $maps['director'][$user->director_id_director];
            }

            return '-';
        }

        if (!empty($user->department_id_department) && isset($maps['department'][$user->department_id_department])) {
            return $maps['department'][$user->department_id_department];
        }

        if (!empty($user->divisi_id_divisi) && isset($maps['division'][$user->divisi_id_divisi])) {
            return $maps['division'][$user->divisi_id_divisi];
        }

        if (!empty($user->section_id_section) && isset($maps['section'][$user->section_id_section])) {
            return $maps['section'][$user->section_id_section];
        }

        if (!empty($user->unit_id_unit) && isset($maps['unit'][$user->unit_id_unit])) {
            return $maps['unit'][$user->unit_id_unit];
        }

        if (!empty($user->director_id_director) && isset($maps['director'][$user->director_id_director])) {
            return $maps['director'][$user->director_id_director];
        }

        return '-';
    }

    public function updateStatus(Request $request, $id)
    {
        $undangan = Undangan::findOrFail($id);

        // Validasi input
        $request->validate([
            'status' => 'required|in:approve,reject,pending,correction',
            'catatan' => 'nullable|string',
        ]);

        // Update status
        $undangan->status = $request->status;
        // Also update related kirim_document rows so their status reflects the
        // undangan status change. Find rows where id_document == undangan id
        // and jenis_document == 'undangan'.
        \App\Models\Kirim_Document::where('id_document', $id)
            ->where('jenis_document', 'undangan')
            ->update([
                'status' => $request->status,
                'updated_at' => now(),
            ]);


        // Jika status 'approve', simpan tanggal pengesahan
        if ($request->status == 'approve') {
            $undangan->tgl_disahkan = now();
        } elseif ($request->status == 'reject') {
            $undangan->tgl_disahkan = now();
        } elseif ($request->status == 'correction') {
            $undangan->tgl_disahkan = now();
        } else {
            $undangan->tgl_disahkan = null; // Reset tanggal disahkan jika status bukan approve atau reject
        }

        // Simpan catatan jika ada
        $undangan->catatan = $request->catatan;

        // Simpan perubahan
        $undangan->save();

        return redirect()->back()->with('success', 'Status undangan berhasil diperbarui.');
    }

    public function updateStatusNotif(Request $request, $id)
    {
        $undangan = Undangan::findOrFail($id);
        $undangan->status = $request->status;
        $undangan->save();

        // Simpan notifikasi
        Notifikasi::create([
            'judul' => "Undangan {$request->status}",
            'judul_document' => $undangan->judul,
            'id_user' => $undangan->pembuat,
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'Status undangan berhasil diperbarui.');
    }
}
