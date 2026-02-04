@extends('layouts.app')

@section('title', 'Cetak Laporan Undangan Rapat')

@section('content')
    <div class="container-fluid px-4 py-0 mt-0">
        <div class="card shadow-sm border-0">
            <div class="card-body py-3">

                <h3 class="fw-bold mb-3">Cetak Laporan Undangan Rapat</h3>

                {{-- Breadcrumb --}}
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="bg-white border rounded-2 px-3 py-2 w-100 d-flex align-items-center">
                            <a href="{{ auth()->user()->level == 'superadmin' ? route('superadmin.dashboard') : route('admin.dashboard') }}"
                                class="text-decoration-none text-primary">
                                Beranda
                            </a>
                            <span class="mx-2 text-muted">/</span>
                            <a href="{{ route('laporan-undangan.superadmin') }}" class="text-decoration-none text-primary">
                                Laporan
                            </a>
                            <span class="mx-2 text-muted">/</span>
                            <span class="text-muted">Cetak Laporan Undangan Rapat</span>
                        </div>
                    </div>
                </div>

                {{-- Row Filter --}}
                {{-- Row Filter --}}
                <div class="col d-flex justify-content-between align-items-center mb-2">
                    <form method="GET" action="{{ route('cetak-laporan-undangan.superadmin') }}"
                        class="row g-2 justify-content-end align-items-center">
                        @csrf
                        @if (Auth::user()->role_id_role == 1)

                            {{-- Divisi --}}
                            <div class="col-12 col-md-auto">
                                <select class="form-select rounded-3" name="kode" id="kode"
                                    aria-label="Pilih Divisi" onchange="this.form.submit()">
                                    <option value="pilih" {{ !request()->filled('kode') ? 'selected' : '' }}>Semua
                                        Divisi
                                    </option>
                                    @foreach ($kode ?? collect() as $k)
                                        <option value="{{ $k }}" {{ request('kode') == $k ? 'selected' : '' }}>
                                            {{ $k }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="col-auto">
                            <div class="input-group">
                                <span class="input-group-text rounded-start-3"><i class="fas fa-search"></i></span>
                                <input type="text" name="search" class="form-control search-input rounded-end-3"
                                    placeholder="Cari" value="{{ request('search') }}" onchange="this.form.submit()"
                                    style="max-width: 200px;">
                            </div>
                        </div>

                        <input type="hidden" name="tgl_awal"
                            value="{{ request('tgl_awal', session('filter_dates.tgl_awal')) }}">
                        <input type="hidden" name="tgl_akhir"
                            value="{{ request('tgl_akhir', session('filter_dates.tgl_akhir')) }}">
                        {{-- Tombol Cetak --}}

                    </form>
                    <div class="col-auto">
                        <button class="btn btn-print rounded-3" onclick="showManagerModal()">
                            <i class="fa-solid fa-print"></i> Cetak Data
                        </button>
                    </div>
                </div>


                {{-- Tabel --}}
                <div class="table-responsive mt-3">
                    <table class="table table-bordered custom-table-bagian">
                        <thead>
                            <tr>
                                <th class="text-center" style="width:5%;">No</th>
                                <th class="text-center" style="width:20%;">Nama Dokumen</th>
                                <th class="text-center" style="width:12%;">Tanggal Masuk</th>
                                {{-- <th class="text-center" style="width:2%;">Seri</th> --}}
                                <th class="text-center" style="width:20%;">Dokumen</th>
                                <th class="text-center" style="width:8%;">Pengirim</th>
                                <th class="text-center" style="width:12%;">Tanggal Disahkan</th>
                                <th class="text-center" style="width:10%;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($undangans->isNotEmpty())
                                @foreach ($undangans as $index => $laporan)
                                    <tr>
                                        <td class="nomor">{{ $index + 1 }}</td>
                                        <td class="nama-dokumen {{ $laporan->status == 'reject' ? 'text-danger' : ($laporan->status == 'correction' ? 'text-warning' : ($laporan->status == 'approve' ? 'text-success' : '')) }}"
                                            style="{{ $laporan->status == 'pending' ? 'color: #0dcaf0;' : '' }}">
                                            {{ $laporan->judul }}
                                        </td>
                                        <td>{{ $laporan->tgl_dibuat->format('d-m-Y') }}</td>
                                        {{-- <td>{{ $laporan->seri_surat }}</td> --}}
                                        <td>{{ $laporan->nomor_undangan }}</td>
                                        <td class="text-center">{{ $laporan->kode ?? '-' }}</td>
                                        <td>{{ $laporan->tgl_disahkan ? $laporan->tgl_disahkan->format('d-m-Y') : '-' }}
                                        </td>
                                        <td class="text-center">

                                            @if ($laporan->status == 'reject')
                                                <span class="badge bg-danger">Ditolak</span>
                                            @elseif ($laporan->status == 'pending')
                                                <span class="badge bg-info">Diproses</span>
                                            @elseif ($laporan->status == 'correction')
                                                <span class="badge bg-warning">Dikoreksi</span>
                                            @else
                                                <span class="badge bg-success">Diterima</span>
                                            @endif

                                        </td>
                                        <!-- <td>
                                    <button class="btn btn-sm1"><img src="/img/arsip/unduh.png" alt="unduh"></button>
                                    <button class="btn btn-sm2" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                        <img src="/img/arsip/delete.png" alt="delete">
                                    </button>
                                    <button class="btn btn-sm3"><img src="/img/arsip/preview.png" alt="preview"></button>
                                </td> -->
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="8">Tidak ada undangan rapat pada tanggal yang dipilih.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <script>
                    function showManagerModal() {
                        const selectHtml = `
                        <select id="manager-select" class="form-control rounded-3">
                            <option value="" disabled selected>-- Pilih --</option>
                            {!! collect($managers)->map(fn($m) => "<option value='{$m->id}'>{$m->fullname}</option>")->implode('') !!}
                        </select>
                    `;

                        Swal.fire({
                            title: 'Pilih Penandatangan',
                            html: `Silahkan pilih karyawan yang akan menandatangani laporan.<br><br>${selectHtml}`,
                            focusConfirm: false,
                            preConfirm: () => {
                                const select = Swal.getPopup().querySelector('#manager-select');
                                const managerId = select ? select.value : '';
                                if (!managerId) {
                                    Swal.showValidationMessage('Silakan pilih Nama Penandatangan.');
                                    return false;
                                }
                                return managerId;
                            },
                            didOpen: () => {
                                const select = Swal.getPopup().querySelector('#manager-select');
                                if (select) {
                                    select.addEventListener('change', () => Swal.resetValidationMessage());
                                }
                            },
                            showCancelButton: true,
                            confirmButtonText: 'Cetak',
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                const managerId = result.value;
                                const url = new URL("{{ route('format-cetakLaporan-risalah') }}", window.location.origin);
                                url.searchParams.append('manager_id', managerId);
                                url.searchParams.append('kode', "{{ request('kode') }}");
                                url.searchParams.append('search', "{{ request('search') }}");
                                url.searchParams.append('tgl_awal',
                                    "{{ request('tgl_awal', session('filter_dates.tgl_awal')) }}");
                                url.searchParams.append('tgl_akhir',
                                    "{{ request('tgl_akhir', session('filter_dates.tgl_akhir')) }}");
                                window.open(url.toString(), '_blank');
                            }
                        });
                    }
                </script>

            </div>
        </div>
    </div>

@endsection
