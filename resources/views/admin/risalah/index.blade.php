@extends('layouts.app')

@section('title', 'Risalah Rapat')

@section('content')
    <div class="container-fluid px-4 py-0 mt-0">
        <div class="card shadow-sm border-0">
            <div class="card-body py-3">

                <h3 class="fw-bold mb-3">Risalah</h3>

                {{-- Breadcrumb --}}
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="bg-white border rounded-2 px-3 py-2 w-100 d-flex align-items-center">
                            <a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-primary">Beranda</a>
                            <span class="text-muted ms-1">/ Risalah</span>
                        </div>
                    </div>
                </div>

                {{-- Row Filter --}}
                <form class="row g-2 align-items-center" method="GET" action="{{ route('admin.risalah.index') }}">
                    <div class="col-auto">
                        <select name="per_page" class="form-select rounded-3" style="max-width:100px;"
                            onchange="this.form.submit()">
                            <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-auto">
                        <select class="form-select rounded-3" name="status" aria-label="Status">
                            <option value="">Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Diproses</option>
                            <option value="correction" {{ request('status') == 'correction' ? 'selected' : '' }}>Dikoreksi
                            </option>
                            <option value="approve" {{ request('status') == 'approve' ? 'selected' : '' }}>Diterima</option>
                            <option value="reject" {{ request('status') == 'reject' ? 'selected' : '' }}>Ditolak</option>
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
                                value="{{ request('search') }}" placeholder="Cari" aria-label="Cari">
                        </div>
                    </div>

                    {{-- Divisi --}}
                    {{-- <div class="col-12 col-md-auto">
                        <select class="form-select rounded-3" name="kode" id="kode" aria-label="Pilih Divisi">
                            <option value="">Semua Divisi</option>
                            @if (isset($kode) && is_object($kode) && $kode->count() > 0)
                                @foreach ($kode as $k)
                                    <option value="{{ $k }}" {{ request('kode') == $k ? 'selected' : '' }}>
                                        {{ $k }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div> --}}

                    {{-- Tombol Filter --}}
                    <div class="col-12 col-md-auto">
                        <button type="submit" class="btn btn-primary rounded-3">
                            <i class="fas fa-filter me-1"></i>Filter
                        </button>
                        <button type="button" class="btn rounded-3 text-white"
                            style="background-color:#1E4178; border-color:#1E4178;" onclick=showCreateModal()>
                            <i class="fas fa-plus me-1"></i>Tambah Risalah
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
                                <th class="text-center" style="width:12%;">Tanggal Masuk</th>
                                {{-- <th class="text-center" style="width:2%;">Seri</th> --}}
                                <th class="text-center" style="width:20%;">Dokumen</th>
                                <th class="text-center" style="width:12%;">Tanggal Disahkan</th>
                                <th class="text-center" style="width:10%;">Divisi</th>
                                <th class="text-center" style="width:10%;">Tipe</th>
                                <th class="text-center" style="width:8%;">Status</th>
                                <th class="text-center" style="width:8%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($risalahs->isEmpty())
                                <tr>
                                    <td colspan="8" class="text-center">Belum ada risalah.</td>
                                </tr>
                            @else
                                @forelse($risalahs as $index => $risalah)
                                    <tr>
                                        <td class="nomor">{{ ($risalahs->firstItem() ?? 0) + $index }}</td>
                                        <td class="nama-dokumen
                                {{ $risalah->status == 'reject' ? 'text-danger' : ($risalah->status == 'correction' ? 'text-warning' : ($risalah->status == 'approve' ? 'text-success' : '')) }}"
                                            style="{{ $risalah->status == 'pending' ? 'color: #0dcaf0;' : '' }}">
                                            {{ Str::limit($risalah->judul ?? '-', 35, '...') }}
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($risalah->tgl_dibuat)->format('d-m-Y') }}</td>
                                        {{-- <td>{{ $risalah->seri_surat }}</td> --}}
                                        <td>{{ $risalah->nomor_risalah }}</td>
                                        <td>{{ $risalah->tgl_disahkan ? \Carbon\Carbon::parse($risalah->tgl_disahkan)->format('d-m-Y') : '-' }}
                                        </td>
                                        <td class="text-center">
                                            {{ $risalah->user->department->kode_department ?? ($risalah->user->divisi->kode_divisi ?? '-') }}
                                        </td>
                                        <td>
                                            @if ($risalah->with_undangan)
                                                <span class="badge px-3 py-2" style="background-color:#35A29F;">
                                                    Dengan Undangan
                                                </span>
                                            @else
                                                <span class="badge px-3 py-2" style="background-color:#3048d0;">
                                                    Tanpa Undangan
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($risalah->status == 'reject')
                                                <span class="badge bg-danger">Ditolak</span>
                                            @elseif ($risalah->status == 'pending')
                                                <span class="badge bg-info">Diproses</span>
                                            @elseif ($risalah->status == 'correction')
                                                <span class="badge bg-warning">Dikoreksi</span>
                                            @else
                                                <span class="badge bg-success">Diterima</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                <button title="Detail"
                                                    class="btn btn-sm rounded-circle text-white border-0 bg-info"
                                                    style="width:30px; height:30px; display:flex; align-items:center; justify-content:center;"
                                                    onclick="window.location.href='{{ route('view.risalahAdmin', ['id' => $risalah->id_risalah]) }}'">
                                                    <i class="fas fa-eye" alt="Detail"></i>
                                                </button>
                                                @if ($risalah->status == 'pending' || $risalah->status == 'correction')
                                                    @if (Auth::user()->fullname == $risalah->nama_notulis_acara)
                                                        <button type="button"
                                                            class="btn btn-sm rounded-circle text-white border-0 bg-secondary"
                                                            style="width:30px; height:30px; display:flex; align-items:center; justify-content:center;"
                                                            onclick="window.location.href='{{ route('risalah.edit', ['id' => $risalah->id_risalah]) }}'">
                                                            <i class="fa-solid fa-pencil fa-lg"></i>
                                                        </button>
                                                    @endif
                                                @elseif ($risalah->status == 'approve' || $risalah->status == 'reject')
                                                    {{-- Button Arsip untuk status approve reject --}}
                                                    <button type="button"
                                                        class="btn btn-sm rounded-circle text-white border-0"
                                                        style="background-color:#FFAD46; width:30px; height:30px; display:flex; align-items:center; justify-content:center;"
                                                        onclick="showArsipConfirmation({{ $risalah->id_risalah }}, '{{ $risalah->judul ?? $risalah->nama_dokumen }}')"
                                                        title="Arsip">
                                                        <i class="fa-solid fa-archive fa-lg"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">Tidak ada data</td>
                                    </tr>
                                @endforelse
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-end mt-3">
                    {{ $risalahs->onEachSide(1)->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Restore --}}
    <div class="modal fade" id="restoreModal" tabindex="-1" aria-labelledby="restoreModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="restoreModalLabel">Pulihkan Risalah</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin memulihkan risalah <strong id="restoreRisalahTitle"></strong>?</p>
                    <p class="text-muted">Risalah yang dipulihkan akan dikembalikan ke daftar utama.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" onclick="confirmRestore()">
                        <i class="fa-solid fa-rotate-left me-1"></i>Pulihkan
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Delete Permanen --}}
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Hapus Permanen Risalah</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus permanen risalah <strong id="deleteRisalahTitle"></strong>?</p>
                    <p class="text-danger"><i class="fa-solid fa-exclamation-triangle me-1"></i>Tindakan ini tidak dapat
                        dibatalkan dan data akan hilang selamanya!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                        <i class="fa-solid fa-trash me-1"></i>Hapus Permanen
                    </button>
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

        // Function untuk menampilkan notifikasi
        function showNotification(message, type) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: type === 'success' ? 'Berhasil!' : 'Gagal',
                    text: message,
                    icon: type,
                    showConfirmButton: true, // tombol OK muncul
                    confirmButtonText: 'OK',
                    confirmButtonColor: type === 'success' ? '#28a745' : '#d33'
                }).then((result) => {
                    if (result.isConfirmed && type === 'success') {
                        // kalau sukses dan user klik OK → balik ke index
                        window.location.href = "{{ route('admin.risalah.index') }}";
                    }
                });
            } else {
                alert(message);
                // fallback redirect
                if (type === 'success') {
                    window.location.href = "{{ route('admin.risalah.index') }}";
                }
            }
        }

        // Select all functionality
        document.getElementById('selectAll')?.addEventListener('change', function() {
            document.querySelectorAll('.selectItem').forEach(cb => cb.checked = this.checked);
            toggleBulkBar();
        });

        function toggleBulkBar() {
            const anyChecked = Array.from(document.querySelectorAll('.selectItem')).some(cb => cb.checked);
            document.getElementById('bulkActions').style.display = anyChecked ? 'flex' : 'none';
        }

        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('selectItem')) toggleBulkBar();
        });

        function showRestoreModal(risalahId, risalahTitle) {
            currentRisalahId = risalahId;
            document.getElementById('restoreRisalahTitle').textContent = risalahTitle;
            const modal = new bootstrap.Modal(document.getElementById('restoreModal'));
            modal.show();
        }

        function showDeleteModal(risalahId, risalahTitle) {
            currentRisalahId = risalahId;
            document.getElementById('deleteRisalahTitle').textContent = risalahTitle;
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        }

        function showCreateModal() {
            //console.log("modal got owo");
            Swal.fire({
                title: 'Pilih Tipe Risalah:',
                html: `<div class="row justify-content-around">
                            <div class="col-sm-5">
                                <div class="row">
                                    <a href="{{ route('admin.risalah.add') }}" class="btn rounded-3 text-white"
                                    style="background-color:#4285f4; border-color:#4285f4;">
                                        <div class="col">
                                            <i class="fa-solid fa-calendar-days" style="font-size: 50px"></i>
                                        </div>

                                        <div class="col">
                                            Risalah Dengan Undangan
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div class="col-sm-5">
                                <div class="row">
                                    <a href="{{ route('admin.risalah-custom.add') }}" class="btn rounded-3 text-white"
                                    style="background-color:#34a853; border-color:#34a853;">
                                        <div class="col">
                                            <i class="fa-solid fa-clipboard-list" style="font-size: 50px"></i>
                                        </div>
                                        <div class="col">
                                            Risalah Tanpa Undangan
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                            `,
                showCloseButton: true,
                showConfirmButton: false
            });
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
                .then(response => response.json())
                .then(data => {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('restoreModal'));
                    modal.hide();
                    if (data.success) {
                        showNotification('Risalah berhasil dipulihkan', 'success');
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        showNotification('Gagal memulihkan risalah', 'error');
                    }
                })
                .catch(() => showNotification('Terjadi kesalahan', 'error'));
        }

        function confirmDelete() {
            if (!currentRisalahId) return;

            fetch(`/risalah/force-delete/${currentRisalahId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
                    modal.hide();
                    if (data.success) {
                        showNotification('Risalah berhasil dihapus permanen', 'success');
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        showNotification('Gagal menghapus risalah', 'error');
                    }
                })
                .catch(() => showNotification('Terjadi kesalahan', 'error'));
        }

        // 🔥 Flash message dari controller
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
