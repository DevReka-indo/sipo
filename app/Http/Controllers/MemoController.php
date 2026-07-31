<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\kategori_barang;
use App\Models\Memo;
use App\Models\Seri;
use App\Models\Arsip;
use App\Models\User;
use App\Models\Unit;
use App\Models\Section;
use App\Models\Divisi;
use App\Models\Notifikasi;
use App\Models\Kirim_Document;
use App\Models\Director;
use App\Models\Department;
use App\Models\BagianKerja;
use App\Models\CounterNomorSurat;
use App\Models\Disposisi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage as StorageFacade;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use App\Http\Controllers\Api\NotifApiController;
use setasign\Fpdi\Fpdi;
use App\Services\QrCodeService;
use ZipArchive;
use Illuminate\Support\Facades\Log;
use App\Services\NotifService;
use App\Support\PositionOrder;

class MemoController extends Controller
{
    /**
     * Memo Terkirim for logged in user (same logic as index but only memos where id_pengirim == auth user)
     */
    public function memoTerkirim(Request $request)
    {
        $divisi = Divisi::all();
        $seri = Seri::all();
        $user = Auth::user();
        $userId = $user->id;

        $memoDiarsipkan = Arsip::where('user_id', $userId)
            ->where('jenis_document', 'App\Models\Memo')
            ->pluck('document_id')
            ->toArray();

        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc') === 'asc' ? 'asc' : 'desc';

        $allowedSortColumns = ['created_at', 'tgl_disahkan', 'tgl_dibuat', 'nomor_memo', 'judul'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'created_at';
        }

        // $query = Memo::with(['divisi', 'user'])
        //     ->whereNotIn('id_memo', $memoDiarsipkan)
        //     ->where('kode_bagian', $user->kode_bagian);

        $kodeBagianUser = collect(explode(';', (string) $user->kode_bagian))
            ->map(fn ($kode) => trim($kode))
            ->filter()
            ->unique()
            ->values();

        $query = Memo::with(['divisi', 'user'])
            ->whereNotIn('id_memo', $memoDiarsipkan)
            ->where(function ($q) use ($kodeBagianUser) {
                foreach ($kodeBagianUser as $kodeBagian) {
                    $q->orWhereRaw(
                        "FIND_IN_SET(?, REPLACE(COALESCE(kode_bagian, ''), ';', ','))",
                        [$kodeBagian]
                    );
                }
            });

        // role admin/staff = hanya memo buatan sendiri
        if ((int) $user->role_id_role === 2) {
            $query->where('pembuat', $userId);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('tgl_dibuat_awal') && $request->filled('tgl_dibuat_akhir')) {
            $query->whereBetween('tgl_dibuat', [$request->tgl_dibuat_awal, $request->tgl_dibuat_akhir]);
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

        if ($request->filled('kode') && $request->kode != 'pilih') {
            $query->where('kode', $request->kode);
        }

        $query->orderBy($sortBy, $sortDirection);

        $kode = (clone $query)
            ->whereNotNull('kode')
            ->pluck('kode')
            ->filter()
            ->unique()
            ->values();

        $perPage = $request->get('per_page', 10);
        $memos = $query->paginate($perPage);

        $memos->getCollection()->transform(function ($memo) {
            $memo->final_status = $memo->status;
            return $memo;
        });

        return view('memo.memo-terkirim', compact(
            'memos',
            'divisi',
            'seri',
            'sortDirection',
            'kode'
        ));
    }

    /**
     * Memo Diterima for logged in user (same logic as index but only memos where id_penerima == auth user)
     */
    public function memoDiterima(Request $request)
    {
        $divisi = Divisi::all();
        $seri = Seri::all();
        $user = Auth::user();
        $userId = (int) $user->id;
        $uid = (string) $userId;

        $memoDiarsipkan = Arsip::where('user_id', $userId)
            ->where('jenis_document', 'App\Models\Memo')
            ->pluck('document_id')
            ->toArray();

        $sortBy = $request->get('sort_by', 'tgl_dibuat'); // default ke tgl_dibuat
        $sortDirection = $request->get('sort_direction', 'desc') === 'asc' ? 'asc' : 'desc';

        $allowedSortColumns = ['created_at', 'tgl_disahkan', 'tgl_dibuat', 'nomor_memo', 'judul'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'tgl_dibuat'; // fallback default
        }

        $query = Memo::with(['divisi', 'user'])
            ->whereNotIn('id_memo', $memoDiarsipkan)
            ->where('status', 'approve')
            ->where(function ($q) use ($uid) {
                $q->whereRaw("FIND_IN_SET(?, REPLACE(COALESCE(tujuan, ''), ';', ','))", [$uid])
                ->orWhereRaw("FIND_IN_SET(?, REPLACE(COALESCE(tembusan, ''), ';', ','))", [$uid])
                ->orWhereRaw("FIND_IN_SET(?, REPLACE(COALESCE(bcc, ''), ';', ','))", [$uid]);
            });

        if ($request->filled('tgl_dibuat_awal') && $request->filled('tgl_dibuat_akhir')) {
            $query->whereBetween('tgl_dibuat', [$request->tgl_dibuat_awal, $request->tgl_dibuat_akhir]);
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

        if ($request->filled('kode') && $request->kode != 'pilih') {
            $query->where('kode', $request->kode);
        }

        $query->orderBy($sortBy, $sortDirection);

        $kode = (clone $query)
            ->whereNotNull('kode')
            ->pluck('kode')
            ->filter()
            ->unique()
            ->values();

        $perPage = $request->get('per_page', 10);
        $memoDiterima = $query->paginate($perPage);

        $memoDiterima->getCollection()->transform(function ($memo) use ($userId) {
            $memo->final_status = $memo->status;

            $isTembusan = collect(explode(';', (string) $memo->tembusan))
                ->map(fn($item) => trim($item))
                ->filter(fn($item) => $item !== '')
                ->contains((string) $userId);

            $isBcc = collect(explode(';', (string) $memo->bcc))
                ->map(fn($item) => trim($item))
                ->filter(fn($item) => $item !== '')
                ->contains((string) $userId);

            if ($isTembusan) {
                $memo->sumber_diterima = 'tembusan';
            } elseif ($isBcc) {
                $memo->sumber_diterima = 'bcc';
            } else {
                $memo->sumber_diterima = 'tujuan';
            }

            return $memo;
        });

        return view('memo.memo-diterima', compact(
            'memoDiterima',
            'divisi',
            'seri',
            'sortDirection',
            'kode'
        ));
    }

    public function superadmin(Request $request)
    {
        $divisi = Divisi::all();
        $kode = Memo::whereNotNull('kode')->pluck('kode')->filter()->unique()->values();
        $seri = Seri::all();
        $userId = Auth::id();

        $memoDiarsipkan = Arsip::where('user_id', Auth::id())->where('jenis_document', 'App\Models\Memo')->pluck('document_id')->toArray();
        $sortBy = $request->get('sort_by', 'tgl_dibuat'); // default ke tgl_dibuat
        $sortDirection = $request->get('sort_direction', 'desc') === 'asc' ? 'asc' : 'desc';

        $allowedSortColumns = ['created_at', 'tgl_disahkan', 'tgl_dibuat', 'nomor_memo', 'judul'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'tgl_dibuat'; // fallback default
        }

        $query = Memo::query()->whereNotIn('id_memo', $memoDiarsipkan)->orderBy($sortBy, $sortDirection);

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
        $arsipMemoQuery = Arsip::where('user_id', $userId)->where('jenis_document', 'memo')->with('document');

        $sortDirection = $request->get('sort_direction', 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy('created_at', $sortDirection);

        if ($request->filled('kode') && $request->kode != 'pilih') {
            $query->where('kode', $request->kode);
        }

        // Pencarian berdasarkan nama dokumen atau nomor memo
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('judul', 'like', '%' . $request->search . '%')->orWhere('nomor_memo', 'like', '%' . $request->search . '%');
            });
        }
        $perPage = $request->get('per_page', 10);
        $memos = $query->paginate($perPage);
        return view('superadmin.memo.index', compact('memos', 'divisi', 'kode', 'seri', 'sortDirection'));
    }

    public function show($id)
    {
        $userId = Auth::id();
        $memo = Memo::with('divisi')->findOrFail($id);
        $pembuat = User::withTrashed()->find($memo->pembuat);
        //dd($pembuat);
        $memo->getCollection()->transform(function ($memo) use ($userId) {
            if ($memo->divisi_id_divisi === Auth::user()->divisi_id_divisi) {
                $memo->final_status = $memo->status; // Memo dari divisi sendiri
            } else {
                $statusKirim = Kirim_Document::where('id_document', $memo->id_memo)->where('jenis_document', 'memo')->where('id_penerima', $userId)->first();
                $memo->final_status = $statusKirim ? $statusKirim->status : '-';
                // Cari status kiriman untuk user login
            }
            return $memo;
        });
        $memo2 = Memo::where('id_memo', $id)->firstOrFail();
        $lampiranData = [];
        if ($memo2->lampiran) {
            // Coba decode sebagai JSON dulu (untuk data baru)
            $jsonData = json_decode($memo2->lampiran, true);
            if ($jsonData !== null && is_array($jsonData)) {
                $lampiranData = $jsonData;
            } else {
                // Jika bukan JSON, ini kemungkinan data BLOB lama - skip untuk sekarang
                // atau bisa dikasih placeholder jika memang ada file
                $lampiranData = [];
            }
        }

        // dd($memo, $memo2, $lampiranData);
        return view(Auth::user()->role->nm_role . '.memo.show', compact('memo', 'pembuat'));
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

        return array_values(array_filter(array_merge($displayList, $legacyNames)));
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


    /**
     * Tentukan approver untuk notifikasi dengan prioritas request -> memo -> fallback fullname.
     */
    private function resolveApproverUserId(Memo $memo, ?Request $request = null): ?int
    {
        if ($request && $request->filled('manager_user_id')) {
            $candidate = (int) $request->manager_user_id;
            if ($candidate > 0) {
                return $candidate;
            }
        }

        if (!empty($memo->manager_user_id)) {
            $candidate = (int) $memo->manager_user_id;
            if ($candidate > 0) {
                return $candidate;
            }
        }

        $penandatangan = User::findByFullname((string) $memo->nama_bertandatangan);
        return $penandatangan ? (int) $penandatangan->id : null;
    }

    /**
     * Recipient notifikasi workflow (create/edit/correction/reject): pembuat + approver.
     */
    private function getWorkflowNotificationRecipients(Memo $memo, ?Request $request = null): array
    {
        return collect([
            (int) $memo->pembuat,
            $this->resolveApproverUserId($memo, $request),
        ])->filter(fn($id) => is_int($id) && $id > 0)->unique()->values()->all();
    }

    /**
     * Recipient notifikasi final approve: pembuat + approver + tujuan + tembusan + bcc.
     */
    private function getFinalNotificationRecipients(Memo $memo, ?int $approverId = null): array
    {
        $base = collect([
            (int) $memo->pembuat,
            $approverId ?: $this->resolveApproverUserId($memo),
        ]);

        $tujuan = $this->parseRecipientUserIds((string) $memo->tujuan);
        $tembusan = $this->parseRecipientUserIds((string) $memo->tembusan);
        $bcc = $this->parseRecipientUserIds((string) $memo->bcc);

        return $base->merge($tujuan)->merge($tembusan)->merge($bcc)->map(fn($id) => (int) $id)->filter(fn($id) => $id > 0)->unique()->values()->all();
    }

    //== Fungsi Nomor Seri dan Dokumen Manual==//
    public function nextSeri(Request $request)
    {
        // Validasi input seri dan nomor surat manual
        $request->validate(
            [
                'seri_surat' => 'required|string|max:50',
                'nomor_surat' => 'required|string|max:100',
            ],
            [
                'seri_surat.required' => 'Seri surat wajib diisi.',
                'nomor_surat.required' => 'Nomor surat wajib diisi.',
            ],
        );

        // Ambil input dari user
        $seriSurat = $request->input('seri_surat');
        $nomorMemo = $request->input('nomor_Memo');

        // Kembalikan hasilnya agar bisa dipakai di tempat lain
        return [
            'seri_surat' => $seriSurat,
            'nomor_Memo' => $nomorMemo,
        ];
    }

    private function getOrgTreeWithUsers()
    {
        $directors = Director::with(['users.position', 'divisi.users.position', 'divisi.department.users.position', 'divisi.department.section.users.position', 'divisi.department.section.unit.users.position', 'department.users.position', 'department.section.users.position', 'department.section.unit.users.position'])->get();

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
        return array_values(
            array_filter($users, function ($user) use ($level) {
                return ($level === 'director' && is_null($user['divisi_id_divisi']) && is_null($user['department_id_department']) && is_null($user['section_id_section']) && is_null($user['unit_id_unit'])) || ($level === 'divisi' && !is_null($user['divisi_id_divisi']) && is_null($user['department_id_department']) && is_null($user['section_id_section']) && is_null($user['unit_id_unit'])) || ($level === 'department' && !is_null($user['department_id_department']) && is_null($user['section_id_section']) && is_null($user['unit_id_unit'])) || ($level === 'section' && !is_null($user['section_id_section']) && is_null($user['unit_id_unit'])) || ($level === 'unit' && !is_null($user['unit_id_unit']));
            }),
        );
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
                    'Supervisor' => 'SPV',
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

        $hierarki =
            collect([$context['unit'] ?? null, $context['section'] ?? null, $context['department'] ?? null, $context['divisi'] ?? null, $context['director'] ?? null])
                ->filter()
                ->first() ?? '-';

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
                'children' => [],
                'state' => ['disabled' => true], // disable root nodes
                'checkbox_disabled' => true, // disable checkbox for director nodes
            ];

            // users at director
            $usersAtDirector = $this->filterUsersAtLevel($director['users'] ?? [], 'director');
            foreach ($usersAtDirector as $user) {
                $dirNode['children'][] = [
                    'id' => 'user-' . $user['id'],
                    'text' => $this->getUserText($user, ['director' => $dirNode['text']]),
                    'icon' => 'fa fa-user',
                ];
            }
            $addedDepartments = [];

            foreach ($director['divisi'] ?? [] as $divisi) {
                $divName = $divisi['nm_divisi'] ?? ($divisi['name_divisi'] ?? 'Divisi');
                $divNode = [
                    'id' => 'divisi-' . ($divisi['id_divisi'] ?? ''),
                    'text' => $divName,
                    'children' => [],
                ];

                // divisi users
                $usersAtDivisi = $this->filterUsersAtLevel($divisi['users'] ?? [], 'divisi');
                foreach ($usersAtDivisi as $user) {
                    $divNode['children'][] = [
                        'id' => 'user-' . $user['id'],
                        'text' => $this->getUserText($user, [
                            'director' => $dirNode['text'],
                            'divisi' => $divName,
                        ]),
                        'icon' => 'fa fa-user',
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
                        'divisi' => $divName,
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
                    'director' => $dirNode['text'],
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
            'children' => [],
        ];

        // users at department
        $usersAtDepartment = $this->filterUsersAtLevel($dept['users'] ?? [], 'department');
        foreach ($usersAtDepartment as $user) {
            $deptNode['children'][] = [
                'id' => 'user-' . $user['id'],
                'text' => $this->getUserText($user, array_merge($ctx, ['department' => $deptName])),
                'icon' => 'fa fa-user',
            ];
        }

        // sections -> units
        foreach ($dept['section'] ?? [] as $section) {
            $sectionName = $section['name_section'] ?? 'Section';
            $sectionNode = [
                'id' => 'section-' . ($section['id_section'] ?? ''),
                'text' => $sectionName,
                'children' => [],
            ];

            $usersAtSection = $this->filterUsersAtLevel($section['users'] ?? [], 'section');
            foreach ($usersAtSection as $user) {
                $sectionNode['children'][] = [
                    'id' => 'user-' . $user['id'],
                    'text' => $this->getUserText(
                        $user,
                        array_merge($ctx, [
                            'department' => $deptName,
                            'section' => $sectionName,
                        ]),
                    ),
                    'icon' => 'fa fa-user',
                ];
            }

            foreach ($section['unit'] ?? [] as $unit) {
                $unitName = $unit['name_unit'] ?? 'Unit';
                $unitNode = [
                    'id' => 'unit-' . ($unit['id_unit'] ?? ''),
                    'text' => $unitName,
                    'children' => [],
                ];

                $usersAtUnit = $this->filterUsersAtLevel($unit['users'] ?? [], 'unit');
                foreach ($usersAtUnit as $user) {
                    $unitNode['children'][] = [
                        'id' => 'user-' . $user['id'],
                        'text' => $this->getUserText(
                            $user,
                            array_merge($ctx, [
                                'department' => $deptName,
                                'section' => $sectionName,
                                'unit' => $unitName,
                            ]),
                        ),
                        'icon' => 'fa fa-user',
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
        if ($user->department_id_department != null) {
            $divisiName = Department::where('id_department', $user->department_id_department)->first();
            if ($divisiName->kode_department != null) {
                $divisiName = $divisiName->kode_department;
            } elseif ($divisiName->kode_department == null) {
                if ($user->divisi_id_divisi == null) {
                    $divisiName = $divisiName->name_department;
                } else {
                    $divisiName = Divisi::where('id_divisi', $user->divisi_id_divisi)->first();
                    if ($divisiName->kode_divisi != null) {
                        $divisiName = $divisiName->kode_divisi;
                    } elseif ($divisiName->kode_divisi == null) {
                        $divisiName = $divisiName->nm_divisi;
                    }
                }
            }
        } elseif ($user->divisi_id_divisi != null) {
            $divisiName = Divisi::where('id_divisi', $user->divisi_id_divisi)->first();
            if ($divisiName->kode_divisi != null) {
                $divisiName = $divisiName->kode_divisi;
            } elseif ($divisiName->kode_divisi == null) {
                $divisiName = $divisiName->nm_divisi;
            }
        } elseif ($user->director_id_director != null) {
            $divisiName = Director::where('id_director', $user->director_id_director)->first();
            $divisiName = $divisiName->kode_director;
        }

        return $divisiName;
    }

    private function containsEmoji($text)
    {
        if (empty($text)) {
            return false;
        }

        // Regex pattern untuk detect emoji
        $emojiPattern = '/[\x{1F600}-\x{1F64F}]|[\x{1F300}-\x{1F5FF}]|[\x{1F680}-\x{1F6FF}]|[\x{1F1E0}-\x{1F1FF}]|[\x{2600}-\x{26FF}]|[\x{2700}-\x{27BF}]/u';

        // Pattern tambahan untuk emoji lainnya
        $additionalEmojiPattern = '/[\x{1F900}-\x{1F9FF}]|[\x{1FA70}-\x{1FAFF}]|[\x{1F780}-\x{1F7FF}]|[\x{1F800}-\x{1F8FF}]/u';

        return preg_match($emojiPattern, $text) || preg_match($additionalEmojiPattern, $text);
    }
    private function validateNoEmoji($request)
    {
        // Fields you want to check
        $fieldsToCheck = ['judul', 'barang', 'satuan', 'barang*', 'satuan*'];
        $errors = [];

        foreach ($fieldsToCheck as $field) {
            if ($request->has($field)) {
                $value = $request->input($field);

                // If it's an array (like barang[]), loop through each value
                if (is_array($value)) {
                    foreach ($value as $index => $item) {
                        if ($this->containsEmoji($item)) {
                            $fieldName = $this->getFieldDisplayName($field);
                            $errors["{$field}.{$index}"] = "Kolom {$fieldName} nomor " . ($index + 1) . ' tidak boleh mengandung emoji.';
                        }
                    }
                }
                // If it's a single string, check directly
                else {
                    if ($this->containsEmoji($value)) {
                        $fieldName = $this->getFieldDisplayName($field);
                        $errors[$field] = "Kolom {$fieldName} tidak boleh mengandung emoji.";
                    }
                }
            }
        }

        return $errors;
    }

    private function getFieldDisplayName($field)
    {
        $names = [
            'judul' => 'perihal',
            'barang' => 'barang',
            'satuan' => 'satuan',
            'barang*' => 'barang',
            'satuan*' => 'satuan',
        ];

        return $names[$field] ?? ucfirst($field);
    }

    public function convertTujuanToUserId(array $rawTujuan)
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

        // Expand hierarchy supaya pilih level atas otomatis mencakup seluruh turunan
        if (!empty($divisions)) {
            $deptFromDivision = Department::whereIn('divisi_id_divisi', $divisions)->pluck('id_department')->map(fn($v) => (int) $v)->all();
            $departments = array_values(array_unique(array_merge($departments, $deptFromDivision)));
        }

        if (!empty($departments)) {
            $sectionFromDepartment = Section::whereIn('department_id_department', $departments)->pluck('id_section')->map(fn($v) => (int) $v)->all();
            $sections = array_values(array_unique(array_merge($sections, $sectionFromDepartment)));
        }

        if (!empty($sections)) {
            $unitFromSection = Unit::whereIn('section_id_section', $sections)->pluck('id_unit')->map(fn($v) => (int) $v)->all();
            $units = array_values(array_unique(array_merge($units, $unitFromSection)));
        }

        // Now query the users who match any of the IDs
        $users = User::where(function ($query) use ($departments, $sections, $divisions, $units, $users) {
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
        })
            ->pluck('id')
            ->toArray();

        // Final tujuan result:
        $tujuanId = $users;
        return $tujuanId;
    }

    //fungsi untuk ngubah daftar penerima dari per-user jadi per hierarki, public supaya bisa dipake di controller CetakMemoPDF juga
    public function simplifyRecipients($tujuanString)
    {
        $userIds = explode(';', $tujuanString);
        $userIds = array_filter($userIds); // remove empty

        // Group user IDs by unit
        $units = DB::table('users')->whereIn('id', $userIds)->get()->groupBy('unit_id');

        $selectedUnitIds = [];
        $remainingUserIds = [];

        foreach ($units as $unitId => $usersInUnit) {
            $totalUsersInUnit = DB::table('users')->where('unit_id', $unitId)->count();

            if (count($usersInUnit) == $totalUsersInUnit) {
                $selectedUnitIds[] = $unitId;
            } else {
                $remainingUserIds = array_merge($remainingUserIds, $usersInUnit->pluck('id')->toArray());
            }
        }

        // Now group selected unit IDs by section
        $sections = DB::table('units')->whereIn('id', $selectedUnitIds)->get()->groupBy('section_id');

        $selectedSectionIds = [];
        $remainingUnitIds = [];

        foreach ($sections as $sectionId => $unitsInSection) {
            $totalUnitsInSection = DB::table('units')->where('section_id', $sectionId)->count();

            if (count($unitsInSection) == $totalUnitsInSection) {
                $selectedSectionIds[] = $sectionId;
            } else {
                $remainingUnitIds = array_merge($remainingUnitIds, $unitsInSection->pluck('id')->toArray());
            }
        }

        // Now group selected section IDs by department
        $departments = DB::table('section')->whereIn('id', $selectedSectionIds)->get()->groupBy('department_id_department');

        $selectedDepartmentIds = [];
        $remainingSectionIds = [];

        foreach ($departments as $departmentId => $sectionsInDept) {
            $totalSectionsInDept = DB::table('section')->where('department_id_department', $departmentId)->count();

            if (count($sectionsInDept) == $totalSectionsInDept) {
                $selectedDepartmentIds[] = $departmentId;
            } else {
                $remainingSectionIds = array_merge($remainingSectionIds, $sectionsInDept->pluck('id')->toArray());
            }
        }

        // Now group selected departments by divisi
        $divisis = DB::table('departments')->whereIn('id', $selectedDepartmentIds)->get()->groupBy('divisi_id');

        $selectedDivisiIds = [];
        $remainingDepartmentIds = [];

        foreach ($divisis as $divisiId => $departmentsInDiv) {
            $totalDeptsInDiv = DB::table('department')->where('divisi_id_divisi', $divisiId)->count();

            if (count($departmentsInDiv) == $totalDeptsInDiv) {
                $selectedDivisiIds[] = $divisiId;
            } else {
                $remainingDepartmentIds = array_merge($remainingDepartmentIds, $departmentsInDiv->pluck('id')->toArray());
            }
        }

        return [
            'divisi' => $selectedDivisiIds,
            'departments' => $remainingDepartmentIds,
            'sections' => $remainingSectionIds,
            'units' => $remainingUnitIds,
            'users' => $remainingUserIds,
        ];
    }
    public function collapseRecipients2(array $selectedUserIds)
    {
        $selected = collect($selectedUserIds)->map(fn($id) => (int) $id);

        // Start from users and move up
        $collapsed = $this->collapseAtLevel($selected, 'unit_id_unit', 'unit');
        $collapsed = $this->collapseAtLevel($collapsed, 'section_id_section', 'section');
        $collapsed = $this->collapseAtLevel($collapsed, 'department_id_department', 'department');
        $collapsed = $this->collapseAtLevel($collapsed, 'divisi_id_divisi', 'divisi');

        return $collapsed->implode(';'); // return string
    }

    protected function collapseAtLevel2(Collection $items, string $parentKey, string $table)
    {
        // Group by parent
        $grouped = DB::table('users')->whereIn('id', $items)->get()->groupBy($parentKey);

        $collapsed = collect();

        foreach ($grouped as $parentId => $children) {
            $allUserIds = DB::table('users')->where($parentKey, $parentId)->pluck('id');

            $selectedIds = $children->pluck('id');

            if ($selectedIds->sort()->values()->all() === $allUserIds->sort()->values()->all()) {
                // All children under this parent are selected → collapse
                $collapsed->push($parentKey . ':' . $parentId);
            } else {
                $collapsed = $collapsed->merge($selectedIds);
            }
        }

        return $collapsed;
    }

    public function collapseRecipients3(array $selectedUserIds)
    {
        $selected = collect($selectedUserIds)->map(fn($id) => (int) $id);

        // Load all user data with parent info
        $users = DB::table('users')
            ->whereIn('id', $selected)
            ->get(['id', 'unit_id_unit', 'section_id_section', 'department_id_department', 'divisi_id_divisi']);

        // Collapse upward
        $result = $this->collapseAtLevel($users, 'unit_id_unit', 'users');
        $result = $this->collapseAtLevel($result, 'section_id_section', 'users');
        $result = $this->collapseAtLevel($result, 'department_id_department', 'users');
        $result = $this->collapseAtLevel($result, 'divisi_id_divisi', 'users');

        // Final clean-up: extract IDs or tags like "unit:5"
        return $result->map(fn($item) => is_array($item) ? "{$item['level']}:{$item['id']}" : $item)->implode(';');
    }

    protected function collapseAtLevel3($items, $levelKey, $userTable)
    {
        $grouped = collect();

        foreach ($items as $item) {
            // item can be user_id (int) or ['level' => ..., 'id' => ...]
            if (is_int($item)) {
                $user = DB::table($userTable)
                    ->where('id', $item)
                    ->first([$levelKey, 'id']);
                $grouped[$user->$levelKey][] = $user->id;
            } else {
                // Already collapsed higher — pass through
                $grouped[] = $item;
            }
        }

        $collapsed = collect();

        foreach ($grouped as $parentId => $userIds) {
            if (is_numeric($parentId)) {
                $allUsersUnderParent = DB::table($userTable)->where($levelKey, $parentId)->pluck('id')->all();

                sort($allUsersUnderParent);
                sort($userIds);

                if ($userIds == $allUsersUnderParent) {
                    // All users selected — collapse
                    $collapsed->push(['level' => str_replace('_id', '', $levelKey), 'id' => $parentId]);
                } else {
                    $collapsed = $collapsed->merge($userIds);
                }
            } else {
                // This is already collapsed at a higher level
                $collapsed->push($parentId);
            }
        }

        return $collapsed;
    }
    protected function collapseHierarchies($selectedUserIds)
    {
        $levels = [
            'unit_id_unit' => 'unit',
            'section_id_section' => 'section',
            'department_id_department' => 'department',
            'divisi_id_divisi' => 'divisi',
        ];

        $userTable = 'users';
        $selected = collect($selectedUserIds); // These are user IDs (integers)

        // Step 1: Collapse at each level progressively from bottom to top
        foreach ($levels as $levelKey => $levelName) {
            $selected = $this->collapseAtLevel($selected, $levelKey, $userTable);
        }

        return $selected;
    }
    protected function collapseAtLevel($items, $levelKey, $userTable)
    {
        $grouped = collect();

        foreach ($items as $item) {
            if (is_int($item)) {
                $user = DB::table($userTable)
                    ->where('id', $item)
                    ->first([$levelKey, 'id']);
                if ($user && $user->$levelKey !== null) {
                    $grouped[$user->$levelKey][] = $user->id;
                }
            } elseif (is_array($item) && isset($item['level'], $item['id'])) {
                // Already collapsed at higher level, just push as-is
                $grouped[] = $item;
            }
        }

        $collapsed = collect();

        foreach ($grouped as $parentId => $userIds) {
            if (is_numeric($parentId)) {
                $allUsersUnderParent = DB::table($userTable)->where($levelKey, $parentId)->pluck('id')->all();

                // Sort both arrays for accurate comparison
                sort($allUsersUnderParent);
                sort($userIds);

                if ($userIds == $allUsersUnderParent) {
                    $collapsed->push([
                        'level' => str_replace(['_id_', '_id'], '', $levelKey), // handles `unit_id_unit` etc.
                        'id' => $parentId,
                    ]);
                } else {
                    $collapsed = $collapsed->merge($userIds);
                }
            } else {
                $collapsed->push($userIds); // $userIds is actually a collapsed object here
            }
        }

        return $collapsed;
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
            12 => 'XII',
        ];
        return $map[$number];
    }
    public function updateDocumentStatus(Memo $memo)
    {
        $recipients = $memo->recipients;

        if ($recipients->every(fn($recipient) => $recipient->status === 'approve')) {
            $memo->update(['status' => 'approve']);
        } elseif ($recipients->contains(fn($recipient) => $recipient->status === 'reject')) {
            $memo->update(['status' => 'reject']);
        } elseif ($recipients->contains(fn($recipient) => $recipient->status === 'correction')) {
            $memo->update(['status' => 'correction']);
        } else {
            $memo->update(['status' => 'pending']);
        }
    }

    public function updateDocumentApprovalDate(Memo $memo)
    {
        if ($memo->status !== 'pending') {
            $memo->update(['tanggal_disahkan' => now()]);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $requestStatus = $request->status;

        $authUser = Auth::user();
        $authId = (int) $authUser->id;

        // Validasi input
        if ($requestStatus == 'approve') {
            $request->validate([
                'status' => 'required|in:approve,reject,pending,correction',
                'catatan' => 'nullable|string',
            ]);
        } else {
            $request->validate([
                'status' => 'required|in:approve,reject,pending,correction',
                'catatan' => 'required|string',
            ]);
        }

        $notifService = app(NotifService::class);

        return DB::transaction(function () use ($id, $request, $requestStatus, $authUser, $authId, $notifService) {
            // LOCK memo row untuk cegah double-approve paralel
            $memo = Memo::where('id_memo', $id)->lockForUpdate()->firstOrFail();

            // Kalau sudah approve dan user pencet approve lagi -> stop (idempotent)
            if ($requestStatus === 'approve' && $memo->status === 'approve') {
                // tetap arahkan sukses biar UX aman
                return redirect()->route('memo.terkirim')->with('success', 'Memo sudah disetujui sebelumnya.');
            }

            $userDivDeptKode = $this->getDivDeptKode($authUser);

            // Lock current kirim row (kalau ada) biar aman dari race
            $currentKirim = Kirim_document::where('id_document', $id)->where('jenis_document', 'memo')->where('id_penerima', $authId)->lockForUpdate()->first();

            // Update memo + current kirim
            $memo->status = $requestStatus;

            if ($currentKirim) {
                $currentKirim->status = $requestStatus;
                $currentKirim->updated_at = now();
                $currentKirim->save();
            }

            // ============================
            // APPROVE
            // ============================
            if ($requestStatus == 'approve') {
                $tglDisahkan = now();
                $memo->tgl_disahkan = $tglDisahkan;

                // Generate nomor memo bila perlu
                if (empty($memo->nomor_memo) || strpos((string) $memo->nomor_memo, 'DRAFT') !== false) {
                    $bulanRomawi = CounterNomorSurat::getBulanRomawi(now()->month);

                    try {
                        $counter = CounterNomorSurat::createNomorSurat([
                            'tanggal_permintaan' => $tglDisahkan,
                            'perusahaan' => 'REKA',
                            'kode_tipe_surat' => 'GEN',
                            'divisi' => $memo->kode_bagian ?? $userDivDeptKode,
                            'bulan' => $bulanRomawi,
                            'tahun' => $tglDisahkan->year,
                            'pic_peminta' => $authUser->fullname,
                            'jenis' => 'Memo',
                            'perihal' => $memo->judul,
                        ]);

                        $memo->nomor_memo = $counter->nomor_surat_generated;
                    } catch (\Exception $e) {
                        Log::error('Generate nomor surat gagal: ' . $e->getMessage());

                        $divDeptKode = $this->getDivDeptKode($authUser);
                        $nextSeri = \App\Models\Seri::getNextSeri(false);

                        $memo->nomor_memo = sprintf('%02d.%02d/REKA/GEN/%s/%s/%d', $nextSeri['seri_tahunan'], $nextSeri['seri_bulanan'], strtoupper($divDeptKode), CounterNomorSurat::getBulanRomawi(now()->month), now()->year);
                    }
                }

                // Generate QR (kalau mau lebih hemat, bisa generate hanya jika qr_approved_by masih null)
                $qrText = 'Disetujui oleh: ' . $authUser->firstname . ' ' . $authUser->lastname . "\nNomor Memo: " . ($memo->nomor_memo ?? '-') . "\nTanggal: " . $tglDisahkan->translatedFormat('l, d F Y H:i:s') . "\nDikeluarkan oleh website SIPO PT Rekaindo Global Jasa";

                try {
                    $memo->qr_approved_by = (new QrCodeService())->generateWithLogo($qrText);
                } catch (\Exception $e) {
                    Log::error('Generate QR Code gagal: ' . $e->getMessage());
                }
                // ----------------------------
                // Kirim ke penerima (Memo Masuk) - idempotent
                // ----------------------------
                $tujuanUserIds = is_array($memo->tujuan) ? $memo->tujuan : explode(';', (string) $memo->tujuan);
                $tujuanUserIds = collect($tujuanUserIds)->map(fn($v) => (int) trim($v))->filter(fn($v) => $v > 0)->unique()->values();

                foreach ($tujuanUserIds as $tujuanId) {
                    if ($tujuanId === $authId) {
                        continue;
                    }

                    $penerima = \App\Models\User::find($tujuanId);
                    if (!$penerima) {
                        continue;
                    }

                    // kirim_document approve ke penerima: tidak dobel
                    \App\Models\Kirim_Document::firstOrCreate(
                        [
                            'id_document' => $memo->id_memo,
                            'jenis_document' => 'memo',
                            'id_penerima' => $penerima->id,
                            'status' => 'approve',
                        ],
                        [
                            'id_pengirim' => $currentKirim ? $currentKirim->id_pengirim : $authId,
                            'updated_at' => now(),
                        ],
                    );

                    // Distribusi final tetap berjalan lewat kirim_document (jangan dihapus).
                }

                // Target notifikasi approve final diambil dari business rule, bukan kirim_document.
                $finalRecipients = $this->getFinalNotificationRecipients($memo, $authId);
                $workflowRecipients = collect($this->getWorkflowNotificationRecipients($memo))->values();

                foreach ($finalRecipients as $recipientId) {
                    $isWorkflowRecipient = $workflowRecipients->contains((int) $recipientId);
                    $judulNotif = $isWorkflowRecipient ? 'Memo Disetujui' : 'Memo Masuk';

                    $notifService->createAndPush(
                        (int) $recipientId,
                        $judulNotif,
                        $memo->judul,
                        (int) $memo->id_memo,
                        'memo'
                    );
                }

                // Self kirim_document approve untuk approver (optional) - idempotent
                if ($authId !== (int) $memo->pembuat) {
                    \App\Models\Kirim_Document::firstOrCreate(
                        [
                            'id_document' => $memo->id_memo,
                            'jenis_document' => 'memo',
                            'id_pengirim' => $authId,
                            'id_penerima' => $authId,
                            'status' => 'approve',
                        ],
                        [
                            'updated_at' => now(),
                        ],
                    );
                }

                // ============================
                // REJECT
                // ============================
            } elseif ($requestStatus == 'reject') {
                $memo->tgl_disahkan = now();

                foreach ($this->getWorkflowNotificationRecipients($memo) as $recipientId) {
                    $notifService->createAndPush(
                        (int) $recipientId,
                        'Memo Ditolak',
                        $memo->judul,
                        (int) $memo->id_memo,
                        'memo'
                    );
                }

                // ============================
                // CORRECTION
                // ============================
            } elseif ($requestStatus == 'correction') {
                foreach ($this->getWorkflowNotificationRecipients($memo) as $recipientId) {
                    $notifService->createAndPush(
                        (int) $recipientId,
                        'Memo Perlu Revisi',
                        $memo->judul,
                        (int) $memo->id_memo,
                        'memo'
                    );
                }

                // ============================
                // PENDING
                // ============================
            } else {
                $memo->tgl_disahkan = null;
            }

            // Catatan
            $memo->catatan = $request->catatan;
            $memo->save();

            return redirect()
                ->route('memo.terkirim')
                ->with('success', 'Memo berhasil di-' . $requestStatus);
        });
    }

    public function edit($id)
    {
        $memo = Memo::findOrFail($id);
        $divisi = Divisi::all();
        $divisiId = Auth::user()->divisi_id_divisi;
        $seri = Seri::all();

        $managers = User::where('role_id_role', 3)->get(['id', 'firstname', 'lastname']);

        $orgTree = $this->getOrgTreeWithUsers();
        $jsTreeData = $this->convertToJsTree($orgTree);
        $mainDirector = $orgTree[0] ?? null;

        $tujuanArray = $memo->tujuan_string ? explode(';', $memo->tujuan_string) : [];
        return view('memo.edit', compact('memo', 'divisi', 'seri', 'managers', 'orgTree', 'jsTreeData', 'mainDirector', 'tujuanArray'));
    }

    public function update(Request $request, $id)
    {
        $memo = Memo::findOrFail($id);

        $emojiErrors = $this->validateNoEmoji($request);
        if (!empty($emojiErrors)) {
            return redirect()->back()->withErrors($emojiErrors)->withInput();
        }

        $request->validate(
            [
                'judul' => 'required|string|max:255',
                'isi_memo' => [
                    'required',
                    function ($attribute, $value, $fail) {
                        $clean = strip_tags($value);
                        $clean = html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        $clean = preg_replace('/\xc2\xa0|\s+/u', '', $clean);
                        if ($clean === '') {
                            $fail('Isi memo tidak boleh kosong.');
                        }
                    },
                ],
                'tujuan' => 'required|array|min:1',
                'tujuanString' => 'required|array|min:1',
                'nomor_memo' => 'required|string|max:255',
                'nama_bertandatangan' => 'required|string|max:255',
                'tgl_dibuat' => 'required|date',
                'tgl_disahkan' => 'nullable|date',
                'kategori_barang' => 'sometimes|required|array|min:1',
                'kategori_barang.*.barang' => 'sometimes|required|string',
                'kategori_barang.*.qty' => 'sometimes|required|integer|min:1',
                'kategori_barang.*.satuan' => 'sometimes|required|string',
            ],
            [
                'kategori_barang.*.barang.required' => 'Nama barang harus diisi.',
                'kategori_barang.*.qty.required' => 'Qty barang harus diisi.',
                'kategori_barang.*.satuan.required' => 'Satuan barang harus diisi.',
            ],
        );

        if ($request->filled('judul')) {
            $memo->judul = $request->judul;
        }
        if ($request->filled('isi_memo')) {
            $memo->isi_memo = $request->isi_memo;
        }
        if ($request->filled('tujuan')) {
            $tujuanId = $this->convertTujuanToUserId($request->tujuan);
            $memo->tujuan = implode(';', $tujuanId);
        }
        if ($request->filled('tujuanString')) {
            $memo->tujuan_string = implode(';', $request->tujuanString);
        }
        if ($request->filled('nomor_memo')) {
            $memo->nomor_memo = $request->nomor_memo;
        }
        if ($request->filled('nama_bertandatangan')) {
            $memo->nama_bertandatangan = $request->nama_bertandatangan;
        }
        if ($request->filled('tgl_dibuat')) {
            $memo->tgl_dibuat = $request->tgl_dibuat;
        }
        if ($request->filled('seri_surat')) {
            $memo->seri_surat = $request->seri_surat;
        }
        if ($request->filled('tgl_disahkan')) {
            $memo->tgl_disahkan = $request->tgl_disahkan;
        }

        // versi lama: lampiran disimpan sebagai BLOB
        if ($request->hasFile('lampiran')) {
            $file = $request->file('lampiran');
            $memo->lampiran = file_get_contents($file->getRealPath());
        }

        // resubmit => balik pending
        $memo->status = 'pending';
        $memo->save();

        Kirim_Document::where('id_document', $memo->id_memo)
            ->where('jenis_document', 'memo')
            ->where('jenis_penerima', 'approver')
            ->update([
                'id_penerima' => (int) $request->manager_user_id,
                'updated_at' => now(),
            ]);
        // Update status pada kirim_document juga jika ada
        // Kirim_Document::where('id_document', $memo->id_memo)
        //     ->where('jenis_document', 'memo')
        //     ->update(['status' => 'pending', 'updated_at' => now()]);

        // ============================
        // ✅ NOTIF + PUSH KE APPROVER DAN PEMBUAT SETIAP KALI MEMO DISIMPAN
        // ============================
        $notifService = app(NotifService::class);

        // ambil semua approver dari kirim_document
        $approverIds = Kirim_Document::where('id_document', $memo->id_memo)
            ->where('jenis_document', 'memo')
            ->pluck('id_penerima')
            ->map(fn($v) => (int) $v)
            ->filter(fn($v) => $v > 0)
            ->values()
            ->toArray();

        // gabungkan approver + pembuat, lalu unique biar tidak dobel
        $recipientIds = collect($approverIds)
            ->push((int) $memo->pembuat)
            ->unique()
            ->values();

        foreach ($recipientIds as $recipientId) {
            $isPembuat = ((int) $recipientId === (int) $memo->pembuat);

            $judulNotif = $isPembuat
                ? 'Memo Anda Berhasil Diupdate'
                : 'Memo Telah Diupdate';

            $bodyNotif = $memo->judul;

            // simpan / update notif database
            Notifikasi::updateOrCreate(
                [
                    'id_user' => (int) $recipientId,
                    'judul' => $judulNotif,
                    'judul_document' => $memo->judul,
                    'id_document' => (int) $memo->id_memo,
                ],
                [
                    'dibaca' => 0,
                    'updated_at' => now(),
                ],
            );

            // push ke mobile SETIAP KALI SAVE
            $notifService->createAndPush(
                (int) $recipientId,
                $judulNotif,
                $bodyNotif,
                (int) $memo->id_memo
            );
        }
        // ============================

        if ($request->has('kategori_barang')) {
            foreach ($request->kategori_barang as $dataBarang) {
                if (isset($dataBarang['id_kategori_barang']) && $dataBarang['id_kategori_barang'] != null) {
                    $barang = $memo->kategoriBarang()->find($dataBarang['id_kategori_barang']);
                    if ($barang) {
                        $barang->update([
                            'memo_id_memo' => $memo->id_memo,
                            'nomor' => $dataBarang['nomor'],
                            'barang' => $dataBarang['barang'],
                            'qty' => $dataBarang['qty'],
                            'satuan' => $dataBarang['satuan'],
                        ]);
                    }
                }
            }
        }

        if (Auth::user()->role_id_role == 1) {
            return redirect()->route('superadmin.memo.index')->with('success', 'Memo berhasil diubah.');
        } else {
            return redirect()
                ->route(Auth::user()->role->nm_role . '.memo.terkirim')
                ->with('success', 'Memo berhasil diubah.');
        }
    }

    //HAPUS SEMENTARA
    public function delete($id)
    {
        $memo = Memo::findOrFail($id);
        $memo->delete();
        Kirim_Document::where('id_document', $id)->where('jenis_document', 'memo')->delete();
        return response()->json(['success' => true]);
    }

    //  menampilkan file yang disimpan dalam database
    public function showFile($id)
    {
        $memo = Memo::findOrFail($id);

        if (!$memo->lampiran) {
            return response()->json(['error' => 'File tidak ditemukan.'], 404);
        }

        $fileContent = base64_decode($memo->lampiran);
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
            'image/png' => 'png',
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
    public function downloadFile($id)
    {
        $memo = Memo::findOrFail($id);

        if (!$memo->lampiran) {
            return redirect()->back()->with('error', 'File tidak ditemukan.');
        }

        $fileData = base64_decode($memo->lampiran);
        $mimeType = finfo_buffer(finfo_open(), $fileData, FILEINFO_MIME_TYPE);
        $extension = $this->getExtension($mimeType);

        return response()->streamDownload(
            function () use ($fileData) {
                echo $fileData;
            },
            "memo_{$id}.$extension",
            ['Content-Type' => $mimeType],
        );
    }

    public function showTerkirim($id)
    {
        $userId = Auth::id();

        $memo = Memo::where('id_memo', $id)->firstOrFail();

        $pembuat = User::withTrashed()
            ->where('id', $memo->pembuat)
            ->first();

        $divDeptKode = $this->getDivDeptKode(Auth::user());

        $memoCollection = collect([$memo]);

        $memoCollection->transform(function ($memo) use ($userId) {
            if ($memo->divisi_id_divisi === Auth::user()->divisi_id_divisi) {
                $memo->final_status = $memo->status;
            } else {
                $statusKirim = Kirim_Document::where('id_document', $memo->id_memo)
                    ->where('jenis_document', 'memo')
                    ->where('id_penerima', $userId)
                    ->first();

                $memo->final_status = $statusKirim ? $statusKirim->status : '-';
            }

            return $memo;
        });

        $memo = $memoCollection->first();

        $memo2 = Memo::where('id_memo', $id)->firstOrFail();

        $lampiranData = [];

        if ($memo2->lampiran) {
            $jsonData = json_decode($memo2->lampiran, true);

            if ($jsonData !== null && is_array($jsonData)) {
                $lampiranData = $jsonData;
            }
        }

        $memoRujukan = null;

        if (!is_null($memo2->feedback)) {
            $memoRujukan = Memo::find($memo2->feedback);
        }

        $tujuanUserIds = $this->parseRecipientUserIds($memo2->tujuan);
        $tujuanLegacyNames = empty($tujuanUserIds)
            ? $this->parseLegacyRecipientNames($memo2->tujuan, $memo2->tujuan_string)
            : [];
        $tujuanDisplayList = $this->buildGroupedRecipientDisplayList($tujuanUserIds, $tujuanLegacyNames);

        $tembusanUserIds = $this->parseRecipientUserIds($memo2->tembusan);
        $tembusanLegacyNames = empty($tembusanUserIds)
            ? $this->parseLegacyRecipientNames($memo2->tembusan)
            : [];
        $tembusanDisplayList = $this->buildGroupedRecipientDisplayList($tembusanUserIds, $tembusanLegacyNames);

        $parentCreatorId = $memo2->pembuat;

        $balasanMemos = Memo::where('feedback', $memo2->id_memo)
            ->where('status', 'approve')
            ->get()
            ->filter(function ($reply) use ($userId, $parentCreatorId) {
                $tujuanArray = array_filter(explode(';', $reply->tujuan ?? ''));
                $replyCreatorId = $reply->pembuat;

                return in_array((string) $userId, $tujuanArray, true)
                    || (int) $userId === (int) $parentCreatorId
                    || (int) $userId === (int) $replyCreatorId;
            })
            ->values();

        $canViewBcc = (int) $userId === (int) $memo2->pembuat;
        $bccDisplayList = [];

        if ($canViewBcc) {
            $bccUserIds = $this->parseRecipientUserIds($memo2->bcc);
            $bccLegacyNames = empty($bccUserIds)
                ? $this->parseLegacyRecipientNames($memo2->bcc)
                : [];
            $bccDisplayList = $this->buildGroupedRecipientDisplayList($bccUserIds, $bccLegacyNames);
        }

        return view('memo.view-memoTerkirim', compact(
            'memo',
            'divDeptKode',
            'pembuat',
            'lampiranData',
            'memoRujukan',
            'balasanMemos',
            'canViewBcc',
            'bccDisplayList',
            'tujuanDisplayList',
            'tembusanDisplayList'
        ));
    }

    public function showDiterima($id)
    {
        $userId = Auth::id();

        $memo = Memo::with(['divisi', 'kirimDocument'])
            ->where('id_memo', $id)
            ->where('status', 'approve')
            ->where(function ($query) use ($userId, $id) {
                $query
                    ->whereHas('kirimDocument', function ($sub) use ($userId) {
                        $sub->where('jenis_document', 'memo')
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
                    ->orWhereExists(function ($sub) use ($userId, $id) {
                        $sub->selectRaw(1)
                            ->from('disposisi')
                            ->where('document_type', 'memo')
                            ->where('document_id', $id)
                            ->whereRaw('JSON_CONTAINS(kepada_user_id, ?)', [json_encode((int) $userId)]);
                    });
            })
            ->firstOrFail();

        $pembuat = User::where('id', $memo->pembuat)->withTrashed()->first();
        $divDeptKode = $this->getDivDeptKode($pembuat);

        $lampiranData = [];
        if ($memo->lampiran) {
            $jsonData = json_decode($memo->lampiran, true);

            if ($jsonData !== null && is_array($jsonData)) {
                $lampiranData = $jsonData;
            }
        }

        $memoRujukan = null;
        if (!is_null($memo->feedback)) {
            $memoRujukan = Memo::find($memo->feedback);
        }

        $tujuanUserIds = $this->parseRecipientUserIds($memo->tujuan);
        $tujuanLegacyNames = empty($tujuanUserIds)
            ? $this->parseLegacyRecipientNames($memo->tujuan, $memo->tujuan_string)
            : [];
        $tujuanDisplayList = $this->buildGroupedRecipientDisplayList($tujuanUserIds, $tujuanLegacyNames);

        $tembusanUserIds = $this->parseRecipientUserIds($memo->tembusan);
        $tembusanLegacyNames = empty($tembusanUserIds)
            ? $this->parseLegacyRecipientNames($memo->tembusan)
            : [];
        $tembusanDisplayList = $this->buildGroupedRecipientDisplayList($tembusanUserIds, $tembusanLegacyNames);

        $parentCreatorId = $memo->pembuat;

        $balasanMemos = Memo::where('feedback', $memo->id_memo)
            ->where('status', 'approve')
            ->get()
            ->filter(function ($reply) use ($userId, $parentCreatorId) {
                $tujuanArray = array_filter(explode(';', $reply->tujuan ?? ''));
                $replyCreatorId = $reply->pembuat;

                return in_array((string) $userId, $tujuanArray, true)
                    || (int) $userId === (int) $parentCreatorId
                    || (int) $userId === (int) $replyCreatorId;
            })
            ->values();

        $canViewBcc = (int) $userId === (int) $memo->pembuat;
        $bccDisplayList = [];

        if ($canViewBcc) {
            $bccUserIds = $this->parseRecipientUserIds($memo->bcc);
            $bccLegacyNames = empty($bccUserIds)
                ? $this->parseLegacyRecipientNames($memo->bcc)
                : [];
            $bccDisplayList = $this->buildGroupedRecipientDisplayList($bccUserIds, $bccLegacyNames);
        }

        $isTembusan = collect(explode(';', (string) $memo->tembusan))
            ->map(fn ($item) => trim($item))
            ->filter(fn ($item) => $item !== '')
            ->contains((string) $userId);

        $isBcc = collect(explode(';', (string) $memo->bcc))
            ->map(fn ($item) => trim($item))
            ->filter(fn ($item) => $item !== '')
            ->contains((string) $userId);

        $isPenerimaLangsung = $memo->kirimDocument
            ->where('jenis_document', 'memo')
            ->where('id_penerima', $userId)
            ->isNotEmpty();

        $isPenerimaDisposisi = Disposisi::where('document_type', 'memo')
            ->where('document_id', $memo->id_memo)
            ->whereJsonContains('kepada_user_id', (int) $userId)
            ->exists();

        if ($isPenerimaLangsung) {
            $sumberDiterima = 'penerima';
        } elseif ($isTembusan) {
            $sumberDiterima = 'tembusan';
        } elseif ($isBcc) {
            $sumberDiterima = 'bcc';
        } elseif ($isPenerimaDisposisi) {
            $sumberDiterima = 'disposisi';
        } else {
            $sumberDiterima = '-';
        }

        return view('memo.view-memoDiterima', compact(
            'memo',
            'pembuat',
            'divDeptKode',
            'lampiranData',
            'balasanMemos',
            'memoRujukan',
            'canViewBcc',
            'bccDisplayList',
            'tujuanDisplayList',
            'tembusanDisplayList',
            'sumberDiterima'
        ));
    }

    // public function showDiterima($id)
    // {
    //     $userId = Auth::id();

    //     $memo = Memo::with(['divisi', 'kirimDocument'])
    //         ->where('id_memo', $id)
    //         ->where('status', 'approve')
    //         ->where(function ($query) use ($userId) {
    //             $query
    //                 ->whereHas('kirimDocument', function ($sub) use ($userId) {
    //                     $sub->where('jenis_document', 'memo')->where('id_penerima', $userId);
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
    //                 });
    //         })
    //         ->firstOrFail();

    //     $pembuat = User::where('id', $memo->pembuat)->withTrashed()->first();
    //     $divDeptKode = $this->getDivDeptKode($pembuat);

    //     $lampiranData = [];
    //     if ($memo->lampiran) {
    //         $jsonData = json_decode($memo->lampiran, true);
    //         if ($jsonData !== null && is_array($jsonData)) {
    //             $lampiranData = $jsonData;
    //         }
    //     }

    //     $memoRujukan = null;
    //     if (!is_null($memo->feedback)) {
    //         $memoRujukan = Memo::find($memo->feedback);
    //     }

    //     $parentCreatorId = $memo->pembuat;

    //     $balasanMemos = Memo::where('feedback', $memo->id_memo)
    //         ->where('status', 'approve')
    //         ->get()
    //         ->filter(function ($reply) use ($userId, $parentCreatorId) {
    //             $tujuanArray = array_filter(explode(';', $reply->tujuan ?? ''));
    //             $replyCreatorId = $reply->pembuat;

    //             return in_array($userId, $tujuanArray) || $userId == $parentCreatorId || $userId == $replyCreatorId;
    //         })
    //         ->values();

    //     $canViewBcc = $userId === (int) $memo->pembuat;
    //     $bccDisplayList = [];
    //     if ($canViewBcc) {
    //         $bccUserIds = $this->parseRecipientUserIds($memo->bcc);
    //         $bccDisplayList = $this->buildGroupedRecipientDisplayList($bccUserIds);
    //     }

    //     $isTembusan = collect(explode(';', (string) $memo->tembusan))
    //         ->map(fn($item) => trim($item))
    //         ->filter(fn($item) => $item !== '')
    //         ->contains((string) $userId);

    //     $sumberDiterima = $memo->kirimDocument->where('jenis_document', 'memo')->where('id_penerima', $userId)->isNotEmpty()
    //         ? 'penerima'
    //         : ($isTembusan ? 'tembusan' : 'bcc');

    //     return view('memo.view-memoDiterima', compact('memo', 'pembuat', 'divDeptKode', 'lampiranData', 'balasanMemos', 'memoRujukan', 'canViewBcc', 'bccDisplayList', 'sumberDiterima'));
    // }

    public function updateStatusNotif(Request $request, $id)
    {
        $memo = Memo::findOrFail($id);
        $memo->status = $request->status;
        $memo->save();

        // Simpan notifikasi
        Notifikasi::create([
            'judul' => "Memo {$request->status}",
            'jenis_document' => 'memo',
            'id_divisi' => $memo->divisi_id,
            'dibaca' => false,
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Status memo berhasil diperbarui.');
    }

    // endpoint GET /api/memos/{id}/lampiran
    public function lampiran($id)
    {
        $memo = Memo::findOrFail($id);

        if (!$memo->lampiran) {
            abort(404, 'Lampiran tidak ditemukan');
        }

        $lampiran = $memo->lampiran;

        // 1️⃣ Coba decode JSON → untuk kasus multiple file
        $decoded = json_decode($lampiran, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            // Kalau ternyata array, kembalikan daftar URL
            $urls = [];
            foreach ($decoded as $index => $fileBase64) {
                $urls[] = route('api.memo.lampiran.single', [
                    'id' => $id,
                    'index' => $index,
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Multiple lampiran ditemukan',
                'data' => $urls,
            ]);
        }

        // 2️⃣ Kalau single file
        $fileData = base64_decode($lampiran);

        if (!$fileData) {
            abort(404, 'Lampiran tidak valid');
        }

        // Deteksi mime type
        $finfo = finfo_open();
        $mimeType = finfo_buffer($finfo, $fileData, FILEINFO_MIME_TYPE);
        finfo_close($finfo);

        $extension = explode('/', $mimeType)[1] ?? 'bin';
        $fileName = "lampiran_{$id}." . $extension;

        return response($fileData, 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="' . $fileName . '"');
    }

    public function lampiranSingle($id, $index)
    {
        $memo = Memo::findOrFail($id);
        $decoded = json_decode($memo->lampiran, true);

        if (json_last_error() !== JSON_ERROR_NONE || !isset($decoded[$index])) {
            abort(404, 'Lampiran tidak ditemukan');
        }

        $fileData = base64_decode($decoded[$index]);

        $finfo = finfo_open();
        $mimeType = finfo_buffer($finfo, $fileData, FILEINFO_MIME_TYPE);
        finfo_close($finfo);

        $extension = explode('/', $mimeType)[1] ?? 'bin';
        $fileName = "lampiran_{$id}_{$index}." . $extension;

        return response($fileData, 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="' . $fileName . '"');
    }

    public function downloadAll($id)
    {
        $memo = Memo::findOrFail($id);

        if (!$memo->lampiran) {
            abort(404, 'Lampiran tidak ditemukan');
        }

        // support single base64 string or JSON array of base64 strings
        $decoded = json_decode($memo->lampiran, true);
        $files = json_last_error() === JSON_ERROR_NONE && is_array($decoded) ? $decoded : [$memo->lampiran];

        if (empty($files)) {
            abort(404, 'Tidak ada lampiran valid');
        }

        $tmpDir = storage_path('app/tmp_lampiran');
        if (!file_exists($tmpDir)) {
            @mkdir($tmpDir, 0755, true);
        }

        $pdf = new Fpdi();
        $pagesAdded = 0;
        $nonPdfFiles = [];
        $tempFiles = [];

        foreach ($files as $index => $fileBase64) {
            $fileData = base64_decode($fileBase64);
            if (!$fileData) {
                continue;
            }

            // simpan file sementara tanpa ekstensi dulu
            $tmpBase = $tmpDir . '/tmp_' . uniqid() . "_{$index}";
            file_put_contents($tmpBase, $fileData);
            $tempFiles[] = $tmpBase;

            // deteksi MIME
            $finfo = finfo_open();
            $mime = finfo_buffer($finfo, $fileData, FILEINFO_MIME_TYPE);
            finfo_close($finfo);

            // MAP untuk image -> ekstensi & tipe untuk FPDF
            $imageMap = [
                'image/jpeg' => ['ext' => 'jpg', 'type' => 'JPG'],
                'image/jpg' => ['ext' => 'jpg', 'type' => 'JPG'],
                'image/png' => ['ext' => 'png', 'type' => 'PNG'],
                'image/gif' => ['ext' => 'gif', 'type' => 'GIF'],
                'image/webp' => ['ext' => 'webp', 'type' => 'WEBP'],
            ];

            // PDF
            if ($mime === 'application/pdf') {
                $tmpPdf = $tmpBase . '.pdf';
                rename($tmpBase, $tmpPdf);
                $tempFiles[] = $tmpPdf;
                try {
                    $pageCount = $pdf->setSourceFile($tmpPdf);
                    for ($p = 1; $p <= $pageCount; $p++) {
                        $tplIdx = $pdf->importPage($p);
                        $size = $pdf->getTemplateSize($tplIdx);
                        // add page with same orientation/size
                        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                        $pdf->useTemplate($tplIdx);
                        $pagesAdded++;
                    }
                } catch (\Throwable $e) {
                    // \Log::error('FPDI import PDF error: ' . $e->getMessage());
                }
                continue;
            }

            // Image
            if (isset($imageMap[$mime])) {
                $ext = $imageMap[$mime]['ext'];
                $typeForFpdf = $imageMap[$mime]['type'];
                $tmpImg = $tmpBase . '.' . $ext;
                rename($tmpBase, $tmpImg);
                $tempFiles[] = $tmpImg;

                try {
                    $pdf->AddPage();
                    // let FPDF scale the image width ; adjust margins as needed
                    $pdf->Image($tmpImg, 10, 10, 190, 0, $typeForFpdf);
                    $pagesAdded++;
                } catch (\Throwable $e) {
                    //\Log::error('FPDF Image error: ' . $e->getMessage());
                }
                continue;
            }

            // Non-image, non-pdf: coba konversi (docx/xlsx/pptx) menggunakan libreoffice jika tersedia
            $convertMap = [
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
                'application/msword' => 'doc',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
                'application/vnd.ms-excel' => 'xls',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
                'application/vnd.ms-powerpoint' => 'ppt',
                // tambahkan tipe lain jika perlu
            ];

            if (isset($convertMap[$mime])) {
                $ext = $convertMap[$mime];
                $tmpDoc = $tmpBase . '.' . $ext;
                rename($tmpBase, $tmpDoc);
                $tempFiles[] = $tmpDoc;

                // coba konversi dengan libreoffice (pastikan libreoffice terinstall)
                // hasil konversi akan berada di same folder dengan nama file.pdf
                $cmd = 'libreoffice --headless --convert-to pdf ' . escapeshellarg($tmpDoc) . ' --outdir ' . escapeshellarg($tmpDir) . ' 2>&1';
                exec($cmd, $out, $ret);
                // hasil file pdf biasanya tmpDoc dengan .pdf ext
                $convertedPdf = $tmpDir . '/' . pathinfo($tmpDoc, PATHINFO_FILENAME) . '.pdf';
                if (file_exists($convertedPdf)) {
                    try {
                        $pageCount = $pdf->setSourceFile($convertedPdf);
                        for ($p = 1; $p <= $pageCount; $p++) {
                            $tplIdx = $pdf->importPage($p);
                            $size = $pdf->getTemplateSize($tplIdx);
                            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                            $pdf->useTemplate($tplIdx);
                            $pagesAdded++;
                        }
                        $tempFiles[] = $convertedPdf;
                        continue;
                    } catch (\Throwable $e) {
                        //\Log::error('FPDI import converted PDF error: ' . $e->getMessage());
                    }
                }

                // jika konversi gagal, masukkan ke daftar nonPdf untuk zip
                $nonPdfFiles[] = $tmpDoc;
                continue;
            }

            // fallback: kalau tidak diketahui tipe, coba treat sebagai image JPG (risky)
            // atau masukkan ke nonPdf untuk zip
            $nonPdfFiles[] = $tmpBase;
        }

        // hasil keluaran
        $outputPdf = storage_path("app/public/memo-{$id}-lampiran.pdf");

        if ($pagesAdded > 0) {
            $pdf->Output($outputPdf, 'F');
        }

        // kalau ada nonPdf files, buat zip berisi pdf (jika ada) + nonPdf
        if (!empty($nonPdfFiles)) {
            $zipPath = storage_path("app/public/memo-{$id}-lampiran.zip");
            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                if (file_exists($outputPdf)) {
                    $zip->addFile($outputPdf, basename($outputPdf));
                }
                foreach ($nonPdfFiles as $f) {
                    if (file_exists($f)) {
                        $zip->addFile($f, basename($f));
                    }
                }
                $zip->close();

                // bersihkan file sementara
                foreach ($tempFiles as $t) {
                    @unlink($t);
                }

                return response()->download($zipPath)->deleteFileAfterSend(true);
            } else {
                return response()->json(['message' => 'Gagal membuat ZIP'], 500);
            }
        }

        // jika ada PDF gabungan, kirim
        if (file_exists($outputPdf)) {
            foreach ($tempFiles as $t) {
                @unlink($t);
            }
            return response()->download($outputPdf)->deleteFileAfterSend(true);
        }

        // tidak ada output
        return response()->json(['message' => 'Tidak ada output yang dapat dihasilkan'], 404);
    }

    public function createCoba(Request $request)
    {
        //$divisiId = auth()->user()->divisi_id_divisi;
        //$divisiName = auth()->user()->divisi->nm_divisi;
        $divisiList = Divisi::all();

        $user = Auth::user();

        // Cek apakah ini memo balasan
        $replyToId = $request->query('reply_to'); // dari ?reply_to=...
        $parentMemo = null;

        if ($replyToId) {
            // sesuaikan dengan nama model & kolommu
            $parentMemo = Memo::find($replyToId);
        }

        if ($user->position_id_position == 1) {
            $idDirektur = Director::where('id_director', $user->director_id_director)->first();
            $kodeDirektur = $idDirektur->kode_director;
        } else {
            $kodeDirektur = '';
        }
        // dd($user);

        $divDeptKode = $this->getDivDeptKode($user);

        // Ambil nomor seri berikutnya
        $nextSeri = Seri::getNextSeri(false);

        // Konversi bulan ke angka Romawi
        $bulanRomawi = $this->convertToRoman(now()->month);

        // Format nomor dokumen baru
        $nomorDokumen = sprintf('%02d.%02d/REKA%s/GEN/%s/%s/%d', $nextSeri['seri_tahunan'], $nextSeri['seri_bulanan'], strtoupper($kodeDirektur), strtoupper($divDeptKode), $bulanRomawi, now()->year);

        // Daftar bagian kerja
        $bagianKerja = BagianKerja::orderBy('kode_bagian')->get();

        // Daftar manager yang satu divisi, department, section, dan unit dengan admin yg membuat suratnya
        if ($user->role_id_role !== 1) {
            $managers = User::with('position:id_position,nm_position')
                ->where('role_id_role', 3)
                ->orderBy('firstname')
                ->get(['id', 'firstname', 'lastname', 'position_id_position']);
        } else {
            $managers = User::with('position:id_position,nm_position')
                ->where('role_id_role', 3)
                ->where('director_id_director', $user->director_id_director)
                ->get(['id', 'firstname', 'lastname', 'position_id_position']);
        }
        $tembusan = [];

        $directors = Director::all();
        $division = Divisi::all();
        $department = Department::all();
        $section = Section::all();
        $unit = Unit::all();

        foreach ($directors as $director) {
            $tembusan[] = [
                'id' => 'director_' . $director->id_director,
                'name' => $director->name_director,
            ];
        }
        foreach ($division as $div) {
            $tembusan[] = [
                'id' => 'division_' . $div->id_divisi,
                'name' => $div->nm_divisi,
            ];
        }
        foreach ($department as $dept) {
            $tembusan[] = [
                'id' => 'department_' . $dept->id_department,
                'name' => $dept->name_department,
            ];
        }
        foreach ($section as $sect) {
            $tembusan[] = [
                'id' => 'section_' . $sect->id_section,
                'name' => $sect->name_section,
            ];
        }
        foreach ($unit as $u) {
            $tembusan[] = [
                'id' => 'unit_' . $u->id_unit,
                'name' => $u->name_unit,
            ];
        }
        // Ambil seluruh user dan struktur organisasi (untuk dropdown tree)
        $users = User::select('id', 'firstname', 'lastname', 'divisi_id_divisi', 'department_id_department', 'section_id_section', 'unit_id_unit')->get();
        $orgTree = $this->getOrgTreeWithUsers();
        $jsTreeData = $this->convertToJsTree($orgTree);
        $mainDirector = $orgTree[0] ?? null; // assuming the first node is the main director
        return view(
            'memo.add',
            [
                'nomorSeriTahunan' => $nextSeri['seri_tahunan'],
                'nomorDokumen' => $nomorDokumen,
                'managers' => $managers,
                'divisiList' => $divisiList,
                'users' => $users,
                'orgTree' => $orgTree,
                'jsTreeData' => $jsTreeData,
                'mainDirector' => $mainDirector,
                //feat tembusan
                'tembusan' => $tembusan,
                // tambahan untuk fitur balasan memo
                'parentMemo' => $parentMemo,
                'replyToId' => $replyToId,
            ],
            compact('divDeptKode', 'bagianKerja'),
        );
    }

    public function storeCoba(Request $request)
    {
        $emojiErrors = $this->validateNoEmoji($request);
        if (!empty($emojiErrors)) {
            return redirect()->back()->withErrors($emojiErrors)->withInput();
        }

        if (!$request->nama_bertandatangan) {
            $request->merge([
                'nama_bertandatangan' => User::where('id', $request->manager_user_id)->first()->firstname . ' ' . User::where('id', $request->manager_user_id)->first()->lastname,
            ]);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'judul' => 'required|string|max:255',
                'isi_memo' => [
                    'required',
                    function ($attribute, $value, $fail) {
                        $clean = strip_tags($value);
                        $clean = html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        $clean = preg_replace('/\xc2\xa0|\s+/u', '', $clean);
                        if ($clean === '') {
                            $fail('Isi memo tidak boleh kosong.');
                        }
                    },
                ],
                'tujuan' => 'required|array|min:1',
                'tujuanString' => 'required|array|min:1',
                'kode_bagian' => 'required|exists:bagian_kerja,kode_bagian',
                'nama_bertandatangan' => 'required|string|max:255',
                'manager_user_id' => 'required|exists:users,id',
                'pembuat' => 'required|string',
                'catatan' => 'nullable|string|max:255',
                'tgl_dibuat' => 'required|date',
                'tgl_disahkan' => 'nullable|date',
                'divisi_id_divisi' => 'nullable',
                'lampiran' => 'nullable',
                'memo_feedback' => 'nullable',
                'lampiran.*' => 'file|mimes:pdf,jpg,jpeg,png|max:2048',
                'qty' => 'sometimes|required|array',
                'qty.*' => 'required|numeric|min:1',
                'satuan' => 'sometimes|required|array|max:50',
                'satuan.*' => 'required|string|max:50',
            ],
            [
                'judul.required' => 'Perihal memo harus diisi.',
                'isi_memo.required' => 'Isi memo tidak boleh kosong.',
                'lampiran.*' => 'Lampiran gagal diunggah, pastikan format dan ukuran file sesuai.',
                'lampiran.*.mimes' => 'File harus berupa PDF, JPG, atau PNG.',
                'lampiran.*.max' => 'Ukuran tiap file tidak boleh lebih dari 2 MB.',
                'kode_bagian.required' => 'Bagian kerja harus dipilih.',
                'kode_bagian.exists' => 'Bagian kerja tidak valid.',
                'qty.*.required' => 'Qty barang harus diisi.',
                'satuan.*.required' => 'Satuan barang harus diisi.',
            ],
        );

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $isiMemo = $request->input('isi_memo');
        $divDeptKode = $this->getDivDeptKode(Auth::user());
        $tujuanId = $this->convertTujuanToUserId($request->tujuan);

        if (!$tujuanId) {
            return redirect()->back()->with('error', 'Tidak ada karyawan di dalam tujuan dokumen.');
        }

        // Proses file lampiran
        $lampiranPath = null;
        if ($request->hasFile('lampiran')) {
            $newFiles = [];
            foreach ($request->file('lampiran') as $file) {
                if ($file->isValid()) {
                    $ext = strtolower($file->getClientOriginalExtension());

                    if ($ext === 'pdf') {
                        $folder = 'lampiran/memo/pdf';
                    } elseif (in_array($ext, ['png', 'jpg', 'jpeg'])) {
                        $folder = 'lampiran/memo/image';
                    } else {
                        $folder = 'lampiran/memo/other';
                    }

                    $filename = time() . '_' . $file->getClientOriginalName();
                    $filePath = $file->storeAs($folder, $filename, 'public');

                    $newFiles[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $filePath,
                        'size' => $file->getSize(),
                        'uploaded_at' => now()->toDateTimeString(),
                    ];
                }
            }

            $allFiles = array_merge($newFiles);
            $lampiranPath = !empty($allFiles) ? json_encode($allFiles) : null;
        }

        $memo = null;
        $idMemoLama = null;

        // FEEDBACK MEMO: jika ini balasan memo
        if ($request->filled('memo_feedback')) {
            $parentNomor = $request->input('memo_feedback');
            $parent = Memo::where('nomor_memo', $parentNomor)->first();

            if ($parent) {
                $idMemoLama = $parent->id_memo;
            }
        }

        // CC/BCC Memo: simpan terpisah dari tujuan
        $tembusanUserIds = [];
        if ($request->has('tembusan') && is_array($request->tembusan)) {
            $tembusanUserIds = $this->convertTujuanToUserId($request->tembusan);
        }

        $bccUserIds = [];
        if ($request->has('bcc') && is_array($request->bcc)) {
            $bccUserIds = $this->convertTujuanToUserId($request->bcc);
        }

        // ===================================================================
        // 🎯 NOMOR MEMO = NULL (Akan di-generate setelah approve)
        // ===================================================================

        $memo = Memo::create([
            'judul' => $request->input('judul'),
            'tujuan' => implode(';', $tujuanId),
            'tujuan_string' => implode(';', $request->input('tujuanString')),
            'isi_memo' => $isiMemo,
            'nomor_memo' => null, // ✅ KOSONG hingga approve
            'kode_bagian' => $request->input('kode_bagian'), // ← PENTING: Simpan kode_bagian
            'seri_surat' => null, // Belum ada seri
            'tgl_dibuat' => $request->input('tgl_dibuat'),
            'tgl_disahkan' => null, // Belum disahkan
            'kode' => $divDeptKode,
            'pembuat' => $request->input('pembuat'),
            'catatan' => $request->input('catatan'),
            'status' => 'pending',
            'nama_bertandatangan' => $request->input('nama_bertandatangan'),
            'manager_user_id' => (int) $request->input('manager_user_id'),
            'lampiran' => $lampiranPath,
            'feedback' => $idMemoLama,
            'tembusan' => !empty($tembusanUserIds) ? implode(';', $tembusanUserIds) : null,
            'bcc' => !empty($bccUserIds) ? implode(';', $bccUserIds) : null,
        ]);

        // Handle kategori barang
        if ($request->has('jumlah_kolom') && !empty($request->nomor)) {
            foreach ($request->nomor as $key => $nomor) {
                kategori_barang::create([
                    'memo_id_memo' => $memo->id_memo,
                    'nomor' => $nomor,
                    'barang' => $request->barang[$key] ?? null,
                    'qty' => $request->qty[$key] ?? null,
                    'satuan' => $request->satuan[$key] ?? null,
                ]);
            }
        }

        $creator = Auth::user();
        $notifService = app(NotifService::class);

        // Workflow tetap pakai kirim_document, tapi source target notif pakai helper business rule.
        Kirim_document::updateOrCreate(
            [
                'id_document' => $memo->id_memo,
                'jenis_document' => 'memo',
                'id_pengirim' => $creator->id,
                'id_penerima' => (int) $request->manager_user_id,
            ],
            [
                'status' => 'pending',
                'jenis_penerima' => 'approver',
                'updated_at' => now(),
            ]
        );

        $workflowRecipients = $this->getWorkflowNotificationRecipients($memo, $request);
        foreach ($workflowRecipients as $recipientId) {
            $judulNotif = ((int) $recipientId === (int) $memo->pembuat)
                ? 'Memo Dalam Proses Persetujuan'
                : 'Memo Menunggu Persetujuan';

            $notifService->createAndPush(
                (int) $recipientId,
                $judulNotif,
                $memo->judul,
                (int) $memo->id_memo,
                'memo'
            );
        }

        return redirect()
        ->route('memo.terkirim')
        ->with('success', 'Dokumen berhasil dibuat dan menunggu persetujuan. Nomor surat akan diberikan setelah disetujui.');
    }

    public function editBaru($id)
    {
        $memo = Memo::findOrFail($id);
        $divisi = Divisi::all();
        $divisiId = Auth::user()->divisi_id_divisi;
        $seri = Seri::all();
        $bagianKerja = BagianKerja::orderBy('kode_bagian')->get();

        $parentMemo = null;
        if ($memo->feedback) {
            $parentMemo = Memo::find($memo->feedback);
        }

        $managers = User::where('role_id_role', 3)->get(['id', 'firstname', 'lastname']);

        // Tambahkan ini
        $penandatanganList = User::with('position:id_position,nm_position')
            ->where('role_id_role', 3)
            ->orderBy('firstname')
            ->get(['id', 'firstname', 'lastname', 'position_id_position']);

        $orgTree = $this->getOrgTreeWithUsers();
        $jsTreeData = $this->convertToJsTree($orgTree);
        $mainDirector = $orgTree[0] ?? null;

        $tujuanArray = [];
        if ($memo->tujuan) {
            $tujuanArray = collect(array_values(array_filter(explode(';', $memo->tujuan), fn($v) => trim($v) !== '')))
                ->filter(fn($v) => is_numeric($v))
                ->map(fn($v) => 'user-' . (int) $v)
                ->values()
                ->all();
        }

        $lampiranData = [];
        if ($memo->lampiran) {
            $jsonData = json_decode($memo->lampiran, true);
            if ($jsonData !== null && is_array($jsonData)) {
                $lampiranData = $jsonData;
            } else {
                $lampiranData = [];
            }
        }

        $tembusan = [];

        $directors = Director::all();
        $division = Divisi::all();
        $department = Department::all();
        $section = Section::all();
        $unit = Unit::all();

        foreach ($directors as $director) {
            $tembusan[] = [
                'id' => 'director_' . $director->id_director,
                'name' => $director->name_director,
            ];
        }
        foreach ($division as $div) {
            $tembusan[] = [
                'id' => 'division_' . $div->id_divisi,
                'name' => $div->nm_divisi,
            ];
        }
        foreach ($department as $dept) {
            $tembusan[] = [
                'id' => 'department_' . $dept->id_department,
                'name' => $dept->name_department,
            ];
        }
        foreach ($section as $sect) {
            $tembusan[] = [
                'id' => 'section_' . $sect->id_section,
                'name' => $sect->name_section,
            ];
        }
        foreach ($unit as $u) {
            $tembusan[] = [
                'id' => 'unit_' . $u->id_unit,
                'name' => $u->name_unit,
            ];
        }

        $selectedTembusan = [];
        if ($memo->tembusan) {
            $cc = array_values(array_filter(explode(';', $memo->tembusan), fn($v) => trim($v) !== ''));
            $selectedTembusan = collect($cc)->filter(fn($v) => is_numeric($v))->map(fn($v) => 'user-' . (int) $v)->values()->all();
        }

        $selectedBcc = [];
        if ($memo->bcc) {
            $bcc = array_values(array_filter(explode(';', $memo->bcc), fn($v) => trim($v) !== ''));
            $selectedBcc = collect($bcc)->filter(fn($v) => is_numeric($v))->map(fn($v) => 'user-' . (int) $v)->values()->all();
        }

        // Prioritas selected approver: manager_user_id, fallback ke nama_bertandatangan.
        $selectedManagerId = !empty($memo->manager_user_id) ? (int) $memo->manager_user_id : null;
        if (!$selectedManagerId && !empty($memo->nama_bertandatangan)) {
            $fallbackManager = User::findByFullname((string) $memo->nama_bertandatangan);
            $selectedManagerId = $fallbackManager ? (int) $fallbackManager->id : null;
        }

        return view('memo.edit', compact('memo', 'divisi', 'seri', 'managers', 'penandatanganList', 'orgTree', 'jsTreeData', 'mainDirector', 'tujuanArray', 'lampiranData', 'parentMemo', 'tembusan', 'selectedTembusan', 'selectedBcc', 'bagianKerja', 'selectedManagerId'));
    }

    public function updateBaru(Request $request, $id)
    {
        $memo = Memo::findOrFail($id);

        $emojiErrors = $this->validateNoEmoji($request);
        if (!empty($emojiErrors)) {
            return redirect()->back()->withErrors($emojiErrors)->withInput();
        }

        $request->validate(
            [
                'judul' => 'required|string|max:255',
                'isi_memo' => [
                    'required',
                    function ($attribute, $value, $fail) {
                        $clean = strip_tags($value);
                        $clean = html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        $clean = preg_replace('/\xc2\xa0|\s+/u', '', $clean);
                        if ($clean === '') {
                            $fail('Isi memo tidak boleh kosong.');
                        }
                    },
                ],
                'tujuan' => 'required|array|min:1',
                'tujuanString' => 'required|array|min:1',
                'nomor_memo' => 'string|max:255',
                'nama_bertandatangan' => 'required|string|max:255',
                'manager_user_id' => 'required|exists:users,id',
                'tgl_dibuat' => 'required|date',
                'kode_bagian' => 'required|exists:bagian_kerja,kode_bagian',
                'tgl_disahkan' => 'nullable|date',
                'lampiran.*' => 'file|mimes:pdf,jpg,jpeg,png|max:2048',
                'kategori_barang' => 'sometimes|required|array|min:1',
                'kategori_barang.*.barang' => 'sometimes|required|string',
                'kategori_barang.*.qty' => 'sometimes|required|integer|min:1',
                'kategori_barang.*.satuan' => 'sometimes|required|string',
            ],
            [
                'lampiran.*' => 'Lampiran gagal diunggah, pastikan format dan ukuran file sesuai ketentuan.',
                'kategori_barang.*.barang.required' => 'Nama barang harus diisi.',
                'kategori_barang.*.qty.required' => 'Qty barang harus diisi.',
                'kategori_barang.*.satuan.required' => 'Satuan barang harus diisi.',
            ],
        );
        // CC/BCC Memo: simpan terpisah dari tujuan
        $tujuanId = $this->convertTujuanToUserId($request->tujuan);
        $tembusanUserIds = [];
        if ($request->has('tembusan') && is_array($request->tembusan)) {
            $tembusanUserIds = $this->convertTujuanToUserId($request->tembusan);
            $memo->tembusan = !empty($tembusanUserIds) ? implode(';', $tembusanUserIds) : null;
        } else {
            $memo->tembusan = null;
        }

        $bccUserIds = [];
        if ($request->has('bcc') && is_array($request->bcc)) {
            $bccUserIds = $this->convertTujuanToUserId($request->bcc);
            $memo->bcc = !empty($bccUserIds) ? implode(';', $bccUserIds) : null;
        } else {
            $memo->bcc = null;
        }

        if ($request->filled('kode_bagian')) {
            $memo->kode_bagian = $request->kode_bagian;
        }
        if ($request->filled('judul')) {
            $memo->judul = $request->judul;
        }
        if ($request->filled('isi_memo')) {
            $memo->isi_memo = $request->isi_memo;
        }
        if ($request->filled('tujuan')) {
            $memo->tujuan = implode(';', $tujuanId);
        }
        if ($request->filled('tujuanString')) {
            $memo->tujuan_string = implode(';', $request->tujuanString);
        }
        if ($request->filled('nomor_memo')) {
            $memo->nomor_memo = $request->nomor_memo;
        }
        if ($request->filled('nama_bertandatangan')) {
            $memo->nama_bertandatangan = $request->nama_bertandatangan;
        }
        if ($request->filled('tgl_dibuat')) {
            $memo->tgl_dibuat = $request->tgl_dibuat;
        }
        if ($request->filled('seri_surat')) {
            $memo->seri_surat = $request->seri_surat;
        }
        if ($request->filled('tgl_disahkan')) {
            $memo->tgl_disahkan = $request->tgl_disahkan;
        }

        $notifService = app(NotifService::class);

        return DB::transaction(function () use ($request, $memo, $notifService) {
            // ============================
            // CC/BCC: simpan terpisah dari tujuan
            // ============================
            $tujuanId = $this->convertTujuanToUserId($request->tujuan);

            $tembusanUserIds = [];
            if ($request->has('tembusan') && is_array($request->tembusan)) {
                $tembusanUserIds = $this->convertTujuanToUserId($request->tembusan);
                $memo->tembusan = !empty($tembusanUserIds) ? implode(';', $tembusanUserIds) : null;
            } else {
                $memo->tembusan = null;
            }

            $bccUserIds = [];
            if ($request->has('bcc') && is_array($request->bcc)) {
                $bccUserIds = $this->convertTujuanToUserId($request->bcc);
                $memo->bcc = !empty($bccUserIds) ? implode(';', $bccUserIds) : null;
            } else {
                $memo->bcc = null;
            }

            // ============================
            // Update field memo
            // ============================
            if ($request->filled('kode_bagian')) {
                $memo->kode_bagian = $request->kode_bagian;
            }
            if ($request->filled('judul')) {
                $memo->judul = $request->judul;
            }
            if ($request->filled('isi_memo')) {
                $memo->isi_memo = $request->isi_memo;
            }
            if ($request->filled('tujuan')) {
                $memo->tujuan = implode(';', $tujuanId);
            }
            if ($request->filled('tujuanString')) {
                $memo->tujuan_string = implode(';', $request->tujuanString);
            }
            if ($request->filled('nomor_memo')) {
                $memo->nomor_memo = $request->nomor_memo;
            }
            if ($request->filled('nama_bertandatangan')) {
                $memo->nama_bertandatangan = $request->nama_bertandatangan;
            }
            if ($request->filled('manager_user_id')) {
                // Sinkronkan approver baru ke memo (source utama notif workflow)
                $memo->manager_user_id = (int) $request->manager_user_id;
            }
            if ($request->filled('tgl_dibuat')) {
                $memo->tgl_dibuat = $request->tgl_dibuat;
            }
            if ($request->filled('seri_surat')) {
                $memo->seri_surat = $request->seri_surat;
            }
            if ($request->filled('tgl_disahkan')) {
                $memo->tgl_disahkan = $request->tgl_disahkan;
            }

            // ============================
            // Lampiran JSON list
            // ============================
            if ($request->hasFile('lampiran')) {
                $existingLampiran = [];
                if ($memo->lampiran) {
                    $existingLampiran = json_decode($memo->lampiran, true) ?? [];
                }

                $newFiles = [];
                foreach ($request->file('lampiran') as $file) {
                    if ($file->isValid()) {
                        $ext = strtolower($file->getClientOriginalExtension());

                        if ($ext === 'pdf') {
                            $folder = 'lampiran/memo/pdf';
                        } elseif (in_array($ext, ['png', 'jpg', 'jpeg'])) {
                            $folder = 'lampiran/memo/image';
                        } else {
                            $folder = 'lampiran/memo/other';
                        }

                        $filename = time() . '_' . $file->getClientOriginalName();
                        $filePath = $file->storeAs($folder, $filename, 'public');

                        $newFiles[] = [
                            'name' => $file->getClientOriginalName(),
                            'path' => $filePath,
                            'size' => $file->getSize(),
                            'uploaded_at' => now()->toDateTimeString(),
                        ];
                    }
                }

                $allFiles = array_merge($existingLampiran, $newFiles);
                $memo->lampiran = !empty($allFiles) ? json_encode($allFiles) : null;
            }

            // ============================
            // resubmit => balik pending
            // ============================
            $memo->status = 'pending';
            $memo->save();

            Kirim_Document::where('id_document', $memo->id_memo)
                ->where('jenis_document', 'memo')
                ->update(['status' => 'pending', 'updated_at' => now()]);

            // Jika approver berubah, sinkronkan kirim_document approver (tetap untuk workflow/inbox).
            Kirim_Document::where('id_document', $memo->id_memo)
                ->where('jenis_document', 'memo')
                ->where('jenis_penerima', 'approver')
                ->update([
                    'id_penerima' => (int) $memo->manager_user_id,
                    'updated_at' => now(),
                ]);

            // ============================
            // ✅ NOTIF + PUSH (PAKAI SERVICE)
            // ============================

            $workflowRecipients = $this->getWorkflowNotificationRecipients($memo, $request);
            foreach ($workflowRecipients as $recipientId) {
                $judulNotif = ((int) $recipientId === (int) $memo->pembuat)
                    ? 'Memo Anda Berhasil Diupdate'
                    : 'Memo Diupdate, Menunggu Persetujuan';

                Log::info('UPDATE MEMO - BEFORE PUSH', [
                    'memo_id' => $memo->id_memo,
                    'recipient_id' => $recipientId,
                    'judul_notif' => $judulNotif,
                    'judul_memo' => $memo->judul,
                ]);

                $notifService->createAndPush(
                    (int) $recipientId,
                    $judulNotif,
                    $memo->judul,
                    (int) $memo->id_memo,
                    'memo'
                );
            }

            // ============================
            // kategori_barang update (tetap)
            // ============================
            if ($request->has('kategori_barang')) {
                foreach ($request->kategori_barang as $dataBarang) {
                    if (isset($dataBarang['id_kategori_barang']) && $dataBarang['id_kategori_barang'] != null) {
                        $barang = $memo->kategoriBarang()->find($dataBarang['id_kategori_barang']);
                        if ($barang) {
                            $barang->update([
                                'memo_id_memo' => $memo->id_memo,
                                'nomor' => $dataBarang['nomor'],
                                'barang' => $dataBarang['barang'],
                                'qty' => $dataBarang['qty'],
                                'satuan' => $dataBarang['satuan'],
                            ]);
                        }
                    }
                }
            }

                    return redirect()
        ->route('memo.terkirim')
        ->with('success', 'Dokumen berhasil dibuat dan menunggu persetujuan. Nomor surat akan diberikan setelah disetujui.');
        });
    }

    public function deleteLampiranExisting($memoId, $index)
    {
        try {
            $memo = Memo::findOrFail($memoId);

            // Parse lampiran data
            $lampiranData = [];
            if ($memo->lampiran) {
                $lampiranData = json_decode($memo->lampiran, true) ?? [];
            }

            // Cek apakah index valid
            if (!isset($lampiranData[$index])) {
                return response()->json(['success' => false, 'message' => 'File tidak ditemukan'], 404);
            }

            // Hapus file fisik jika ada
            if (isset($lampiranData[$index]['path']) && StorageFacade::disk('public')->exists($lampiranData[$index]['path'])) {
                StorageFacade::disk('public')->delete($lampiranData[$index]['path']);
            }

            // Hapus dari array
            unset($lampiranData[$index]);
            // Reindex array
            $lampiranData = array_values($lampiranData);

            // Update memo dengan lampiran data yang baru
            $memo->lampiran = empty($lampiranData) ? null : json_encode($lampiranData);
            $memo->save();

            return response()->json(['success' => true, 'message' => 'File berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat menghapus file'], 500);
        }
    }
}
