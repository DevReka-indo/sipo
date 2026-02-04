@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
    <div class="container-fluid px-4 py-0 mt-0">

        {{-- Header --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body py-3">
                <h3 class="fw-bold mb-2">Beranda</h3>
                <p class="mb-0">
                    Selamat datang <strong>{{ auth()->user()->firstname . ' ' . auth()->user()->lastname ?? 'Pengguna' }}</strong>
                    di <a href="#" class="text-decoration-none fw-semibold">Sistem Persuratan</a>!
                    Anda login sebagai
                    <span class="badge rounded-pill text-bg-warning text-dark">
                        {{ trim(preg_replace('/\([^)]*\)/', '', Auth::user()->position->nm_position)) }}
                    </span>
                </p>
            </div>
        </div>

        {{-- Tinjauan Dokumen --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body py-3">
                <h4 class="fw-bold mb-3">Tinjauan Dokumen</h4>

                <div class="row g-3">
                    {{-- MEMO KELUAR --}}
                    <div class="col-12 col-sm-6 col-lg-3">
                        <a href="{{ route('admin.memo.terkirim') }}" class="text-decoration-none">
                            <div class="card border-0 shadow-sm h-100 card-hover"
                                 style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); cursor: pointer;">
                                <div class="card-body text-white">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <p class="mb-1 text-white small">Memo Keluar</p>
                                            <h2 class="fw-bold mb-0">{{ $jumlahMemoKeluar ?? 0 }}</h2>
                                        </div>
                                        <div class="bg-white bg-opacity-25 rounded-3 p-3">
                                            <i class="fas fa-paper-plane"></i>
                                        </div>
                                    </div>
                                    <small class="text-white">
                                        <i class="fas fa-arrow-right me-1"></i> Lihat Detail
                                    </small>
                                </div>
                            </div>
                        </a>
                    </div>

                    {{-- MEMO MASUK --}}
                    <div class="col-12 col-sm-6 col-lg-3">
                        <a href="{{ route('admin.memo.diterima') }}" class="text-decoration-none">
                            <div class="card border-0 shadow-sm h-100 card-hover"
                                 style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); cursor: pointer;">
                                <div class="card-body text-white">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <p class="mb-1 text-white small">Memo Masuk</p>
                                            <h2 class="fw-bold mb-0">{{ $jumlahMemoMasuk ?? 0 }}</h2>
                                        </div>
                                        <div class="bg-white bg-opacity-25 rounded-3 p-3">
                                            <i class="fas fa-inbox"></i>
                                        </div>
                                    </div>
                                    <small class="text-white">
                                        <i class="fas fa-arrow-right me-1"></i> Lihat Detail
                                    </small>
                                </div>
                            </div>
                        </a>
                    </div>

                    {{-- UNDANGAN KELUAR --}}
                    <div class="col-12 col-sm-6 col-lg-3">
                        <a href="{{ route('admin.undangan.terkirim') }}" class="text-decoration-none">
                            <div class="card border-0 shadow-sm h-100 card-hover"
                                 style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); cursor: pointer;">
                                <div class="card-body text-white">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <p class="mb-1 text-white small">Undangan Keluar</p>
                                            <h2 class="fw-bold mb-0">{{ $jumlahUndanganKeluar ?? 0 }}</h2>
                                        </div>
                                        <div class="bg-white bg-opacity-25 rounded-3 p-3">
                                            <i class="fas fa-calendar-plus"></i>
                                        </div>
                                    </div>
                                    <small class="text-white">
                                        <i class="fas fa-arrow-right me-1"></i> Lihat Detail
                                    </small>
                                </div>
                            </div>
                        </a>
                    </div>

                    {{-- UNDANGAN MASUK --}}
                    <div class="col-12 col-sm-6 col-lg-3">
                        <a href="{{ route('admin.undangan.diterima') }}" class="text-decoration-none">
                            <div class="card border-0 shadow-sm h-100 card-hover"
                                 style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); cursor: pointer;">
                                <div class="card-body text-white">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <p class="mb-1 text-white small">Undangan Masuk</p>
                                            <h2 class="fw-bold mb-0">{{ $jumlahUndanganMasuk ?? 0 }}</h2>
                                        </div>
                                        <div class="bg-white bg-opacity-25 rounded-3 p-3">
                                            <i class="fas fa-calendar-check"></i>
                                        </div>
                                    </div>
                                    <small class="text-white">
                                        <i class="fas fa-arrow-right me-1"></i> Lihat Detail
                                    </small>
                                </div>
                            </div>
                        </a>
                    </div>

                    {{-- RISALAH RAPAT --}}
                    <div class="col-12 col-lg-12">
                        <a href="{{ route('admin.risalah.index') }}" class="text-decoration-none">
                            <div class="card border-0 shadow-sm h-100 card-hover"
                                 style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); cursor: pointer;">
                                <div class="card-body text-white">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <p class="mb-1 text-white small">Risalah Rapat</p>
                                            <h2 class="fw-bold mb-0">{{ $jumlahRisalah ?? 0 }}</h2>
                                        </div>
                                        <div class="bg-white bg-opacity-25 rounded-3 p-4">
                                            <i class="fas fa-clipboard-list fa-2x"></i>
                                        </div>
                                    </div>
                                    <small class="text-white">
                                        <i class="fas fa-arrow-right me-1"></i> Lihat Detail
                                    </small>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Chart Aktivitas Dokumen (6 Bulan Terakhir) --}}
        {{-- <div class="card shadow-sm border-0 mb-4">
            <div class="card-body py-3">
                <h4 class="fw-bold mb-3">Aktivitas Dokumen (6 Bulan Terakhir)</h4>
                <div class="position-relative" style="height: 280px; width: 100%;">
                    <canvas id="chartAktivitas" style="width: 100%; height: 100%; display: block;"></canvas>
                </div>
            </div>
        </div> --}}

        {{-- Chart Distribusi Dokumen --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body py-3">
                <h4 class="fw-bold mb-3">Distribusi Dokumen</h4>
                <div class="position-relative" style="height: 280px; width: 100%;">
                    <canvas id="chartDistribusi" style="width: 100%; height: 100%; display: block;"></canvas>
                </div>
            </div>
        </div>

        {{-- Aktivitas Terbaru --}}
        <div class="card shadow-sm border-0">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="fw-bold mb-0">Aktivitas Terbaru</h4>
                    <span class="badge bg-primary">{{ $notifikasiByDate->flatten()->count() }} Notifikasi</span>
                </div>

                @if($notifikasiByDate->isEmpty())
                    <div class="text-center py-5">
                        <i class="fas fa-bell-slash fs-1 text-muted mb-3"></i>
                        <p class="text-muted">Belum ada aktivitas terbaru</p>
                    </div>
                @else
                    <div class="row gy-2">
                        @foreach ($notifikasiByDate as $tanggal => $list)
                            {{-- Header tanggal --}}
                            @if (count($notifikasiByDate) > 1)
                                <div class="col-12">
                                    <div class="d-flex align-items-center my-2">
                                        <h6 class="text-muted mb-0 me-2">{{ $tanggal }}</h6>
                                        <hr class="flex-grow-1 m-0">
                                    </div>
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
                                            case str_contains($judul, 'koreksi') || str_contains($judul, 'revisi'):
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
                                            case str_contains($judul, 'revisi') || str_contains($judul, 'koreksi'):
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
                                    <a href="{{ $notif->link ?? '#' }}" class="text-decoration-none">
                                        <div class="card border-start border-{{ $bgColor }} border-3 shadow-sm mb-2 hover-shadow-lg"
                                             style="transition: all 0.2s; cursor: pointer;">
                                            <div class="card-body p-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3 flex-shrink-0">
                                                        <div class="bg-{{ $bgColor }} bg-opacity-10 d-flex align-items-center justify-content-center"
                                                            style="width: 46px; height: 46px; border-radius: 12px;">
                                                            <i class="{{ $icon }} text-white" style="font-size: 20px;"></i>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="fw-bold mb-1 text-dark lh-sm">{{ $notif->judul }}</h6>
                                                        <p class="mb-0 text-muted small lh-sm">
                                                            <i class="far fa-clock me-1"></i>
                                                            {{ \Carbon\Carbon::parse($notif->updated_at)->locale('id')->translatedFormat('l, d F Y \p\u\k\u\l H.i') }}
                                                            @if($notif->judul_document)
                                                                • <span class="text-{{ $bgColor }} fw-semibold">{{ $notif->judul_document }}</span>
                                                            @endif
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Data dari controller untuk chart distribusi
    const memoKeluar = {{ $jumlahMemoKeluar ?? 0 }};
    const memoMasuk = {{ $jumlahMemoMasuk ?? 0 }};
    const undanganKeluar = {{ $jumlahUndanganKeluar ?? 0 }};
    const undanganMasuk = {{ $jumlahUndanganMasuk ?? 0 }};
    const risalah = {{ $jumlahRisalah ?? 0 }};

    // Data untuk chart aktivitas 6 bulan
    let chartLabels = [];
    let memoData = [];
    let undanganData = [];
    let risalahData = [];

    @if(isset($chartData) && is_array($chartData))
        const safeLabels = {!! json_encode($chartData['labels'] ?? []) !!};
        const safeMemo = {!! json_encode($chartData['memo'] ?? []) !!};
        const safeUndangan = {!! json_encode($chartData['undangan'] ?? []) !!};
        const safeRisalah = {!! json_encode($chartData['risalah'] ?? []) !!};

        if (Array.isArray(safeLabels)) chartLabels = safeLabels;
        if (Array.isArray(safeMemo)) memoData = safeMemo;
        if (Array.isArray(safeUndangan)) undanganData = safeUndangan;
        if (Array.isArray(safeRisalah)) risalahData = safeRisalah;
    @else
        // Dummy data untuk demo
        chartLabels = ['Agu', 'Sep', 'Okt', 'Nov', 'Des', 'Jan'];
        memoData = [12, 19, 15, 18, 22, 25];
        undanganData = [8, 14, 10, 16, 20, 18];
        risalahData = [5, 7, 6, 9, 11, 13];
    @endif

    // Chart Aktivitas Dokumen (6 Bulan Terakhir)
    const ctxAktivitas = document.getElementById('chartAktivitas');
    if (ctxAktivitas) {
        new Chart(ctxAktivitas, {
            type: 'bar',
            data: {
                labels: chartLabels,
                datasets: [
                    {
                        label: 'Memo',
                        data: memoData,
                        backgroundColor: 'rgba(102, 126, 234, 0.8)',
                        borderColor: 'rgb(102, 126, 234)',
                        borderWidth: 1,
                        borderRadius: 4
                    },
                    {
                        label: 'Undangan',
                        data: undanganData,
                        backgroundColor: 'rgba(67, 233, 123, 0.8)',
                        borderColor: 'rgb(67, 233, 123)',
                        borderWidth: 1,
                        borderRadius: 4
                    },
                    {
                        label: 'Risalah',
                        data: risalahData,
                        backgroundColor: 'rgba(250, 112, 154, 0.8)',
                        borderColor: 'rgb(250, 112, 154)',
                        borderWidth: 1,
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 15,
                            font: {
                                size: 12,
                                weight: '500'
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.85)',
                        padding: 12,
                        cornerRadius: 8,
                        titleFont: { size: 14, weight: 'bold' },
                        bodyFont: { size: 13 },
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y;
                            },
                            afterBody: function(tooltipItems) {
                                const total = tooltipItems.reduce((sum, item) => sum + item.parsed.y, 0);
                                return 'Total: ' + total;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        stacked: true,
                        grid: { display: false },
                        ticks: { font: { size: 11 } }
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        ticks: {
                            stepSize: 5,
                            font: { size: 12 }
                        },
                        grid: { color: 'rgba(0, 0, 0, 0.05)' }
                    }
                }
            }
        });
    }

    // Chart Distribusi Dokumen
    const ctxDistribusi = document.getElementById('chartDistribusi');
    if (ctxDistribusi) {
        new Chart(ctxDistribusi, {
            type: 'bar',
            data: {
                labels: ['Memo Keluar', 'Memo Masuk', 'Undangan Keluar', 'Undangan Masuk', 'Risalah Rapat'],
                datasets: [{
                    label: 'Jumlah Dokumen',
                    data: [memoKeluar, memoMasuk, undanganKeluar, undanganMasuk, risalah],
                    backgroundColor: [
                        'rgba(102, 126, 234, 0.8)',
                        'rgba(240, 147, 251, 0.8)',
                        'rgba(79, 172, 254, 0.8)',
                        'rgba(67, 233, 123, 0.8)',
                        'rgba(250, 112, 154, 0.8)'
                    ],
                    borderColor: [
                        'rgb(102, 126, 234)',
                        'rgb(240, 147, 251)',
                        'rgb(79, 172, 254)',
                        'rgb(67, 233, 123)',
                        'rgb(250, 112, 154)'
                    ],
                    borderWidth: 2,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.85)',
                        padding: 12,
                        cornerRadius: 8,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.parsed.y + ' dokumen';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            font: {
                                size: 12
                            },
                            callback: function(value) {
                                return Number.isInteger(value) ? value : '';
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                size: 11
                            }
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // Hover effect untuk kartu notifikasi
    const notifCards = document.querySelectorAll('.hover-shadow-lg');
    notifCards.forEach(function(card) {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '';
        });
    });

    // Hover effect untuk card statistik
    const statCards = document.querySelectorAll('.card-hover');
    statCards.forEach(function(card) {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-4px)';
            this.style.boxShadow = '0 8px 20px rgba(0,0,0,0.2)';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '';
        });
    });
});
</script>

<style>
.card-hover {
    transition: all 0.3s ease;
}
</style>
@endpush
