@extends('layouts.app')

@section('title', 'Risalah')

@section('content')
    <div class="container-fluid px-4 py-0 mt-0">
        <div class="card shadow-sm border-0">
            <div class="card-body py-3">

                <h3 class="fw-bold mb-3">Risalah</h3>

                {{-- Breadcrumb --}}
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="bg-white border rounded-2 px-3 py-2 w-100 d-flex align-items-center">
                            <a href="{{ route('superadmin.dashboard') }}"
                                class="text-decoration-none text-primary">Beranda</a>
                            <span class="text-muted ms-1">/ Risalah</span>
                        </div>
                    </div>
                </div>

                @php
                    // Normalisasi nilai agar placeholder muncul bila tidak valid/kosong
                    $allowedStatus = ['pending', 'approve', 'reject', 'correction'];
                    $status = old('status', request('status'));
                    if (!in_array($status, $allowedStatus, true)) {
                        $status = null;
                    }

                    $allowedKode = array_map('strval', $kode->toArray());
                    $selectedKode = (string) old('kode', request('kode'));
                    if (!in_array($selectedKode, $allowedKode, true)) {
                        $selectedKode = null;
                    }
                @endphp

                {{-- Row Filter --}}
                <form class="row g-2 align-items-center" method="GET" action="{{ route('superadmin.risalah.index') }}">
                    <div class="col-auto">
                        <select name="per_page" class="form-select rounded-3" style="max-width:100px;"
                            onchange="this.form.submit()">
                            <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>

                    {{-- Status --}}
                    <div class="col-12 col-md-auto">
                        <select class="form-select rounded-3" name="status" aria-label="Status">
                            <option value="" @selected(is_null($status)) disabled>Status</option>
                            <option value="pending" @selected($status === 'pending')>Diproses</option>
                            <option value="approve" @selected($status === 'approve')>Diterima</option>
                            <option value="reject" @selected($status === 'reject')>Ditolak</option>
                            <option value="correction" @selected($status === 'correction')>Dikoreksi</option>
                        </select>
                    </div>

                    {{-- Tanggal Awal --}}
                    <div class="col-12 col-md-auto">
                        <input type="date" class="form-control rounded-3" name="tgl_dibuat_awal"
                            value="{{ request('tgl_dibuat_awal') }}" placeholder="Tanggal Awal" aria-label="Tanggal Awal">
                    </div>

                    {{-- Separator panah (hidden di mobile) --}}
                    <div class="col-auto d-none d-md-flex align-items-center">
                        <span class="mx-1">→</span>
                    </div>

                    {{-- Tanggal Akhir --}}
                    <div class="col-12 col-md-auto">
                        <input type="date" class="form-control rounded-3" name="tgl_dibuat_akhir"
                            value="{{ request('tgl_dibuat_akhir') }}" placeholder="Tanggal Akhir"
                            aria-label="Tanggal Akhir">
                    </div>

                    {{-- Pencarian --}}
                    <div class="col-12 col-md">
                        <div class="input-group">
                            <span class="input-group-text rounded-start-3"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control rounded-end-3" name="search"
                                value="{{ request('search') }}" placeholder="Cari judul atau nomor risalah"
                                aria-label="Cari">
                        </div>
                    </div>

                    {{-- Kode --}}
                    <div class="col-12 col-md-auto">
                        <select class="form-select rounded-3" name="kode" aria-label="Pilih Kode">
                            <option value="" @selected(is_null($selectedKode))>Pilih Kode</option>
                            @foreach ($kode as $k)
                                <option value="{{ $k }}" @selected($selectedKode === $k)>{{ $k }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Divisi --}}
                    {{-- <div class="col-12 col-md-auto">
          <select class="form-select rounded-3" name="divisi_id_divisi" aria-label="Pilih Divisi">
            <option value="" @selected(is_null($selectedDivisi)) disabled>Pilih Divisi</option>
            @foreach ($divisi as $d)
              <option value="{{ $d->id_divisi }}" @selected($selectedDivisi == $d->id_divisi)>{{ $d->nm_divisi }}</option>
            @endforeach
          </select>
        </div> --}}

                    {{-- Tombol Filter --}}
                    <div class="col-12 col-md-auto">
                        <button type="submit" class="btn btn-primary rounded-3">
                            <i class="fas fa-filter me-1"></i>Filter
                        </button>
                    </div>
                </form>

                {{-- Tabel --}}
                <div class="table-responsive mt-3">
                    <table class="table table-bordered custom-table-bagian">
                        <thead>
                            <tr>
                                <th class="text-center" style="width:5%;">No</th>
                                <th class="text-center" style="width:20%;">Perihal</th>
                                <th class="text-center" style="width:12%;">Tanggal Dibuat</th>
                                {{-- <th class="text-center" style="width:8%;">Seri</th> --}}
                                <th class="text-center" style="width:15%;">Nomor Risalah</th>
                                <th class="text-center" style="width:12%;">Agenda</th>
                                <th class="text-center" style="width:10%;">Divisi</th>
                                <th class="text-center" style="width:8%;">Status</th>
                                <th class="text-center" style="width:10%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($risalahs as $index => $risalah)
                                <tr>
                                    <td class="nomor">{{ ($risalahs->firstItem() ?? 0) + $index }}</td>
                                    <td class="nama-dokumen
                                {{ $risalah->status == 'reject' ? 'text-danger' : ($risalah->status == 'correction' ? 'text-warning' : ($risalah->status == 'approve' ? 'text-success' : '')) }}"
                                        style="{{ $risalah->status == 'pending' ? 'color: #0dcaf0;' : '' }}">
                                        {{ $risalah->judul ?? '-' }}
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($risalah->tgl_dibuat)->format('d-m-Y') ?? '-' }}</td>
                                    {{-- <td>{{ $risalah->seri_surat ?? '-' }}</td> --}}
                                    <td>{{ $risalah->nomor_risalah ?? '-' }}</td>
                                    <td>{{ $risalah->agenda ?? '-' }}</td>
                                    <td>{{ $risalah->kode ?? '-' }}</td>
                                    <td class="text-center">
                                        @if ($risalah->status == 'reject')
                                            <span class="badge bg-danger px-3 py-2">Ditolak</span>
                                        @elseif ($risalah->status == 'pending')
                                            <span class="badge bg-info px-3 py-2">Diproses</span>
                                        @elseif ($risalah->status == 'correction')
                                            <span class="badge bg-warning px-3 py-2">Dikoreksi</span>
                                        @else
                                            <span class="badge bg-success px-3 py-2">Diterima</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            @if ($risalah->status == 'pending' || $risalah->status == 'correction')
                                                <button type="button"
                                                    class="btn btn-sm rounded-circle text-white border-0 bg-secondary"
                                                    style="width:30px; height:30px; display:flex; align-items:center; justify-content:center;"
                                                    onclick="window.location.href='{{ route('risalah.edit', ['id' => $risalah->id_risalah]) }}'">
                                                    <i class="fa-solid fa-pencil fa-lg"></i>
                                                </button>
                                            @else
                                                <button type="button"
                                                    class="btn btn-sm rounded-circle text-white border-0"
                                                    style="background-color:#FFAD46; width:30px; height:30px; display:flex; align-items:center; justify-content:center;"
                                                    onclick="showArsipConfirmation({{ $risalah->id_risalah }}, '{{ $risalah->judul ?? $risalah->nama_dokumen }}')"
                                                    title="Arsip">
                                                    <i class="fa-solid fa-archive fa-lg"></i>
                                                </button>
                                            @endif

                                            <button type="button" class="btn btn-sm rounded-circle text-white border-0"
                                                style="background-color:#F25961; width:30px; height:30px; display:flex; align-items:center; justify-content:center;"
                                                onclick="showDeleteConfirmation({{ $risalah->id_risalah }}, '{{ $risalah->judul ?? $risalah->nama_dokumen }}')"
                                                title="Hapus">
                                                <i class="fa-solid fa-trash fa-lg"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div class="d-flex justify-content-end mt-3">
                    {{ $risalahs->onEachSide(1)->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>




@endsection

@push('scripts')
    <script>
        let currentRisalahId = null;

        // ---------- Arsip ----------
        function showArsipConfirmation(risalahId, risalahTitle) {
            currentRisalahId = risalahId;
            Swal.fire({
                title: 'Arsip Risalah?',
                html: `Apakah Anda yakin ingin mengarsipkan risalah <strong>${risalahTitle}</strong>?<br><br>
               <span class="text-muted">Risalah yang diarsipkan akan dipindahkan ke arsip dan tidak akan muncul di daftar utama.</span>`,
                icon: 'question',
                showCancelButton: true,
                reverseButtons: true,
                confirmButtonText: '<i class="fa-solid fa-archive me-1"></i>Arsip',
                cancelButtonText: 'Batal',
                customClass: {
                    actions: 'swal2-actions swal2-actions-custom',
                    confirmButton: 'btn btn-warning px-4 py-2',
                    cancelButton: 'btn btn-secondary px-4 py-2'
                },
                buttonsStyling: false
            }).then(result => {
                if (result.isConfirmed) confirmArsip();
            });
        }

        function confirmArsip() {
            if (!currentRisalahId) return;

            Swal.fire({
                title: 'Mengarsipkan...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            fetch(`/arsip/${currentRisalahId}/Risalah/simpan`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: 'Risalah berhasil diarsipkan',
                            icon: 'success',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#31CE36'
                        }).then(() => window.location.reload());
                    } else {
                        showNotification('Gagal mengarsipkan risalah', 'error');
                    }
                })
                .catch(() => showNotification('Terjadi kesalahan', 'error'));
        }

        // ---------- Delete ----------
        function showDeleteConfirmation(risalahId, risalahTitle) {
            currentRisalahId = risalahId;
            Swal.fire({
                title: 'Hapus Risalah?',
                html: `Apakah Anda yakin ingin menghapus risalah <strong>${risalahTitle}</strong>?<br><br>
               <span class="text-danger"><i class="fa-solid fa-exclamation-triangle me-1"></i>
               Tindakan ini tidak dapat dibatalkan!</span>`,
                icon: 'warning',
                showCancelButton: true,
                reverseButtons: true,
                confirmButtonText: '<i class="fa-solid fa-trash me-1"></i>Hapus',
                cancelButtonText: 'Batal',
                customClass: {
                    actions: 'swal2-actions swal2-actions-custom',
                    confirmButton: 'btn btn-danger px-4 py-2',
                    cancelButton: 'btn btn-warning px-4 py-2'
                },
                buttonsStyling: false
            }).then(result => {
                if (result.isConfirmed) confirmDelete();
            });
        }

        function confirmDelete() {
            if (!currentRisalahId) return;

            Swal.fire({
                title: 'Menghapus...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            fetch(`/risalah/delete/${currentRisalahId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: data.message || 'Risalah berhasil dihapus',
                            icon: 'success',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#31CE36'
                        }).then(() => window.location.reload());
                    } else {
                        showNotification('Gagal menghapus risalah', 'error');
                    }
                })
                .catch(() => showNotification('Terjadi kesalahan', 'error'));
        }

        // ---------- Restore ----------
        function showRestoreModal(risalahId, risalahTitle) {
            currentRisalahId = risalahId;
            document.getElementById('restoreRisalahTitle').textContent = risalahTitle;
            new bootstrap.Modal(document.getElementById('restoreModal')).show();
        }

        function confirmRestore() {
            if (!currentRisalahId) return;

            fetch(`/risalah/restore/${currentRisalahId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    }
                })
                .then(res => res.json())
                .then(data => {
                    bootstrap.Modal.getInstance(document.getElementById('restoreModal')).hide();
                    if (data.success) {
                        showNotification('Risalah berhasil dipulihkan', 'success');
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        showNotification('Gagal memulihkan risalah', 'error');
                    }
                })
                .catch(() => showNotification('Terjadi kesalahan', 'error'));
        }

        // ---------- Utils ----------
        function showNotification(message, type) {
            Swal.fire({
                title: type === 'success' ? 'Berhasil!' : 'Error!',
                text: message,
                icon: type,
                confirmButtonText: 'OK',
                confirmButtonColor: type === 'success' ? '#28a745' : '#d33'
            });
        }

        // ---------- Flash Message ----------
        document.addEventListener("DOMContentLoaded", function() {
            @if (session('success'))
                showNotification('{{ session('success') }}', 'success');
            @endif

            @if (session('error'))
                showNotification('{{ session('error') }}', 'error');
            @endif
        });
    </script>
@endpush
