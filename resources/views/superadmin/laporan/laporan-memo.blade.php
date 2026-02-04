@extends('layouts.app')

@section('title', 'Laporan Memo')

@section('content')
    <div class="container-fluid px-4 py-0 mt-0">
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-body py-3">

                {{-- Judul --}}
                <h3 class="fw-bold mb-3">Laporan Memo</h3>

                {{-- Breadcrumb --}}
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="bg-white border rounded-2 px-3 py-2 w-100 d-flex align-items-center">
                            <a href="{{ auth()->user()->level == 'admin' ? route('admin.dashboard') : route('superadmin.dashboard') }}"
                                class="text-decoration-none text-primary">
                                Beranda
                            </a>
                            <span class="mx-2 text-muted">/</span>
                            <span class="text-muted">Laporan Memo</span>
                        </div>
                    </div>
                </div>

                {{-- Form Filter --}}
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body">
                        <form action="{{ route('cetak-laporan-memo.superadmin') }}" method="POST" id="filter-form">
                            @csrf
                            <div class="row">
                                {{-- Tanggal Awal --}}
                                <div class="col-md-6 mb-3">
                                    <div class="card border-0 shadow-sm rounded-3">
                                        <div class="card-header py-2 rounded-top-3"
                                            style="background:#e3f2fd;border-bottom:1px solid #bbdefb;">
                                            <i class="fa fa-calendar me-2 text-primary"></i>
                                            <span class="fw-semibold">Tanggal Awal</span>
                                        </div>
                                        <div class="card-body">
                                            <input type="date" name="tgl_awal" id="tgl_awal" class="form-control"
                                                required>
                                            <small class="text-danger">* Masukkan tanggal awal filter data memo!</small>
                                        </div>
                                    </div>
                                </div>

                                {{-- Tanggal Akhir --}}
                                <div class="col-md-6 mb-3">
                                    <div class="card border-0 shadow-sm rounded-3">
                                        <div class="card-header py-2 rounded-top-3"
                                            style="background:#e3f2fd;border-bottom:1px solid #bbdefb;">
                                            <i class="fa fa-calendar me-2 text-primary"></i>
                                            <span class="fw-semibold">Tanggal Akhir</span>
                                        </div>
                                        <div class="card-body">
                                            <input type="date" name="tgl_akhir" id="tgl_akhir" class="form-control"
                                                required>
                                            <small class="text-danger">* Masukkan tanggal akhir filter data memo!</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Tombol Aksi --}}
                            <div class="d-flex justify-content-end mt-3">
                                <button class="btn btn-cancel me-2" id="cancel-button">Reset</button>
                                <button type="submit" class="btn btn-filter" id="filter-button">Filter</button>
                            </div>

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handler untuk tombol Batal
            document.getElementById('cancel-button').addEventListener('click', function() {
                // Reset nilai input tanggal
                document.getElementById('tgl_awal').value = '';
                document.getElementById('tgl_akhir').value = '';

                // Optional: Reset form validation state
                const form = document.getElementById('filter-form');
                form.classList.remove('was-validated');

                // Optional: Remove any error messages if using Bootstrap validation
                const invalidFeedbacks = form.querySelectorAll('.invalid-feedback');
                invalidFeedbacks.forEach(feedback => feedback.style.display = 'none');

                // Optional: Reset input classes
                const inputs = form.querySelectorAll('.form-control');
                inputs.forEach(input => {
                    input.classList.remove('is-invalid', 'is-valid');
                });

                console.log('Filter tanggal telah direset');
            });

            // Optional: Tambahkan validasi saat submit
            document.getElementById('filter-form').addEventListener('submit', function(e) {
                const tglAwal = document.getElementById('tgl_awal').value;
                const tglAkhir = document.getElementById('tgl_akhir').value;

                if (tglAwal && tglAkhir && tglAwal > tglAkhir) {
                    e.preventDefault();
                    alert('Tanggal awal tidak boleh lebih besar dari tanggal akhir!');
                    return false;
                }
            });
        });
    </script>
@endsection
