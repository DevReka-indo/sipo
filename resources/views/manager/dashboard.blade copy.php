@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
    <div class="container-fluid px-4 py-0 mt-0">

        {{-- Header --}}
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body py-3">
                <h3 class="fw-bold mb-2">Beranda</h3>
                <p class="mb-0">
                    Selamat datang <strong>{{ auth()->user()->firstname . ' ' . auth()->user()->lastname ?? '-' }}</strong>
                    di
                    <a href="#" class="text-decoration-none fw-semibold">Sistem Persuratan</a>!
                    Anda login sebagai
                    <span
                        class="badge rounded-pill text-bg-warning text-dark">{{ trim(preg_replace('/\([^)]*\)/', '', Auth::user()->position->nm_position)) }}</span>
                </p>
                {{-- Tinjauan --}}
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-body py-3">
                        <h4 class="fw-bold mb-3">Tinjauan</h4>

                        <div class="row g-3">
                            {{-- MEMO --}}
                            {{-- <div class="col-12 col-md-4">
                                <div class="card card-stats card-round" style="background:#e9f2ff;">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <p class="mb-0 text-uppercase fw-bold text-dark fs-5">Memo</p>
                                            <a href="{{ route('memo.manager') }}"
                                                class="small fw-semibold text-decoration-none">Lihat Semua</a>
                                        </div>
                                        <hr class="my-2">
                                        <div class="row align-items-center">
                                            <div class="col-3">
                                                <div class="icon-big text-center icon-primary bubble-shadow-small">
                                                    <i class="fa-solid fa-file-signature"></i>
                                                </div>
                                            </div>
                                            <div class="col-7 col-stats">
                                                <div class="numbers">
                                                    <h4 class="card-title mb-0">{{ $jumlahMemo ?? 0 }}</h4>
                                                    <p class="card-category mb-0">Memo</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> --}}
                            <div class="col-12 col-md-4">
                                <div class="card border-0 shadow-sm h-100" style="background:#e9f2ff;">
                                    <div class="card-body d-flex flex-column justify-content-between">

                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="mb-0 text-uppercase fw-bold fs-5 text-dark">
                                                Memo
                                            </span>
                                            <i class="fa-solid fa-file-signature text-primary fs-1"></i>
                                        </div>

                                        <div class="mt-3">
                                            <h3 class="fw-bold mb-0">
                                                {{ $jumlahMemo ?? 0 }}
                                            </h3>
                                            <small class="text-muted">Total Memo</small>
                                        </div>

                                        <div>
                                        <a href="{{ route('memo.manager') }}"
                                        class="stretched-link text-decoration-none small fw-semibold mt-3">
                                            Lihat semua →
                                        </a>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            {{-- RISALAH RAPAT --}}
                            {{-- <div class="col-12 col-md-4">
                                <div class="card card-stats card-round" style="background:#e9f2ff;">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <p class="mb-0 text-uppercase fw-bold text-dark fs-5">Risalah Rapat</p>
                                            <a href="{{ route('risalah.manager') }}"
                                                class="small fw-semibold text-decoration-none">Lihat Semua</a>
                                        </div>
                                        <hr class="my-2">
                                        <div class="row align-items-center">
                                            <div class="col-3">
                                                <div class="icon-big text-center icon-info bubble-shadow-small">
                                                    <i class="fa-solid fa-clipboard-list"></i>
                                                </div>
                                            </div>
                                            <div class="col-7 col-stats">
                                                <div class="numbers">
                                                    <h4 class="card-title mb-0">{{ $jumlahRisalah ?? 0 }}</h4>
                                                    <p class="card-category mb-0">Risalah Rapat</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> --}}
                            <div class="col-12 col-md-4">
                                <div class="card border-0 shadow-sm h-100" style="background:#e9f2ff;">
                                    <div class="card-body d-flex flex-column justify-content-between">

                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="mb-0 text-uppercase fw-bold fs-5 text-dark">
                                                Risalah Rapat
                                            </span>
                                            <i class="fa-solid fa-clipboard-list text-primary fs-1 text-info"></i>
                                        </div>

                                        <div class="mt-3">
                                            <h3 class="fw-bold mb-0">
                                                {{ $jumlahRisalah ?? 0 }}
                                            </h3>
                                            <small class="text-muted">Total Risalah Rapat</small>
                                        </div>

                                        <div>
                                        <a href="{{ route('risalah.manager') }}"
                                        class="stretched-link text-decoration-none small fw-semibold mt-3">
                                            Lihat semua →
                                        </a>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            {{-- UNDANGAN RAPAT --}}
                            {{-- <div class="col-12 col-md-4">
                                <div class="card card-stats card-round" style="background:#e9f2ff;">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <p class="mb-0 text-uppercase fw-bold text-dark fs-5">Undangan Rapat</p>
                                            <a href="{{ route('undangan.manager') }}"
                                                class="small fw-semibold text-decoration-none">Lihat Semua</a>
                                        </div>
                                        <hr class="my-2">
                                        <div class="row align-items-center">
                                            <div class="col-3">
                                                <div class="icon-big text-center icon-success bubble-shadow-small">
                                                    <i class="fa-solid fa-calendar-days"></i>
                                                </div>
                                            </div>
                                            <div class="col-7 col-stats">
                                                <div class="numbers">
                                                    <h4 class="card-title mb-0">{{ $jumlahUndangan ?? 0 }}</h4>
                                                    <p class="card-category mb-0">Undangan Rapat</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> --}}
                            <div class="col-12 col-md-4">
                                <div class="card border-0 shadow-sm h-100" style="background:#e9f2ff;">
                                    <div class="card-body d-flex flex-column justify-content-between">

                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="mb-0 text-uppercase fw-bold fs-5 text-dark">
                                                Undangan Rapat
                                            </span>
                                            <i class="fa-solid fa-calendar-days text-primary fs-1 text-success"></i>
                                        </div>

                                        <div class="mt-3">
                                            <h3 class="fw-bold mb-0">
                                                {{ $jumlahUndangan ?? 0 }}
                                            </h3>
                                            <small class="text-muted">Total Undangan Rapat</small>
                                        </div>

                                        <div>
                                        <a href="{{ route('undangan.manager') }}"
                                        class="stretched-link text-decoration-none small fw-semibold mt-3">
                                            Lihat semua →
                                        </a>
                                        </div>

                                    </div>
                                </div>
                            </div>

                        </div> {{-- /row --}}
                    </div>
                </div>

                {{-- Aktivitas --}}
                <div class="card shadow-sm border-0">
                    <div class="card-body py-3">
                        <h4 class="fw-bold mb-3">Aktivitas</h4>

                        @php
                            $documentConfig = [
                                'Undangan' => [
                                    'color' => 'success',
                                    'icon' => 'fa-solid fa-calendar-week',
                                ],
                                'Memo' => [
                                    'color' => 'primary',
                                    'icon' => 'fa-solid fa-file-text',
                                ],
                                'Risalah' => [
                                    'color' => 'info',
                                    'icon' => 'fa-solid fa-clipboard',
                                ],
                                'default' => [
                                    'color' => 'secondary',
                                    'icon' => 'fa-solid fa-file',
                                ],
                            ];
                        @endphp

                        <div class="row gy-2">
                            @foreach ($notifikasiByDate as $tanggal => $list)
                                {{-- Header tanggal --}}
                                @if (count($notifikasiByDate) > 1)
                                    <div class="col-12">
                                        <h5 class="text-muted mb-2 mt-3">{{ $tanggal }}</h5>
                                    </div>
                                @endif

                                @foreach ($list as $notif)
                                    @php
                                        $judul = strtolower($notif->judul);

                                        // Deteksi berdasarkan kata kunci
                                        if (str_contains($judul, 'risalah')) {
                                            $icon = 'fas fa-clipboard-list';
                                            switch (true) {
                                                case str_contains($judul, 'tolak'):
                                                    $bgColor = 'danger';
                                                    break;
                                                case str_contains($judul, 'koreksi'):
                                                    $bgColor = 'warning';
                                                    break;
                                                case str_contains($judul, 'setuju') ||
                                                    str_contains($judul, 'masuk') ||
                                                    str_contains($judul, 'kirim'):
                                                    $bgColor = 'success';
                                                    break;
                                                default:
                                                    $bgColor = 'secondary';
                                                    break;
                                            }
                                        } elseif (str_contains($judul, 'undangan')) {
                                            $icon = 'fas fa-calendar-check';
                                            switch (true) {
                                                case str_contains($judul, 'tolak'):
                                                    $bgColor = 'danger';
                                                    break;
                                                case str_contains($judul, 'koreksi'):
                                                    $bgColor = 'warning';
                                                    break;
                                                case str_contains($judul, 'setuju') ||
                                                    str_contains($judul, 'masuk') ||
                                                    str_contains($judul, 'kirim'):
                                                    $bgColor = 'success';
                                                    break;
                                                default:
                                                    $bgColor = 'secondary';
                                                    break;
                                            }
                                        } elseif (str_contains($judul, 'memo')) {
                                            $icon = 'fas fa-file-alt';
                                            switch (true) {
                                                case str_contains($judul, 'tolak'):
                                                    $bgColor = 'danger';
                                                    break;
                                                case str_contains($judul, 'revisi'):
                                                    $bgColor = 'warning';
                                                    break;
                                                case str_contains($judul, 'setuju') ||
                                                    str_contains($judul, 'masuk') ||
                                                    str_contains($judul, 'kirim'):
                                                    $bgColor = 'success';
                                                    break;
                                                default:
                                                    $bgColor = 'secondary';
                                                    break;
                                            }
                                        } elseif (str_contains($judul, 'surat')) {
                                            $bgColor = 'warning';
                                            $icon = 'fas fa-envelope';
                                        } elseif (str_contains($judul, 'laporan')) {
                                            $bgColor = 'danger';
                                            $icon = 'fas fa-chart-bar';
                                        } else {
                                            $bgColor = 'secondary';
                                            $icon = 'fas fa-file';
                                        }
                                    @endphp

                                    <div class="col-12">
                                        <div class="card border shadow-sm mb-0"
                                            style="background:#fff; border-radius:12px;">
                                            <div class="card-body p-3" style="min-height:80px;">
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3 flex-shrink-0">
                                                        <div class="bg-{{ $bgColor }} d-flex align-items-center justify-content-center"
                                                            style="width:46px; height:46px; border-radius:50%;">
                                                            <i class="{{ $icon }} text-white"
                                                                style="font-size:20px;"></i>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="fw-bold mb-1 text-dark lh-sm">{{ $notif->judul }}</h6>
                                                        <p class="mb-0 text-muted small lh-sm">
                                                            {{ \Carbon\Carbon::parse($notif->updated_at)->locale('id')->translatedFormat('l, d F Y \p\u\k\u\l H.i') }}
                                                            •
                                                            <span
                                                                class="text-primary fw-bold">{{ $notif->judul_document }}</span>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endforeach
                        </div>
                    </div>

                </div>
            </div>

        @endsection
