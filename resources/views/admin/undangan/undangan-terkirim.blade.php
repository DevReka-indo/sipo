@extends('layouts.app')

@section('title', 'Undangan Rapat')

@section('content')
    <div class="container-fluid px-4 py-0 mt-0">
        <div class="card shadow-sm border-0">
            <div class="card-body py-3">

                <h3 class="fw-bold mb-3">Undangan Keluar</h3>

                {{-- Breadcrumb --}}
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="bg-white border rounded-2 px-3 py-2 w-100 d-flex align-items-center">
                            <a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-primary">Beranda</a>
                            <span class="text-muted ms-1">/ Undangan Keluar</span>
                        </div>
                    </div>
                </div>

                {{-- Row Filter --}}
                <form class="row g-2 align-items-center" method="GET" action="{{ route('admin.undangan.terkirim') }}">
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
                            <option value="approve" {{ request('status') == 'approve' ? 'selected' : '' }}>Diterima</option>
                            <option value="reject" {{ request('status') == 'reject' ? 'selected' : '' }}>Ditolak</option>
                            <option value="correction" {{ request('status') == 'correction' ? 'selected' : '' }}>Dikoreksi
                            </option>
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
                        <select class="form-select rounded-3" name="kode" id="kode" aria-label="Pilih Divisi"
                            onchange="this.form.submit()">
                            <option value="" {{ !request()->filled('kode') ? 'selected' : '' }}>Semua Divisi
                            </option>
                            @foreach ($kode ?? collect() as $k)
                                <option value="{{ $k }}" {{ request('kode') == $k ? 'selected' : '' }}>
                                    {{ $k }}
                                </option>
                            @endforeach
                        </select>
                    </div> --}}

                    {{-- Tombol Filter --}}
                    <div class="col-12 col-md-auto">
                        <button type="submit" class="btn btn-primary rounded-3">
                            <i class="fas fa-filter me-1"></i>Filter
                        </button>
                        {{-- Tombol Tambah --}}
                        <a href="{{ route('undangan-admin/add') }}" class="btn rounded-3 text-white"
                            style="background-color:#1E4178; border-color:#1E4178;">
                            <i class="fas fa-plus me-1"></i>Tambah Undangan
                        </a>
                    </div>
                </form>

                {{-- Tabel --}}
                <div class="table-responsive mt-3">
                    <table class="table table-bordered custom-table-bagian">
                        <thead>
                            <tr>
                                <th class="text-center" style="width:5%;">No</th>
                                <th class="text-center" style="width:20%;">Perihal</th>
                                <th class="text-center" style="width:12%;">Tanggal Rapat</th>
                                {{-- <th class="text-center" style="width:2%;">Seri</th> --}}
                                <th class="text-center" style="width:20%;">Dokumen</th>
                                <th class="text-center" style="width:12%;">Tanggal Disahkan</th>
                                <th class="text-center" style="width:10%;">Divisi</th>
                                <th class="text-center" style="width:8%;">Status</th>
                                <th class="text-center" style="width:8%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($undangans->isEmpty())
                                <tr>
                                    <td colspan="8" class="text-center">Belum ada undangan yang terkirim.</td>
                                </tr>
                            @else
                                @foreach ($undangans as $index => $undangan)
                                    <tr>
                                        <td class="nomor">{{ ($undangans->firstItem() ?? 0) + $index }}</td>
                                        <td class="nama-dokumen
                            {{ $undangan->status == 'reject' ? 'text-danger' : ($undangan->status == 'correction' ? 'text-warning' : ($undangan->status == 'approve' ? 'text-success' : '')) }}"
                                            style="{{ $undangan->status == 'pending' ? 'color: #0dcaf0;' : '' }}">
                                            {{ Str::limit($undangan->judul ?? '-', 35, '...') }}
                                        </td>
                                        <td>{{ isset($undangan->tgl_rapat) ? \Carbon\Carbon::parse($undangan->tgl_rapat)->format('d-m-Y') : '-' }}
                                        </td>
                                        {{-- <td>{{ $undangan->seri_surat }}</td> --}}
                                        <td>{{ $undangan->nomor_undangan }}</td>
                                        <td>{{ $undangan->tgl_disahkan ? \Carbon\Carbon::parse($undangan->tgl_disahkan)->format('d-m-Y') : '-' }}
                                        </td>
                                        <td class="text-center">{{ $undangan->kode ?? 'No Divisi Assigned' }}</td>
                                        <td class="text-center">
                                            @if ($undangan->status == 'reject')
                                                <span class="badge bg-danger px-3 py-2">Ditolak</span>
                                            @elseif ($undangan->status == 'pending')
                                                <span class="badge bg-info px-3 py-2">Diproses</span>
                                            @elseif ($undangan->status == 'correction')
                                                <span class="badge bg-warning px-3 py-2">Dikoreksi</span>
                                            @else
                                                <span class="badge bg-success px-3 py-2">Diterima</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                <button title="Detail"
                                                    class="btn btn-sm rounded-circle text-white border-0 bg-info"
                                                    style="width:30px; height:30px; display:flex; align-items:center; justify-content:center;"
                                                    onclick="window.location.href='{{ route('view.undangan', ['id' => $undangan->id_undangan]) }}'">
                                                    <i class="fas fa-eye" alt="Detail"></i>
                                                </button>
                                                @if ($undangan->status == 'pending' || $undangan->status == 'correction')
                                                    <button type="button"
                                                        class="btn btn-sm rounded-circle text-white border-0 bg-secondary"
                                                        style="width:30px; height:30px; display:flex; align-items:center; justify-content:center;"
                                                        onclick="window.location.href='{{ route('undangan.edit', ['id' => $undangan->id_undangan]) }}'">
                                                        <i class="fa-solid fa-pencil fa-lg"></i>
                                                    </button>
                                                @elseif ($undangan->status == 'approve' || $undangan->status == 'reject')
                                                    {{-- Button Arsip untuk status approve/reject --}}
                                                    <button type="button"
                                                        class="btn btn-sm rounded-circle text-white border-0"
                                                        style="background-color:#FFAD46; width:30px; height:30px; display:flex; align-items:center; justify-content:center;"
                                                        onclick="showArsipConfirmation({{ $undangan->id_undangan }}, '{{ $undangan->judul ?? $undangan->nama_dokumen }}')"
                                                        title="Arsip">
                                                        <i class="fa-solid fa-archive fa-lg"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-end mt-3">
                    {{ $undangans->onEachSide(1)->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>



    {{-- Modal Restore --}}
    <div class="modal fade" id="restoreModal" tabindex="-1" aria-labelledby="restoreModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="restoreModalLabel">Pulihkan Undangan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin memulihkan undangan <strong id="restoreUndanganTitle"></strong>?</p>
                    <p class="text-muted">Undangan yang dipulihkan akan dikembalikan ke daftar utama.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" id="confirmRestore">
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
                    <h5 class="modal-title" id="deleteModalLabel">Hapus Permanen Undangan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus permanen undangan <strong id="deleteUndanganTitle"></strong>?</p>
                    <p class="text-danger"><i class="fa-solid fa-exclamation-triangle me-1"></i>Tindakan ini tidak dapat
                        dibatalkan dan data akan hilang selamanya!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">
                        <i class="fa-solid fa-trash me-1"></i>Hapus Permanen
                    </button>
                </div>
            </div>
        </div>
    </div>


@endsection

@push('scripts')
    <script>
        let currentUndanganId = null;

        // ---------- Arsip ----------
        function showArsipConfirmation(undanganId, undanganTitle) {
            currentUndanganId = undanganId;
            Swal.fire({
                title: 'Arsip Undangan?',
                html: `Apakah Anda yakin ingin mengarsipkan undangan <strong>${undanganTitle}</strong>?<br><br>
                       <span class="text-muted">Undangan yang diarsipkan akan dipindahkan ke arsip dan tidak akan muncul di daftar utama.</span>`,
                icon: 'question',
                showCancelButton: true,
                reverseButtons: true,
                confirmButtonText: '<i class="fa-solid fa-archive me-1"></i>Arsip',
                cancelButtonText: 'Batal',
                customClass: {
                    actions: 'swal2-actions swal2-actions-custom',
                    confirmButton: 'btn btn-warning px-4 py-2',
                    cancelButton: 'btn btn-outline-secondary px-4 py-2'
                },
                buttonsStyling: false
            }).then(result => {
                if (result.isConfirmed) confirmArsip();
            });
        }

        function confirmArsip() {
            if (!currentUndanganId) return;

            Swal.fire({
                title: 'Mengarsipkan...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            fetch(`/arsip/${currentUndanganId}/Undangan/simpan`, {
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
                            text: 'Undangan berhasil diarsipkan',
                            icon: 'success',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#31CE36'
                        }).then(() => window.location.reload());
                    } else {
                        showAlert('Gagal mengarsipkan undangan', 'error');
                    }
                })
                .catch(() => showAlert('Terjadi kesalahan', 'error'));
        }

        // Event Listener untuk Modal Restore
        document.addEventListener("DOMContentLoaded", function() {
            let restoreModal = document.getElementById("restoreModal");
            let confirmRestoreBtn = document.getElementById("confirmRestore");

            let deleteModal = document.getElementById("deleteModal");
            let confirmDeleteBtn = document.getElementById("confirmDelete");

            // Event Listener untuk Modal Restore
            restoreModal.addEventListener("show.bs.modal", function(event) {
                let button = event.relatedTarget;
                currentRoute = button.getAttribute("data-route");
                let undanganTitle = button.getAttribute("data-title");

                document.getElementById('restoreUndanganTitle').textContent = undanganTitle;
            });

            // Event Listener untuk Tombol "Pulihkan" di Modal Restore
            confirmRestoreBtn.addEventListener("click", function(event) {
                event.preventDefault();

                if (!currentRoute) {
                    showAlert('Route tidak ditemukan', 'error');
                    return;
                }

                // Disable button
                confirmRestoreBtn.disabled = true;
                confirmRestoreBtn.innerHTML =
                    '<i class="fa-solid fa-spinner fa-spin me-1"></i>Memulihkan...';

                fetch(currentRoute, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
                            .getAttribute("content"),
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        _method: "DELETE"
                    })
                }).then(response => {
                    if (response.ok) {
                        let modalInstance = bootstrap.Modal.getInstance(restoreModal);
                        modalInstance.hide();

                        showAlert('Undangan berhasil dipulihkan', 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        throw new Error('Network response was not ok');
                    }
                }).catch(error => {
                    console.error("Error:", error);
                    showAlert('Terjadi kesalahan saat memulihkan undangan', 'error');
                }).finally(() => {
                    // Re-enable button
                    confirmRestoreBtn.disabled = false;
                    confirmRestoreBtn.innerHTML =
                        '<i class="fa-solid fa-rotate-left me-1"></i>Pulihkan';
                });
            });

            // Event Listener untuk Modal Delete
            deleteModal.addEventListener("show.bs.modal", function(event) {
                let button = event.relatedTarget;
                currentRoute = button.getAttribute("data-route");
                let undanganTitle = button.getAttribute("data-title");

                document.getElementById('deleteUndanganTitle').textContent = undanganTitle;
            });

            // Event Listener untuk Tombol "Hapus Permanen" di Modal Delete
            confirmDeleteBtn.addEventListener("click", function(event) {
                event.preventDefault();

                if (!currentRoute) {
                    showAlert('Route tidak ditemukan', 'error');
                    return;
                }

                // Disable button
                confirmDeleteBtn.disabled = true;
                confirmDeleteBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Menghapus...';

                fetch(currentRoute, {
                    method: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
                            .getAttribute("content"),
                        "Content-Type": "application/json"
                    }
                }).then(response => {
                    if (response.ok) {
                        let modalInstance = bootstrap.Modal.getInstance(deleteModal);
                        modalInstance.hide();

                        showAlert('Undangan berhasil dihapus permanen', 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        throw new Error('Network response was not ok');
                    }
                }).catch(error => {
                    console.error("Error:", error);
                    showAlert('Terjadi kesalahan saat menghapus undangan', 'error');
                }).finally(() => {
                    // Re-enable button
                    confirmDeleteBtn.disabled = false;
                    confirmDeleteBtn.innerHTML =
                        '<i class="fa-solid fa-trash me-1"></i>Hapus Permanen';
                });
            });
        });

        // Select all functionality
        document.getElementById('selectAll')?.addEventListener('change', function() {
            document.querySelectorAll('.selectItem').forEach(cb => cb.checked = this.checked);
            toggleBulkBar();
        });

        // Toggle bulk bar
        function toggleBulkBar() {
            const anyChecked = Array.from(document.querySelectorAll('.selectItem')).some(cb => cb.checked);
            const bulkActions = document.getElementById('bulkActions');
            if (bulkActions) {
                bulkActions.style.display = anyChecked ? 'flex' : 'none';
            }
        }

        // Listen per-item checkbox
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('selectItem')) toggleBulkBar();
        });

        // Function untuk menampilkan SweetAlert di tengah
        function showAlert(message, type) {
            const config = {
                text: message,
                confirmButtonText: 'OK',
                allowOutsideClick: false,
                allowEscapeKey: false,
                customClass: {
                    confirmButton: 'btn btn-primary px-4 py-2',
                },
                buttonsStyling: false
            };

            if (type === 'success') {
                config.title = 'Berhasil!';
                config.icon = 'success';
                config.customClass.confirmButton = 'btn btn-success px-4 py-2';
            } else if (type === 'error') {
                config.title = 'Error!';
                config.icon = 'error';
                config.customClass.confirmButton = 'btn btn-danger px-4 py-2';
            } else if (type === 'warning') {
                config.title = 'Peringatan!';
                config.icon = 'warning';
                config.customClass.confirmButton = 'btn btn-warning px-4 py-2';
            } else if (type === 'info') {
                config.title = 'Informasi';
                config.icon = 'info';
                config.customClass.confirmButton = 'btn btn-info px-4 py-2';
            }

            if (typeof Swal !== 'undefined') {
                return Swal.fire(config);
            } else {
                // Fallback jika SweetAlert tidak tersedia
                alert(message);
                return Promise.resolve();
            }
        }

        // Flash messages dari session Laravel
        document.addEventListener('DOMContentLoaded', function() {
            @if (session('success'))
                showAlert('{{ session('success') }}', 'success');
            @endif

            @if (session('error'))
                showAlert('{{ session('error') }}', 'error');
            @endif

            @if (session('warning'))
                showAlert('{{ session('warning') }}', 'warning');
            @endif

            @if (session('info'))
                showAlert('{{ session('info') }}', 'info');
            @endif

            // CRUD Operation Messages - Store (Create)
            @if (session('success') === 'Undangan berhasil dibuat.' || session('undangan_created'))
                showAlert('Undangan berhasil dibuat dan disimpan!', 'success');
            @endif

            @if (session('error') === 'Gagal membuat undangan.' || session('undangan_create_failed'))
                showAlert('Gagal membuat undangan. Silakan periksa kembali data yang dimasukkan.', 'error');
            @endif

            // CRUD Operation Messages - Update
            @if (session('success') === 'Undangan berhasil diperbarui.' || session('undangan_updated'))
                showAlert('Undangan berhasil diperbarui!', 'success');
            @endif

            @if (session('error') === 'Gagal memperbarui undangan.' || session('undangan_update_failed'))
                showAlert('Gagal memperbarui undangan. Silakan periksa kembali data yang dimasukkan.', 'error');
            @endif

            // Existing Archive Messages
            @if (session('success') === 'Undangan berhasil diarsipkan.')
                showAlert('Undangan berhasil diarsipkan.', 'success');
            @endif

            @if (session('success') === 'Undangan terpilih berhasil dihapus permanen.')
                showAlert('Undangan terpilih berhasil dihapus permanen.', 'success');
            @endif

            @if (session('success') === 'Undangan terpilih berhasil dipulihkan.')
                showAlert('Undangan terpilih berhasil dipulihkan.', 'success');
            @endif

            // Validation Error Messages
            @if ($errors->any())
                let errorMessages = [];
                @foreach ($errors->all() as $error)
                    errorMessages.push('{{ $error }}');
                @endforeach
                showAlert('Terdapat kesalahan pada form:\n' + errorMessages.join('\n'), 'error');
            @endif

            // Additional specific error conditions
            @if (session('error') === 'File tidak valid.' || session('invalid_file'))
                showAlert('File yang diupload tidak valid. Silakan pilih file dengan format yang benar.', 'error');
            @endif

            @if (session('error') === 'Data tidak ditemukan.' || session('data_not_found'))
                showAlert('Data undangan tidak ditemukan.', 'error');
            @endif

            @if (session('error') === 'Tidak memiliki izin.' || session('unauthorized'))
                showAlert('Anda tidak memiliki izin untuk melakukan aksi ini.', 'error');
            @endif

            // Additional success conditions
            @if (session('success') === 'File berhasil diupload.' || session('file_uploaded'))
                showAlert('File berhasil diupload!', 'success');
            @endif

            @if (session('warning') === 'Data sudah ada.' || session('data_exists'))
                showAlert('Data dengan informasi tersebut sudah ada dalam sistem.', 'warning');
            @endif
        });
    </script>
@endpush
