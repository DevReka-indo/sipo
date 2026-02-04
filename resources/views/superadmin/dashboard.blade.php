@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
    <div class="container-fluid px-4 py-0 mt-0">

        {{-- Header --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body py-3">
                <h3 class="fw-bold mb-2">Beranda</h3>
                <p class="mb-0">
                    Selamat datang
                    <strong>{{ auth()->user()->firstname . ' ' . auth()->user()->lastname ?? 'Pengguna' }}</strong>
                    di <a href="#" class="text-decoration-none fw-semibold">Sistem Persuratan</a>!
                    Anda login sebagai
                    <span
                        class="badge rounded-pill text-bg-warning text-dark">{{ ucfirst(auth()->user()->role->nm_role) }}</span>
                </p>
            </div>
        </div>

        {{-- Tinjauan Dokumen --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body py-3">
                <h4 class="fw-bold mb-3">Tinjauan Dokumen</h4>

                <div class="row g-3">
                    {{-- MEMO --}}
                    <div class="col-12 col-sm-6 col-lg-4">
                        <a href="{{ route('superadmin.memo.index') }}" class="text-decoration-none">
                            <div class="card border-0 shadow-sm h-100 card-hover"
                                style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); cursor: pointer;">
                                <div class="card-body text-white">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <p class="mb-1 text-white small">Total Memo</p>
                                            <h2 class="fw-bold mb-0">{{ $jumlahMemoKeluar ?? 0 }}</h2>
                                            <p class="mb-0 text-white small">Di seluruh sistem</p>
                                        </div>
                                        <div class="bg-white bg-opacity-25 rounded-3 p-3">
                                            <i class="fas fa-file-signature fs-3"></i>
                                        </div>
                                    </div>
                                    <small class="text-white">
                                        <i class="fas fa-arrow-right me-1"></i> Lihat Semua
                                    </small>
                                </div>
                            </div>
                        </a>
                    </div>

                    {{-- UNDANGAN --}}
                    <div class="col-12 col-sm-6 col-lg-4">
                        <a href="{{ route('superadmin.undangan.index') }}" class="text-decoration-none">
                            <div class="card border-0 shadow-sm h-100 card-hover"
                                style="background: linear-gradient(135deg, #43e97b 0%, #14ceac 100%); cursor: pointer;">
                                <div class="card-body text-white">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <p class="mb-1 text-white small">Total Undangan</p>
                                            <h2 class="fw-bold mb-0">{{ $jumlahUndanganKeluar ?? 0 }}</h2>
                                            <p class="mb-0 text-white small">Di seluruh sistem</p>
                                        </div>
                                        <div class="bg-white bg-opacity-25 rounded-3 p-3">
                                            <i class="fas fa-calendar-days fs-3"></i>
                                        </div>
                                    </div>
                                    <small class="text-white">
                                        <i class="fas fa-arrow-right me-1"></i> Lihat Semua
                                    </small>
                                </div>
                            </div>
                        </a>
                    </div>

                    {{-- RISALAH --}}
                    <div class="col-12 col-sm-6 col-lg-4">
                        <a href="{{ route('superadmin.risalah.index') }}" class="text-decoration-none">
                            <div class="card border-0 shadow-sm h-100 card-hover"
                                style="background: linear-gradient(135deg, #fa709a 0%, #cab640 100%); cursor: pointer;">
                                <div class="card-body text-white">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <p class="mb-1 text-white small">Total Risalah</p>
                                            <h2 class="fw-bold mb-0">{{ $jumlahRisalah ?? 0 }}</h2>
                                            <p class="mb-0 text-white small">Di seluruh sistem</p>
                                        </div>
                                        <div class="bg-white bg-opacity-25 rounded-3 p-3">
                                            <i class="fas fa-clipboard-list fs-3"></i>
                                        </div>
                                    </div>
                                    <small class="text-white">
                                        <i class="fas fa-arrow-right me-1"></i> Lihat Semua
                                    </small>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Chart Aktivitas Dokumen --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body py-3">
                <h4 class="fw-bold mb-3">Aktivitas Dokumen (6 Bulan Terakhir)</h4>
                <div class="position-relative" style="height: 280px; width: 100%;">
                    <canvas id="chartAktivitas" style="width: 100%; height: 100%; display: block;"></canvas>
                </div>
            </div>
        </div>

        {{-- Menu Aktivitas Cepat --}}
        <div class="card shadow-sm border-0">
            <div class="card-body py-3">
                <h4 class="fw-bold mb-3">Akses Cepat</h4>

                <div class="row g-3">
                    {{-- Histori Memo --}}
                    <div class="col-12 col-md-4">
                        <a href="{{ route('superadmin.memo.index') }}" class="text-decoration-none">
                            <div class="card border-0 shadow-sm h-100 menu-card-hover"
                                style="border-left: 4px solid #667eea !important;">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3 flex-shrink-0">
                                            <div class="bg-primary bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center"
                                                style="width: 50px; height: 50px;">
                                                <i class="fas fa-folder text-white fs-4"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="fw-bold mb-1 text-dark">Histori Memo</h6>
                                            <p class="mb-0 text-muted small">Kelola seluruh memo sistem</p>
                                        </div>
                                        <div>
                                            <i class="fas fa-chevron-right text-muted"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    {{-- Kelola User --}}
                    <div class="col-12 col-md-4">
                        <a href="{{ route('user.manage') }}" class="text-decoration-none">
                            <div class="card border-0 shadow-sm h-100 menu-card-hover"
                                style="border-left: 4px solid #43e97b !important;">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3 flex-shrink-0">
                                            <div class="bg-success bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center"
                                                style="width: 50px; height: 50px;">
                                                <i class="fas fa-user-plus text-white fs-4"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="fw-bold mb-1 text-dark">Kelola User</h6>
                                            <p class="mb-0 text-muted small">Tambah & edit pengguna</p>
                                        </div>
                                        <div>
                                            <i class="fas fa-chevron-right text-muted"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    {{-- Histori Permintaan Surat --}}
                    <div class="col-12 col-md-4">
                        <a href="{{ route('laporan-memo.superadmin') }}" class="text-decoration-none">
                            <div class="card border-0 shadow-sm h-100 menu-card-hover"
                                style="border-left: 4px solid #fa709a !important;">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3 flex-shrink-0">
                                            <div class="bg-danger bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center"
                                                style="width: 50px; height: 50px;">
                                                <i class="fas fa-file-pen text-white fs-4"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="fw-bold mb-1 text-dark">Permintaan Surat</h6>
                                            <p class="mb-0 text-muted small">Riwayat permintaan surat</p>
                                        </div>
                                        <div>
                                            <i class="fas fa-chevron-right text-muted"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let chartLabels = [];
            let memoData = [];
            let undanganData = [];
            let risalahData = [];

            @if (isset($chartData) && is_array($chartData))
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
                chartLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'];
                memoData = [12, 19, 15, 18, 22, 25];
                undanganData = [8, 14, 10, 16, 20, 18];
                risalahData = [5, 7, 6, 9, 11, 13];
            @endif

            const ctx = document.getElementById('chartAktivitas');
            if (ctx) {
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: chartLabels,
                        datasets: [{
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
                                titleFont: {
                                    size: 14,
                                    weight: 'bold'
                                },
                                bodyFont: {
                                    size: 13
                                },
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ' + context.parsed.y;
                                    },
                                    afterBody: function(tooltipItems) {
                                        const total = tooltipItems.reduce((sum, item) => sum + item
                                            .parsed.y, 0);
                                        return 'Total: ' + total;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                stacked: true,
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: {
                                        size: 11
                                    }
                                }
                            },
                            y: {
                                stacked: true,
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 5,
                                    font: {
                                        size: 12
                                    }
                                },
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            }
                        }
                    }
                });
            }

            // Hover effects
            document.querySelectorAll('.card-hover').forEach(card => {
                card.addEventListener('mouseenter', () => {
                    card.style.transform = 'translateY(-4px)';
                    card.style.boxShadow = '0 8px 20px rgba(0,0,0,0.2)';
                });
                card.addEventListener('mouseleave', () => {
                    card.style.transform = 'translateY(0)';
                    card.style.boxShadow = '';
                });
            });

            document.querySelectorAll('.menu-card-hover').forEach(card => {
                card.addEventListener('mouseenter', () => {
                    card.style.transform = 'translateX(4px)';
                    card.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
                });
                card.addEventListener('mouseleave', () => {
                    card.style.transform = 'translateX(0)';
                    card.style.boxShadow = '';
                });
            });
        });
    </script>

    <style>
        .card-hover,
        .menu-card-hover {
            transition: all 0.3s ease;
        }
    </style>
@endpush
