@extends('layouts.app')

@section('title', 'Arsip Undangan')

@section('content')
    <div class="container-fluid px-4 py-0 mt-0">
        <div class="card shadow-sm border-0">
            <div class="card-body py-3">

                <h3 class="fw-bold mb-3">Arsip Undangan</h3>

                {{-- Breadcrumb --}}
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="bg-white border rounded-2 px-3 py-2 w-100 d-flex align-items-center">
                            <a href="{{ route(Auth::user()->role->nm_role . '.dashboard') }}"
                                class="text-decoration-none text-primary">Beranda</a>
                            <span class="text-muted ms-1">/ Arsip Undangan</span>
                        </div>
                    </div>
                </div>


                {{-- Row Filter --}}
                <form class="row g-2 align-items-center" method="GET" action="{{ route('arsip.undangan') }}">
                    <div class="col-auto">
                        <select name="per_page" class="form-select rounded-3" style="max-width:100px;"
                            onchange="this.form.submit()">
                            <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                    {{-- Status
                    <div class="col-12 col-md-auto">
                        <select class="form-select rounded-3" name="status" aria-label="Status">
                            <option value="" @selected(is_null($status))>Semua Status</option>
                            <option value="pending" @selected($status === 'pending')>Diproses</option>
                            <option value="approve" @selected($status === 'approve')>Diterima</option>
                            <option value="reject" @selected($status === 'reject')>Ditolak</option>
                        </select>
                    </div> --}}

                    {{-- Tanggal Awal --}}
                    <div class="col-12 col-md-auto">
                        <input type="date" class="form-control rounded-3" name="start_date"
                            value="{{ request('tgl_dibuat_awal') }}" placeholder="Tanggal Awal" aria-label="Tanggal Awal">
                    </div>

                    {{-- Separator panah (hidden di mobile) --}}
                    <div class="col-auto d-none d-md-flex align-items-center">
                        <span class="mx-1">→</span>
                    </div>

                    {{-- Tanggal Akhir --}}
                    <div class="col-12 col-md-auto">
                        <input type="date" class="form-control rounded-3" name="end_date"
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
                                <th class="text-center" style="width:20%;">Nama Dokumen</th>
                                <th class="text-center" style="width:12%;">Tanggal Masuk</th>
                                {{-- <th class="text-center" style="width:2%;">Seri</th> --}}
                                <th class="text-center" style="width:20%;">Dokumen</th>
                                <th class="text-center" style="width:12%;">Tanggal Disahkan</th>
                                <th class="text-center" style="width:10%;">Divisi</th>
                                <th class="text-center" style="width:8%;">Status</th>
                                <th class="text-center" style="width:8%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (isset($arsipUndangan) && $arsipUndangan->count() > 0)
                                @foreach ($arsipUndangan as $arsip)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td class="nama-dokumen
                        {{ $arsip->document->status == 'reject' ? 'text-danger' : ($arsip->document->status == 'correction' ? 'text-warning' : ($arsip->document->status == 'approve' ? 'text-success' : '')) }}"
                                            style="{{ $arsip->document->status == 'pending' ? 'color: #0dcaf0;' : '' }}">
                                            {{ $arsip->document->judul }}
                                        </td>
                                        <td>{{ $arsip->document ? $arsip->document->tgl_dibuat->format('d-m-Y') : '-' }}
                                        </td>
                                        {{-- <td>{{ $arsip->document ? $arsip->document->seri_surat : '-' }}</td> --}}
                                        <td class="text-center">
                                            {{ $arsip->document ? $arsip->document->nomor_undangan : '-' }}</td>
                                        <td>{{ $arsip->document
                                            ? \Carbon\Carbon::parse($arsip->document->getAttributes()['tgl_disahkan'])->format('d-m-Y')
                                            : '-' }}
                                        </td>

                                        <td class="text-center">{{ $arsip->document->kode ?? '-' }}</td>
                                        </td>
                                        <td class="text-center">
                                            @if ($arsip->document->status == 'reject')
                                                <span class="badge bg-danger px-3 py-2">Ditolak</span>
                                            @elseif ($arsip->document->status == 'pending')
                                                <span class="badge bg-info px-3 py-2">Diproses</span>
                                            @elseif ($arsip->document->status == 'pending')
                                                <span class="badge bg-warning px-3 py-2">Dikoreksi</span>
                                            @elseif ($arsip->document->status == 'approve')
                                                <span class="badge bg-success px-3 py-2">Diterima</span>
                                            @else
                                                <span class="badge bg-secondary px-3 py-2">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                <button title="Detail"
                                                    class="btn btn-sm rounded-circle text-white border-0 bg-info"
                                                    style="width:30px; height:30px; display:flex; align-items:center; justify-content:center;"
                                                    onclick="window.location.href='{{ route('view.undangan-arsip', $arsip->document->id_undangan) }}'">
                                                    <i class="fas fa-eye" alt="Detail"></i>
                                                </button>
                                                <button title="Unarchive"
                                                    class="btn btn-sm rounded-circle text-white border-0 unarchive-btn"
                                                    style="background-color:#198754; width:30px; height:30px; display:flex; align-items:center; justify-content:center;"
                                                    data-id="{{ $arsip->document->id_undangan }}"
                                                    data-title="{{ $arsip->document->judul }}" type="button">
                                                    <i class="fas fa-arrow-up-from-bracket" alt="Unarchive"></i>
                                                </button>
                                                <button title="Unduh"
                                                    class="btn btn-sm rounded-circle text-white border-0 bg-secondary"
                                                    style="width:30px; height:30px; display:flex; align-items:center; justify-content:center;"
                                                    onclick="window.location.href='{{ route('cetakundangan', ['id' => $arsip->document->id_undangan]) }}'">
                                                    <i class="fas fa-download" alt="Unduh"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="10" class="text-center">Tidak ada data arsip undangan rapat</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-end mt-3">
                    {{ $arsipUndangan->onEachSide(1)->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>

        {{-- Modal Delete Permanen --}}
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">Hapus Permanen undangan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Apakah Anda yakin ingin menghapus permanen undangan <strong id="deleteundanganTitle"></strong>?
                        </p>
                        <p class="text-danger"><i class="fa-solid fa-exclamation-triangle me-1"></i>Tindakan ini tidak
                            dapat
                            dibatalkan dan data akan hilang selamanya!</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                            <i class="fa-solid fa-trash me-1"></i>Hapus Permanen
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <script>
            let currentUndanganId = null;
            let currentUndanganTitle = null;

            document.addEventListener("DOMContentLoaded", function() {

                // Handle unarchive button clicks using event delegation
                document.addEventListener('click', function(e) {
                    if (e.target.closest('.unarchive-btn')) {
                        const btn = e.target.closest('.unarchive-btn');
                        const id = btn.getAttribute('data-id');
                        const title = btn.getAttribute('data-title');

                        if (id && title) {
                            showRestoreConfirmation(id, title);
                        }
                    }
                });

                // Select all functionality
                const selectAllBtn = document.getElementById('selectAll');
                if (selectAllBtn) {
                    selectAllBtn.addEventListener('change', function() {
                        document.querySelectorAll('.selectItem').forEach(cb => cb.checked = this.checked);
                        toggleBulkBar();
                    });
                }

                // Listen per-item checkbox changes
                document.addEventListener('change', function(e) {
                    if (e.target.classList.contains('selectItem')) {
                        toggleBulkBar();
                    }
                });

                // Handle flash messages
                @if (session('success') === 'undangan terpilih berhasil dihapus permanen.')
                    showNotification('undangan terpilih berhasil dihapus permanen.', 'success');
                @endif

                @if (session('success') === 'undangan terpilih berhasil dipulihkan.')
                    showNotification('undangan terpilih berhasil dipulihkan.', 'success');
                @endif
            });

            // Function to show restore confirmation using SweetAlert
            function showRestoreConfirmation(undanganId, undanganTitle) {
                currentUndanganId = undanganId;
                currentUndanganTitle = undanganTitle;

                Swal.fire({
                    title: 'Keluarkan Undangan?',
                    html: `Apakah Anda yakin ingin mengeluarkan undangan rapat <strong>${undanganTitle}</strong> dari arsip?<br><br>
                       <span class="text-muted">Undangan yang dikeluarkan akan dikembalikan ke daftar utama.</span>`,
                    icon: 'question',
                    showCancelButton: true,
                    reverseButtons: true,
                    confirmButtonText: '<i class="fa-solid fa-arrow-up-from-bracket me-1"></i>Keluarkan',
                    cancelButtonText: 'Batal',
                    customClass: {
                        actions: 'swal2-actions swal2-actions-custom',
                        confirmButton: 'btn btn-success px-4 py-2',
                        cancelButton: 'btn btn-outline-secondary px-4 py-2'
                    },
                    buttonsStyling: false
                }).then(result => {
                    if (result.isConfirmed) {
                        confirmRestore();
                    }
                });
            }

            // Function to confirm restore - using your existing route
            function confirmRestore() {
                if (!currentUndanganId) {
                    showNotification('ID undangan tidak ditemukan', 'error');
                    return;
                }

                Swal.fire({
                    title: 'Memproses...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                // Make AJAX call to restore using your actual route
                fetch(`/arsip/${currentUndanganId}/undangan`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (response.ok) {
                            return response.json().catch(() => ({
                                success: true
                            }));
                        } else {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                    })
                    .then(data => {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: data.message || 'Undangan berhasil dipulihkan',
                            icon: 'success',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#31CE36'
                        }).then(() => window.location.reload());
                    })
                    .catch(error => {
                        console.error('Error:', error);

                        if (error.message.includes('404')) {
                            showNotification('Route restore tidak ditemukan', 'error');
                        } else if (error.message.includes('500')) {
                            showNotification('Terjadi kesalahan server', 'error');
                        } else {
                            showNotification('Terjadi kesalahan saat memulihkan undangan', 'error');
                        }
                    });
            }

            // Toggle bulk actions bar
            function toggleBulkBar() {
                const bulkActions = document.getElementById('bulkActions');
                if (bulkActions) {
                    const anyChecked = Array.from(document.querySelectorAll('.selectItem')).some(cb => cb.checked);
                    bulkActions.style.display = anyChecked ? 'flex' : 'none';
                }
            }

            // Function to show delete modal
            function showDeleteModal(undanganId, undanganTitle) {
                currentUndanganId = undanganId;
                currentUndanganTitle = undanganTitle;

                const titleElement = document.getElementById('deleteundanganTitle');
                if (titleElement) {
                    titleElement.textContent = undanganTitle;
                }

                const deleteModal = document.getElementById('deleteModal');
                if (deleteModal) {
                    const modal = new bootstrap.Modal(deleteModal);
                    modal.show();
                }
            }

            // Function to confirm delete - using your existing route
            function confirmDelete() {
                if (!currentUndanganId) {
                    showNotification('ID undangan tidak ditemukan', 'error');
                    return;
                }

                // Show loading state
                const confirmBtn = document.querySelector('#deleteModal .btn-danger');
                const originalText = confirmBtn ? confirmBtn.innerHTML : 'Hapus';

                if (confirmBtn) {
                    confirmBtn.disabled = true;
                    confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Menghapus...';
                }

                // Make AJAX call for permanent delete using your existing route
                fetch(`/undangan/force-delete/${currentUndanganId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        // Reset button state first
                        if (confirmBtn) {
                            confirmBtn.disabled = false;
                            confirmBtn.innerHTML = originalText;
                        }

                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Close modal
                            const deleteModal = document.getElementById('deleteModal');
                            if (deleteModal) {
                                const modal = bootstrap.Modal.getInstance(deleteModal);
                                if (modal) {
                                    modal.hide();
                                }
                            }

                            // Show success message
                            showNotification(data.message || 'Undangan berhasil dihapus permanen', 'success');

                            // Reload page after delay
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            showNotification(data.message || 'Gagal menghapus undangan', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);

                        if (error.message.includes('404')) {
                            showNotification('Route delete tidak ditemukan', 'error');
                        } else if (error.message.includes('500')) {
                            showNotification('Terjadi kesalahan server', 'error');
                        } else {
                            showNotification('Terjadi kesalahan saat menghapus undangan', 'error');
                        }
                    });
            }

            // Function to show Sweet Alert notifications
            function showNotification(message, type) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: type === 'success' ? 'Berhasil!' : 'Error!',
                        text: message,
                        icon: type === 'success' ? 'success' : 'error',
                        confirmButtonText: 'OK',
                        confirmButtonColor: type === 'success' ? '#28a745' : '#dc3545'
                    });
                } else {
                    // Fallback to alert if SweetAlert is not loaded
                    alert(`${type === 'success' ? 'Berhasil' : 'Error'}: ${message}`);
                }
            }

            // Clear current data when modals are hidden
            document.getElementById('deleteModal').addEventListener('hidden.bs.modal', function() {
                currentUndanganId = null;
                currentUndanganTitle = null;
            });
        </script>
    @endsection
