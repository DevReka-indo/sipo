@extends('layouts.app')

@section('title', 'Edit Kode Bagian')

@section('content')
<div class="container-fluid">

    <h4 class="mb-4">
        <i class="fas fa-edit me-2"></i>
        Edit Kode Bagian Kerja
    </h4>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('kode-bagian.update', $bagian->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Kode Bagian</label>
                    <input type="text" name="kode_bagian"
                           class="form-control"
                           value="{{ old('kode_bagian', $bagian->kode_bagian) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama Bagian</label>
                    <input type="text" name="nama_bagian"
                           class="form-control"
                           value="{{ old('nama_bagian', $bagian->nama_bagian) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Kategori</label>
                    <input type="text" name="kategori"
                           class="form-control"
                           value="{{ old('kategori', $bagian->kategori) }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="is_active" class="form-select">
                        <option value="1" {{ $bagian->is_active ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ !$bagian->is_active ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="{{ route('kode-bagian.index') }}" class="btn btn-secondary me-2">
                        Batal
                    </a>
                    <button class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Update
                    </button>
                </div>

            </form>
        </div>
    </div>

</div>
@endsection
