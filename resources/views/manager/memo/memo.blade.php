@extends('layouts.app')

@section('title', 'Memo')

@section('content')
    <div class="container-fluid px-4 py-0 mt-0">
        <div class="card shadow-sm border-0">
            <div class="card-body py-3">

                <h3 class="fw-bold mb-3">Memo</h3>

                {{-- Breadcrumb --}}
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="bg-white border rounded-2 px-3 py-2 w-100 d-flex align-items-center">
                            <a href="{{ route('manager.dashboard') }}" class="text-decoration-none text-primary">Beranda</a>
                            <span class="text-muted ms-1">/ Memo</span>
                        </div>
                    </div>
                </div>

                {{-- Row Filter --}}
                <form class="row g-2 align-items-center" method="GET" action="{{ route('memo.manager') }}">
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

                    {{-- Filter tanggal dibuat --}}
                    <div class="col-12 col-md-auto">
                        <input type="date" class="form-control rounded-3" name="tgl_dibuat_awal"
                            value="{{ request('tgl_dibuat_awal') }}">
                    </div>

                    <div class="col-auto d-none d-md-flex align-items-center">
                        <span class="mx-1">→</span>
                    </div>

                    <div class="col-12 col-md-auto">
                        <input type="date" class="form-control rounded-3" name="tgl_dibuat_akhir"
                            value="{{ request('tgl_dibuat_akhir') }}">
                    </div>

                    {{-- Pencarian --}}
                    <div class="col-12 col-md">
                        <div class="input-group">
                            <span class="input-group-text rounded-start-3"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control rounded-end-3" name="search"
                                value="{{ request('search') }}" placeholder="Cari Memo">
                        </div>
                    </div>

                    {{-- Divisi --}}
                    <div class="col-12 col-md-auto">
                        <select class="form-select rounded-3" name="kode" onchange="this.form.submit()">
                            <option value="" {{ !request()->filled('kode') ? 'selected' : '' }}>Semua Divisi</option>
                            @foreach ($kode ?? collect() as $k)
                                <option value="{{ $k }}" {{ request('kode') == $k ? 'selected' : '' }}>
                                    {{ $k }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-auto">
                        <button type="submit" class="btn btn-primary rounded-3">
                            <i class="fas fa-filter me-1"></i>Filter
                        </button>
                        <a href="{{ route('memo-manager/add2') }}" class="btn rounded-3 text-white"
                            style="background-color:#1E4178;">
                            <i class="fas fa-plus me-1"></i>Tambah Memo
                        </a>
                    </div>
                </form>

                {{-- Table --}}
                <div class="table-responsive mt-3">
                    <table class="table table-bordered custom-table-bagian">
                        <thead>
                            <tr>
                                <th class="text-center" style="width:5%;">No</th>
                                <th class="text-center" style="width:20%;">Judul Memo</th>
                                <th class="text-center" style="width:12%;">Tanggal Memo</th>
                                <th class="text-center" style="width:20%;">Nomor Memo</th>
                                <th class="text-center" style="width:12%;">Tanggal Disahkan</th>
                                <th class="text-center" style="width:10%;">Divisi</th>
                                <th class="text-center" style="width:8%;">Jenis</th>
                                <th class="text-center" style="width:8%;">Status</th>
                                <th class="text-center" style="width:8%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($memos as $index => $kirim)
                                <tr>
                                    <td class="nomor">{{ ($memos->firstItem() ?? 0) + $index }}</td>
                                    @if (Auth::user()->divisi_id_divisi == $kirim->memo->divisi_id_divisi)
                                        <td class="nama-dokumen
                        {{ $kirim->memo->status == 'reject' ? 'text-danger' : ($kirim->memo->status == 'correction' ? 'text-warning' : ($kirim->memo->status == 'approve' ? 'text-success' : '')) }}"
                                            style="{{ $kirim->memo->status == 'pending' ? 'color: #0dcaf0;' : '' }}">
                                            {{ Str::limit($kirim->memo->judul, 35, '...') }}
                                        </td>
                                    @else
                                        <td class="nama-dokumen {{ $kirim->status == 'reject' || $kirim->status == 'correction' ? 'text-danger' : ($kirim->status == 'pending' ? '' : 'text-success') }}"
                                            style="{{ $kirim->status == 'pending' ? 'color: #0dcaf0;' : '' }}">
                                            {{ Str::limit($kirim->memo->judul, 35, '...') }}
                                        </td>
                                    @endif

                                    <!-- <td>{{ $kirim->memo->tgl_dibuat }}</td> -->
                                    <td>{{ $kirim->memo->tgl_dibuat ? \Carbon\Carbon::parse($kirim->memo->tgl_dibuat)->format('d-m-Y') : '-' }}
                                    </td>
                                    <td>{{ $kirim->memo->nomor_memo }}</td>
                                    <td>{{ $kirim->memo->tgl_disahkan ? \Carbon\Carbon::parse($kirim->memo->tgl_disahkan)->format('d-m-Y') : '-' }}
                                    </td>
                                    <td class="text-center">{{ $kirim->memo->kode ?? '-' }}</td>
                                    <td>
                                        @if ($kirim->jenis == 'masuk')
                                            <span class="badge px-3 py-2" style="background-color:#35A29F;">
                                                Memo Masuk
                                            </span>
                                        @elseif ($kirim->jenis == 'keluar')
                                            <span class="badge px-3 py-2" style="background-color:#3048d0;">
                                                Memo Keluar
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if (Auth::user()->divisi_id_divisi == $kirim->memo->divisi_id_divisi)
                                            @if ($kirim->memo->status == 'reject')
                                                <span class="badge bg-danger">Ditolak</span>
                                            @elseif ($kirim->memo->status == 'pending')
                                                <span class="badge bg-info">Diproses</span>
                                            @elseif ($kirim->memo->status == 'correction')
                                                <span class="badge bg-warning">Dikoreksi</span>
                                            @else
                                                <span class="badge bg-success">Diterima</span>
                                            @endif
                                        @else
                                            @if ($kirim->status == 'reject')
                                                <span class="badge bg-danger">Ditolak</span>
                                            @elseif ($kirim->status == 'pending')
                                                <span class="badge bg-info">Diproses</span>
                                            @elseif ($kirim->status == 'correction')
                                                <span class="badge bg-warning">Dikoreksi</span>
                                            @else
                                                <span class="badge bg-success">Diterima</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-2">
                                            <button title="Detail"
                                                class="btn btn-sm rounded-circle text-white border-0 bg-info"
                                                style="width:30px; height:30px; display:flex; align-items:center; justify-content:center;"
                                                onclick="window.location.href='{{ route('view.memo-terkirim', ['id' => $kirim->memo->id_memo]) }}'">
                                                <i class="fas fa-eye" alt="Detail"></i>
                                            </button>
                                            @if ($kirim->memo->status == 'approve' || $kirim->memo->status == 'reject')
                                                {{-- Button Arsip untuk status approve reject --}}
                                                <button type="button"
                                                    class="btn btn-sm rounded-circle text-white border-0"
                                                    style="background-color:#FFAD46; width:30px; height:30px; display:flex; align-items:center; justify-content:center;"
                                                    onclick="showArsipConfirmation({{ $kirim->memo->id_memo }}, '{{ $kirim->memo->judul ?? $kirim->memo->nama_dokumen }}')"
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
                    cancelButton: 'btn btn-secondary px-4 py-2'
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
                            text: 'Memo berhasil diarsipkan',
                            icon: 'success',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#31CE36'
                        }).then(() => window.location.reload());
                    } else {
                        showNotification('Gagal mengarsipkan memo', 'error');
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
