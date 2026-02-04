@extends('layouts.app')

@section('title', 'Counter Nomor Surat')

@section('content')
    <div class="container-fluid">

        <h4 class="mb-4">
            <i class="fas fa-list-ol me-2"></i>
            Counter Nomor Surat
        </h4>

        {{-- ❌ TIDAK PUNYA AKSES --}}
        @if (!$authorized)
            <div class="alert alert-danger text-center">
                <i class="fas fa-lock me-1"></i>
                {{ $message }}
            </div>
        @else
            {{-- ℹ️ INFO KODE BAGIAN --}}
            <div class="alert alert-info">
                <strong>Kode Bagian:</strong> {{ $kodeBagian ?? 'SEMUA' }}
            </div>

            {{-- 🔍 FILTER --}}
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('counter-nomor-surat.index') }}" class="row g-3">

                        {{-- Filter Kode Bagian (hanya jika user punya lebih dari 1 kode) --}}
                        @if (!empty($kodeBagianList) && count($kodeBagianList) > 1)
                            <div class="col-md-3">
                                <label class="form-label">Kode Bagian</label>
                                <select name="kode_bagian" class="form-select">
                                    <option value="">-- Semua Kode --</option>
                                    @foreach ($kodeBagianList as $kode)
                                        <option value="{{ $kode }}" {{ $filterKode == $kode ? 'selected' : '' }}>
                                            {{ $kode }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        {{-- Filter Tahun --}}
                        <div class="col-md-3">
                            <label class="form-label">Tahun</label>
                            <select name="tahun" class="form-select">
                                <option value="">-- Semua Tahun --</option>
                                @foreach ($tahunList as $tahun)
                                    <option value="{{ $tahun }}" {{ $filterTahun == $tahun ? 'selected' : '' }}>
                                        {{ $tahun }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Filter Kode Tipe Surat --}}
                        <div class="col-md-3">
                            <label class="form-label">Tipe Surat</label>
                            <select name="kode_tipe_surat" class="form-select">
                                <option value="">-- Semua Tipe --</option>
                                @foreach ($kodeTipeSuratList as $kode)
                                    <option value="{{ $kode }}"
                                        {{ request('kode_tipe_surat') == $kode ? 'selected' : '' }}>
                                        {{ $kode }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Tombol Filter --}}
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-filter me-1"></i> Filter
                            </button>
                            <a href="{{ route('counter-nomor-surat.index') }}" class="btn btn-secondary">
                                <i class="fas fa-redo me-1"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- 📭 BELUM ADA DATA --}}
            @if ($data->isEmpty())
                <div class="alert alert-warning text-center">
                    <i class="fas fa-info-circle me-1"></i>
                    Belum ada data nomor surat untuk filter yang dipilih.
                </div>
            @else
                {{-- 📊 TABEL --}}
                <div class="card">
                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Nomor Surat</th>
                                    <th>Jenis</th>
                                    <th>Divisi</th>
                                    <th>Perihal</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data as $i => $row)
                                    <tr>
                                        {{-- <td>{{ $i + 1 }}</td> --}}
                                        <td>{{ $data->firstItem() + $i }}</td>
                                        <td>{{ \Carbon\Carbon::parse($row->tanggal_permintaan)->format('d-m-Y') }}</td>
                                        <td class="fw-bold">{{ $row->nomor_surat_generated }}</td>
                                        <td>{{ $row->jenis }}</td>
                                        <td>{{ $row->divisi }}</td>
                                        <td>{{ $row->perihal }}</td>
                                        <td>
                                            @if ($row->is_used)
                                                <span class="badge bg-success">Digunakan</span>
                                            @else
                                                <span class="badge bg-secondary">Belum</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        {{-- 🔢 PAGINATION --}}
                        <div class="mt-3 d-flex justify-content-end">
                            {{ $data->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>
            @endif
        @endif

    </div>
@endsection
