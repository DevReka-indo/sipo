@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('content')
    <div class="container-fluid px-4 py-0 mt-0">
        <div class="card shadow-sm border-0">
            <div class="card-body py-3">

                <div class="row mb-3">
                    <div class="col">
                        <h3 class="fw-bold mb-3">Manajemen Pengguna</h3>
                    </div>
                    {{-- Tambah & Import --}}
                    <div class="col-auto d-flex gap-2">
                        <div class="col-12 col-md-auto ms-auto d-flex gap-2">
                            <button type="button" class="btn btn-success rounded-3" onclick="showUploadModal()">
                                <i class="far fa-file-excel"></i>
                                Import File
                            </button>
                            <button type="button" class="btn btn-black rounded-3" data-bs-toggle="modal"
                                data-bs-target="#addUserModal">
                                + Tambah Pengguna
                            </button>
                        </div>
                    </div>
                </div>
                {{-- Breadcrumb --}}
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="bg-white border rounded-2 px-3 py-2 w-100 d-flex align-items-center">
                            <a href="{{ route('superadmin.dashboard') }}"
                                class="text-decoration-none text-primary">Beranda</a>
                            <span class="text-muted ms-1">/ Pengaturan / Manajemen Pengguna</span>
                        </div>
                    </div>
                </div>

                {{-- Search & Filter --}}
                <form method="GET" action="{{ route('user.manage') }}" class="row g-2 mb-3 align-items-center">
                    <input type="hidden" name="view" value="{{ $view }}">
                    <div class="col-auto">
                        <select name="per_page" class="form-select rounded-3" style="max-width:100px;">
                            <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                    {{-- Search --}}
                    <div class="col-12 col-md">
                        <div class="input-group">
                            <span class="input-group-text rounded-start-3"><i class="fas fa-search"></i></span>
                            <input type="text" name="search" class="form-control rounded-end-3"
                                placeholder="Cari Nama atau NIP..." value="{{ request('search') }}">
                        </div>
                    </div>

                    {{-- Role --}}
                    <div class="col-12 col-md-auto">
                        <select name="role" class="form-select rounded-3">
                            <option value="">Semua Role</option>
                            <option value="1" {{ request('role') === '1' ? 'selected' : '' }}>Superadmin
                            </option>
                            <option value="3" {{ request('role') === '3' ? 'selected' : '' }}>Admin</option>
                            <option value="2" {{ request('role') === '2' ? 'selected' : '' }}>User</option>
                        </select>
                    </div>

                    {{-- Divisi --}}
                    {{-- <div class="col-12 col-md-auto">
                        <select name="divisi" class="form-select rounded-3">
                            <option value="">Filter Divisi</option>
                            @foreach ($kodeItems as $item)
                                <option value="{{ $item['kode'] }}"
                                    {{ request('kode') == $item['kode'] ? 'selected' : '' }}>
                                    {{ $item['kode'] }} - {{ $item['label'] }} ({{ ucfirst($item['tipe']) }})
                                </option>
                            @endforeach
                        </select>
                    </div> --}}

                    {{-- Sort --}}
                    {{-- <div class="col-12 col-md-auto">
                        <select name="sort" class="form-select rounded-3">
                            <option value="asc" {{ $sortOrder == 'asc' ? 'selected' : '' }}>Sort A-Z</option>
                            <option value="desc" {{ $sortOrder == 'desc' ? 'selected' : '' }}>Sort Z-A</option>
                        </select>
                    </div> --}}

                    {{-- Aktif / non-Aktif --}}
                    <div class="col-12 col-md-auto">
                        <select name="view" class="form-select rounded-3">
                            <option value="all" {{ $view == 'all' ? 'selected' : '' }}>Semua User</option>
                            <option value="active" {{ $view == 'active' ? 'selected' : '' }}>User Aktif</option>
                            <option value="deleted" {{ $view == 'deleted' ? 'selected' : '' }}>User Non-Aktif</option>
                        </select>
                    </div>

                    {{-- Tombol Filter --}}
                    <div class="col-12 col-md-auto">
                        <button type="submit" class="btn btn-primary rounded-3">
                            <i class="fas fa-filter me-1"></i>Filter
                        </button>
                    </div>

                    {{-- Tambah & Import --}}


                </form>

                {{-- Table --}}
                <div class="table-responsive">
                    <table class="table table-bordered custom-table-bagian align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center">Nama</th>
                                <th class="text-center">NIP</th>
                                <th class="text-center">Bagian Kerja</th>
                                <th class="text-center">Posisi</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Hak Akses</th>
                                @if ($view !== 'deleted')
                                    <th>Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if ($user->profile_image)
                                                <img src="data:image/png;base64,{{ $user->profile_image }}"
                                                    class="rounded-circle me-2" width="35" height="35">
                                            @else
                                                <i class="fas fa-user-circle fa-2x text-secondary me-2"></i>
                                            @endif
                                            {{ $user->firstname }} {{ $user->lastname }}
                                        </div>
                                    </td>
                                    <td>{{ $user->nip }}</td>
                                    <td>
                                        @if ($user->unit)
                                            {{ $user->unit->name_unit }}
                                        @elseif($user->section)
                                            {{ $user->section->name_section }}
                                        @elseif($user->department)
                                            {{ $user->department->name_department }}
                                        @elseif($user->divisi)
                                            {{ $user->divisi->nm_divisi }}
                                        @elseif($user->director)
                                            {{ $user->director->name_director }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $user->position->nm_position ?? '-' }}</td>
                                    <td class="text-center">
                                        @if ($user->deleted_at)
                                            <form action="{{ route('user-manage.restore', $user->id) }}" method="POST"
                                                class="d-inline restore-form">
                                                @csrf
                                                @method('PUT')
                                                <button type="button" class="btn btn-danger btn-sm btn-restore"
                                                    style="width: 80px;" data-id="{{ $user->id }}"
                                                    data-firstname="{{ $user->firstname }}"
                                                    data-lastname="{{ $user->lastname }}">Non-Aktif</button>
                                            </form>
                                        @else
                                            <form action="{{ route('user-manage.destroy', $user->id) }}" method="POST"
                                                class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-success btn-sm btn-delete"
                                                    style="width: 80px;" data-id="{{ $user->id }}"
                                                    data-firstname="{{ $user->firstname }}"
                                                    data-lastname="{{ $user->lastname }}">Aktif</button>
                                            </form>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($user->role->id_role == 1)
                                            <span class="badge bg-primary">
                                                Superadmin
                                            </span>
                                        @elseif($user->role->id_role == 2)
                                            <span class="badge bg-info">
                                                User
                                            </span>
                                        @elseif($user->role->id_role == 3)
                                            <span class="badge bg-warning">
                                                Admin
                                            </span>
                                        @endif
                                    </td>
                                    @if ($view !== 'deleted')
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-2">

                                                <!-- Tombol View User -->
                                                <button type="button"
                                                    class="btn btn-sm rounded-circle text-white border-0"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#viewUserModal{{ $user->id }}"
                                                    style="background-color:#51a1f1; width:30px; height:30px; display:flex; align-items:center; justify-content:center;"
                                                    title="Lihat">
                                                    <i class="fa-solid fa-eye"></i>
                                                </button>

                                                <!-- Tombol Edit -->
                                                <button class="btn btn-sm rounded-circle text-white border-0"
                                                    style="background-color:#FBC02D; width:30px; height:30px; display:flex; align-items:center; justify-content:center;"
                                                    title="Edit" data-bs-toggle="modal"
                                                    data-bs-target="#editUserModal{{ $user->id }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>

                                            </div>
                                        </td>
                                    @endif
                                </tr>

                                {{-- Modal Edit User --}}
                                <div class="modal fade" id="editUserModal{{ $user->id }}" tabindex="-1"
                                    aria-hidden="true">
                                    <div class="modal-dialog modal-xl modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-lg">
                                            <form action="{{ route('user-manage/update', $user->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header bg-warning text-dark">
                                                    <div>
                                                        <h5 class="modal-title fw-bold mb-1">
                                                            <i class="fas fa-user-edit me-2"></i>Edit Data Pengguna
                                                        </h5>
                                                        <small class="opacity-75">Perbarui informasi pengguna
                                                            {{ $user->firstname }}</small>
                                                    </div>
                                                    <button type="button" class="btn-close"
                                                        data-bs-dismiss="modal"></button>
                                                </div>

                                                <div class="modal-body bg-light p-4"
                                                    style="max-height: 70vh; overflow-y: auto;">
                                                    {{-- Informasi Akun --}}
                                                    <div class="card border-0 shadow-sm mb-3">
                                                        <div class="card-header bg-white">
                                                            <h6 class="mb-0 fw-semibold text-warning">
                                                                <i class="fas fa-id-card me-2"></i>Informasi Akun
                                                            </h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="row g-3">
                                                                <div class="col-md-6">
                                                                    <label class="form-label fw-semibold">
                                                                        <i class="fas fa-hashtag text-muted me-1"></i>ID
                                                                        Pengguna
                                                                    </label>
                                                                    <input type="text" class="form-control bg-light"
                                                                        value="{{ $user->id }}" disabled>
                                                                    <small class="text-muted">ID tidak dapat diubah</small>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label fw-semibold">
                                                                        <i
                                                                            class="fas fa-envelope text-muted me-1"></i>Email
                                                                        <span class="text-danger">*</span>
                                                                    </label>
                                                                    <input type="email" name="email"
                                                                        class="form-control" value="{{ $user->email }}"
                                                                        placeholder="contoh@email.com" required>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- Data Pribadi --}}
                                                    <div class="card border-0 shadow-sm mb-3">
                                                        <div class="card-header bg-white">
                                                            <h6 class="mb-0 fw-semibold text-warning">
                                                                <i class="fas fa-user me-2"></i>Data Pribadi
                                                            </h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="row g-3">
                                                                <div class="col-md-6">
                                                                    <label class="form-label fw-semibold">
                                                                        Nama Depan <span class="text-danger">*</span>
                                                                    </label>
                                                                    <input type="text" name="firstname"
                                                                        class="form-control"
                                                                        value="{{ $user->firstname }}"
                                                                        placeholder="Masukkan nama depan" required>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label fw-semibold">Nama
                                                                        Akhir</label>
                                                                    <input type="text" name="lastname"
                                                                        class="form-control"
                                                                        value="{{ $user->lastname }}"
                                                                        placeholder="Masukkan nama akhir (opsional)">
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label fw-semibold">
                                                                        <i class="fas fa-id-badge text-muted me-1"></i>NIP
                                                                        <span class="text-danger">*</span>
                                                                    </label>
                                                                    <input type="text" name="nip"
                                                                        class="form-control" value="{{ $user->nip }}"
                                                                        placeholder="Masukkan NIP" required>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label fw-semibold">
                                                                        <i class="fas fa-phone text-muted me-1"></i>No.
                                                                        Telepon
                                                                        <span class="text-danger">*</span>
                                                                    </label>
                                                                    <input type="text" name="phone_number"
                                                                        class="form-control"
                                                                        value="{{ $user->phone_number }}"
                                                                        placeholder="08xxxxxxxxxx" required>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- Keamanan --}}
                                                    <div class="card border-0 shadow-sm mb-3">
                                                        <div class="card-header bg-white">
                                                            <h6 class="mb-0 fw-semibold text-warning">
                                                                <i class="fas fa-lock me-2"></i>Keamanan
                                                            </h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="alert alert-info py-2 mb-3">
                                                                <small>
                                                                    <i class="fas fa-info-circle me-1"></i>
                                                                    <strong>Info:</strong> Kosongkan field kata sandi jika
                                                                    tidak ingin mengubah password
                                                                </small>
                                                            </div>
                                                            <div class="row g-3">
                                                                <div class="col-md-6">
                                                                    <label class="form-label fw-semibold">
                                                                        <i class="fas fa-key text-muted me-1"></i>Kata
                                                                        Sandi Baru (Opsional)
                                                                    </label>
                                                                    <input type="password" name="password"
                                                                        id="password_edit_{{ $user->id }}"
                                                                        class="form-control"
                                                                        placeholder="Minimal 8 karakter" minlength="8">
                                                                    <small class="text-muted">Minimal 8 karakter</small>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label fw-semibold">Konfirmasi Kata
                                                                        Sandi</label>
                                                                    <input type="password" name="password_confirmation"
                                                                        id="password_confirmation_edit_{{ $user->id }}"
                                                                        class="form-control"
                                                                        placeholder="Ulangi kata sandi" minlength="8"
                                                                        oninput="this.setCustomValidity(this.value !== document.getElementById('password_edit_{{ $user->id }}').value ? 'Konfirmasi kata sandi tidak cocok' : '')">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- Organisasi & Posisi --}}
                                                    <div class="card border-0 shadow-sm mb-3">
                                                        <div class="card-header bg-white">
                                                            <h6 class="mb-0 fw-semibold text-warning">
                                                                <i class="fas fa-building me-2"></i>Organisasi & Posisi
                                                            </h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="row g-3">
                                                                <div class="col-md-6">
                                                                    <label class="form-label fw-semibold">
                                                                        <i
                                                                            class="fas fa-sitemap text-muted me-1"></i>Organisasi
                                                                        <span class="text-danger">*</span>
                                                                    </label>
                                                                    @php
                                                                        $orgName =
                                                                            $user->unit->name_unit ??
                                                                            ($user->section->name_section ??
                                                                                ($user->department->name_department ??
                                                                                    ($user->divisi->nm_divisi ??
                                                                                        ($user->director
                                                                                            ->name_director ??
                                                                                            '-'))));
                                                                    @endphp
                                                                    <select class="form-select parent_id_select"
                                                                        name="parent_id" required>
                                                                        <option value="{{ $user->parent_id ?? '' }}"
                                                                            selected>
                                                                            {{ $orgName }}
                                                                        </option>
                                                                        @php
                                                                            if ($mainDirector) {
                                                                                renderOrgOptions($mainDirector);
                                                                            }
                                                                        @endphp
                                                                    </select>
                                                                    <small class="text-muted">
                                                                        <i class="fas fa-info-circle me-1"></i>
                                                                        Biarkan pilihan awal jika tidak ingin mengubah
                                                                    </small>
                                                                    <input type="hidden" name="parent_type"
                                                                        class="parent_type_input">
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label fw-semibold">
                                                                        <i
                                                                            class="fas fa-briefcase text-muted me-1"></i>Posisi
                                                                        <span class="text-danger">*</span>
                                                                    </label>
                                                                    <select name="position_id_position"
                                                                        class="form-select" required>
                                                                        <option
                                                                            value="{{ $user->position->id_position }}">
                                                                            {{ $user->position->nm_position }}
                                                                        </option>
                                                                        @foreach ($positions as $p)
                                                                            @if ($p->id_position != $user->position->id_position)
                                                                                <option value="{{ $p->id_position }}">
                                                                                    {{ $p->nm_position }}
                                                                                </option>
                                                                            @endif
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- Hak Akses & Kode Bagian --}}
                                                    <div class="card border-0 shadow-sm">
                                                        <div class="card-header bg-white">
                                                            <h6 class="mb-0 fw-semibold text-warning">
                                                                <i class="fas fa-shield-alt me-2"></i>Hak Akses & Area
                                                                Kerja
                                                            </h6>
                                                        </div>
                                                        <div class="card-body">
                                                            {{-- Hak Akses --}}
                                                            <div class="mb-4">
                                                                <label class="form-label fw-semibold mb-3">
                                                                    <i class="fas fa-user-shield text-muted me-1"></i>Hak
                                                                    Akses
                                                                    <span class="text-danger">*</span>
                                                                </label>
                                                                <div class="row g-2">
                                                                    <div class="col-md-4">
                                                                        <div class="border rounded p-3 h-100">
                                                                            <div class="form-check">
                                                                                <input class="form-check-input"
                                                                                    type="radio" name="role_id_role"
                                                                                    value="1"
                                                                                    id="role1_edit_{{ $user->id }}"
                                                                                    {{ $user->role_id_role == 1 ? 'checked' : '' }}
                                                                                    required>
                                                                                <label class="form-check-label"
                                                                                    for="role1_edit_{{ $user->id }}">
                                                                                    <div class="fw-bold text-primary">
                                                                                        <i
                                                                                            class="fas fa-star me-1"></i>Superadmin
                                                                                    </div>
                                                                                    <small class="text-muted">Akses penuh
                                                                                        sistem</small>
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="border rounded p-3 h-100">
                                                                            <div class="form-check">
                                                                                <input class="form-check-input"
                                                                                    type="radio" name="role_id_role"
                                                                                    value="2"
                                                                                    id="role2_edit_{{ $user->id }}"
                                                                                    {{ $user->role_id_role == 2 ? 'checked' : '' }}>
                                                                                <label class="form-check-label"
                                                                                    for="role2_edit_{{ $user->id }}">
                                                                                    <div class="fw-bold text-info">
                                                                                        <i
                                                                                            class="fas fa-user me-1"></i>User
                                                                                    </div>
                                                                                    <small class="text-muted">Akses
                                                                                        terbatas</small>
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="border rounded p-3 h-100">
                                                                            <div class="form-check">
                                                                                <input class="form-check-input"
                                                                                    type="radio" name="role_id_role"
                                                                                    value="3"
                                                                                    id="role3_edit_{{ $user->id }}"
                                                                                    {{ $user->role_id_role == 3 ? 'checked' : '' }}>
                                                                                <label class="form-check-label"
                                                                                    for="role3_edit_{{ $user->id }}">
                                                                                    <div class="fw-bold text-warning">
                                                                                        <i
                                                                                            class="fas fa-cog me-1"></i>Admin
                                                                                    </div>
                                                                                    <small class="text-muted">Kelola
                                                                                        data</small>
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            {{-- Kode Bagian - REDESIGNED --}}
                                                            @php
                                                                // ambil kode_bagian user, format: "SAR;IT;MMLH"
                                                                $selectedKodeBagian = $user->kode_bagian
                                                                    ? explode(';', $user->kode_bagian)
                                                                    : [];
                                                            @endphp

                                                            <div>
                                                                <label class="form-label fw-semibold mb-2">
                                                                    <i class="fas fa-tags text-muted me-1"></i>Kode Bagian
                                                                    {{-- <span class="text-danger">*</span> --}}
                                                                </label>
                                                                <p class="text-muted small mb-3">
                                                                    <i class="fas fa-info-circle me-1"></i>
                                                                    Pilih satu atau lebih bagian kerja yang akan dikelola
                                                                    pengguna.
                                                                    <strong>Centang kotak</strong> untuk memilih.
                                                                </p>

                                                                {{-- Hidden input sebagai fallback jika tidak ada yang dipilih --}}
                                                                <input type="hidden" name="kode_bagian[]"
                                                                    value="">

                                                                {{-- Badge terpilih saat ini --}}
                                                                @if (count($selectedKodeBagian) > 0)
                                                                    <div class="alert alert-success py-2 mb-3">
                                                                        <small class="fw-semibold">
                                                                            <i class="fas fa-check-circle me-1"></i>
                                                                            Terpilih saat ini
                                                                            ({{ count($selectedKodeBagian) }}):
                                                                        </small>
                                                                        <div class="mt-2">
                                                                            @foreach ($selectedKodeBagian as $kode)
                                                                                @php
                                                                                    $bagian = $bagianKerja->firstWhere(
                                                                                        'kode_bagian',
                                                                                        $kode,
                                                                                    );
                                                                                @endphp
                                                                                <span class="badge bg-success me-1 mb-1">
                                                                                    {{ $kode }}{{ $bagian ? ' - ' . $bagian->nama_bagian : '' }}
                                                                                </span>
                                                                            @endforeach
                                                                        </div>
                                                                    </div>
                                                                @endif

                                                                {{-- Daftar Checkbox dalam Card dengan scroll --}}
                                                                <div class="border rounded bg-white"
                                                                    style="max-height: 320px; overflow-y: auto;">
                                                                    @foreach ($bagianKerja as $index => $b)
                                                                        <div
                                                                            class="px-3 py-2 {{ $index > 0 ? 'border-top' : '' }}">
                                                                            <div
                                                                                class="form-check d-flex align-items-center">
                                                                                {{-- Checkbox --}}
                                                                                <input class="form-check-input mt-0"
                                                                                    type="checkbox" name="kode_bagian[]"
                                                                                    value="{{ $b->kode_bagian }}"
                                                                                    id="bagian_edit_{{ $user->id }}_{{ $b->kode_bagian }}"
                                                                                    {{ in_array($b->kode_bagian, $selectedKodeBagian) ? 'checked' : '' }}>

                                                                                {{-- Label: Hapus w-100 agar lebar hanya sebatas konten --}}
                                                                                <label
                                                                                    class="form-check-label d-flex align-items-center gap-2 ms-2 cursor-pointer"
                                                                                    for="bagian_edit_{{ $user->id }}_{{ $b->kode_bagian }}">

                                                                                    {{-- Badge dengan lebar tetap supaya nama bagian sejajar lurus ke bawah --}}
                                                                                    <span
                                                                                        class="badge bg-warning text-dark text-uppercase"
                                                                                        style="width: 70px;">
                                                                                        {{ $b->kode_bagian }}
                                                                                    </span>

                                                                                    <span
                                                                                        class="text-dark">{{ $b->nama_bagian ?? '-' }}</span>

                                                                                    {{-- Ikon Centang (Sekarang diletakkan tepat setelah teks nama) --}}
                                                                                    @if (in_array($b->kode_bagian, $selectedKodeBagian))
                                                                                        <i
                                                                                            class="fas fa-check-circle text-success small ms-1"></i>
                                                                                    @endif
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>

                                                                <div class="alert alert-info mt-3 mb-0 py-2">
                                                                    <small>
                                                                        <i class="fas fa-lightbulb me-1"></i>
                                                                        <strong>Tips:</strong> Scroll ke bawah untuk melihat
                                                                        lebih banyak pilihan.
                                                                        Anda bisa memilih lebih dari satu bagian.
                                                                    </small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="modal-footer bg-light">
                                                    <button type="button" class="btn btn-outline-secondary"
                                                        data-bs-dismiss="modal">
                                                        <i class="fas fa-times me-1"></i>Batal
                                                    </button>
                                                    <button type="submit" class="btn btn-warning text-dark">
                                                        <i class="fas fa-save me-1"></i>Perbarui Data
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>


                                {{-- Modal View User --}}
                                <div class="modal fade" id="viewUserModal{{ $user->id }}" tabindex="-1"
                                    aria-hidden="true">
                                    <div class="modal-dialog modal-xl modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-lg">
                                            <div class="modal-header bg-primary text-white">
                                                <div>
                                                    <h5 class="modal-title fw-bold mb-1">
                                                        <i class="fas fa-user-circle me-2"></i>Detail Data Pengguna
                                                    </h5>
                                                    <small class="opacity-75">Informasi lengkap pengguna
                                                        {{ $user->firstname }}</small>
                                                </div>
                                                <button type="button" class="btn-close btn-close-white"
                                                    data-bs-dismiss="modal"></button>
                                            </div>

                                            <div class="modal-body bg-light p-4"
                                                style="max-height: 70vh; overflow-y: auto;">
                                                {{-- Informasi Akun --}}
                                                <div class="card border-0 shadow-sm mb-3">
                                                    <div class="card-header bg-white">
                                                        <h6 class="mb-0 fw-semibold text-primary">
                                                            <i class="fas fa-id-card me-2"></i>Informasi Akun
                                                        </h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row g-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-semibold text-secondary">
                                                                    <i class="fas fa-hashtag text-muted me-1"></i>ID
                                                                    Pengguna
                                                                </label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text bg-light border-end-0">
                                                                        <i class="fas fa-fingerprint text-primary"></i>
                                                                    </span>
                                                                    <input type="text"
                                                                        class="form-control bg-light border-start-0"
                                                                        value="{{ $user->id }}" readonly>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-semibold text-secondary">
                                                                    <i class="fas fa-envelope text-muted me-1"></i>Email
                                                                </label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text bg-light border-end-0">
                                                                        <i class="fas fa-at text-primary"></i>
                                                                    </span>
                                                                    <input type="email"
                                                                        class="form-control bg-light border-start-0"
                                                                        value="{{ $user->email }}" readonly>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-semibold text-secondary">
                                                                    <i class="fas fa-shield-alt text-muted me-1"></i>Status
                                                                    Akun
                                                                </label>
                                                                @php
                                                                    $status = $user->deleted_at ? 'Non-Aktif' : 'Aktif';
                                                                    $statusClass = $user->deleted_at
                                                                        ? 'danger'
                                                                        : 'success';
                                                                    $statusIcon = $user->deleted_at
                                                                        ? 'times-circle'
                                                                        : 'check-circle';
                                                                @endphp
                                                                <div class="input-group">
                                                                    <span class="input-group-text bg-light border-end-0">
                                                                        <i
                                                                            class="fas fa-{{ $statusIcon }} text-{{ $statusClass }}"></i>
                                                                    </span>
                                                                    <input type="text"
                                                                        class="form-control bg-light border-start-0 fw-bold text-{{ $statusClass }}"
                                                                        value="{{ $status }}" readonly>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Data Pribadi --}}
                                                <div class="card border-0 shadow-sm mb-3">
                                                    <div class="card-header bg-white">
                                                        <h6 class="mb-0 fw-semibold text-primary">
                                                            <i class="fas fa-user me-2"></i>Data Pribadi
                                                        </h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row g-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-semibold text-secondary">
                                                                    <i class="fas fa-user-tag text-muted me-1"></i>Nama
                                                                    Depan
                                                                </label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text bg-light border-end-0">
                                                                        <i class="fas fa-signature text-primary"></i>
                                                                    </span>
                                                                    <input type="text"
                                                                        class="form-control bg-light border-start-0"
                                                                        value="{{ $user->firstname }}" readonly>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-semibold text-secondary">
                                                                    <i class="fas fa-user-tag text-muted me-1"></i>Nama
                                                                    Belakang
                                                                </label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text bg-light border-end-0">
                                                                        <i class="fas fa-signature text-primary"></i>
                                                                    </span>
                                                                    <input type="text"
                                                                        class="form-control bg-light border-start-0"
                                                                        value="{{ $user->lastname ?? '-' }}" readonly>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-semibold text-secondary">
                                                                    <i class="fas fa-id-badge text-muted me-1"></i>NIP
                                                                </label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text bg-light border-end-0">
                                                                        <i class="fas fa-address-card text-primary"></i>
                                                                    </span>
                                                                    <input type="text"
                                                                        class="form-control bg-light border-start-0"
                                                                        value="{{ $user->nip }}" readonly>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-semibold text-secondary">
                                                                    <i class="fas fa-phone text-muted me-1"></i>No. Telepon
                                                                </label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text bg-light border-end-0">
                                                                        <i class="fas fa-mobile-alt text-primary"></i>
                                                                    </span>
                                                                    <input type="text"
                                                                        class="form-control bg-light border-start-0"
                                                                        value="{{ $user->phone_number }}" readonly>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Organisasi & Posisi --}}
                                                <div class="card border-0 shadow-sm mb-3">
                                                    <div class="card-header bg-white">
                                                        <h6 class="mb-0 fw-semibold text-primary">
                                                            <i class="fas fa-building me-2"></i>Organisasi & Posisi
                                                        </h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row g-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-semibold text-secondary">
                                                                    <i
                                                                        class="fas fa-sitemap text-muted me-1"></i>Organisasi
                                                                </label>
                                                                @php
                                                                    $orgName =
                                                                        $user->unit->name_unit ??
                                                                        ($user->section->name_section ??
                                                                            ($user->department->name_department ??
                                                                                ($user->divisi->nm_divisi ??
                                                                                    ($user->director->name_director ??
                                                                                        '-'))));
                                                                @endphp
                                                                <div class="input-group">
                                                                    <span class="input-group-text bg-light border-end-0">
                                                                        <i class="fas fa-network-wired text-primary"></i>
                                                                    </span>
                                                                    <input type="text"
                                                                        class="form-control bg-light border-start-0"
                                                                        value="{{ $orgName }}" readonly>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-semibold text-secondary">
                                                                    <i class="fas fa-briefcase text-muted me-1"></i>Posisi
                                                                </label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text bg-light border-end-0">
                                                                        <i class="fas fa-user-tie text-primary"></i>
                                                                    </span>
                                                                    <input type="text"
                                                                        class="form-control bg-light border-start-0"
                                                                        value="{{ $user->position->nm_position ?? '-' }}"
                                                                        readonly>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Hak Akses & Area Kerja --}}
                                                <div class="card border-0 shadow-sm">
                                                    <div class="card-header bg-white">
                                                        <h6 class="mb-0 fw-semibold text-primary">
                                                            <i class="fas fa-shield-alt me-2"></i>Hak Akses & Area Kerja
                                                        </h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row g-3">
                                                            {{-- Hak Akses --}}
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-semibold text-secondary">
                                                                    <i class="fas fa-user-shield text-muted me-1"></i>Hak
                                                                    Akses
                                                                </label>
                                                                @php
                                                                    switch ($user->role_id_role) {
                                                                        case 1:
                                                                            $roleName = 'Superadmin';
                                                                            $roleClass = 'primary';
                                                                            $roleIcon = 'star';
                                                                            break;
                                                                        case 2:
                                                                            $roleName = 'User';
                                                                            $roleClass = 'info';
                                                                            $roleIcon = 'user';
                                                                            break;
                                                                        case 3:
                                                                            $roleName = 'Admin';
                                                                            $roleClass = 'warning';
                                                                            $roleIcon = 'cog';
                                                                            break;
                                                                        default:
                                                                            $roleName = '-';
                                                                            $roleClass = 'secondary';
                                                                            $roleIcon = 'question';
                                                                    }
                                                                @endphp
                                                                <div class="input-group">
                                                                    <span class="input-group-text bg-light border-end-0">
                                                                        <i
                                                                            class="fas fa-{{ $roleIcon }} text-{{ $roleClass }}"></i>
                                                                    </span>
                                                                    <input type="text"
                                                                        class="form-control bg-light border-start-0 fw-bold text-{{ $roleClass }}"
                                                                        value="{{ $roleName }}" readonly>
                                                                </div>
                                                            </div>

                                                            {{-- Kode Bagian - REDESIGNED --}}
                                                            <div class="col-12">
                                                                <label class="form-label fw-semibold text-secondary mb-2">
                                                                    <i class="fas fa-tags text-muted me-1"></i>Kode Bagian
                                                                    Kerja
                                                                </label>

                                                                @php
                                                                    $kodeBagianArray = $user->kode_bagian
                                                                        ? explode(';', $user->kode_bagian)
                                                                        : [];
                                                                @endphp

                                                                @if (count($kodeBagianArray) > 0)
                                                                    {{-- Display as badges --}}
                                                                    <div class="border rounded p-3 bg-white">
                                                                        <div
                                                                            class="d-flex justify-content-between align-items-center mb-2">
                                                                            <small class="text-muted fw-semibold">
                                                                                <i
                                                                                    class="fas fa-check-circle text-success me-1"></i>
                                                                                Total: <span
                                                                                    class="badge bg-primary">{{ count($kodeBagianArray) }}</span>
                                                                                bagian
                                                                            </small>
                                                                        </div>
                                                                        <div class="d-flex flex-wrap gap-2">
                                                                            @foreach ($kodeBagianArray as $kode)
                                                                                @php
                                                                                    $bagian = $bagianKerja->firstWhere(
                                                                                        'kode_bagian',
                                                                                        trim($kode),
                                                                                    );
                                                                                @endphp
                                                                                <div
                                                                                    class="badge bg-primary-subtle border border-primary text-primary px-3 py-2">
                                                                                    <i class="fas fa-tag me-1"></i>
                                                                                    <strong>{{ trim($kode) }}</strong>
                                                                                    @if ($bagian)
                                                                                        <span class="ms-1">-
                                                                                            {{ $bagian->nama_bagian }}</span>
                                                                                    @endif
                                                                                </div>
                                                                            @endforeach
                                                                        </div>
                                                                    </div>
                                                                @else
                                                                    {{-- No data --}}
                                                                    <div class="border rounded p-4 bg-white text-center">
                                                                        <i class="fas fa-inbox text-muted mb-2"
                                                                            style="font-size: 2rem;"></i>
                                                                        <p class="text-muted mb-0">Tidak ada kode bagian
                                                                            yang ditugaskan</p>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="modal-footer bg-light">
                                                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                                                    <i class="fas fa-times-circle me-1"></i>Tutup
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Pengguna tidak ditemukan</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="d-flex justify-content-end mt-3">
                    {{ $users->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Tambah User - Redesigned (No Extra CSS/JS) --}}
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <form action="{{ route('user-manage/add') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                        <div>
                            <h5 class="modal-title fw-bold mb-1"><i class="fas fa-user-plus me-2"></i>Tambah Pengguna Baru
                            </h5>
                            <small class="opacity-75">Lengkapi formulir di bawah untuk menambahkan pengguna</small>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body bg-light p-4" style="max-height: 70vh; overflow-y: auto;">
                        {{-- Informasi Akun --}}
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-white">
                                <h6 class="mb-0 fw-semibold text-primary"><i class="fas fa-id-card me-2"></i> Informasi
                                    Akun</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold"><i
                                                class="fas fa-hashtag text-muted me-1"></i>ID Pengguna</label>
                                        <input type="text" name="id" class="form-control bg-light"
                                            placeholder="Otomatis terisi" disabled>
                                        <small class="text-muted">ID akan dibuat otomatis oleh sistem</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="fas fa-envelope text-muted me-1"></i> Email <span
                                                class="text-danger">*</span>
                                        </label>
                                        <input type="email" name="email" class="form-control"
                                            placeholder="contoh@email.com" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Data Pribadi --}}
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-white">
                                <h6 class="mb-0 fw-semibold text-primary"><i class="fas fa-user me-2"></i> Data Pribadi
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            Nama Depan <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" name="firstname" class="form-control"
                                            placeholder="Masukkan nama depan" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Nama Akhir</label>
                                        <input type="text" name="lastname" class="form-control"
                                            placeholder="Masukkan nama akhir (opsional)">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            NIP <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" name="nip" class="form-control"
                                            placeholder="Masukkan NIP" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            No. Telepon <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" name="phone_number" class="form-control"
                                            placeholder="08xxxxxxxxxx" required minlength="10" maxlength="15"
                                            pattern="\d{10,15}" title="Nomor telepon harus 10-15 digit angka">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Keamanan --}}
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-white">
                                <h6 class="mb-0 fw-semibold text-primary"><i class="fas fa-lock me-2"></i> Keamanan</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="fas fa-key text-muted me-1"></i>Kata Sandi <span
                                                class="text-danger">*</span>
                                        </label>
                                        <input type="password" name="password" id="password" class="form-control"
                                            placeholder="Minimal 8 karakter" minlength="8" required>
                                        <small class="text-muted">Minimal 8 karakter</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            Konfirmasi Kata Sandi <span class="text-danger">*</span>
                                        </label>
                                        <input type="password" name="password_confirmation" id="password_confirmation"
                                            class="form-control" placeholder="Ulangi kata sandi" minlength="8" required
                                            oninput="this.setCustomValidity(this.value !== document.getElementById('password').value ? 'Konfirmasi kata sandi tidak cocok' : '')">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Organisasi & Posisi --}}
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-white">
                                <h6 class="mb-0 fw-semibold text-primary"><i class="fas fa-building me-2"></i> Organisasi
                                    & Posisi</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="parent_id" class="form-label fw-semibold">
                                            <i class="fas fa-sitemap text-muted me-1"></i>Pilih Organisasi <span
                                                class="text-danger">*</span>
                                        </label>
                                        <select class="form-select" id="parent_id" name="parent_id" required>
                                            <option value="">-- Pilih Organisasi --</option>
                                            @php
                                                function renderOrgOptions($node, $level = 0)
                                                {
                                                    $indent = str_repeat('&nbsp;', $level * 4);

                                                    if (isset($node->name_director)) {
                                                        echo "<option value='{$node->id_director}' data-type='director'>{$indent}Direktur: {$node->name_director}</option>";
                                                    } elseif (isset($node->nm_divisi)) {
                                                        echo "<option value='{$node->id_divisi}' data-type='divisi'>{$indent}→ Divisi: {$node->nm_divisi}</option>";
                                                    } elseif (isset($node->name_department)) {
                                                        echo "<option value='{$node->id_department}' data-type='department'>{$indent}→ Departemen: {$node->name_department}</option>";
                                                    } elseif (isset($node->name_section)) {
                                                        echo "<option value='{$node->id_section}' data-type='section'>{$indent}→ Bagian: {$node->name_section}</option>";
                                                    } elseif (isset($node->name_unit)) {
                                                        echo "<option value='{$node->id_unit}' data-type='unit'>{$indent}→ Unit: {$node->name_unit}</option>";
                                                    }

                                                    if (isset($node->subDirectors)) {
                                                        foreach ($node->subDirectors as $subDir) {
                                                            renderOrgOptions($subDir, $level + 1);
                                                        }
                                                    }
                                                    if (isset($node->divisi)) {
                                                        foreach ($node->divisi as $div) {
                                                            renderOrgOptions($div, $level + 1);
                                                        }
                                                    }
                                                    if (isset($node->department)) {
                                                        foreach ($node->department as $dept) {
                                                            renderOrgOptions($dept, $level + 1);
                                                        }
                                                    }
                                                    if (isset($node->section)) {
                                                        foreach ($node->section as $sec) {
                                                            renderOrgOptions($sec, $level + 1);
                                                        }
                                                    }
                                                    if (isset($node->unit)) {
                                                        foreach ($node->unit as $unit) {
                                                            renderOrgOptions($unit, $level + 1);
                                                        }
                                                    }
                                                }

                                                if ($mainDirector) {
                                                    renderOrgOptions($mainDirector);
                                                }
                                            @endphp
                                        </select>
                                        <input type="hidden" name="parent_type" id="parent_type">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="fas fa-briefcase text-muted me-1"></i>Posisi <span
                                                class="text-danger">*</span>
                                        </label>
                                        <select name="position_id_position" id="position_id_position" class="form-select"
                                            required>
                                            <option value="">-- Pilih Posisi --</option>
                                            @foreach ($positions as $p)
                                                <option value="{{ $p->id_position }}">{{ $p->nm_position }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Hak Akses & Kode Bagian --}}
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h6 class="mb-0 fw-semibold text-primary"><i class="fas fa-shield-alt me-2"></i> Hak Akses
                                    & Area Kerja</h6>
                            </div>
                            <div class="card-body">
                                {{-- Hak Akses --}}
                                <div class="mb-4">
                                    <label class="form-label fw-semibold mb-3">
                                        <i class="fas fa-user-shield text-muted me-1"></i>Hak Akses <span
                                            class="text-danger">*</span>
                                    </label>
                                    <div class="row g-2">
                                        <div class="col-md-4">
                                            <div class="border rounded p-3 h-100">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="role_id_role"
                                                        value="1" id="role1" required>
                                                    <label class="form-check-label" for="role1">
                                                        <div class="fw-bold text-primary"><i class="fas fa-star me-1"></i>
                                                            Superadmin</div>
                                                        <small class="text-muted">Akses penuh sistem</small>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="border rounded p-3 h-100">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="role_id_role"
                                                        value="2" id="role2">
                                                    <label class="form-check-label" for="role2">
                                                        <div class="fw-bold text-info"> <i
                                                                class="fas fa-user me-1"></i>User</div>
                                                        <small class="text-muted">Akses terbatas</small>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="border rounded p-3 h-100">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="role_id_role"
                                                        value="3" id="role3">
                                                    <label class="form-check-label" for="role3">
                                                        <div class="fw-bold text-warning"> <i
                                                                class="fas fa-cog me-1"></i>Admin</div>
                                                        <small class="text-muted">Kelola data</small>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Daftar Checkbox dalam Card dengan scroll --}}
                                <div>
                                    <label class="form-label fw-semibold mb-2">
                                        <i class="fas fa-tags text-muted me-1"></i>Kode Bagian
                                        {{-- <span class="text-danger">*</span> --}}
                                    </label>
                                    <p class="text-muted small mb-3">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Pilih satu atau lebih bagian kerja yang akan dikelola
                                        pengguna.
                                        <strong>Centang kotak</strong> untuk memilih.
                                    </p>
                                </div>
                                <div class="border rounded bg-white" style="max-height: 320px; overflow-y: auto;">
                                    @foreach ($bagianKerja as $index => $b)
                                        <div class="px-3 py-2 {{ $index > 0 ? 'border-top' : '' }}">
                                            {{-- Hapus w-100 dan gunakan d-inline-flex agar area klik pas dengan konten --}}
                                            <div class="d-flex align-items-center">

                                                {{-- Checkbox --}}
                                                <div class="form-check m-0 d-flex align-items-center">
                                                    <input class="form-check-input mt-0" type="checkbox"
                                                        name="kode_bagian[]" value="{{ $b->kode_bagian }}"
                                                        id="bagian_{{ $b->kode_bagian }}">

                                                    {{-- Label diletakkan di samping input langsung --}}
                                                    <label for="bagian_{{ $b->kode_bagian }}"
                                                        class="d-flex align-items-center ms-3 mb-0 cursor-pointer"
                                                        style="gap: 12px;" {{-- Jarak antar Badge dan Nama --}}>
                                                        <span class="badge bg-primary text-uppercase"
                                                            style="width: 70px; display: inline-block; text-align: center;">
                                                            {{ $b->kode_bagian }}
                                                        </span>

                                                        <span class="text-dark fw-medium">
                                                            {{ $b->nama_bagian ?? '-' }}
                                                        </span>
                                                    </label>
                                                </div>

                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="alert alert-info mt-3 mb-0 py-2">
                                    <small>
                                        💡 <strong>Tips:</strong> Scroll ke bawah untuk melihat lebih banyak pilihan.
                                        Anda bisa memilih lebih dari satu bagian.
                                    </small>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-primary">
                            Simpan Pengguna
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Notifikasi --}}
    <div class="modal fade" id="successAddUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content bg-success text-white text-center rounded-3">
                <div class="modal-body">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <p>User berhasil ditambahkan!</p>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="successEditUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content bg-info text-white text-center rounded-3">
                <div class="modal-body">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <p>User berhasil diperbarui!</p>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content bg-danger text-white text-center rounded-3">
                <div class="modal-body">
                    <i class="fas fa-times-circle fa-2x mb-2"></i>
                    <p id="errorPasswordMessage">Terjadi kesalahan.</p>
                </div>
            </div>
        </div>
    </div>
    <style>
        .swal2-icon.no-border {
            border: none !important;
        }
    </style>
@endsection

@push('scripts')
    <script>
        // Function untuk menampilkan notifikasi
        function showNotification(message, type) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: type === 'success' ? 'Berhasil!' : 'Gagal',
                    text: message,
                    icon: type,
                    showConfirmButton: true, // tombol OK muncul
                    confirmButtonText: 'OK',
                    confirmButtonColor: type === 'success' ? '#28a745' : '#d33'
                }).then((result) => {
                    if (result.isConfirmed && type === 'success') {
                        // kalau sukses dan user klik OK → balik ke index
                        window.location.href = "{{ route('user.manage') }}";
                    }
                });
            } else {
                alert(message);
                // fallback redirect
                if (type === 'success') {
                    window.location.href = "{{ route('user.manage') }}";
                }
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            @if (session('success'))
                showNotification('{{ session('success') }}', 'success');
            @endif

            @if (session('error'))
                showNotification('{{ session('error') }}', 'error');
            @endif
        });

        document.addEventListener("DOMContentLoaded", () => {
            document.querySelectorAll('.btn-delete').forEach(btn => {
                btn.addEventListener('click', function() {
                    let userId = this.dataset.id;
                    let firstname = this.dataset.firstname; // ambil dari atribut data-firstname
                    let lastname = this.dataset.lastname; // ambil dari atribut data-lastname
                    let fullName = `${firstname} ${lastname}`; // gabungkan nama lengkap

                    Swal.fire({
                        title: 'Yakin ingin menonaktifkan <b style="color:red;">' +
                            fullName + '</b>?',
                        text: "Pengguna yang tidak aktif tidak dapat menggunakan sistem. Pengguna nonaktif dapat diaktifkan kembali.",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#6c757d",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Ya, nonaktifkan",
                        cancelButtonText: "Batal"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch(`/user-manage/delete/${userId}`, {
                                    method: "DELETE",
                                    headers: {
                                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                        "Accept": "application/json"
                                    }
                                })
                                .then(res => res.json())
                                .then(data => {
                                    Swal.fire({
                                        title: "Berhasil!",
                                        text: data.success,
                                        icon: "success"
                                    }).then(() => {
                                        location.reload(); // refresh tabel
                                    });
                                })
                                .catch(err => {
                                    Swal.fire("Error!", "Gagal menonaktifkan pengguna",
                                        "error");
                                });
                        }
                    });
                });
            });
        });

        document.addEventListener("DOMContentLoaded", () => {
            document.querySelectorAll('.btn-restore').forEach(btn => {
                btn.addEventListener('click', function() {
                    let userId = this.dataset.id;
                    let firstname = this.dataset.firstname; // ambil dari atribut data-firstname
                    let lastname = this.dataset.lastname; // ambil dari atribut data-lastname
                    let fullName = `${firstname} ${lastname}`; // gabungkan nama lengkap

                    Swal.fire({
                        title: 'Yakin ingin mengaktifkan <b style="color:green;">' +
                            fullName + '</b>?',
                        text: "Pengguna yang diaktifkan dapat kembali menggunakan sistem.",
                        icon: "question",
                        showCancelButton: true,
                        confirmButtonColor: "#4bb543",
                        cancelButtonColor: "#6c757d",
                        confirmButtonText: "Ya, aktifkan",
                        cancelButtonText: "Batal"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch(`/user-manage/restore/${userId}`, {
                                    method: "PUT",
                                    headers: {
                                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                        "Accept": "application/json"
                                    }
                                })
                                .then(res => res.json())
                                .then(data => {
                                    Swal.fire({
                                        title: "Berhasil!",
                                        text: data.success,
                                        icon: "success"
                                    }).then(() => {
                                        location.reload(); // refresh tabel
                                    });
                                })
                                .catch(err => {
                                    Swal.fire("Error!", "Gagal mengaktifkan pengguna",
                                        "error");
                                });
                        }
                    });
                });
            });
        });
        document.querySelectorAll('.parent_id_select').forEach(function(select) {
            select.addEventListener('change', function() {
                var selectedOption = this.options[this.selectedIndex];
                var type = selectedOption.getAttribute('data-type');

                // cari hidden input di parent div yang sama
                var hiddenInput = this.closest('.col-md-6').querySelector('.parent_type_input');
                hiddenInput.value = type;
                console.log('Selected type:', type, 'for parent ID:', this.value);
            });
        });

        //Create parent type
        document.getElementById('parent_id').addEventListener('change', function() {
            var selectedOption = this.options[this.selectedIndex];
            var type = selectedOption.getAttribute('data-type');
            document.getElementById('parent_type').value = type;
            console.log('Selected type:', type, 'for parent ID:', this.value);

        });


        document.addEventListener('DOMContentLoaded', function() {
            const allPositions = @json($positions);

            const positionMap = {
                'director': [1],
                'divisi': [2, 3, 4],
                'department': [3, 4, 5, 6, 7, 8],
                'section': [5, 6, 7, 8, 9],
                'unit': [9]
            };

            document.querySelectorAll('.edit_parent_id').forEach(function(parentSelect) {
                const positionSelect = parentSelect.closest('.modal').querySelector('.edit_position');
                const hiddenType = parentSelect.closest('.modal').querySelector('.edit_parent_type');

                function updatePositions() {
                    const selectedOption = parentSelect.options[parentSelect.selectedIndex];
                    const type = selectedOption ? selectedOption.getAttribute('data-type') : null;

                    // Set hidden input
                    hiddenType.value = type;

                    // Kosongkan posisi
                    positionSelect.innerHTML = '';

                    if (type && positionMap[type]) {
                        positionSelect.disabled = false;
                        let filtered = allPositions.filter(pos => positionMap[type].includes(pos
                            .id_position));
                        filtered.forEach(pos => {
                            let opt = document.createElement('option');
                            opt.value = pos.id_position;
                            opt.textContent = pos.nm_position;
                            positionSelect.appendChild(opt);
                        });
                    } else {
                        positionSelect.disabled = true;
                        let opt = document.createElement('option');
                        opt.textContent = '-- Pilih posisi setelah pilih induk --';
                        positionSelect.appendChild(opt);
                    }
                }

                // Run pertama kali
                updatePositions();

                // Event listener
                parentSelect.addEventListener('change', updatePositions);
            });
        });



        const editParentId = document.getElementById('edit_parent_id');
        if (editParentId) {
            editParentId.addEventListener('change', function() {
                var selectedOption = this.options[this.selectedIndex];
                var type = selectedOption.getAttribute('data-type');
                document.getElementById('edit_parent_type').value = type;
            });
        }

        function showUploadModal() {
            Swal.fire({
                title: 'Import File?',
                html: `
            Anda dapat mengunggah file Excel untuk menambahkan pengguna baru.<br>
            Unduh format file Excel <a href="/Format Data User SIPO.xlsx" target="_blank">disini</a>.<br>
            <span class="text-danger" style="font-size: medium">
                Hanya mendukung format <strong>.xlsx</strong>
            </span>
            <br><br>
            <input type="file" id="fileInput"
                class="form-control rounded-3"
                style="padding:20px;"
                accept=".xlsx">
        `,
                iconHtml: `<i class="fas fa-cloud-arrow-up"></i>`,
                customClass: {
                    icon: 'no-border'
                },
                showCancelButton: true,
                cancelButtonText: 'Batal',
                confirmButtonText: 'Unggah',
                preConfirm: () => {
                    const file = document.getElementById('fileInput').files[0];
                    if (!file) {
                        Swal.showValidationMessage('Harap pilih file Excel yang valid');
                        return false;
                    }

                    // ✅ Check extension
                    const validExtensions = ['xlsx'];
                    const fileExtension = file.name.split('.').pop().toLowerCase();
                    if (!validExtensions.includes(fileExtension)) {
                        Swal.showValidationMessage(
                            'Format file tidak valid. Harap pilih file Excel (.xlsx)');
                        return false;
                    }

                    // ✅ Prepare FormData
                    const formData = new FormData();
                    formData.append('file_user', file);

                    // ✅ Send to backend
                    return fetch("{{ route('user-manage.import') }}", {
                            method: "POST",
                            body: formData,
                            headers: {
                                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                "X-Requested-With": "XMLHttpRequest",
                                "Accept": "application/json"
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (!data.status) {
                                throw new Error(data.message || "Gagal mengimpor file");
                            }
                            return data; // this will be `result.value`
                        })
                        .catch(error => {
                            Swal.showValidationMessage(error.message);
                        });
                }
            }).then((result) => {
                console.log(result);

                Swal.fire({
                    icon: result.value.status ? 'success' : 'error',
                    title: result.value.status ? 'Berhasil' : 'Gagal',
                    text: result.value.message,
                    //confirmButtonColor: '#28a745',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.reload();
                    }
                });
            });
        }
    </script>
@endpush
