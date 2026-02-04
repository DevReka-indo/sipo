@extends('layouts.app')

@section('title', 'Kelola Kode Bagian Kerja')

@section('content')
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">
                <i class="fas fa-building me-2"></i>
                Kelola Kode Bagian Kerja
            </h4>

            <a href="{{ route('kode-bagian.create') }}" class="btn btn-success">
                <i class="fas fa-plus me-1"></i>
                Tambah Kode Bagian
            </a>
        </div>


        {{-- 🔍 FILTER --}}
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" class="row g-3">

                    <div class="col-md-4">
                        <label class="form-label">Kategori</label>
                        <select name="kategori" class="form-select">
                            <option value="">-- Semua Kategori --</option>
                            @foreach ($kategoriList as $kategori)
                                <option value="{{ $kategori }}" {{ $filterKategori == $kategori ? 'selected' : '' }}>
                                    {{ $kategori }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">-- Semua Status --</option>
                            <option value="1" {{ $filterStatus === '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ $filterStatus === '0' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>

                    <div class="col-md-4 d-flex align-items-end">
                        <button class="btn btn-primary me-2">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                        <a href="{{ route('kode-bagian.index') }}" class="btn btn-secondary">
                            Reset
                        </a>
                    </div>

                </form>
            </div>
        </div>

        {{-- 📊 TABLE --}}
        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th width="60">No</th>
                            <th>Kode Bagian</th>
                            <th>Nama Bagian</th>
                            <th>Kategori</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data as $i => $row)
                            <tr>
                                <td>{{ $data->firstItem() + $i }}</td>
                                <td class="fw-bold">{{ $row->kode_bagian }}</td>
                                <td>{{ $row->nama_bagian }}</td>
                                <td>{{ $row->kategori ?? '-' }}</td>
                                <td>
                                    @if ($row->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($row->trashed())
                                        {{-- ♻️ RECOVERY --}}
                                        <form action="{{ route('kode-bagian.restore', $row->id) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-success" title="Pulihkan">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        </form>
                                    @else
                                        {{-- ✏️ EDIT --}}
                                        <a href="{{ route('kode-bagian.edit', $row->id) }}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        {{-- 🗑 DELETE --}}
                                        <form action="{{ route('kode-bagian.destroy', $row->id) }}" method="POST"
                                            class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    Belum ada data kode bagian kerja
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- 🔢 PAGINATION --}}
                <div class="mt-3 d-flex justify-content-end">
                    {{ $data->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>

    </div>
@endsection
