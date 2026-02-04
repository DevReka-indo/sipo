<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Http\Request;
use App\Models\Risalah;
use App\Models\RisalahDetail;
use App\Models\Seri;
use App\Models\SeriRisalah;
use App\Models\Arsip;
use App\Models\Notifikasi;
use App\Models\Kirim_Document;
use App\Models\BackupRisalah;
use App\Models\User;
use App\Models\Divisi;
use App\Models\Undangan;
use App\Models\Department;
use App\Models\Director;
use App\Models\BagianKerja;
use App\Models\CounterNomorSurat;
use App\Http\Controllers\MemoController;
use App\Http\Controllers\Api\NotifApiController;
use App\Services\QrCodeService;

class RisalahController extends Controller
{
    public function index(Request $request)
    {
        // $divisi = Divisi::all();
        $seri = SeriRisalah::all();
        $userId = Auth::id();
        $kode = DB::table('risalah')
            ->whereNotNull('kode')        // pastikan hanya yang ada kodenya
            ->distinct()
            ->pluck('kode');

        // Ambil ID memo yang sudah diarsipkan oleh user ini
        $risalahDiarsipkan = Arsip::where('user_id', $userId)->where('jenis_document', 'App\Models\Risalah')->pluck('document_id')->toArray();

        $allowedSortColumns = ['created_at', 'tgl_disahkan', 'tgl_dibuat', 'nomor_risalah', 'judul'];
        $sortBy = in_array($request->get('sort_by'), $allowedSortColumns) ? $request->get('sort_by') : 'created_at';
        $sortDirection = $request->get('sort_direction', 'desc') === 'desc' ? 'desc' : 'asc';

        // Query awal: risalah belum diarsipkan
        $query = Risalah::query()
            ->whereNotIn('id_risalah', $risalahDiarsipkan)
            ->where(function ($q) use ($userId) {
                // Jika user terlibat dalam kirimDocument jenis risalah
                $q->orWhereHas('kirimDocument', function ($query) use ($userId) {
                    $query->where('jenis_document', 'risalah')
                        ->where(function ($query) use ($userId) {
                            $query->where('id_pengirim', $userId)
                                ->orWhere('id_penerima', $userId);
                        });
                });
            });

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter tanggal dibuat
        if ($request->filled('tgl_dibuat_awal') && $request->filled('tgl_dibuat_akhir')) {
            $query->whereBetween('tgl_dibuat', [$request->tgl_dibuat_awal, $request->tgl_dibuat_akhir]);
        } elseif ($request->filled('tgl_dibuat_awal')) {
            $query->whereDate('tgl_dibuat', '>=', $request->tgl_dibuat_awal);
        } elseif ($request->filled('tgl_dibuat_akhir')) {
            $query->whereDate('tgl_dibuat', '<=', $request->tgl_dibuat_akhir);
        }

        // --- Filter kode risalah ---
        if ($request->filled('kode')) {
            $query->where('kode', $request->kode);
        }

        // Filter search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('judul', 'like', '%' . $request->search . '%')
                    ->orWhere('nomor_risalah', 'like', '%' . $request->search . '%');
            });
        }

        // Sorting & pagination
        $perPage = $request->get('per_page', 10);
        $risalahs = $query->with('kirimDocument')
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage);

        // Tambah final_status
        $risalahs->getCollection()->transform(function ($risalah) use ($userId) {
            $statusKirim = Kirim_Document::where('id_document', $risalah->id_risalah)
                ->where('jenis_document', 'risalah')
                ->where('id_penerima', $userId)
                ->first();
            $risalah->final_status = $statusKirim ? $statusKirim->status : '-';
            return $risalah;
        });

        // (Opsional) Ambil semua kirimDocuments user ini
        $kirimDocuments = Kirim_Document::where('jenis_document', 'risalah')
            ->where(function ($query) use ($userId) {
                $query->where('id_pengirim', $userId)
                    ->orWhere('id_penerima', $userId);
            })
            ->get();
        return view(
            Auth::user()->role->nm_role . '.risalah.index',
            compact('risalahs', 'seri', 'sortDirection', 'kirimDocuments', 'kode')
        );
    }

    public function superadmin(Request $request)
    {
        $divisi = Divisi::all();
        $seri = SeriRisalah::all();
        $userId = Auth::id();
        $kode = Risalah::withTrashed()
            ->whereNotNull('kode')
            ->pluck('kode')
            ->filter()
            ->unique()
            ->values();

        $risalahDiarsipkan = Arsip::where('user_id', Auth::id())->where('jenis_document', 'App\Models\Risalah')->pluck('document_id')->toArray();
        $sortBy = $request->get('sort_by', 'created_at'); // default ke created_at
        $sortDirection = $request->get('sort_direction', 'desc') === 'asc' ? 'asc' : 'desc';

        $allowedSortColumns = ['created_at', 'tgl_disahkan', 'tgl_dibuat', 'nomor_risalah', 'judul'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'created_at'; // fallback default
        }

        $query = Risalah::query()
            ->whereNotIn('id_risalah', $risalahDiarsipkan)
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

        // Ambil semua arsip memo berdasarkan user login
        $arsipRisalahQuery = Arsip::where('user_id', $userId)
            ->where('jenis_document', 'risalah')
            ->with('document');

        $sortDirection = $request->get('sort_direction', 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy('created_at', $sortDirection);

        if ($request->filled('divisi_id_divisi') && $request->divisi_id_divisi != 'pilih') {
            $query->where('divisi_id_divisi', $request->divisi_id_divisi);
        }
        if ($request->filled('kode') && $request->kode != 'pilih') {
            $query->where('kode', $request->kode);
        }

        // Pencarian berdasarkan nama dokumen atau nomor memo
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('judul', 'like', '%' . $request->search . '%')
                    ->orWhere('nomor_risalah', 'like', '%' . $request->search . '%');
            });
        }
        $perPage = $request->get('per_page', 10); // Default ke 10 jika tidak ada input
        $risalahs = $query->paginate($perPage);

        return view('superadmin.risalah.index', compact('risalahs', 'divisi', 'seri', 'sortDirection', 'kode'));
    }

    public function create()
    {
        $idUser = Auth::user();
        $user = User::where('id', $idUser->id)->first();

        $userId = Auth::id();
        $listUndangan = Kirim_Document::where('jenis_document', 'undangan')
            ->where('status', 'approve')
            ->where(function($q) use ($userId) {
                $q->where('id_pengirim', $userId)
                ->orWhere('id_penerima', $userId);
            })
            ->pluck('id_document')
            ->unique();

        $undangan = Undangan::whereIn('id_undangan', $listUndangan)
            ->get()
            ->map(function ($item) {
                return (object) $item->toArray();
            });

        $risalah = new Risalah();

        // Daftar bagian kerja
        $bagianKerja = BagianKerja::orderBy('kode_bagian')->get();

        $users = User::orderBy('firstname')->get();
        $self = Auth::user();

        return view(Auth::user()->role->nm_role . '.risalah.add', [
            'risalah' => $risalah,
            'users' => $users,
            'kode_bagian' => $bagianKerja,
            'bagianKerja' => $bagianKerja,
            'self' => $self,
            'undangan' => $undangan
        ]);
    }

    public function createCustom()
    {
        $idUser = Auth::user();
        $user = User::where('id', $idUser->id)->first();

        $userId = Auth::id();
        $risalah = new Risalah();

        // Daftar bagian kerja
        $bagianKerja = BagianKerja::orderBy('kode_bagian')->get();

        $users = User::orderBy('firstname')->get();
        $orgTree = $this->getOrgTreeWithUsers();
        $jsTreeData = $this->convertToJsTree($orgTree);
        $mainDirector = $orgTree[0] ?? null;

        return view(Auth::user()->role->nm_role . '.risalah.add-custom', [
            'risalah' => $risalah,
            'kode_bagian' => $bagianKerja,
            'bagianKerja' => $bagianKerja,
            'users' => $users,
            'orgTree' => $orgTree,
            'jsTreeData' => $jsTreeData,
            'mainDirector' => $mainDirector
        ]);
    }

    //function generate js tree (thx diva)
    public function getOrgTreeWithUsers()
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
    public function filterUsersAtLevel($users, $level)
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

    public function getUserText($user, $context)
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

    public function convertToJsTree($tree)
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
    public function buildDeptNode(array $dept, array $ctx = [])
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
            'kode_bagian' => 'required|string|exists:bagian_kerja,kode_bagian',
            'agenda' => 'required|string',
            'tempat' => 'required|string',
            'waktu_mulai' => 'required|string',
            'waktu_selesai' => 'required|string',
            'tujuan' => 'required_without:with_undangan',
            'judul' => 'required|string',
            'pembuat' => 'required|string',
            'nomor' => 'nullable|array',
            'topik' => 'nullable|array',
            'pembahasan' => 'nullable|array',
            'tindak_lanjut' => 'nullable|array',
            'target' => 'nullable|array',
            'pic' => 'nullable|array',
            'lampiran' => 'nullable',
            'lampiran.*' => 'file|mimes:pdf,jpg,jpeg,png|max:2048',
        ], [
            'tujuan.required_without' => 'Minimal satu peserta acara harus dipilih.',
            'kode_bagian.required' => 'Bagian kerja wajib dipilih.',
            'kode_bagian.exists' => 'Kode bagian tidak valid.',
            'lampiran.*.mimes' => 'File harus berupa PDF, JPG, atau PNG.',
            'lampiran.*.max' => 'Ukuran tiap file tidak boleh lebih dari 2 MB.',
        ]);

        // Proses file lampiran (jika ada)
        $lampiranPath = null;
        if ($request->hasFile('lampiran')) {
            $newFiles = [];
            foreach ($request->file('lampiran') as $file) {
                if ($file->isValid()) {
                    $ext = strtolower($file->getClientOriginalExtension());

                    if ($ext === 'pdf') {
                        $folder = 'lampiran/risalah/pdf';
                    } elseif (in_array($ext, ['png', 'jpg', 'jpeg'])) {
                        $folder = 'lampiran/risalah/image';
                    } else {
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

            $lampiranPath = !empty($newFiles) ? json_encode($newFiles) : null;
        }

        // Ambil kode bagian dari form
        $kodeBagian = $request->input('kode_bagian');

        $pemimpin = User::where('id', $request->pemimpin_acara)->first();
        $notulis = User::where('id', $request->notulis_acara)->first();

        $namaPemimpinAcara = $pemimpin?->fullname;
        $namaNotulisAcara = $notulis?->fullname;

        $tujuan = [];
        if ($request->with_undangan) {
            $undangan = Undangan::where('id_undangan', $request->with_undangan)->first();
            if ($undangan) {
                $tujuan = explode(';', $undangan->tujuan);
            }
        } else {
            $tujuan = $request->tujuan;
        }

        // Generate QR Code untuk Notulis (tanpa nomor risalah karena belum ada)
        $qrText = "Notulis Acara: " . $namaNotulisAcara
            . "\nNomor Risalah: (Menunggu Persetujuan)"
            . "\nTanggal: " . now()->translatedFormat('l, d F Y H:i:s')
            . "\nDikeluarkan oleh website SIPO PT Rekaindo Global Jasa";
        $qrService = new QRCodeService();
        $qrNotulisAcara = $qrService->generateWithLogo($qrText);

        // Simpan risalah TANPA nomor_risalah (akan di-generate saat approve)
        $risalah = Risalah::create([
            'tgl_dibuat' => $request->tgl_dibuat,
            'seri_surat' => null, // Belum ada
            'kode_bagian' => $kodeBagian,
            'nomor_risalah' => null, // Belum di-generate
            'agenda' => $request->agenda,
            'tempat' => $request->tempat,
            'waktu_mulai' => $request->waktu_mulai,
            'waktu_selesai' => $request->waktu_selesai,
            'status' => 'pending',
            'judul' => $request->judul,
            'pembuat' => $request->pembuat,
            'lampiran' => $lampiranPath,
            'nama_pemimpin_acara' => $namaPemimpinAcara,
            'nama_notulis_acara' => $namaNotulisAcara,
            'qr_notulis_acara' => $qrNotulisAcara,
            'kode' => $kodeBagian,
            'risalah_id_risalah' => $request->id_risalah,
            'with_undangan' => $request->with_undangan,
            'tujuan' => implode(';', $tujuan),
        ]);

        // Create details
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

        // Send to recipient
        $penerima = $pemimpin;
        if (!$penerima) {
            return back()->withErrors(['nama_bertandatangan' => 'Nama penerima tidak ditemukan.']);
        }

        $sudahDikirim = Kirim_Document::where('id_document', $risalah->id_risalah)
            ->where('jenis_document', 'risalah')
            ->where('id_pengirim', Auth::id())
            ->where('id_penerima', $penerima->id)
            ->exists();

        $push = new NotifApiController();

        if (!$sudahDikirim) {
            Kirim_Document::firstOrCreate([
                'id_document' => $risalah->id_risalah,
                'jenis_document' => 'risalah',
                'id_pengirim' => Auth::id(),
                'id_penerima' => $penerima->id,
                'updated_at' => now(),
            ], [
                'status' => 'pending'
            ]);

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

        return redirect()->route(Auth::user()->role->nm_role . '.risalah.index')
            ->with('success', 'Risalah berhasil ditambahkan dan menunggu persetujuan');
    }

    public function updateDocumentStatus(Risalah $risalah)
    {
        $recipients = $risalah->recipients;

        if ($recipients->every(fn($recipient) => $recipient->status === 'approve')) {
            $risalah->update(['status' => 'approve']);
        } elseif ($recipients->contains(fn($recipient) => $recipient->status === 'reject')) {
            $risalah->update(['status' => 'reject']);
        } else {
            $risalah->update(['status' => 'pending']);
        }
    }

    public function updateDocumentApprovalDate(Risalah $risalah)
    {
        if ($risalah->status !== 'pending') {
            $risalah->update(['tanggal_disahkan' => now()]);
        }
    }

    public function approve(risalah $risalah)
    {
        $risalah->update([
            'status' => 'approve',
            'tanggal_disahkan' => now() // Set tanggal disahkan
        ]);

        return redirect()->back()->with('success', 'Dokumen berhasil disetujui.');
    }

    public function reject(Risalah $risalah)
    {
        $risalah->update([
            'status' => 'reject',
            'tanggal_disahkan' => now() // Set tanggal disahkan
        ]);

        return redirect()->back()->with('error', 'Dokumen ditolak.');
    }

    public function edit($id)
    {
        // Ambil data risalah beserta detailnya
        $divisi = Divisi::all();
        $seri = SeriRisalah::all();
        $user = Auth::User();
        $risalah = Risalah::with('risalahDetails')->findOrFail($id);
        $departmentId = $user->department_id_department;
        $divisiId = $user->divisi_id_divisi;

        $undangan = DB::select("
        SELECT *
        FROM undangan
        WHERE judul NOT IN (
            SELECT judul FROM risalah
        )
        AND EXISTS (
            SELECT 1
            FROM users
            WHERE
                users.id   = undangan.pembuat
                AND (
                    (users.department_id_department IS NOT NULL AND users.department_id_department = ?)
                    OR
                    (users.department_id_department IS NULL AND users.divisi_id_divisi = ?)
                )
        )
    ", [$departmentId, $divisiId]);

        if ($risalah->with_undangan) {
            $invite = Undangan::where('judul', $risalah->judul)->first();
            $listUser = explode(';', $invite->tujuan);
            $users = User::whereIn('id', $listUser)->get();
        } else {
            $users = User::orderBy('firstname')->get();
        }


        $risalah->pemimpin = User::whereRaw("CONCAT_WS(' ',firstname, lastname) = ?", [
            $risalah->nama_pemimpin_acara
        ])->first() ?? "";
        $risalah->notulis = User::whereRaw("CONCAT_WS(' ', firstname, lastname) = ?", [
            $risalah->nama_notulis_acara
        ])->first() ?? "";



        $lampiranData = [];
        if ($risalah->lampiran) {
            // Coba decode sebagai JSON dulu (untuk data baru)
            $jsonData = json_decode($risalah->lampiran, true);
            if ($jsonData !== null && is_array($jsonData)) {
                $lampiranData = $jsonData;
            } else {
                // Jika bukan JSON, ini kemungkinan data BLOB lama - skip untuk sekarang
                // atau bisa dikasih placeholder jika memang ada file
                $lampiranData = [];
            }
        }

        $orgTree = $this->getOrgTreeWithUsers();
        $jsTreeData = $this->convertToJsTree($orgTree);
        $mainDirector = $orgTree[0] ?? null;
        $tujuanArray = explode(';', $risalah->tujuan);
        // Ambil daftar manajer berdasarkan divisi yang sama

        // kode_bagian
        $bagianKerja = BagianKerja::orderBy('kode_bagian')->get();

        return view(Auth::user()->role->nm_role . '.risalah.edit', compact(
            'risalah',
            'divisi',
            'seri',
            'undangan',
            'users',
            'lampiranData',
            'orgTree',
            'jsTreeData',
            'mainDirector',
            'tujuanArray',
            'bagianKerja'
        ));
    }

    public function update(Request $request, $id)
    {
        // Validasi data
        $request->validate([
            'judul' => 'required',
            'agenda' => 'required',
            'tempat' => 'required',
            'kode_bagian' => 'required|string|exists:bagian_kerja,kode_bagian',
            'waktu_mulai' => 'required',
            'waktu_selesai' => 'required',
            'nomor.*' => 'required',
            'topik.*' => 'required',
            'pembahasan.*' => 'required',
            'tindak_lanjut.*' => 'required',
            'tujuan' => 'required_without:with_undangan',
            'target.*' => 'required',
            'pic.*' => 'required',
            'lampiran' => 'nullable',
            'lampiran.*' => 'file|mimes:pdf,jpg,jpeg,png|max:2048',
        ], [
            'lampiran.*' => 'Lampiran gagal diunggah. Pastikan format dan ukuran file sesuai ketentuan.',
            'lampiran.*.mimes' => 'File harus berupa PDF, JPG, atau PNG.',
            'kode_bagian.required' => 'Bagian kerja wajib dipilih.',
            'kode_bagian.exists' => 'Kode bagian tidak valid.',
            'tujuan.required_without' => 'Minimal satu peserta acara harus dipilih.',
            'lampiran.*.max' => 'Ukuran tiap file tidak boleh lebih dari 2 MB.',
        ]);

        $risalah = Risalah::findOrFail($id);

        // ⚠️ PENTING: Cek jika sudah approve dan user coba ganti kode_bagian
        if ($risalah->status == 'approve' && $request->kode_bagian != $risalah->kode_bagian) {
            return back()->with('error', 'Tidak dapat mengubah kode bagian setelah risalah disetujui.');
        }

        // Proses file lampiran baru (jika ada)
        $existingLampiran = $risalah->lampiran ? json_decode($risalah->lampiran, true) : [];
        $allFiles = is_array($existingLampiran) ? $existingLampiran : [];

        if ($request->hasFile('lampiran')) {
            foreach ($request->file('lampiran') as $file) {
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $fileName = time() . '_' . uniqid() . '_' . $originalName;

                if (in_array($extension, ['pdf'])) {
                    $folder = 'lampiran/risalah/pdf';
                } elseif (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                    $folder = 'lampiran/risalah/image';
                } else {
                    $folder = 'lampiran/risalah/other';
                }

                $filePath = $file->storeAs($folder, $fileName, 'public');

                if ($filePath) {
                    $allFiles[] = [
                        'name' => $originalName,
                        'path' => $filePath,
                        'size' => $file->getSize(),
                        'type' => $file->getMimeType()
                    ];
                }
            }
        }

        $lampiranPath = !empty($allFiles) ? json_encode($allFiles) : $risalah->lampiran;

        $notulis = User::where('id', $request->notulis_acara)->first();
        $pemimpin = User::where('id', $request->pemimpin_acara)->first();

        // Update QR notulis (dengan nomor lama jika sudah ada, atau placeholder jika belum)
        $nomorRisalahText = $risalah->nomor_risalah ?? '(Menunggu Persetujuan)';
        $qrText = "Notulis Acara: " . $notulis->fullname
            . "\nNomor Risalah: " . $nomorRisalahText
            . "\nTanggal: " . now()->translatedFormat('l, d F Y H:i:s')
            . "\nDikeluarkan oleh website SIPO PT Rekaindo Global Jasa";
        $qrService = new QRCodeService();
        $qrNotulisAcara = $qrService->generateWithLogo($qrText);

        if ($request->with_undangan) {
            $tujuan = $risalah->tujuan;
        } else {
            $tujuan = implode(';', $request->tujuan);
        }

        $risalah->update([
            'tgl_dibuat' => $request->tgl_dibuat,
            'judul' => $request->judul,
            'agenda' => $request->agenda,
            'kode_bagian' => $request->kode_bagian,
            'tempat' => $request->tempat,
            'waktu_mulai' => $request->waktu_mulai,
            'waktu_selesai' => $request->waktu_selesai,
            'nama_pemimpin_acara' => $pemimpin->fullname,
            'nama_notulis_acara' => $notulis->fullname,
            'qr_notulis_acara' => $qrNotulisAcara,
            'lampiran' => $lampiranPath,
            'status' => 'pending', // Reset ke pending saat edit
            'tujuan' => $tujuan,
        ]);

        $statusKirimDokumen = Kirim_Document::where('id_document', $risalah->id_risalah)
            ->where('jenis_document', 'risalah')
            ->first();

        if ($statusKirimDokumen) {
            $statusKirimDokumen->status = 'pending';
            $statusKirimDokumen->id_pengirim = $notulis->id;
            $statusKirimDokumen->id_penerima = $pemimpin->id;
            $statusKirimDokumen->save();
        }

        // Hapus dan update risalahDetails
        if ($request->has('nomor')) {
            if ($risalah->risalahDetails()->exists()) {
                $risalah->risalahDetails()->delete();
            }

            foreach ($request->nomor as $index => $nomor) {
                $risalah->risalahDetails()->create([
                    'nomor' => $nomor,
                    'topik' => $request->topik[$index],
                    'pembahasan' => $request->pembahasan[$index],
                    'tindak_lanjut' => $request->tindak_lanjut[$index],
                    'target' => $request->target[$index],
                    'pic' => $request->pic[$index],
                ]);
            }
        }

        // Redirect
        if (Auth::user()->role->id_role == 3) {
            return redirect()->route('risalah.manager')->with('success', 'Risalah berhasil diperbarui.');
        }
        return redirect()->route(Auth::user()->role->nm_role . '.risalah.index')->with('success', 'Risalah berhasil diperbarui.');
    }

    public function destroy($id, Request $request)
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
            'message' => 'Risalah berhasil dihapus.'
        ]);
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

    public function view($id)
    {
        $userId = Auth::id();
        $risalah = Risalah::where('id_risalah', $id)->firstOrFail();

        // Ambil data undangan yang judulnya sama
        $undangan = Undangan::where('judul', $risalah->judul)->first();

        // Bungkus risalah dalam collection agar bisa diproses transform
        $risalahCollection = collect([$risalah]);

        $risalahCollection->transform(function ($risalah) use ($userId) {
            if ($risalah->divisi_id_divisi === Auth::user()->divisi_id_divisi) {
                $risalah->final_status = $risalah->status;
            } else {
                $statusKirim = Kirim_Document::where('id_document', $risalah->id_risalah)
                    ->where('jenis_document', 'risalah')
                    ->where('id_penerima', $userId)
                    ->first();
                $risalah->final_status = $statusKirim ? $statusKirim->status : '-';
            }
            return $risalah;
        });

        $risalah = $risalahCollection->first();

        // Cek apakah undangan dan tujuannya tidak null
        if ($undangan && $undangan->tujuan) {
            $userIds = explode(';', $undangan->tujuan);
            $pdfController = new \App\Http\Controllers\CetakPDFController();
            $listNama = \App\Models\User::with(['position', 'director', 'divisi', 'department', 'section', 'unit'])
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
                $lampiranData = [];
            }
        }


        return view(Auth::user()->role->nm_role . '.risalah.view', compact('risalah', 'undangan', 'tujuanUsernames', 'lampiranData'));
    }

    public function updateStatus(Request $request, $id)
    {
        $push = new NotifApiController();

        try {
            $risalah = Risalah::findOrFail($id);
            $userId = Auth::id();

            $rules = [
                'status' => 'required|in:pending,approve,reject,correction',
            ];

            // Jika status reject atau correction, catatan wajib diisi
            if (in_array($request->status, ['reject', 'correction'])) {
                $rules['catatan'] = 'required|string';
            }

            $validated = $request->validate($rules);

            // Update status
            $risalah->status = $request->status;
            $currentKirim = Kirim_document::where('id_document', $id)
                ->where('jenis_document', 'risalah')
                ->where('id_penerima', $userId)
                ->first();

            if ($currentKirim) {
                $currentKirim->status = $request->status;
                $currentKirim->updated_at = now();
                $currentKirim->save();
            }

            // ============================================
            // GENERATE NOMOR SURAT SAAT APPROVE
            // ============================================
            if ($request->status == 'approve') {
                // Cek apakah nomor sudah pernah di-generate
                if (empty($risalah->nomor_risalah)) {
                    $maxAttempts = 10;
                    $attempt = 0;
                    $counterNomorSurat = null;
                    $nomorRisalah = null;

                    while ($attempt < $maxAttempts) {
                        try {
                            // Generate nomor surat otomatis
                            $bulanRomawi = CounterNomorSurat::getBulanRomawi(now()->month);
                            $tahun = now()->year;
                            $kodeBagian = $risalah->kode_bagian;

                            // Get last seri_tahun untuk kode bagian ini di tahun ini
                            $lastSeriTahun = CounterNomorSurat::getLastSeriTahun(
                                $tahun,
                                'RIS',
                                $kodeBagian
                            );

                            $nextSeriTahun = $lastSeriTahun + 1;
                            $seriTahunanPadded = str_pad($nextSeriTahun, 2, '0', STR_PAD_LEFT);

                            // Format nomor: RIS-{seri_tahun}/REKA/{kode_bagian}/{BULAN_ROMAWI}/{TAHUN}
                            // Contoh: RIS-07/REKA/SEC/II/2025
                            $nomorRisalah = sprintf(
                                "RIS-%s/REKA/%s/%s/%d",
                                $seriTahunanPadded,
                                strtoupper($kodeBagian),
                                $bulanRomawi,
                                $tahun
                            );

                            // Simpan counter ke database
                            // PENTING: seri_bulan diisi dengan '00' atau NULL karena tidak digunakan
                            $counterNomorSurat = CounterNomorSurat::create([
                                'tanggal_permintaan' => now(),
                                'seri_tahun' => $seriTahunanPadded,
                                'seri_bulan' => '00', // Tidak digunakan untuk risalah
                                'perusahaan' => 'REKA',
                                'kode_tipe_surat' => 'RIS',
                                'divisi' => $kodeBagian,
                                'bulan' => $bulanRomawi,
                                'tahun' => $tahun,
                                'pic_peminta' => Auth::user()->fullname,
                                'jenis' => 'Risalah',
                                'perihal' => $risalah->judul,
                                'nomor_surat_generated' => $nomorRisalah,
                                'is_used' => true
                            ]);

                            // Update risalah dengan nomor yang baru di-generate
                            $risalah->nomor_risalah = $nomorRisalah;
                            $risalah->seri_surat = $nextSeriTahun;

                            break; // Success

                        } catch (\Illuminate\Database\QueryException $e) {
                            if ($e->getCode() == '23000') {
                                // Duplicate - hapus dan retry
                                if ($counterNomorSurat) {
                                    $counterNomorSurat->delete();
                                }
                                $attempt++;
                                usleep(100000);
                                continue;
                            }
                            throw $e;
                        }
                    }

                    if (!$nomorRisalah) {
                        return back()->with('error', 'Gagal generate nomor risalah. Silakan coba lagi.');
                    }
                }

                // Set tanggal disahkan
                $risalah->tgl_disahkan = now();

                // Generate QR Code Pemimpin Acara dengan nomor risalah yang sudah ada
                $qrText = "Pemimpin Acara: " . Auth::user()->firstname . ' ' . Auth::user()->lastname
                    . "\nNomor Risalah: " . $risalah->nomor_risalah
                    . "\nTanggal Pengesahan: " . $risalah->tgl_disahkan->translatedFormat('l, d F Y H:i:s')
                    . "\nDikeluarkan oleh website SIPO PT Rekaindo Global Jasa";
                $qrService = new QRCodeService();
                $qrBase64 = $qrService->generateWithLogo($qrText);
                $risalah->qr_pemimpin_acara = $qrBase64;

                // Update QR Code Notulis dengan nomor risalah yang baru
                $qrTextNotulis = "Notulis Acara: " . $risalah->nama_notulis_acara
                    . "\nNomor Risalah: " . $risalah->nomor_risalah
                    . "\nTanggal: " . now()->translatedFormat('l, d F Y H:i:s')
                    . "\nDikeluarkan oleh website SIPO PT Rekaindo Global Jasa";
                $qrNotulisAcara = $qrService->generateWithLogo($qrTextNotulis);
                $risalah->qr_notulis_acara = $qrNotulisAcara;

                // Kirim ke semua tujuan
                $tujuanArray = explode(';', $risalah->tujuan);

                foreach ($tujuanArray as $idTujuan) {
                    $idTujuan = trim($idTujuan);
                    if (!$idTujuan) continue;

                    $users = User::where('id', $idTujuan)->get();

                    foreach ($users as $user) {
                        Kirim_Document::firstOrCreate([
                            'id_document' => $risalah->id_risalah,
                            'jenis_document' => 'risalah',
                            'id_pengirim' => $currentKirim->id_pengirim,
                            'id_penerima' => $user->id,
                            'updated_at' => now()
                        ], [
                            'status' => 'approve'
                        ]);

                        Notifikasi::create([
                            'judul' => "Risalah Masuk",
                            'judul_document' => $risalah->judul,
                            'id_user' => $user->id,
                            'updated_at' => now()
                        ]);

                        $push->sendToUser(
                            $user->id,
                            "Risalah Masuk",
                            $risalah->judul
                        );
                    }
                }

                Notifikasi::create([
                    'judul' => "Risalah Disetujui",
                    'judul_document' => $risalah->judul,
                    'id_user' => $risalah->pembuat,
                    'updated_at' => now()
                ]);

                $push->sendToUser(
                    $risalah->pembuat,
                    "Risalah Disetujui",
                    $risalah->judul
                );

            } elseif ($request->status == 'reject') {
                $risalah->tgl_disahkan = now();

                Notifikasi::create([
                    'judul' => "Risalah Ditolak",
                    'judul_document' => $risalah->judul,
                    'id_user' => $risalah->pembuat,
                    'updated_at' => now()
                ]);

                $push->sendToUser(
                    $risalah->pembuat,
                    "Risalah Ditolak",
                    $risalah->judul
                );

            } elseif ($request->status == 'correction') {
                $risalah->tgl_disahkan = now();

                Notifikasi::create([
                    'judul' => "Risalah Perlu Revisi",
                    'judul_document' => $risalah->judul,
                    'id_user' => $risalah->pembuat,
                    'updated_at' => now()
                ]);

                $push->sendToUser(
                    $risalah->pembuat,
                    "Risalah Perlu Revisi",
                    $risalah->judul
                );

            } else {
                $risalah->tgl_disahkan = null;
            }

            // Simpan catatan jika ada
            $risalah->catatan = $request->catatan;

            // Simpan perubahan
            $risalah->save();

            return redirect()->back()->with('success', 'Status risalah berhasil diperbarui.');

        } catch (\Exception $e) {
            Log::error('Error updating risalah status: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    //  menampilkan file yang disimpan dalam database
    public function showFile($id)
    {
        $risalah = Risalah::findOrFail($id);

        if (!$risalah->lampiran) {
            return response()->json(['error' => 'File tidak ditemukan.'], 404);
        }

        $fileContent = base64_decode($risalah->lampiran);
        if (!$fileContent) {
            return response()->json(['error' => 'File corrupt atau tidak bisa di-decode.'], 500);
        }

        // Pastikan MIME type valid
        $finfo = finfo_open();
        $mimeType = finfo_buffer($finfo, $fileContent, FILEINFO_MIME_TYPE);
        finfo_close($finfo);

        // Validasi MIME type
        $validMimeTypes = [
            'application/pdf' => 'pdf',
            'image/jpeg' => 'jpg',
            'image/png' => 'png'
        ];

        if (!isset($validMimeTypes[$mimeType])) {
            return response()->json(['error' => 'Format file tidak didukung.'], 400);
        }

        return response($fileContent)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="dokumen.' . $validMimeTypes[$mimeType] . '"');
    }

    private function validateMimeType($mimeType)
    {
        // Valid MIME types for PDF, JPG, PNG, JPEG
        $validMimeTypes = ['application/pdf', 'image/jpeg', 'image/png'];

        if (in_array($mimeType, $validMimeTypes)) {
            return $mimeType;
        }

        return 'application/octet-stream'; // Default fallback MIME type if not valid
    }

    // Fungsi tambahan untuk mendapatkan ekstensi dari MIME type
    private function getExtension($mimeType)
    {
        $map = [
            'application/pdf' => 'pdf',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        ];
        return $map[$mimeType] ?? 'bin';
    }

    // Fungsi download file
    // Fungsi download file
    public function downloadFile($id)
    {
        $risalah = Risalah::findOrFail($id);

        if (!$risalah->lampiran) {
            return redirect()->back()->with('error', 'File tidak ditemukan.');
        }

        $fileData = base64_decode($risalah->lampiran);
        $mimeType = finfo_buffer(finfo_open(), $fileData, FILEINFO_MIME_TYPE);
        $extension = $this->getExtension($mimeType);

        return response()->streamDownload(function () use ($fileData) {
            echo $fileData;
        }, "risalah_{$id}.$extension", ['Content-Type' => $mimeType]);
    }

    public function deleteLampiranExisting($id, $index)
    {
        try {
            $risalah = Risalah::findOrFail($id);

            if (!$risalah->lampiran) {
                return response()->json(['success' => false, 'message' => 'Tidak ada lampiran yang ditemukan.']);
            }

            $lampiranData = json_decode($risalah->lampiran, true);

            if (!is_array($lampiranData) || !isset($lampiranData[$index])) {
                return response()->json(['success' => false, 'message' => 'File tidak ditemukan.']);
            }

            // Hapus file dari storage jika ada path
            if (isset($lampiranData[$index]['path'])) {
                $filePath = storage_path('app/public/' . $lampiranData[$index]['path']);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            // Hapus dari array
            unset($lampiranData[$index]);

            // Reindex array
            $lampiranData = array_values($lampiranData);

            // Update database
            $risalah->lampiran = !empty($lampiranData) ? json_encode($lampiranData) : null;
            $risalah->save();

            return response()->json(['success' => true, 'message' => 'File berhasil dihapus.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function updateStatusNotif(Request $request, $id)
    {
        $risalah = Risalah::findOrFail($id);
        $risalah->status = $request->status;
        $risalah->save();

        // Simpan notifikasi
        Notifikasi::create([
            'judul' => "Risalah {$request->status}",
            'jenis_document' => 'risalah',
            'id_user' => $risalah->pembuat,
            'dibaca'         => false,
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'Status memo berhasil diperbarui.');
    }
}
