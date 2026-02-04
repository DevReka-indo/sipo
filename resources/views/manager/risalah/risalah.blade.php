@extends('layouts.app')

@section('title', 'Risalah Rapat')

@section('content')
    <div class="container-fluid px-4 py-0 mt-0">
        <div class="card shadow-sm border-0">
            <div class="card-body py-3">

                <h3 class="fw-bold mb-3">Risalah</h3>

                {{-- Breadcrumb --}}
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="bg-white border rounded-2 px-3 py-2 w-100 d-flex align-items-center">
                            <a href="#" class="text-decoration-none text-primary">Beranda</a>
                            <span class="text-muted ms-1">/ Risalah</span>
                        </div>
                    </div>
                </div>

                {{-- Row Filter --}}
                <form class="row g-2 align-items-center" method="GET" action="{{ route('risalah.manager') }}">
                    <div class="col-auto">
                        <select name="per_page" class="form-select rounded-3" style="max-width:100px;"
                            onchange="this.form.submit()">
                            <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>

                    {{-- Status --}}
                    <div class="col-12 col-md-auto">
                        <select class="form-select rounded-3" name="status">
                            <option value="">Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Diproses</option>
                            <option value="correction" {{ request('status') == 'correction' ? 'selected' : '' }}>Dikoreksi
                            </option>
                            <option value="approve" {{ request('status') == 'approve' ? 'selected' : '' }}>Diterima</option>
                            <option value="reject" {{ request('status') == 'reject' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>

                    {{-- Tanggal --}}
                    <div class="col-12 col-md-auto">
                        <input type="date" class="form-control rounded-3" name="tgl_dibuat_awal"
                            value="{{ request('tgl_dibuat_awal') }}" placeholder="Tanggal Awal">
                    </div>
                    <div class="col-auto d-none d-md-flex align-items-center"><span class="mx-1">→</span></div>
                    <div class="col-12 col-md-auto">
                        <input type="date" class="form-control rounded-3" name="tgl_dibuat_akhir"
                            value="{{ request('tgl_dibuat_akhir') }}" placeholder="Tanggal Akhir">
                    </div>

                    {{-- Keyword --}}
                    <div class="col-12 col-md">
                        <div class="input-group">
                            <span class="input-group-text rounded-start-3"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control rounded-end-3" name="search"
                                value="{{ request('search') }}" placeholder="Cari">
                        </div>
                    </div>

                    {{-- Kode --}}
                    {{-- <div class="col-12 col-md-auto">
                        <select class="form-select rounded-3" name="kode" id="kode" aria-label="Pilih Divisi">
                            <option value="" {{ !request()->filled('kode') ? 'selected' : '' }}>Semua Divisi</option>
                            @foreach ($kode as $k)
                                <option value="{{ $k }}" {{ request('kode') == $k ? 'selected' : '' }}>
                                    {{ $k }}
                                </option>
                            @endforeach
                        </select>
                    </div> --}}

                    {{-- Tombol --}}
                    <div class="col-12 col-md-auto">
                        <button type="submit" class="btn btn-primary rounded-3">
                            <i class="fas fa-filter me-1"></i>Filter
                        </button>
                        <button type="button" class="btn rounded-3 text-white"
                            style="background-color:#1E4178; border-color:#1E4178;" onclick=showCreateModal()>
                            <i class="fas fa-plus me-1"></i>Tambah Risalah
                        </button>
                    </div>
                </form>

                {{-- Tabel --}}
                <div class="table-responsive mt-3">
                    <table class="table table-bordered custom-table-bagian">
                        <thead>
                            <tr>
                                <th class="text-center" style="width:5%;">No</th>
                                <th class="text-center" style="width:20%;">Perihal</th>
                                <th class="text-center" style="width:12%;">Tanggal Risalah</th>
                                {{-- <th class="text-center" style="width:2%;">Seri</th> --}}
                                <th class="text-center" style="width:20%;">Dokumen</th>
                                <th class="text-center" style="width:12%;">Tanggal Disahkan</th>
                                <th class="text-center" style="width:10%;">Divisi</th>
                                <th class="text-center" style="width:10%;">Tipe</th>
                                <th class="text-center" style="width:8%;">Status</th>
                                <th class="text-center" style="width:8%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($risalahs as $index => $risalah)
                                <tr>
                                    <td class="nomor">{{ ($risalahs->firstItem() ?? 0) + $index }}</td>
                                    <td class="nama-dokumen
                                        {{ $risalah->status == 'reject' ? 'text-danger' : ($risalah->status == 'correction' ? 'text-warning' : ($risalah->status == 'approve' ? 'text-success' : '')) }}"
                                        style="{{ $risalah->status == 'pending' ? 'color: #0dcaf0;' : '' }}">
                                        {{ Str::limit($risalah->judul ?? '-', 35, '...') }}
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($risalah->tgl_dibuat)->format('d-m-Y') }}</td>
                                    {{-- <td>{{ $risalah->seri_surat }}</td> --}}
                                    <td>{{ $risalah->nomor_risalah }}</td>
                                    <td>{{ $risalah->tgl_disahkan ? \Carbon\Carbon::parse($risalah->tgl_disahkan)->format('d-m-Y') : '-' }}
                                    </td>
                                    <td class="text-center">
                                        {{ $risalah->user->department->kode_department ?? ($risalah->user->divisi->kode_divisi ?? '-') }}
                                    </td>
                                    <td>
                                        @if ($risalah->with_undangan)
                                            <span class="badge px-3 py-2" style="background-color:#35A29F;">
                                                Dengan Undangan
                                            </span>
                                        @else
                                            <span class="badge px-3 py-2" style="background-color:#3048d0;">
                                                Tanpa Undangan
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($risalah->status == 'reject')
                                            <span class="badge bg-danger">Ditolak</span>
                                        @elseif ($risalah->status == 'pending')
                                            <span class="badge bg-info">Diproses</span>
                                        @elseif ($risalah->status == 'correction')
                                            <span class="badge bg-warning">Dikoreksi</span>
                                        @else
                                            <span class="badge bg-success">Diterima</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-2">
                                            <button title="Detail"
                                                class="btn btn-sm rounded-circle text-white border-0 bg-info"
                                                style="width:30px; height:30px; display:flex; align-items:center; justify-content:center;"
                                                onclick="window.location.href='{{ route('persetujuan.risalah', ['id' => $risalah->id_risalah ?? '1']) }}'">
                                                <i class="fas fa-eye" alt="Detail"></i>
                                            </button>
                                            @if ($risalah->status == 'pending' || $risalah->status == 'correction')
                                                @if (Auth::user()->fullname == $risalah->nama_notulis_acara)
                                                    <button type="button"
                                                        class="btn btn-sm rounded-circle text-white border-0 bg-secondary"
                                                        style="width:30px; height:30px; display:flex; align-items:center; justify-content:center;"
                                                        onclick="window.location.href='{{ route('risalah.edit', ['id' => $risalah->id_risalah]) }}'">
                                                        <i class="fa-solid fa-pencil fa-lg"></i>
                                                    </button>
                                                @endif
                                                {{-- Button Arsip pakai SweetAlert --}}
                                            @elseif ($risalah->status == 'approve' || $risalah->status == 'reject')
                                                <button type="button"
                                                    class="btn btn-sm rounded-circle text-white border-0"
                                                    style="background-color:#FFAD46; width:30px; height:30px; display:flex; align-items:center; justify-content:center;"
                                                    onclick="showArsipConfirmation({{ $risalah->id_risalah ?? '' }}, '{{ $risalah->judul ?? $risalah->judul }}')"
                                                    title="Arsip">
                                                    <i class="fa-solid fa-archive fa-lg"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">Belum ada risalah</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="d-flex justify-content-end mt-3">
                    {{ $risalahs->onEachSide(1)->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let currentRisalahId = null;

        // ---------- Arsip ----------
        function showArsipConfirmation(risalahId, risalahTitle) {
            currentRisalahId = risalahId;
            Swal.fire({
                title: 'Arsip Risalah?',
                html: `Apakah Anda yakin ingin mengarsipkan risalah <strong>${risalahTitle}</strong>?<br><br>
               <span class="text-muted">Risalah yang diarsipkan akan dipindahkan ke arsip dan tidak akan muncul di daftar utama.</span>`,
                icon: 'question',
                showCancelButton: true,
                reverseButtons: true,
                confirmButtonText: '<i class="fa-solid fa-archive me-1"></i>Arsip',
                cancelButtonText: 'Batal',
                customClass: {
                    actions: 'swal2-actions swal2-actions-custom',
                    confirmButton: 'btn btn-warning px-4 py-2',
                    cancelButton: 'btn btn-secondary px-4 py-2'
                },
                buttonsStyling: false
            }).then(result => {
                if (result.isConfirmed) confirmArsip();
            });
        }

        function confirmArsip() {
            if (!currentRisalahId) return;

            Swal.fire({
                title: 'Mengarsipkan...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            fetch(`/arsip/${currentRisalahId}/Risalah/simpan`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: 'Risalah berhasil diarsipkan',
                            icon: 'success',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#31CE36'
                        }).then(() => window.location.reload());
                    } else {
                        showNotification('Gagal mengarsipkan risalah', 'error');
                    }
                })
                .catch(() => showNotification('Terjadi kesalahan', 'error'));
        }

        // ---------- Utils ----------
        function showNotification(message, type) {
            Swal.fire({
                title: type === 'success' ? 'Berhasil!' : 'Error!',
                text: message,
                icon: type,
                confirmButtonText: 'OK',
                confirmButtonColor: type === 'success' ? '#28a745' : '#d33'
            });
        }

        // ---------- Flash Message ----------
        document.addEventListener("DOMContentLoaded", function() {
            @if (session('success'))
                showNotification('{{ session('success') }}', 'success');
            @endif
            @if (session('error'))
                showNotification('{{ session('error') }}', 'error');
            @endif
        });

        function showCreateModal() {
            //console.log("modal got owo");
            Swal.fire({
                title: 'Pilih Tipe Risalah:',
                html: `<div class="row justify-content-around">
                            <div class="col-sm-5">
                                <div class="row">
                                    <a href="{{ route('add-risalah.manager') }}" class="btn rounded-3 text-white"
                                    style="background-color:#4285f4; border-color:#4285f4;">
                                        <div class="col">
                                            <i class="fa-solid fa-calendar-days" style="font-size: 50px"></i>
                                        </div>

                                        <div class="col">
                                            Risalah Dengan Undangan
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div class="col-sm-5">
                                <div class="row">
                                    <a href="{{ route('add-risalah.custom.manager') }}" class="btn rounded-3 text-white"
                                    style="background-color:#34a853; border-color:#34a853;">
                                        <div class="col">
                                            <i class="fa-solid fa-clipboard-list" style="font-size: 50px"></i>
                                        </div>
                                        <div class="col">
                                            Risalah Tanpa Undangan
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                            `,
                showCloseButton: true,
                showConfirmButton: false
            });
        }
    </script>
@endpush
