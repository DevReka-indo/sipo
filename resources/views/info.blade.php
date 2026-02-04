@extends('layouts.app')

@section('title', 'Informasi SIPO')

@section('content')
    <style>
        /* ===== HERO SECTION ===== */
        .hero {
            position: relative;
            width: 100%;
            border-radius: 12px;
            overflow: hidden;
            background: #1a1a1a;
            /* warna solid background */
            padding: 40px 30px;
            /* ruang dalam */
            color: #fff;
        }

        .hero h5 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .hero p {
            font-size: 16px;
            line-height: 1.6;
            margin: 0;
            max-width: 1100px;
            /* batasi lebar teks di layar besar */
        }

        /* ===== Responsive Mobile ===== */
        @media (max-width: 991.98px) {
            .hero {
                padding: 20px 15px;
                text-align: left;
            }

            .hero h5 {
                font-size: 18px;
                margin-bottom: 8px;
            }

            .hero p {
                font-size: 14px;
                line-height: 1.45;
                max-width: 100%;
                word-break: break-word;
            }
        }

        /* Logo */
        .reka-info img {
            max-width: 260px;
            width: 100%;
            height: auto;
        }
    </style>

    <div class="info">
        <div class="row">
            <div class="col-12"><!-- full width dalam card -->
                <div class="card">

                    {{-- Header --}}
                    <h3 class="fw-bold mb-3">Info</h3>

                    {{-- Breadcrumb --}}
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="bg-white border rounded-2 px-3 py-2 w-100 d-flex align-items-center">
                                <a href="{{ route(Auth::user()->role->nm_role . '.dashboard') }}"
                                    class="text-decoration-none text-primary">
                                    Beranda
                                </a>
                                <span class="mx-2 text-muted">/</span>
                                <span class="text-muted">Info</span>
                            </div>
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="card-body text-center">
                        <!-- Hero dengan background solid -->
                        <div class="hero mb-4 text-start">
                            <h5>Tentang Sistem</h5>
                            <p>
                                Sistem manajemen persuratan ini dirancang untuk memudahkan pengelolaan Memo, Undangan Rapat,
                                dan Risalah Rapat di dalam ruang lingkup PT Rekaindo Global Jasa. Sistem ini memungkinkan
                                pembuatan, pengeditan, persetujuan, dan pengarsipan dokumen secara efisien.
                            </p>
                        </div>

                        <!-- Logo -->
                        <div>
                            <img src="/assets/img/logo-reka.png" alt="Info">
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
