@extends('layouts.app')

@section('title', 'Detail Undangan Rapat')

@section('content')

    <div class="container-fluid px-4 py-0 mt-0">
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-body py-3">

                <h3 class="fw-bold mb-3">Detail Undangan Rapat</h3>

                {{-- Breadcrumb --}}
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="bg-white border rounded-2 px-3 py-2 w-100 d-flex align-items-center">
                            <a href="{{ route(Auth::user()->role->nm_role . '.dashboard') }}"
                                class="text-decoration-none text-primary">Beranda</a>
                            <span class="mx-2 text-muted">/</span>
                            <a href="{{ route('arsip.undangan') }}" class="text-decoration-none text-primary">Undangan
                                Rapat</a>
                            <span class="mx-2 text-muted">/</span>
                            <span class="text-muted">Detail Undangan Rapat</span>
                        </div>
                    </div>
                </div>

                <div class="row ">
                    {{-- Kolom kiri: Informasi Detail Memo --}}
                    <div class="col-md-6 mb-3">
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-header py-2 rounded-top-3"
                                style="background:#e3f2fd;border-bottom:1px solid #bbdefb;">
                                <i class="fa fa-file-alt me-2 text-primary"></i>
                                <span class="fw-semibold">Informasi Detail Undangan rapat</span>
                            </div>
                            <div class="card-body">

                                <div class="info-row d-flex flex-column flex-sm-row">
                                    <div class="info-label">No Surat</div>
                                    <div class="info-value">{{ $undangan->nomor_undangan }}</div>
                                </div>

                                <div class="info-row d-flex flex-column flex-sm-row">
                                    <div class="info-label">Perihal</div>
                                    <div class="info-value">{{ $undangan->judul }}</div>
                                </div>

                                <div class="info-row d-flex flex-column flex-sm-row">
                                    <div class="info-label">Hari, Tanggal</div>
                                    <div class="info-value">
                                        {{ \Carbon\Carbon::parse($undangan->tgl_rapat)->translatedFormat('l, d F Y') }}
                                    </div>
                                </div>
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
                                    <div class="info-value">
                                        {{ $undangan->user ? $undangan->user->firstname . ' ' . $undangan->user->lastname : 'N/A' }}
                                    </div>
                                </div>

                                <div class="info-row d-flex flex-column flex-sm-row">
                                    <div class="info-label">Status</div>
                                    <div class="info-value">
                                        @if ($undangan->status == 'reject')
                                            <span class="badge bg-danger px-3 py-2">Ditolak</span>
                                        @elseif ($undangan->status == 'pending')
                                            <span class="badge bg-info px-3 py-2">Diproses</span>
                                        @elseif ($undangan->status == 'correction')
                                            <span class="badge bg-warning px-3 py-2">Dikoreksi</span>
                                        @else
                                            <span class="badge bg-success px-3 py-2">Diterima</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="info-row d-flex flex-column flex-sm-row">
                                    <div class="info-label">File</div>
                                    <div class="info-value">
                                        <a onclick="window.location.href='{{ route('view-undanganPDF', $undangan->id_undangan) }}'"
                                            class="btn btn-sm btn-custom me-2 rounded-2">
                                            <i class="fa fa-eye me-1"></i> Lihat
                                        </a>
                                        @if ($undangan->status == 'approve')
                                            <a onclick="window.location.href='{{ route('cetakundangan', ['id' => $undangan->id_undangan]) }}'"
                                                class="btn btn-sm btn-custom rounded-2">
                                                <i class="fa fa-download me-1"></i> Unduh
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> {{-- /row --}}

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-header py-2 rounded-top-3"
                                style="background:#e3f2fd;border-bottom:1px solid #bbdefb;">
                                <i class="fa fa-file-alt me-2 text-primary"></i>
                                <span class="fw-semibold">Daftar Tujuan</span>
                            </div>
                            <div class="card-body">

                                <div class="info-row d-flex flex-column flex-sm-row">
                                    <div class="info-label">Kepada</div>
                                    <div class="info-value">
                                        <pre style="font-family: Public Sans, sans-serif">{{ $undangan->tujuan }}</pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Catatan --}}
                @if ($undangan->catatan)
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card border-0 shadow-sm rounded-3">
                                <div class="card-header py-2 rounded-top-3"
                                    style="background:#fff3cd;border-bottom:1px solid #ffeeba;">
                                    <i class="fa fa-sticky-note me-2 text-warning"></i>
                                    <span class="fw-semibold">Catatan</span>
                                </div>
                                <div class="card-body">
                                    <textarea class="form-control" rows="4" readonly>{{ $undangan->catatan }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
