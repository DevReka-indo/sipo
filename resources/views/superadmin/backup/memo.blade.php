@extends('layouts.app')

@section('title', 'Pemulihan Memo')

@section('content')
    <div class="container-fluid px-4 py-0 mt-0">
        <div class="card shadow-sm border-0">
            <div class="card-body py-3">

                <h3 class="fw-bold mb-3">Pemulihan Memo</h3>

                {{-- Breadcrumb --}}
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="bg-white border rounded-2 px-3 py-2 w-100 d-flex align-items-center">
                            <a href="{{ route('superadmin.dashboard') }}"
                                class="text-decoration-none text-primary">Beranda</a>
                            <span class="text-muted ms-1">/ Pemulihan Memo</span>
                        </div>
                    </div>
                </div>
                <form method="GET" action="{{ route('memo.backup') }}">
                    {{-- Filter --}}
                    <div class="d-flex flex-wrap align-items-center gap-2">

                        {{-- Per Page --}}
                        <div>
                            <select name="per_page" class="form-select rounded-3" style="max-width:100px;">
                                <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                            </select>
                        </div>

                        {{-- Rentang Tanggal (awal - akhir) --}}
                        <div class="d-flex align-items-center">
                            <input type="date" name="tgl_dibuat_awal" class="form-control rounded-3 me-2"
                                value="{{ request('tgl_dibuat_awal') }}" style="max-width: 160px;">
                            <span class="mx-1">→</span>
                            <input type="date" name="tgl_dibuat_akhir" class="form-control rounded-3"
                                value="{{ request('tgl_dibuat_akhir') }}" style="max-width: 160px;">
                        </div>

                        {{-- Search --}}
                        <div class="flex-grow-1">
                            <div class="input-group">
                                <span class="input-group-text rounded-start-3"><i class="fas fa-search"></i></span>
                                <input type="text" name="search" class="form-control rounded-end-3"
                                    placeholder="Cari..." value="{{ request('search') }}">
                            </div>
                        </div>

                        {{-- Divisi --}}
                        <div>
                            <select name="kode" class="form-select rounded-3" onchange="this.form.submit()">
                                <option value="pilih" {{ !request()->filled('kode') ? 'selected' : '' }}>Semua Divisi
                                </option>
                                @foreach ($kode as $k)
                                    <option value="{{ $k }}" {{ request('kode') == $k ? 'selected' : '' }}>
                                        {{ $k }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Tombol Filter --}}
                        <div>
                            <button type="submit" class="btn btn-primary rounded-3">
                                <i class="fas fa-filter me-1"></i>Filter
                            </button>
                        </div>

                        {{-- Tombol Aksi Massal (pojok kanan) --}}
                        <div class="d-none ms-auto" id="bulkActionButtons">
                            <button type="button" class="btn btn-success rounded-3 me-1" id="bulkRestoreBtn">
                                <i class="fa-solid fa-rotate-left me-1"></i>Pulihkan
                            </button>
                            <button type="button" class="btn btn-danger rounded-3" id="bulkDeleteBtn">
                                <i class="fa-solid fa-trash me-1"></i>Hapus Permanen
                            </button>
                        </div>
                    </div>
                </form>

                {{-- Table --}}
                <div class="table-responsive mt-3">
                    <table class="table table-bordered custom-table-bagian">
                        <thead>
                            <tr>
                                <th class="text-center" style="width:5%;"><input type="checkbox" id="selectAll"></th>
                                <th class="text-center" style="width:5%;">No</th>
                                <th class="text-center" style="width:25%;">Nama Dokumen</th>
                                <th class="text-center" style="width:12%;">Tanggal Memo</th>
                                {{-- <th class="text-center" style="width:5%;">Seri</th> --}}
                                <th class="text-center" style="width:25%;">Dokumen</th>
                                <th class="text-center" style="width:12%;">Tanggal Disahkan</th>
                                <th class="text-center" style="width:10%;">Divisi</th>
                                <th class="text-center" style="width:8%;">Status</th>
                                <th class="text-center" style="width:10%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($memos->isEmpty())
                                <tr>
                                    <td colspan="9" class="text-center">Tidak ada data memo yang ditemukan.</td>
                                </tr>
                            @else
                                @foreach ($memos as $index => $memo)
                                    <tr>
                                        <td class="text-center">
                                            <input type="checkbox" name="selected_ids[]" value="{{ $memo->id_memo }}"
                                                class="selectItem">
                                        </td>
                                        <td class="nomor">{{ ($memos->firstItem() ?? 0) + $index }}</td>
                                        <td class="text-danger">{{ Str::limit($memo->judul, 35, '...') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($memo->tgl_dibuat)->format('d-m-Y') }}</td>
                                        {{-- <td>{{ $memo->seri_surat }}</td> --}}
                                        <td>{{ $memo->nomor_memo }}</td>
                                        <td>{{ $memo->tgl_disahkan ? \Carbon\Carbon::parse($memo->tgl_disahkan)->format('d-m-Y') : '-' }}
                                        </td>
                                        <td class="text-center">{{ $memo->kode ?? 'No Divisi Assigned' }}</td>
                                        <td class="text-center">
                                            @if ($memo->status == 'reject')
                                                <span class="badge bg-danger">Ditolak</span>
                                            @elseif ($memo->status == 'pending')
                                                <span class="badge bg-info">Diproses</span>
                                            @elseif ($memo->status == 'correction')
                                                <span class="badge bg-warning">Dikoreksi</span>
                                            @else
                                                <span class="badge bg-success">Diterima</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                {{-- Restore --}}
                                                <button type="button" class="btn-sm rounded-circle text-white border-0"
                                                    style="background-color:#31CE36; width:30px; height:30px; display:flex; align-items:center; justify-content:center;"
                                                    onclick="showRestoreConfirmation({{ $memo->id_memo }}, '{{ addslashes($memo->judul ?? ($memo->nama_dokumen ?? 'Dokumen')) }}')"
                                                    title="Pulihkan">
                                                    <i class="fa fa-rotate-left"></i>
                                                </button>

                                                {{-- Delete --}}
                                                <button type="button"
                                                    class="btn btn-sm rounded-circle text-white border-0"
                                                    style="background-color:#F25961; width:30px; height:30px; display:flex; align-items:center; justify-content:center;"
                                                    onclick="showDeleteConfirmation({{ $memo->id_memo }}, '{{ addslashes($memo->judul ?? ($memo->nama_dokumen ?? 'Dokumen')) }}')"
                                                    title="Hapus">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="d-flex justify-content-end mt-3">
                    {{ $memos->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
                <form id="bulkForm" method="POST">
                    @csrf
                    <div id="bulkIds"></div>
                </form>
            </div>
        </div>
    </div>

    {{-- Script toggle resize + tombol --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let currentMemoId = null;

            // === Restore Confirmation with SweetAlert ===
            window.showRestoreConfirmation = function(memoId, memoTitle) {
                console.log('showRestoreConfirmation called with:', memoId, memoTitle);
                currentMemoId = memoId;

                // Check if SweetAlert is available
                if (typeof Swal === 'undefined') {
                    console.error('SweetAlert is not loaded!');
                    if (confirm('Pulihkan Dokumen?\n\nData akan dikembalikan ke menu utama')) {
                        confirmRestore();
                    }
                    return;
                }

                Swal.fire({
                    title: 'Pulihkan Dokumen?',
                    html: `Apakah Anda yakin ingin memulihkan dokumen <strong>${memoTitle}</strong>?<br><br>
                           <span >Data akan dikembalikan ke menu utama</span>`,
                    icon: 'question',
                    showCancelButton: true,
                    reverseButtons: true,
                    confirmButtonText: '<i class="fa-solid fa-rotate-left me-1"></i>Pulihkan',
                    cancelButtonText: 'Batal',
                    customClass: {
                        actions: 'swal2-actions swal2-actions-custom',
                        confirmButton: 'btn btn-primary px-4 py-2',
                        cancelButton: 'btn btn-secondary px-4 py-2'
                    },
                    buttonsStyling: false
                }).then(result => {
                    if (result.isConfirmed) confirmRestore();
                });
            };

            function confirmRestore() {
                if (!currentMemoId) return;

                Swal.fire({
                    title: 'Memulihkan...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                fetch(`{{ route('memo.bulk-restore') }}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content'),
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            selected_ids: [currentMemoId]
                        })
                    })
                    .then(res => {
                        console.log('Response status:', res.status);
                        console.log('Response headers:', res.headers);

                        if (!res.ok) {
                            throw new Error(`HTTP error! status: ${res.status}`);
                        }

                        // Check if response is JSON
                        const contentType = res.headers.get('content-type');
                        if (contentType && contentType.includes('application/json')) {
                            return res.json();
                        } else {
                            // If not JSON, assume success (might be a redirect)
                            return {
                                success: true,
                                message: 'Dokumen berhasil dipulihkan'
                            };
                        }
                    })
                    .then(data => {
                        console.log('API Response:', data);

                        // Check for success in various possible formats
                        const isSuccess = data.success || data.status === 'success' || data.message?.includes(
                            'berhasil') || data.message?.includes('success');

                        if (isSuccess) {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: 'Dokumen berhasil dipulihkan ke menu utama',
                                icon: 'success',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#31CE36'
                            }).then(() => window.location.reload());
                        } else {
                            Swal.fire({
                                title: 'Gagal!',
                                text: data.message || 'Gagal memulihkan dokumen',
                                icon: 'error',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#d33'
                            });
                        }
                    })
                    .catch((error) => {
                        console.error('Restore error:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: 'Terjadi kesalahan saat memulihkan dokumen',
                            icon: 'error',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#d33'
                        });
                    });
            }
            // === Delete Confirmation with SweetAlert (Double Confirmation) ===
            window.showDeleteConfirmation = function(memoId, memoTitle) {
                console.log('showDeleteConfirmation called with:', memoId, memoTitle);
                currentMemoId = memoId;

                // Check if SweetAlert is available
                if (typeof Swal === 'undefined') {
                    console.error('SweetAlert is not loaded!');
                    if (confirm('Hapus Dokumen?\n\nTindakan ini tidak dapat dibatalkan!')) {
                        if (confirm(
                                'Konfirmasi kedua: Apakah Anda benar-benar yakin ingin menghapus dokumen ini secara permanen?'
                            )) {
                            confirmDelete();
                        }
                    }
                    return;
                }

                // First confirmation
                Swal.fire({
                    title: 'Hapus Dokumen?',
                    html: `Apakah Anda yakin ingin menghapus dokumen <strong>${memoTitle}</strong>?<br><br>
                           <span style="color: #d33;"><i class="fa-solid fa-exclamation-triangle me-1"></i>Tindakan ini tidak dapat dibatalkan!</span>`,
                    icon: 'warning',
                    showCancelButton: true,
                    reverseButtons: true,
                    confirmButtonText: '<i class="fa-solid fa-trash me-1"></i>Hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#d33',
                    customClass: {
                        actions: 'swal2-actions swal2-actions-custom',
                        confirmButton: 'btn btn-danger px-4 py-2',
                        cancelButton: 'btn btn-secondary px-4 py-2'
                    },
                    buttonsStyling: false
                }).then(result => {
                    if (result.isConfirmed) {
                        // Second confirmation
                        Swal.fire({
                            title: 'Konfirmasi Kedua',
                            html: `Apakah Anda <strong>BENAR-BENAR YAKIN</strong> ingin menghapus dokumen <strong>${memoTitle}</strong> secara permanen?<br><br>
                                   <span style="color: #d33; font-weight: bold;"><i class="fa-solid fa-exclamation-triangle me-1"></i>Data akan dihapus selamanya dan tidak dapat dipulihkan!</span>`,
                            icon: 'error',
                            showCancelButton: true,
                            reverseButtons: true,
                            confirmButtonText: '<i class="fa-solid fa-trash me-1"></i>YA, HAPUS PERMANEN',
                            cancelButtonText: 'Batal',
                            confirmButtonColor: '#d33',
                            customClass: {
                                actions: 'swal2-actions swal2-actions-custom',
                                confirmButton: 'btn btn-danger px-4 py-2',
                                cancelButton: 'btn btn-secondary px-4 py-2'
                            },
                            buttonsStyling: false
                        }).then(secondResult => {
                            if (secondResult.isConfirmed) {
                                confirmDelete();
                            }
                        });
                    }
                });
            };

            function confirmDelete() {
                if (!currentMemoId) return;

                Swal.fire({
                    title: 'Menghapus...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                fetch(`{{ route('memo.bulk-force-delete') }}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content'),
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            selected_ids: [currentMemoId]
                        })
                    })
                    .then(res => {
                        console.log('Response status:', res.status);
                        console.log('Response headers:', res.headers);

                        if (!res.ok) {
                            throw new Error(`HTTP error! status: ${res.status}`);
                        }

                        // Check if response is JSON
                        const contentType = res.headers.get('content-type');
                        if (contentType && contentType.includes('application/json')) {
                            return res.json();
                        } else {
                            // If not JSON, assume success (might be a redirect)
                            return {
                                success: true,
                                message: 'Memo berhasil dihapus'
                            };
                        }
                    })
                    .then(data => {
                        console.log('API Response:', data);

                        // Check for success in various possible formats
                        const isSuccess = data.success || data.status === 'success' || data.message?.includes(
                            'berhasil') || data.message?.includes('success');

                        if (isSuccess) {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: 'Memo berhasil dihapus secara permanen',
                                icon: 'success',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#31CE36'
                            }).then(() => window.location.reload());
                        } else {
                            Swal.fire({
                                title: 'Gagal!',
                                text: data.message || 'Gagal menghapus dokumen',
                                icon: 'error',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#d33'
                            });
                        }
                    })
                    .catch((error) => {
                        console.error('Delete error:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: 'Terjadi kesalahan saat menghapus dokumen',
                            icon: 'error',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#d33'
                        });
                    });
            }

            // === Bulk Action Checkbox ===
            const selectAll = document.getElementById("selectAll");
            const checkboxes = document.querySelectorAll(".selectItem");
            const bulkActionButtons = document.getElementById("bulkActionButtons");
            const bulkForm = document.getElementById("bulkForm");
            const bulkIds = document.getElementById("bulkIds");

            function toggleBulkButtons() {
                let anyChecked = document.querySelectorAll(".selectItem:checked").length > 0;
                if (anyChecked) {
                    bulkActionButtons.classList.remove("d-none");
                } else {
                    bulkActionButtons.classList.add("d-none");
                }
            }

            if (selectAll) {
                selectAll.addEventListener("change", function() {
                    checkboxes.forEach(cb => (cb.checked = selectAll.checked));
                    toggleBulkButtons();
                });
            }
            checkboxes.forEach(cb => cb.addEventListener("change", toggleBulkButtons));

            // === Bulk Restore/Delete ===
            document.getElementById("bulkRestoreBtn").addEventListener("click", function() {
                const selectedItems = document.querySelectorAll(".selectItem:checked");
                const selectedIds = Array.from(selectedItems).map(item => item.value);
                if (selectedItems.length === 0) {
                    Swal.fire({
                        title: 'Peringatan!',
                        text: 'Pilih dokumen yang akan dipulihkan terlebih dahulu',
                        icon: 'warning',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#ffc107'
                    });
                    return;
                }

                Swal.fire({
                    title: 'Pulihkan Dokumen?',
                    html: `Apakah Anda yakin ingin memulihkan <strong>${selectedItems.length}</strong> dokumen?<br><br>
                           <span style="color: #000;">Data akan dikembalikan ke menu utama</span>`,
                    icon: 'question',
                    showCancelButton: true,
                    reverseButtons: true,
                    confirmButtonText: '<i class="fa-solid fa-rotate-left me-1"></i>Pulihkan',
                    cancelButtonText: 'Batal',
                    customClass: {
                        actions: 'swal2-actions swal2-actions-custom',
                        confirmButton: 'btn btn-primary px-4 py-2',
                        cancelButton: 'btn btn-secondary px-4 py-2'
                    },
                    buttonsStyling: false
                }).then(result => {
                    if (result.isConfirmed) {
                        // submitBulkForm("{{ route('memo.bulk-restore') }}");

                        fetch(`{{ route('memo.bulk-restore') }}`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]').getAttribute(
                                        'content'),
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    selected_ids: selectedIds
                                })
                            }).then(res => {
                                console.log('Response status:', res.status);
                                console.log('Response headers:', res.headers);

                                if (!res.ok) {
                                    throw new Error(`HTTP error! status: ${res.status}`);
                                }

                                // Check if response is JSON
                                const contentType = res.headers.get('content-type');
                                if (contentType && contentType.includes('application/json')) {
                                    return res.json();
                                } else {
                                    // If not JSON, assume success (might be a redirect)
                                    return {
                                        success: true,
                                        message: 'Memo berhasil dipulihkan'
                                    };
                                }
                            })
                            .then(data => {
                                console.log('API Response:', data);

                                // Check for success in various possible formats
                                const isSuccess = data.success || data.status === 'success' ||
                                    data.message?.includes(
                                        'berhasil') || data.message?.includes('success');

                                if (isSuccess) {
                                    Swal.fire({
                                        title: 'Berhasil!',
                                        text: 'Semua memo berhasil dipulihkan ke menu utama',
                                        icon: 'success',
                                        confirmButtonText: 'OK',
                                        confirmButtonColor: '#31CE36'
                                    }).then(() => window.location.reload());
                                } else {
                                    Swal.fire({
                                        title: 'Gagal!',
                                        text: data.message ||
                                            'Gagal memulihkan dokumen',
                                        icon: 'error',
                                        confirmButtonText: 'OK',
                                        confirmButtonColor: '#d33'
                                    });
                                }
                            })
                            .catch((error) => {
                                console.error('Delete error:', error);
                                Swal.fire({
                                    title: 'Error!',
                                    text: 'Terjadi kesalahan saat memulihkan dokumen',
                                    icon: 'error',
                                    confirmButtonText: 'OK',
                                    confirmButtonColor: '#d33'
                                });
                            });
                    }
                });
            });
            document.getElementById("bulkDeleteBtn").addEventListener("click", function() {
                const selectedItems = document.querySelectorAll(".selectItem:checked");
                const selectedIds = Array.from(selectedItems).map(item => item.value);
                if (selectedItems.length === 0) {
                    Swal.fire({
                        title: 'Peringatan!',
                        text: 'Pilih dokumen yang akan dihapus terlebih dahulu',
                        icon: 'warning',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#ffc107'
                    });
                    return;
                }

                // First confirmation for bulk delete
                Swal.fire({
                    title: 'Hapus Dokumen?',
                    html: `Apakah Anda yakin ingin menghapus <strong>${selectedItems.length}</strong> dokumen secara permanen?<br><br>
                           <span style="color: #d33;"><i class="fa-solid fa-exclamation-triangle me-1"></i>Tindakan ini tidak dapat dibatalkan!</span>`,
                    icon: 'warning',
                    showCancelButton: true,
                    reverseButtons: true,
                    confirmButtonText: '<i class="fa-solid fa-trash me-1"></i>Hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#d33',
                    customClass: {
                        actions: 'swal2-actions swal2-actions-custom',
                        confirmButton: 'btn btn-danger px-4 py-2',
                        cancelButton: 'btn btn-secondary px-4 py-2'
                    },
                    buttonsStyling: false
                }).then(result => {
                    if (result.isConfirmed) {
                        // Second confirmation for bulk delete
                        Swal.fire({
                            title: 'Konfirmasi Kedua',
                            html: `Apakah Anda <strong>BENAR-BENAR YAKIN</strong> ingin menghapus <strong>${selectedItems.length}</strong> dokumen secara permanen?<br><br>
                                   <span style="color: #d33; font-weight: bold;"><i class="fa-solid fa-exclamation-triangle me-1"></i>Data akan dihapus selamanya dan tidak dapat dipulihkan!</span>`,
                            icon: 'error',
                            showCancelButton: true,
                            reverseButtons: true,
                            confirmButtonText: '<i class="fa-solid fa-trash me-1"></i>YA, HAPUS PERMANEN',
                            cancelButtonText: 'Batal',
                            confirmButtonColor: '#d33',
                            customClass: {
                                actions: 'swal2-actions swal2-actions-custom',
                                confirmButton: 'btn btn-danger px-4 py-2',
                                cancelButton: 'btn btn-secondary px-4 py-2'
                            },
                            buttonsStyling: false
                        }).then(secondResult => {
                            if (secondResult.isConfirmed) {
                                //submitBulkForm("{{ route('memo.bulk-force-delete') }}");
                                fetch(`{{ route('memo.bulk-force-delete') }}`, {
                                        method: 'DELETE',
                                        headers: {
                                            'X-CSRF-TOKEN': document.querySelector(
                                                    'meta[name="csrf-token"]')
                                                .getAttribute(
                                                    'content'),
                                            'Content-Type': 'application/json',
                                        },
                                        body: JSON.stringify({
                                            selected_ids: selectedIds
                                        })
                                    }).then(res => {
                                        console.log('Response status:', res.status);
                                        console.log('Response headers:', res.headers);

                                        if (!res.ok) {
                                            throw new Error(
                                                `HTTP error! status: ${res.status}`);
                                        }

                                        // Check if response is JSON
                                        const contentType = res.headers.get(
                                            'content-type');
                                        if (contentType && contentType.includes(
                                                'application/json')) {
                                            return res.json();
                                        } else {
                                            // If not JSON, assume success (might be a redirect)
                                            return {
                                                success: true,
                                                message: 'Memo berhasil dihapus'
                                            };
                                        }
                                    })
                                    .then(data => {
                                        console.log('API Response:', data);

                                        // Check for success in various possible formats
                                        const isSuccess = data.success || data
                                            .status === 'success' ||
                                            data.message?.includes(
                                                'berhasil') || data.message?.includes(
                                                'success');

                                        if (isSuccess) {
                                            Swal.fire({
                                                title: 'Berhasil!',
                                                text: 'Semua memo berhasil dihapus secara permanen',
                                                icon: 'success',
                                                confirmButtonText: 'OK',
                                                confirmButtonColor: '#31CE36'
                                            }).then(() => window.location.reload());
                                        } else {
                                            Swal.fire({
                                                title: 'Gagal!',
                                                text: data.message ||
                                                    'Gagal menghapus dokumen',
                                                icon: 'error',
                                                confirmButtonText: 'OK',
                                                confirmButtonColor: '#d33'
                                            });
                                        }
                                    })
                                    .catch((error) => {
                                        console.error('Delete error:', error);
                                        Swal.fire({
                                            title: 'Error!',
                                            text: 'Terjadi kesalahan saat menghapus dokumen',
                                            icon: 'error',
                                            confirmButtonText: 'OK',
                                            confirmButtonColor: '#d33'
                                        });
                                    });
                            }
                        });
                    }
                });
            });

            function submitBulkForm(actionUrl) {
                bulkForm.setAttribute("action", actionUrl);
                bulkIds.innerHTML = ""; // reset

                document.querySelectorAll(".selectItem:checked").forEach(cb => {
                    let input = document.createElement("input");
                    input.type = "hidden";
                    input.name = "selected_ids[]";
                    input.value = cb.value;
                    bulkIds.appendChild(input);
                });

                bulkForm.submit();
            }
        });
    </script>
    @if (session('success') === 'bulk delete success')
        <script>
            Swal.fire({
                title: 'Berhasil!',
                text: 'Semua memo berhasil dihapus secara permanen',
                icon: 'success',
                confirmButtonText: 'OK',
                confirmButtonColor: '#31CE36'
            });
        </script>
    @endif

    @if (session('error') === 'bulk delete failed')
        <script>
            Swal.fire({
                title: 'Gagal!',
                text: 'Gagal menghapus memo secara permanen',
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#d33'
            });
        </script>
    @endif

@endsection
