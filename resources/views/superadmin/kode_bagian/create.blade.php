@extends('layouts.app')

@section('title', 'Tambah Kode Bagian')

@section('content')
<div class="container-fluid">

    <h4 class="mb-4">
        <i class="fas fa-plus me-2"></i>
        Tambah Kode Bagian Kerja
    </h4>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('kode-bagian.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Kode Bagian</label>
                    <input type="text" name="kode_bagian"
                           class="form-control @error('kode_bagian') is-invalid @enderror"
                           value="{{ old('kode_bagian') }}"
                           placeholder="Contoh: SEC" required>
                    @error('kode_bagian')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama Bagian</label>
                    <input type="text" name="nama_bagian"
                           class="form-control @error('nama_bagian') is-invalid @enderror"
                           value="{{ old('nama_bagian') }}"
                           placeholder="Contoh: Sekretaris Perusahaan" required>
                    @error('nama_bagian')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Kategori</label>
                    <input type="text" name="kategori"
                           class="form-control"
                           value="{{ old('kategori') }}"
                           placeholder="Opsional">
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="is_active" class="form-select">
                        <option value="1" selected>Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="{{ route('kode-bagian.index') }}" class="btn btn-secondary me-2">
                        Batal
                    </a>
                    <button class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Simpan
                    </button>
                </div>

            </form>
        </div>
    </div>

</div>
@endsection
