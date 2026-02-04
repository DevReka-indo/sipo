@extends('layouts.app')

@section('title', 'Arsip Memo')

@section('content')
    <div class="container-fluid px-4 py-0 mt-0">
        <div class="card shadow-sm border-0">
            <div class="card-body py-3">

                <h3 class="fw-bold mb-3">Arsip Memo</h3>

                {{-- Breadcrumb --}}
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="bg-white border rounded-2 px-3 py-2 w-100 d-flex align-items-center">
                            <a href="{{ route(Auth::user()->role->nm_role . '.dashboard') }}"
                                class="text-decoration-none text-primary">Beranda</a>
                            <span class="text-muted ms-1">/ Arsip Memo</span>
                        </div>
                    </div>
                </div>


                {{-- Row Filter --}}
                <form class="row g-2 align-items-center" method="GET" action="{{ route('arsip.memo') }}">
                    <div class="col-auto">
                        <select name="per_page" class="form-select rounded-3" style="max-width:100px;">
                            <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                    {{-- Tanggal Awal --}}
                    <div class="col-12 col-md-auto">
                        <input type="date" class="form-control rounded-3" name="start_date"
                            value="{{ request('start_date') }}" placeholder="Tanggal Awal" aria-label="Tanggal Awal">
                    </div>

                    {{-- Separator panah (hidden di mobile) --}}
                    <div class="col-auto d-none d-md-flex align-items-center">
                        <span class="mx-1">→</span>
                    </div>

                    {{-- Tanggal Akhir --}}
                    <div class="col-12 col-md-auto">
                        <input type="date" class="form-control rounded-3" name="end_date"
                            value="{{ request('end_date') }}" placeholder="Tanggal Akhir" aria-label="Tanggal Akhir">
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
                            @if (isset($allowedDiv) && is_object($allowedDiv) && $allowedDiv->count() > 0)
                                @foreach ($allowedDiv as $k)
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
                    </div>
                </form>
                </form>

                {{-- Tabel --}}
                <div class="table-responsive mt-3">
                    <table class="table table-bordered custom-table-bagian">
                        <thead>
                            <tr>
                                <th class="text-center" style="width:5%;">No</th>
                                <th class="text-center" style="width:25%;">Nama Dokumen</th>
                                <th class="text-center" style="width:12%;">Tanggal Masuk</th>
                                <th class="text-center" style="width:25%;">Dokumen</th>
                                <th class="text-center" style="width:12%;">Tanggal Disahkan</th>
                                <th class="text-center" style="width:10%;">Divisi</th>
                                <th class="text-center" style="width:8%;">Status</th>
                                <th class="text-center" style="width:8%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <tbody>
                            @if ($arsipMemo->count() > 0)
                                @foreach ($arsipMemo as $index => $arsip)
                                    <tr>
                                        <td class="nomor">{{ ($arsipMemo->firstItem() ?? 0) + $index }}</td>
                                        <td class="nama-dokumen
                                {{ $arsip->document->status == 'reject' ? 'text-danger' : ($arsip->document->status == 'correction' ? 'text-warning' : ($arsip->document->status == 'approve' ? 'text-success' : '')) }}"
                                            style="{{ $arsip->document->status == 'pending' ? 'color: #0dcaf0;' : '' }}">
                                            {{ Str::limit($arsip->document->judul ?? '-', 40, '...') }}
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($arsip->document->tgl_dibuat)->format('d-m-Y') ?? '-' }}
                                        </td>
                                        <td>{{ $arsip->document->nomor_memo ?? '-' }}</td>
                                        <td>{{ $arsip->document->tgl_disahkan ? \Carbon\Carbon::parse($arsip->document->tgl_disahkan)->format('d-m-Y') : '-' }}
                                        </td>
                                        <td class="text-center">{{ $arsip->document->kode ?? '-' }}</td>
                                        </td>
                                        <td class="text-center">
                                            @if ($arsip->document->status == 'reject')
                                                <span class="badge bg-danger">Ditolak</span>
                                            @elseif ($arsip->document->status == 'pending')
                                                <span class="badge bg-info">Diproses</span>
                                            @elseif ($arsip->document->status == 'correction')
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
                                                    onclick="window.location.href='{{ route('view.memo-arsip', $arsip->document->id_memo) }}'">
                                                    <i class="fas fa-eye" alt="Detail"></i>
                                                </button>
                                                <button title="Unarchive"
                                                    class="btn btn-sm rounded-circle text-white border-0"
                                                    style="background-color:#198754; width:30px; height:30px; display:flex; align-items:center; justify-content:center;"
                                                    onclick="showRestoreConfirmation({{ $arsip->document->id_memo }}, '{{ $arsip->document->judul }}')">
                                                    <i class="fas fa-arrow-up-from-bracket" alt="Unarchive"></i>
                                                </button>
                                                <button title="Unduh"
                                                    class="btn btn-sm rounded-circle text-white border-0 bg-secondary"
                                                    style="width:30px; height:30px; display:flex; align-items:center; justify-content:center;"
                                                    onclick="window.location.href='{{ route('cetakmemo', ['id' => $arsip->document->id_memo]) }}'">
                                                    <i class="fas fa-download" alt="Unduh"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="10" class="text-center">Tidak ada data memo terarsip</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div class="d-flex justify-content-end mt-3">
                    {{ $arsipMemo->onEachSide(1)->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Delete Permanen --}}
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Hapus Permanen Memo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus permanen memo <strong id="deleteMemoTitle"></strong>?</p>
                    <p class="text-danger"><i class="fa-solid fa-exclamation-triangle me-1"></i>Tindakan ini tidak dapat
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
        let currentMemoId = null;

        // Select all functionality
        document.getElementById('selectAll')?.addEventListener('change', function() {
            document.querySelectorAll('.selectItem').forEach(cb => cb.checked = this.checked);
            toggleBulkBar();
        });

        // Toggle bulk bar
        function toggleBulkBar() {
            const anyChecked = Array.from(document.querySelectorAll('.selectItem')).some(cb => cb.checked);
            document.getElementById('bulkActions').style.display = anyChecked ? 'flex' : 'none';
        }

        // Listen per-item checkbox
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('selectItem')) toggleBulkBar();
        });

        // Function untuk menampilkan konfirmasi restore dengan SweetAlert
        function showRestoreConfirmation(memoId, memoTitle) {
            currentMemoId = memoId;
            Swal.fire({
                title: 'Keluarkan Memo?',
                html: `Apakah Anda yakin ingin mengeluarkan memo <strong>${memoTitle}</strong> dari arsip?<br><br>
                       <span class="text-muted">Memo yang dikeluarkan akan dikembalikan ke daftar utama.</span>`,
                icon: 'question',
                showCancelButton: true,
                reverseButtons: true,
                confirmButtonText: '<i class="fas fa-arrow-up-from-bracket me-1"></i>Keluarkan',
                cancelButtonText: 'Batal',
                customClass: {
                    actions: 'swal2-actions swal2-actions-custom',
                    confirmButton: 'btn btn-success px-4 py-2',
                    cancelButton: 'btn btn-outline-secondary px-4 py-2'
                },
                buttonsStyling: false
            }).then(result => {
                if (result.isConfirmed) confirmRestore();
            });
        }

        // Function untuk menampilkan modal delete
        function showDeleteModal(memoId, memoTitle) {
            currentMemoId = memoId;
            document.getElementById('deleteMemoTitle').textContent = memoTitle;

            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        }

        // Function untuk konfirmasi restore
        function confirmRestore() {
            if (!currentMemoId) return;

            Swal.fire({
                title: 'Memulihkan...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            // Implement AJAX call untuk restore
            fetch(`/arsip/${currentMemoId}/Memo`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: 'Memo berhasil dipulihkan',
                            icon: 'success',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#31CE36'
                        }).then(() => window.location.reload());
                    } else {
                        showNotification('Gagal memulihkan memo', 'error');
                    }
                })
                .catch(error => {
                    showNotification('Terjadi kesalahan', 'error');
                });
        }

        // Function untuk konfirmasi delete
        function confirmDelete() {
            if (!currentMemoId) return;

            // Implement AJAX call untuk delete permanen
            fetch(`/memo/force-delete/${currentMemoId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Close modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
                        modal.hide();

                        // Show success message
                        showNotification('Memo berhasil dihapus permanen', 'success');

                        // Reload page or update table
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        showNotification('Gagal menghapus memo', 'error');
                    }
                })
                .catch(error => {
                    showNotification('Terjadi kesalahan', 'error');
                });
        }

        // Function untuk menampilkan notifikasi
        function showNotification(message, type) {
            // Implement sesuai dengan library notifikasi yang digunakan
            // Contoh menggunakan SweetAlert atau library lain
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: type === 'success' ? 'Berhasil!' : 'Error!',
                    text: message,
                    icon: type,
                    timer: 3000,
                    showConfirmButton: false
                });
            } else {
                // Fallback alert
                alert(message);
            }
        }

        // Success flash messages
        document.addEventListener('DOMContentLoaded', function() {
            @if (session('success') === 'Memo terpilih berhasil dihapus permanen.')
                showNotification('Memo terpilih berhasil dihapus permanen.', 'success');
            @endif

            @if (session('success') === 'Memo terpilih berhasil dipulihkan.')
                showNotification('Memo terpilih berhasil dipulihkan.', 'success');
            @endif
        });
    </script>
@endsection
