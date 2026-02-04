@extends('layouts.app')

@section('title', 'Data Perusahaan')

@section('content')
    <div class="container-fluid">

        <!-- Breadcrumb -->
        <div class="page-header">
            <h4 class="page-title">Data Perusahaan</h4>
            <ul class="breadcrumbs">
                <li class="nav-home">
                    <a href="{{ url('dashboard') }}">
                        <i class="fas fa-home"></i>
                    </a>
                </li>
                <li class="separator">
                    <i class="fas fa-angle-right"></i>
                </li>
                <li class="nav-item">
                    <a href="javascript:void(0)">Pengaturan</a>
                </li>
                <li class="separator">
                    <i class="fas fa-angle-right"></i>
                </li>
                <li class="nav-item">
                    <a href="javascript:void(0)">Data Perusahaan</a>
                </li>
            </ul>
        </div>

        <div class="card shadow-sm p-4">
            <h3 class="mb-3"><b>Data Perusahaan</b></h3>
            <hr>
            <div class="row g-4">
                <!-- Kolom Form -->
                <div class="col-md-8 col-12">
                    <form id="formPerusahaan" action="{{ route('data-perusahaan.update') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Nama Instansi</label>
                            <input type="text" class="form-control" name="nama_instansi"
                                value="{{ $perusahaan->nama_instansi ?? '' }}" readonly required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Alamat Situs Web</label>
                            <input type="text" class="form-control" name="alamat_web"
                                value="{{ $perusahaan->alamat_web ?? '' }}" readonly required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Telepon</label>
                            <input type="text" class="form-control" name="telepon"
                                value="{{ $perusahaan->telepon ?? '' }}" readonly required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="{{ $perusahaan->email ?? '' }}"
                                readonly required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea class="form-control" name="alamat" rows="3" readonly required>{{ $perusahaan->alamat ?? '' }}</textarea>
                        </div>

                        @if (Auth::user()->role->nm_role == 'superadmin')
                            <div class="mb-3">
                                <label class="form-label">Logo Perusahaan</label>
                                <input type="file" class="form-control" name="logo" accept="image/*" disabled>
                            </div>

                            <!-- Tombol aksi -->
                            <div id="buttonGroup" class="d-flex gap-2">
                                <button type="button" class="btn btn-primary" id="editButton">Edit</button>
                                <button type="button" class="btn btn-secondary d-none" id="cancelButton">Batal</button>
                                <button type="submit" class="btn btn-success d-none" id="saveButton">Simpan</button>
                            </div>
                        @endif
                    </form>
                </div>

                <!-- Kolom Logo -->
                <div class="col-md-4 col-12 d-flex align-items-center justify-content-center">
                    <div class="border rounded p-3 text-center w-100"
                        style="min-height: 300px; display: flex; align-items: center; justify-content: center;">
                        @if (isset($perusahaan) && $perusahaan->logo)
                            <img src="data:image/png;base64,{{ $perusahaan->logo }}" alt="Logo Perusahaan" class="img-fluid"
                                style="max-height: 100%; max-width: 100%; object-fit: contain;">
                        @else
                            <p>Logo tidak tersedia.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
        <script>
            document.getElementById('editButton')?.addEventListener('click', function() {
                let inputs = document.querySelectorAll('#formPerusahaan input, #formPerusahaan textarea');
                inputs.forEach(input => input.removeAttribute('readonly'));
                document.querySelector('input[name="logo"]').removeAttribute('disabled');

                document.getElementById('editButton').classList.add('d-none');
                document.getElementById('cancelButton').classList.remove('d-none');
                document.getElementById('saveButton').classList.remove('d-none');
            });

            document.getElementById('cancelButton')?.addEventListener('click', function() {
                let inputs = document.querySelectorAll('#formPerusahaan input, #formPerusahaan textarea');
                inputs.forEach(input => input.setAttribute('readonly', true));
                document.querySelector('input[name="logo"]').setAttribute('disabled', true);

                document.getElementById('editButton').classList.remove('d-none');
                document.getElementById('cancelButton').classList.add('d-none');
                document.getElementById('saveButton').classList.add('d-none');
            });

            // Success flash messages
            document.addEventListener('DOMContentLoaded', function() {
                @if (session('success'))
                    showNotification('Data perusahaan berhasil diperbarui.', 'success');
                @endif
                @if (session('error'))
                    showNotification("Terjadi kesalahan saat memperbarui data perusahaan: {{ session('error') }}",
                        "error");
                @endif
            });
        </script>
        </script>
    @endpush
@endsection
