@extends('layouts.app')

@section('title', 'Detail Memo Keluar')

@section('content')


    <div class="container-fluid px-4 py-0 mt-0">
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-body py-3">

                <h3 class="fw-bold mb-3">Detail Memo Keluar</h3>

                {{-- Breadcrumb --}}
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="bg-white border rounded-2 px-3 py-2 w-100 d-flex align-items-center">
                            <a href="{{ route('dashboard') }}" class="text-decoration-none text-primary">Beranda</a>
                            <span class="mx-2 text-muted">/</span>
                            <a href="{{ route('memo.terkirim') }}" class="text-decoration-none text-primary">Memo Keluar</a>
                            <span class="mx-2 text-muted">/</span>
                            <span class="text-muted">Detail Memo Keluar</span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    {{-- Kolom kiri: Informasi Detail Memo --}}
                    <div class="col-md-6 mb-3">
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-header py-2 rounded-top-3"
                                style="background:#e3f2fd;border-bottom:1px solid #bbdefb;">
                                <i class="fa fa-file-alt me-2 text-primary"></i>
                                <span class="fw-semibold">Informasi Detail Memo</span>
                            </div>
                            <div class="card-body">

                                @if ($memoRujukan)
                                    <div class="info-row d-flex flex-column flex-sm-row">
                                        <div class="info-label">Merujuk Memo</div>
                                        <div class="info-value">{{ $memoRujukan->nomor_memo }}</div>
                                    </div>
                                @endif

                                <div class="info-row d-flex flex-column flex-sm-row">
                                    <div class="info-label">No Surat</div>
                                    <div class="info-value">{{ $memo->nomor_memo }}</div>
                                </div>

                                <div class="info-row d-flex flex-column flex-sm-row">
                                    <div class="info-label">Perihal</div>
                                    <div class="info-value">{{ $memo->judul }}</div>
                                </div>

                                <div class="info-row d-flex flex-column flex-sm-row">
                                    <div class="info-label">Hari, Tanggal</div>
                                    <div class="info-value">
                                        {{ \Carbon\Carbon::parse($memo->tgl_dibuat)->translatedFormat('l, d F Y') }}
                                    </div>
                                </div>

                                <div class="info-row d-flex flex-column flex-sm-row">
                                    <div class="info-label">Kepada</div>
                                    <div class="info-value">
                                        @php
                                            $rawTujuanIds = collect(explode(';', (string) $memo->tujuan))
                                                ->map(fn($id) => trim($id))
                                                ->filter(fn($id) => $id !== '' && is_numeric($id))
                                                ->map(fn($id) => (int) $id)
                                                ->unique()
                                                ->values();

                                            $legacyTujuanNames = collect(explode(';', (string) $memo->tujuan_string))
                                                ->map(fn($name) => trim($name))
                                                ->filter(fn($name) => $name !== '')
                                                ->values()
                                                ->all();

                                            $tujuanRingkas = [];

                                            if ($rawTujuanIds->isNotEmpty()) {
                                                $selectedUsers = \App\Models\User::with([
                                                    'position:id_position,nm_position',
                                                    'department:id_department,name_department',
                                                ])
                                                    ->whereIn('id', $rawTujuanIds)
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

                                                $selectedIdSet = $selectedUsers->pluck('id')->flip();
                                                $remainingIds = $selectedUsers->pluck('id')->all();

                                                $directorMap = \App\Models\Director::pluck(
                                                    'name_director',
                                                    'id_director',
                                                );
                                                $divisionMap = \App\Models\Divisi::pluck('nm_divisi', 'id_divisi');
                                                $departmentMap = \App\Models\Department::pluck(
                                                    'name_department',
                                                    'id_department',
                                                );
                                                $sectionMap = \App\Models\Section::pluck('name_section', 'id_section');
                                                $unitMap = \App\Models\Unit::pluck('name_unit', 'id_unit');

                                                $scopes = [
                                                    ['col' => 'director_id_director', 'map' => $directorMap],
                                                    ['col' => 'divisi_id_divisi', 'map' => $divisionMap],
                                                    ['col' => 'department_id_department', 'map' => $departmentMap],
                                                    ['col' => 'section_id_section', 'map' => $sectionMap],
                                                    ['col' => 'unit_id_unit', 'map' => $unitMap],
                                                ];

                                                $sectionToDepartmentMap = \App\Models\Section::pluck(
                                                    'department_id_department',
                                                    'id_section',
                                                );
                                                $unitToSectionMap = \App\Models\Unit::pluck(
                                                    'section_id_section',
                                                    'id_unit',
                                                );
                                                $groupedDepartmentIds = [];
                                                $groupedSectionIds = [];

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
                                                                in_array(
                                                                    (int) $parentDeptId,
                                                                    $groupedDepartmentIds,
                                                                    true,
                                                                )
                                                            ) {
                                                                $coveredUserIds = $selectedUsers
                                                                    ->where('section_id_section', $groupId)
                                                                    ->pluck('id')
                                                                    ->all();
                                                                $remainingIds = array_values(
                                                                    array_diff($remainingIds, $coveredUserIds),
                                                                );
                                                                continue;
                                                            }
                                                        }

                                                        if ($scope['col'] === 'unit_id_unit') {
                                                            $parentSectionId = $unitToSectionMap[$groupId] ?? null;
                                                            $parentDeptId = $parentSectionId
                                                                ? $sectionToDepartmentMap[$parentSectionId] ?? null
                                                                : null;

                                                            if (
                                                                (!empty($parentSectionId) &&
                                                                    in_array(
                                                                        (int) $parentSectionId,
                                                                        $groupedSectionIds,
                                                                        true,
                                                                    )) ||
                                                                (!empty($parentDeptId) &&
                                                                    in_array(
                                                                        (int) $parentDeptId,
                                                                        $groupedDepartmentIds,
                                                                        true,
                                                                    ))
                                                            ) {
                                                                $coveredUserIds = $selectedUsers
                                                                    ->where('unit_id_unit', $groupId)
                                                                    ->pluck('id')
                                                                    ->all();
                                                                $remainingIds = array_values(
                                                                    array_diff($remainingIds, $coveredUserIds),
                                                                );
                                                                continue;
                                                            }
                                                        }

                                                        $allMemberIds = \App\Models\User::where(
                                                            $scope['col'],
                                                            $groupId,
                                                        )->pluck('id');
                                                        if ($allMemberIds->isEmpty()) {
                                                            continue;
                                                        }

                                                        $allSelected = $allMemberIds->every(
                                                            fn($memberId) => $selectedIdSet->has($memberId),
                                                        );
                                                        if ($allSelected) {
                                                            $scopeName = $scope['map'][$groupId] ?? 'ID ' . $groupId;
                                                            $tujuanRingkas[] = $scopeName;

                                                            if ($scope['col'] === 'department_id_department') {
                                                                $groupedDepartmentIds[] = (int) $groupId;
                                                            }
                                                            if ($scope['col'] === 'section_id_section') {
                                                                $groupedSectionIds[] = (int) $groupId;
                                                            }

                                                            $remainingIds = array_values(
                                                                array_diff($remainingIds, $allMemberIds->all()),
                                                            );
                                                        }
                                                    }
                                                }

                                                $remainingUsers = $selectedUsers
                                                    ->whereIn('id', $remainingIds)
                                                    ->sortBy(fn($u) => trim($u->firstname . ' ' . $u->lastname));

                                                foreach ($remainingUsers as $user) {
                                                    $fullName = trim($user->firstname . ' ' . $user->lastname);
                                                    $positionName = $user->position->nm_position ?? '-';
                                                    $positionLower = strtolower($positionName);
                                                    $isStaff =
                                                        str_contains($positionLower, 'staff') ||
                                                        str_contains($positionLower, 'staf');

                                                    $bagianKerja = '-';
                                                    if ($isStaff) {
                                                        if (
                                                            !empty($user->unit_id_unit) &&
                                                            isset($unitMap[$user->unit_id_unit])
                                                        ) {
                                                            $bagianKerja = $unitMap[$user->unit_id_unit];
                                                        } elseif (
                                                            !empty($user->section_id_section) &&
                                                            isset($sectionMap[$user->section_id_section])
                                                        ) {
                                                            $bagianKerja = $sectionMap[$user->section_id_section];
                                                        } elseif (
                                                            !empty($user->department_id_department) &&
                                                            isset($departmentMap[$user->department_id_department])
                                                        ) {
                                                            $bagianKerja =
                                                                $departmentMap[$user->department_id_department];
                                                        } elseif (
                                                            !empty($user->divisi_id_divisi) &&
                                                            isset($divisionMap[$user->divisi_id_divisi])
                                                        ) {
                                                            $bagianKerja = $divisionMap[$user->divisi_id_divisi];
                                                        } elseif (
                                                            !empty($user->director_id_director) &&
                                                            isset($directorMap[$user->director_id_director])
                                                        ) {
                                                            $bagianKerja = $directorMap[$user->director_id_director];
                                                        }
                                                    } else {
                                                        if (
                                                            !empty($user->department_id_department) &&
                                                            isset($departmentMap[$user->department_id_department])
                                                        ) {
                                                            $bagianKerja =
                                                                $departmentMap[$user->department_id_department];
                                                        } elseif (
                                                            !empty($user->divisi_id_divisi) &&
                                                            isset($divisionMap[$user->divisi_id_divisi])
                                                        ) {
                                                            $bagianKerja = $divisionMap[$user->divisi_id_divisi];
                                                        } elseif (
                                                            !empty($user->section_id_section) &&
                                                            isset($sectionMap[$user->section_id_section])
                                                        ) {
                                                            $bagianKerja = $sectionMap[$user->section_id_section];
                                                        } elseif (
                                                            !empty($user->unit_id_unit) &&
                                                            isset($unitMap[$user->unit_id_unit])
                                                        ) {
                                                            $bagianKerja = $unitMap[$user->unit_id_unit];
                                                        } elseif (
                                                            !empty($user->director_id_director) &&
                                                            isset($directorMap[$user->director_id_director])
                                                        ) {
                                                            $bagianKerja = $directorMap[$user->director_id_director];
                                                        }
                                                    }

                                                    $positionClean =
                                                        preg_replace('/^\s*\([^)]*\)\s*/', '', $positionName) ?:
                                                        $positionName;
                                                    $tujuanRingkas[] =
                                                        $fullName . ' - ' . $bagianKerja . ' (' . $positionClean . ')';
                                                }
                                            }

                                            $tujuanList = $rawTujuanIds->isNotEmpty()
                                                ? array_values(array_filter($tujuanRingkas))
                                                : array_values(array_filter($legacyTujuanNames));
                                        @endphp

                                        @if (!empty($tujuanList))
                                            @foreach ($tujuanList as $index => $tujuan)
                                                <p class="m-0">{{ $index + 1 }}. {{ $tujuan }}</p>
                                            @endforeach
                                        @else
                                            -
                                        @endif
                                    </div>
                                </div>
                                @php
                                    $rawTembusan = array_values(
                                        array_filter(explode(';', $memo->tembusan ?? ''), fn($t) => trim($t) !== ''),
                                    );

                                    $tembusanUserIds = collect($rawTembusan)
                                        ->filter(fn($t) => is_numeric($t))
                                        ->map(fn($t) => (int) $t)
                                        ->unique()
                                        ->values();

                                    $legacyTembusan = collect($rawTembusan)
                                        ->filter(fn($t) => !is_numeric($t))
                                        ->values()
                                        ->all();

                                    $tembusanRingkas = [];

                                    if ($tembusanUserIds->isNotEmpty()) {
                                        $selectedUsers = \App\Models\User::with([
                                            'position:id_position,nm_position',
                                            'department:id_department,name_department',
                                        ])
                                            ->whereIn('id', $tembusanUserIds)
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

                                        $selectedIdSet = $selectedUsers->pluck('id')->flip();
                                        $remainingIds = $selectedUsers->pluck('id')->all();

                                        $directorMap = \App\Models\Director::pluck('name_director', 'id_director');
                                        $divisionMap = \App\Models\Divisi::pluck('nm_divisi', 'id_divisi');
                                        $departmentMap = \App\Models\Department::pluck(
                                            'name_department',
                                            'id_department',
                                        );
                                        $sectionMap = \App\Models\Section::pluck('name_section', 'id_section');
                                        $unitMap = \App\Models\Unit::pluck('name_unit', 'id_unit');

                                        $scopes = [
                                            [
                                                'col' => 'director_id_director',
                                                'label' => 'Direktur',
                                                'map' => $directorMap,
                                            ],
                                            ['col' => 'divisi_id_divisi', 'label' => 'Divisi', 'map' => $divisionMap],
                                            [
                                                'col' => 'department_id_department',
                                                'label' => 'Departemen',
                                                'map' => $departmentMap,
                                            ],
                                            ['col' => 'section_id_section', 'label' => 'Bagian', 'map' => $sectionMap],
                                            ['col' => 'unit_id_unit', 'label' => 'Unit', 'map' => $unitMap],
                                        ];

                                        foreach ($scopes as $scope) {
                                            $groupIds = $selectedUsers
                                                ->whereIn('id', $remainingIds)
                                                ->pluck($scope['col'])
                                                ->filter()
                                                ->unique()
                                                ->values();

                                            foreach ($groupIds as $groupId) {
                                                $allMemberIds = \App\Models\User::where($scope['col'], $groupId)->pluck(
                                                    'id',
                                                );
                                                if ($allMemberIds->isEmpty()) {
                                                    continue;
                                                }

                                                $allSelected = $allMemberIds->every(
                                                    fn($memberId) => $selectedIdSet->has($memberId),
                                                );
                                                if ($allSelected) {
                                                    $scopeName = $scope['map'][$groupId] ?? 'ID ' . $groupId;
                                                    $tembusanRingkas[] = $scope['label'] . ': ' . $scopeName;
                                                    $remainingIds = array_values(
                                                        array_diff($remainingIds, $allMemberIds->all()),
                                                    );
                                                }
                                            }
                                        }

                                        $remainingUsers = $selectedUsers
                                            ->whereIn('id', $remainingIds)
                                            ->sortBy(fn($u) => trim($u->firstname . ' ' . $u->lastname));

                                        foreach ($remainingUsers as $user) {
                                            $fullName = trim($user->firstname . ' ' . $user->lastname);
                                            $positionName = $user->position->nm_position ?? '-';
                                            $positionLower = strtolower($positionName);
                                            $isStaff =
                                                str_contains($positionLower, 'staff') ||
                                                str_contains($positionLower, 'staf');

                                            $bagianKerja = '-';
                                            if ($isStaff) {
                                                if (
                                                    !empty($user->unit_id_unit) &&
                                                    isset($unitMap[$user->unit_id_unit])
                                                ) {
                                                    $bagianKerja = $unitMap[$user->unit_id_unit];
                                                } elseif (
                                                    !empty($user->section_id_section) &&
                                                    isset($sectionMap[$user->section_id_section])
                                                ) {
                                                    $bagianKerja = $sectionMap[$user->section_id_section];
                                                } elseif (
                                                    !empty($user->department_id_department) &&
                                                    isset($departmentMap[$user->department_id_department])
                                                ) {
                                                    $bagianKerja = $departmentMap[$user->department_id_department];
                                                } elseif (
                                                    !empty($user->divisi_id_divisi) &&
                                                    isset($divisionMap[$user->divisi_id_divisi])
                                                ) {
                                                    $bagianKerja = $divisionMap[$user->divisi_id_divisi];
                                                } elseif (
                                                    !empty($user->director_id_director) &&
                                                    isset($directorMap[$user->director_id_director])
                                                ) {
                                                    $bagianKerja = $directorMap[$user->director_id_director];
                                                }
                                            } else {
                                                if (
                                                    !empty($user->department_id_department) &&
                                                    isset($departmentMap[$user->department_id_department])
                                                ) {
                                                    $bagianKerja = $departmentMap[$user->department_id_department];
                                                } elseif (
                                                    !empty($user->divisi_id_divisi) &&
                                                    isset($divisionMap[$user->divisi_id_divisi])
                                                ) {
                                                    $bagianKerja = $divisionMap[$user->divisi_id_divisi];
                                                } elseif (
                                                    !empty($user->section_id_section) &&
                                                    isset($sectionMap[$user->section_id_section])
                                                ) {
                                                    $bagianKerja = $sectionMap[$user->section_id_section];
                                                } elseif (
                                                    !empty($user->unit_id_unit) &&
                                                    isset($unitMap[$user->unit_id_unit])
                                                ) {
                                                    $bagianKerja = $unitMap[$user->unit_id_unit];
                                                } elseif (
                                                    !empty($user->director_id_director) &&
                                                    isset($directorMap[$user->director_id_director])
                                                ) {
                                                    $bagianKerja = $directorMap[$user->director_id_director];
                                                }
                                            }

                                            $positionClean =
                                                preg_replace('/^\s*\([^)]*\)\s*/', '', $positionName) ?: $positionName;
                                            $tembusanRingkas[] =
                                                $fullName . ' - ' . $bagianKerja . ' (' . $positionClean . ')';
                                        }
                                    }

                                    $tembusanList = array_values(
                                        array_filter(array_merge($tembusanRingkas, $legacyTembusan)),
                                    );
                                @endphp
                                @if (!empty($tembusanList))
                                    <div class="info-row d-flex flex-column flex-sm-row">
                                        <div class="info-label">Tembusan</div>
                                        <div class="info-value">


                                            @foreach ($tembusanList as $index => $tembusan)
                                                <p class="m-0">{{ $index + 1 }}. {{ $tembusan }}</p>
                                            @endforeach


                                        </div>
                                    </div>
                                @endif
                                @if (($canViewBcc ?? false) && !empty($bccDisplayList ?? []))
                                    <div class="info-row d-flex flex-column flex-sm-row">
                                        <div class="info-label">BCC</div>
                                        <div class="info-value">
                                            @foreach ($bccDisplayList as $index => $bccItem)
                                                <p class="m-0">{{ $index + 1 }}. {{ $bccItem }}</p>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                    </div>

                    {{-- Kolom kanan: Kepada --}}
                    <div class="col-md-6 mb-3">
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-header py-2 rounded-top-3"
                                style="background:#e3f2fd;border-bottom:1px solid #bbdefb;">
                                <i class="fa fa-user me-2 text-primary"></i>
                                <span class="fw-semibold">Detail</span>
                            </div>

                            <div class="card-body">
                                <div class="info-row d-flex flex-column flex-sm-row">
                                    <div class="info-label">Pembuat</div>
                                    <div class="info-value">{{ $pembuat?->firstname }} {{ $pembuat?->lastname }}</div>
                                </div>

                                <div class="info-row d-flex flex-column flex-sm-row">
                                    <div class="info-label">Status</div>
                                    <div class="info-value">
                                        @if ($memo->kode != $divDeptKode)
                                            @if ($memo->status == 'reject')
                                                <span class="badge bg-danger">Ditolak</span>
                                            @elseif ($memo->status == 'pending')
                                                <span class="badge bg-info">Diproses</span>
                                            @elseif ($memo->status == 'correction')
                                                <span class="badge bg-warning">Dikoreksi</span>
                                            @else
                                                <span class="badge bg-success">Diterima</span>
                                            @endif
                                        @else
                                            @if ($memo->status == 'reject')
                                                <span class="badge bg-danger">Ditolak</span>
                                            @elseif ($memo->status == 'pending')
                                                <span class="badge bg-info">Diproses</span>
                                            @elseif ($memo->status == 'correction')
                                                <span class="badge bg-warning">Dikoreksi</span>
                                            @else
                                                <span class="badge bg-success">Diterima</span>
                                            @endif
                                        @endif
                                    </div>
                                </div>

                                <div class="info-row d-flex flex-column flex-sm-row">
                                    <div class="info-label">File</div>
                                    <div class="info-value">
                                        <a href="{{ route('view-memoPDF', $memo->id_memo) }}" target="_blank"
                                            rel="noopener noreferrer" class="btn btn-sm btn-custom me-2 rounded-2">
                                            <i class="fa fa-eye me-1"></i> Lihat
                                        </a>

                                        <a href="{{ route('cetakmemo', ['id' => $memo->id_memo]) }}"
                                            class="btn btn-sm btn-custom rounded-2">
                                            <i class="fa fa-download me-1"></i> Unduh
                                        </a>
                                    </div>
                                </div>
                                @if ($lampiranData)
                                    <div class="info-row d-flex flex-column flex-sm-row">
                                        <div class="info-label">Lampiran</div>
                                        <div class="info-value w-100">
                                            <div class="d-flex align-items-center justify-content-between mb-3">
                                                <span class="fw-semibold">Daftar Lampiran</span>
                                                <a href="{{ route('download-semua-lampiran', $memo->id_memo) }}"
                                                    class="btn btn-sm btn-success rounded-2">
                                                    <i class="fas fa-download me-1"></i> Unduh Semua
                                                </a>
                                            </div>
                                            <div class="row">
                                                @foreach ($lampiranData as $index => $lampiran)
                                                    <div class="col-md-12">
                                                        <div class="border rounded p-2">
                                                            <div class="d-flex align-items-center justify-content-between">
                                                                <div class="flex-grow-1">
                                                                    <small class="text-truncate d-block"
                                                                        title="{{ $lampiran['name'] ?? 'File Lampiran' }}">
                                                                        <i class="fas fa-file text-primary me-1"></i>
                                                                        {{ Str::limit($lampiran['name'], 32, '...') ?? 'File Lampiran ' . ($index + 1) }}
                                                                    </small>

                                                                </div>
                                                                <div class="ms-2">
                                                                    @if (isset($lampiran['path']) && file_exists(storage_path('app/public/' . $lampiran['path'])))
                                                                        <a href="{{ asset('storage/' . $lampiran['path']) }}"
                                                                            download="{{ $lampiran['name'] ?? 'file' }}"
                                                                            class="btn btn-sm btn-outline-success me-1"
                                                                            title="Download">
                                                                            <i class="fas fa-download"></i>
                                                                        </a>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div> {{-- /row --}}
                @if ($memo->status === 'approve')
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card border-0 shadow-sm rounded-3">
                                <div class="card-header py-2 rounded-top-3"
                                    style="background:#e3f2fd;border-bottom:1px solid #bbdefb;">
                                    <i class="fa fa-file-alt me-2 text-primary"></i>
                                    <span class="fw-semibold">Balasan Memo</span>
                                </div>
                                <div class="card-body">
                                    @if ($balasanMemos->isNotEmpty())
                                        <div class="table-responsive mt-3">
                                            <table class="table table-bordered custom-table-bagian">
                                                <thead>
                                                    <tr>
                                                        <th class="text-center" style="width:25%;">Nama Dokumen</th>
                                                        <th class="text-center" style="width:25%;">Dokumen</th>
                                                        <th class="text-center" style="width:10%;">Divisi</th>
                                                        <th class="text-center" style="width:10%;">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($balasanMemos as $balasanMemo)
                                                        <tr>
                                                            <td class="text-center">{{ $balasanMemo->judul ?? '-' }}</td>
                                                            <td class="text-center">{{ $balasanMemo->nomor_memo ?? '-' }}
                                                            </td>
                                                            <td class="text-center">{{ $balasanMemo->kode ?? '-' }}</td>
                                                            <td>
                                                                <div class="d-flex justify-content-center gap-2">
                                                                    <a onclick="window.location.href='{{ route('view-memoPDF', $balasanMemo->id_memo) }}'"
                                                                        class="btn btn-sm btn-custom me-2 rounded-2">
                                                                        <i class="fa fa-eye me-1"></i> Lihat Memo
                                                                    </a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        {{-- JIKA BELUM ADA MEMO BALASAN --}}
                                        <div class="d-flex justify-content-between align-items-center">
                                            <p class="mb-0">Belum ada memo balasan.</p>
                                        </div>
                                    @endif

                                </div>

                            </div>
                        </div>
                    </div>
                @elseif ($memo->status === 'pending' && $memo->nama_bertandatangan == Auth::user()->fullname)
                    <form id="approvalForm" method="POST" action="{{ route('memo.updateStatus', $memo->id_memo) }}">
                        @csrf
                        @method('PUT')

                        {{-- Row 4: Pengesahan dan Catatan --}}
                        <div class="row mb-4">
                            <div class="col-md-12" id="pengesahanCol">
                                <div class="card border-0 shadow-sm rounded-3 h-100">
                                    <div class="card-header py-2 rounded-top-3"
                                        style="background:#e3f2fd;border-bottom:1px solid #bbdefb;">
                                        <i class="fas fa-signature text-primary me-1"></i>
                                        <span class="fw-semibold">Pengesahan</span>
                                        <span style="color: red; font-size: 12px;">*</span>
                                    </div>
                                    <div class="card-body d-flex align-items-center justify-content-center">
                                        <div class="d-flex gap-4">
                                            <div class="form-check">
                                                <input type="radio" class="form-check-input approval-checkbox"
                                                    id="approve" name="status" value="approve">
                                                <label class="form-check-label" for="approve">Diterima</label>
                                            </div>

                                            <div class="form-check">
                                                <input type="radio" class="form-check-input approval-checkbox"
                                                    id="reject" name="status" value="reject">
                                                <label class="form-check-label" for="reject">Ditolak</label>
                                            </div>

                                            <div class="form-check">
                                                <input type="radio" class="form-check-input approval-checkbox"
                                                    id="correction" name="status" value="correction">
                                                <label class="form-check-label" for="correction">Dikoreksi</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6" id="catatanCol" style="display:none;">
                                <div class="card border-0 shadow-sm rounded-3 h-100">
                                    <div class="card-header py-2 rounded-top-3"
                                        style="background:#fff3cd;border-bottom:1px solid #ffeeba;">
                                        <i class="fa fa-sticky-note me-2 text-warning"></i>
                                        <span class="fw-semibold">Catatan</span>
                                        <span style="color: red; font-size: 12px;">*</span>
                                    </div>
                                    <div class="card-body d-flex flex-column">
                                        <textarea id="catatan" name="catatan" class="form-control flex-grow-1" rows="4"
                                            placeholder="Berikan Catatan"></textarea>
                                        <small id="catatanError" class="text-danger mt-1" style="display:none;">Catatan
                                            wajib diisi</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ACTION --}}
                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('memo.terkirim') }}" class="btn rounded-3"
                                style="background:#fff;color:#0d6efd;border:1px solid #0d6efd;">Batal</a>
                            <button type="button" class="btn btn-primary rounded-3" id="submitBtn">Kirim</button>
                        </div>
                    </form>
                @endif
                {{-- Catatan --}}
                @if (($memo->catatan && $memo->status == 'correction') || $memo->status == 'reject')
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card border-0 shadow-sm rounded-3">
                                <div class="card-header py-2 rounded-top-3"
                                    style="background:#fff3cd;border-bottom:1px solid #ffeeba;">
                                    <i class="fa fa-sticky-note me-2 text-warning"></i>
                                    <span class="fw-semibold">Catatan</span>
                                </div>
                                <div class="card-body">
                                    <textarea class="form-control" rows="4" readonly>{{ $memo->catatan }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const checkboxes = document.querySelectorAll('.approval-checkbox');
                const catatanCol = document.getElementById('catatanCol');
                const catatanInput = document.getElementById('catatan');
                const tujuanDivisiRow = document.getElementById('tujuanDivisiRow');
                const submitBtn = document.getElementById('submitBtn');
                const approvalForm = document.getElementById('approvalForm');
                let statusValue = null;
                let isSubmitting = false;

                // Radio button logic
                checkboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {

                        statusValue = this.value;
                        const pengesahanCol = document.getElementById('pengesahanCol');

                        if (statusValue === 'approve') {
                            catatanCol.style.display = 'none';
                            catatanInput.required = false;
                            // Pengesahan tetap full width untuk approve
                            pengesahanCol.className = 'col-md-12';
                        } else if (statusValue === 'reject' || statusValue === 'correction') {
                            catatanCol.style.display = 'block';
                            catatanInput.required = true;
                            // Pengesahan menjadi setengah width untuk reject/correction
                            pengesahanCol.className = 'col-md-6';
                        } else {
                            catatanCol.style.display = 'none';
                            catatanInput.required = false;
                            pengesahanCol.className = 'col-md-12';
                        }
                    });
                });

                // Submit button logic
                if (submitBtn) {
                    submitBtn.addEventListener('click', function() {
                        if (isSubmitting) return;

                        if (!statusValue) {

                            alert('Pilih status pengesahan terlebih dahulu!');
                            return;
                        }

                        if ((statusValue === 'reject' || statusValue === 'correction') && catatanInput.value
                            .trim() === '') {
                            document.getElementById('catatanError').style.display = 'block';
                            catatanInput.focus();
                            return;
                        }

                        isSubmitting = true;
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Memproses...';

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: 'Mohon tunggu',
                                text: 'Proses persetujuan memo sedang berjalan...',
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                showConfirmButton: false,
                                didOpen: () => Swal.showLoading()
                            });
                        }

                        approvalForm.submit();
                    });
                }

                // Jika ada notifikasi sukses dari server, tampilkan modal sukses (fallback jika redirect back)
                const successMessage = "{{ session('success') }}";
                if (successMessage) {
                    const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                    successModal.show();
                }
            });

            function showNotification(message, type) {
                // Implement sesuai dengan library notifikasi yang digunakan
                // Contoh menggunakan SweetAlert atau library lain
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: type === 'success' ? 'Berhasil!' : 'Error!',
                        text: message,
                        icon: type,
                        showConfirmButton: false
                    });
                } else {
                    // Fallback alert
                    alert(message);
                }
            }
        </script>

        <!-- Bootstrap JS and Popper.js -->
        <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const diterimaCheckbox = document.getElementById('approve');
                const tindakLanjutSelect = document.getElementById('nextAction');
                const formPengiriman = document.getElementById('formPengiriman');

                function togglePengiriman() {
                    if (diterimaCheckbox && tindakLanjutSelect && formPengiriman) {
                        if (diterimaCheckbox.checked && tindakLanjutSelect.value === 'dilanjutkan') {
                            formPengiriman.style.display = 'block';
                        } else {
                            formPengiriman.style.display = 'none';
                            const posisiPenerima = document.getElementById('posisi_penerima');
                            const divisiPenerima = document.getElementById('divisi_penerima');
                            if (posisiPenerima) posisiPenerima.value = '';
                            if (divisiPenerima) divisiPenerima.value = '';
                        }
                    }
                }

                if (diterimaCheckbox) diterimaCheckbox.addEventListener('change', togglePengiriman);
                if (tindakLanjutSelect) tindakLanjutSelect.addEventListener('change', togglePengiriman);

                const formPengiriman = document.getElementById('formPengiriman');
                if (formPengiriman) {
                    formPengiriman.style.display = 'none';
                }
            });
        </script>
    @endpush
@endsection
