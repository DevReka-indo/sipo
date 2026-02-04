@extends('layouts.app')

@section('title', 'Memo Admin')

@section('content')
    <div class="container-fluid px-4 py-0 mt-0">
        <div class="card shadow-sm border-0">
            <div class="card-body py-3">

                <h3 class="fw-bold mb-3">Memo</h3>

                {{-- Breadcrumb --}}
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="bg-white border rounded-2 px-3 py-2 w-100 d-flex align-items-center">
                            <a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-primary">Beranda</a>
                            <span class="text-muted ms-1">/ Memo</span>
                        </div>
                    </div>
                </div>


                {{-- Row Filter --}}
                <form class="row g-2 align-items-center" method="GET" action="{{ route('admin.memo.index') }}">
                    <div class="col-auto">
                        <select name="per_page" class="form-select rounded-3" style="max-width:100px;">
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
                    <div class="col-12 col-md-auto">
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
                    </div>

                    {{-- Tombol Filter --}}
                    <div class="col-12 col-md-auto">
                        <button type="submit" class="btn btn-primary rounded-3">
                            <i class="fas fa-filter me-1"></i>Filter
                        </button>
                        <a href="{{ route('admin.memo.store2') }}" class="btn rounded-3 text-white"
                            style="background-color:#1E4178; border-color:#1E4178;">
                            <i class="fas fa-plus me-1"></i>Tambah Memo
                        </a>
                    </div>
                </form>
                {{-- Tabel --}}
                <div class="table-responsive mt-3">
                    <table class="table table-bordered custom-table-bagian">
                        <thead>
                            <tr>
                                <th class="text-center" style="width:5%;">No</th>
                                <th class="text-center" style="width:20%;">Nama Dokumen</th>
                                <th class="text-center" style="width:12%;">Tanggal Dibuat</th>
                                <th class="text-center" style="width:20%;">Dokumen</th>
                                <th class="text-center" style="width:12%;">Tanggal Disahkan</th>
                                <th class="text-center" style="width:10%;">Divisi</th>
                                <th class="text-center" style="width:8%;">Jenis</th>
                                <th class="text-center" style="width:8%;">Status</th>
                                <th class="text-center" style="width:8%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($memos as $index => $memo)
                                <tr>
                                    <td class="nomor">{{ ($memos->firstItem() ?? 0) + $index }}</td>
                                    <td class="nama-dokumen
                                {{ $memo->status == 'reject' ? 'text-danger' : ($memo->status == 'correction' ? 'text-warning' : ($memo->status == 'approve' ? 'text-success' : '')) }}"
                                        style="{{ $memo->status == 'pending' ? 'color: #0dcaf0;' : '' }}">
                                        {{ Str::limit($memo->judul ?? '-', 35, '...') }}
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($memo->tgl_dibuat)->format('d-m-Y') ?? '-' }}</td>
                                    <td>{{ $memo->nomor_memo ?? '-' }}</td>
                                    <td>{{ $memo->tgl_disahkan ? \Carbon\Carbon::parse($memo->tgl_disahkan)->format('d-m-Y') : '-' }}
                                    </td>
                                    <td class="text-center">{{ $memo->kode ?? '-' }}</td>
                                    </td>
                                    <td>
                                        @if($memo->jenis == 'masuk')
                                            <span class="badge px-3 py-2" style="background-color:#35A29F;">
                                                Memo Masuk
                                            </span>
                                        @else
                                            <span class="badge px-3 py-2" style="background-color:#3048d0;">
                                                Memo Keluar
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($memo->status == 'reject')
                                            <span class="badge bg-danger px-3 py-2">Ditolak</span>
                                        @elseif ($memo->status == 'pending')
                                            <span class="badge bg-info px-3 py-2">Diproses</span>
                                        @elseif ($memo->status == 'correction')
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
                                                onclick="window.location.href='{{ route('memo.show', ['id' => $memo->id_memo]) }}'">
                                                <i class="fas fa-eye" alt="Detail"></i>
                                            </button>
                                            @if ($memo->status == 'pending' || $memo->status == 'correction')
                                                <button type="button"
                                                    class="btn btn-sm rounded-circle text-white border-0 bg-secondary"
                                                    style="width:30px; height:30px; display:flex; align-items:center; justify-content:center;"
                                                    onclick="window.location.href='{{ route('memo.edit-baru', ['id_memo' => $memo->id_memo]) }}'">
                                                    <i class="fa-solid fa-pencil fa-lg"></i>
                                                </button>
                                            @elseif ($memo->status == 'approve' || $memo->status == 'reject')
                                                {{-- Button Arsip untuk status approve reject --}}
                                                <button type="button"
                                                    class="btn btn-sm rounded-circle text-white border-0"
                                                    style="background-color:#FFAD46; width:30px; height:30px; display:flex; align-items:center; justify-content:center;"
                                                    onclick="showArsipConfirmation({{ $memo->id_memo }}, '{{ $memo->judul ?? $memo->nama_dokumen }}')"
                                                    title="Arsip">
                                                    <i class="fa-solid fa-archive fa-lg"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-end mt-3">
                    {{ $memos->onEachSide(1)->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>



@endsection

@push('scripts')
    <script>
        let currentMemoId = null;

        // ---------- Arsip ----------
        function showArsipConfirmation(memoId, memoTitle) {
            currentMemoId = memoId;
            Swal.fire({
                title: 'Arsip Memo?',
                html: `Apakah Anda yakin ingin mengarsipkan memo <strong>${memoTitle}</strong>?<br><br>
                           <span class="text-muted">Memo yang diarsipkan akan dipindahkan ke arsip dan tidak akan muncul di daftar utama.</span>`,
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
            if (!currentMemoId) return;

            Swal.fire({
                title: 'Mengarsipkan...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            fetch(`/arsip/${currentMemoId}/Memo/simpan`, {
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
                            text: data.message || 'Memo berhasil diarsipkan',
                            icon: 'success',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#31CE36'
                        }).then(() => window.location.reload());
                    } else {
                        showNotification(data.message || 'Gagal mengarsipkan memo', 'error');
                    }
                })
                .catch(() => showNotification('Terjadi kesalahan', 'error'));
        }

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

        // Function untuk menampilkan modal restore
        function showRestoreModal(memoId, memoTitle) {
            currentMemoId = memoId;
            document.getElementById('restoreMemoTitle').textContent = memoTitle;

            const modal = new bootstrap.Modal(document.getElementById('restoreModal'));
            modal.show();
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

            // Implement AJAX call untuk restore
            fetch(`/memo/restore/${currentMemoId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Close modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('restoreModal'));
                        modal.hide();

                        // Show success message
                        showNotification('Memo berhasil dipulihkan', 'success');

                        // Reload page or update table
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
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
                    showConfirmButton: true,
                    customClass: {
                        confirmButton: 'btn btn-success px-4 py-2',
                    },
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

            @if (session('success') === 'Dokumen berhasil dibuat.')
                showNotification("Memo berhasil dibuat dan disimpan.", "success");
            @endif

            @if (session('success') === 'Memo berhasil diubah.')
                showNotification("Memo berhasil diubah dan disimpan.", "success");
            @endif
            @if (session('error'))
                showNotification("Gagal membuat memo. Silahkan periksa kembali data yang dimasukkan", "error");
            @endif
        });
    </script>
@endpush
