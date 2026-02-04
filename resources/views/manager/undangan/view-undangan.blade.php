@extends('layouts.app')

@section('title', 'Detail Undangan Rapat')

@section('content')
    <div class="container-fluid px-4 py-0 mt-0">
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-body py-3">
                <h3 class="fw-bold mb-3">Detail Undangan Rapat</h3>

                {{-- Breadcrumb --}}
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="bg-white border rounded-2 px-3 py-2 w-100 d-flex align-items-center">
                            <a href="{{ route('manager.dashboard') }}" class="text-decoration-none text-primary">Beranda</a>
                            <span class="mx-2 text-muted">/</span>
                            <a href="{{ route('undangan.manager') }}" class="text-decoration-none text-primary">Undangan
                                Rapat</a>
                            <span class="mx-2 text-muted">/</span>
                            <span class="text-muted">Detail Undangan Rapat</span>
                        </div>
                    </div>
                </div>

                {{-- Row 1: Informasi Nomor Seri & Detail --}}
                <div class="row">
                    {{-- Kolom kiri: Informasi Nomor Seri --}}
                    <div class="col-md-6 mb-3">
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-header py-2 rounded-top-3"
                                style="background:#e3f2fd;border-bottom:1px solid #bbdefb;">
                                <i class="fa fa-file-alt me-2 text-primary"></i>
                                <span class="fw-semibold">Informasi Nomor Seri</span>
                            </div>
                            <div class="card-body">
                                <div class="info-row d-flex flex-column flex-sm-row">
                                    <div class="info-label">No Surat</div>
                                    <div class="info-value">{{ $undangan->nomor_undangan }}</div>
                                </div>

                                {{-- <div class="info-row d-flex flex-column flex-sm-row">
                                    <div class="info-label">Seri Tahunan Surat</div>
                                    <div class="info-value">{{ $undangan->seri_surat }}</div>
                                </div> --}}

                                <div class="info-row d-flex flex-column flex-sm-row">
                                    <div class="info-label">Perihal</div>
                                    <div class="info-value">{{ $undangan->judul }}</div>
                                </div>

                                <div class="info-row d-flex flex-column flex-sm-row">
                                    <div class="info-label">Hari, Tanggal</div>
                                    <div class="info-value">
                                        {{ \Carbon\Carbon::parse($undangan->tgl_dibuat)->translatedFormat('l, d F Y') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Kolom kanan: Detail --}}
                    <div class="col-md-6 mb-3">
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-header py-2 rounded-top-3"
                                style="background:#e3f2fd;border-bottom:1px solid #bbdefb;">
                                <i class="fa fa-user me-2 text-primary"></i>
                                <span class="fw-semibold">Detail</span>
                            </div>
                            <div class="card-body">
                                <div class="info-row d-flex flex-column flex-sm-row">
                                    <div class="info-label">Pembuat</div>
                                    <div class="info-value">
                                        {{ $undangan->user ? $undangan->user->firstname . ' ' . $undangan->user->lastname : 'N/A' }}
                                    </div>
                                </div>

                                <div class="info-row d-flex flex-column flex-sm-row">
                                    <div class="info-label">Status</div>
                                    <div class="info-value">
                                        @if ($undangan->status == 'reject')
                                            <span class="badge bg-danger px-3 py-2">Ditolak</span>
                                        @elseif ($undangan->status == 'pending')
                                            <span class="badge bg-info px-3 py-2">Diproses</span>
                                        @elseif ($undangan->status == 'correction')
                                            <span class="badge bg-warning px-3 py-2">Dikoreksi</span>
                                        @else
                                            <span class="badge bg-success px-3 py-2">Diterima</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="info-row d-flex flex-column flex-sm-row">
                                    <div class="info-label">Pengesahan</div>
                                    <div class="info-value">
                                        {{ $undangan->tgl_disahkan ? \Carbon\Carbon::parse($undangan->tgl_disahkan)->format('d-m-Y') : '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Row 2: Informasi Detail Undangan Rapat --}}
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-header py-2 rounded-top-3"
                                style="background:#e3f2fd;border-bottom:1px solid #bbdefb;">
                                <i class="fas fa-calendar-alt me-2 text-primary"></i>
                                <span class="fw-semibold">Informasi Detail Undangan Rapat</span>
                            </div>
                            <div class="card-body">
                                <div class="info-row d-flex flex-column flex-sm-row">
                                    <div class="info-label">No Surat</div>
                                    <div class="info-value">
                                        {{ $undangan->nomor_undangan }}
                                    </div>
                                </div>

                                <div class="info-row d-flex flex-column flex-sm-row">
                                    <div class="info-label">Pengirim</div>
                                    <div class="info-value">
                                        {{ $undangan->kode }}
                                    </div>
                                </div>

                                <div class="info-row d-flex flex-column flex-sm-row">
                                    <div class="info-label">Perihal</div>
                                    <div class="info-value">
                                        {{ $undangan->judul }}
                                    </div>
                                </div>

                                <div class="info-row d-flex flex-column flex-sm-row">
                                    <div class="info-label">Hari,Tanggal</div>
                                    <div class="info-value">
                                        {{ \Carbon\Carbon::parse($undangan->tgl_rapat)->translatedFormat('l, d F Y') }}
                                    </div>
                                </div>

                                <div class="info-row d-flex flex-column flex-sm-row">
                                    <div class="info-label">File</div>
                                    <div class="info-value">
                                        <a onclick="window.location.href='{{ route('view-undanganPDF', $undangan->id_undangan) }}'"
                                            class="btn btn-sm btn-custom me-2 rounded-2">
                                            <i class="fa fa-eye me-1"></i> Lihat
                                        </a>
                                        @if ($undangan->status == 'approve')
                                            <a onclick="window.location.href='{{ route('cetakundangan', ['id' => $undangan->id_undangan]) }}'"
                                                class="btn btn-sm btn-custom rounded-2">
                                                <i class="fa fa-download me-1"></i> Unduh
                                            </a>
                                        @endif
                                    </div>
                                </div>
                                @if ($lampiranData)
                                    <div class="info-row d-flex flex-column flex-sm-row">
                                        <div class="info-label">Lampiran</div>
                                        <div class="info-value w-100">
                                            <div class="d-flex align-items-center justify-content-between mb-3">
                                                <span class="fw-semibold">Daftar Lampiran</span>
                                                <a href="{{ route('download-semua-lampiran-undangan', $undangan->id_undangan) }}"
                                                    class="btn btn-sm btn-success rounded-2">
                                                    <i class="fas fa-download me-1"></i> Unduh Semua
                                                </a>
                                            </div>
                                            <div class="row">
                                                @foreach ($lampiranData as $index => $lampiran)
                                                    <div class="col-md-12">
                                                        <div class="border rounded p-2">
                                                            <div class="d-flex align-items-center justify-content-between">
                                                                <div class="flex-grow-1">
                                                                    <small class="text-truncate d-block"
                                                                        title="{{ $lampiran['name'] ?? 'File Lampiran' }}">
                                                                        <i class="fas fa-file text-primary me-1"></i>
                                                                        {{ Str::limit($lampiran['name'], 32, '...') ?? 'File Lampiran ' . ($index + 1) }}
                                                                    </small>

                                                                </div>
                                                                <div class="ms-2">
                                                                    @if (isset($lampiran['path']) && file_exists(storage_path('app/public/' . $lampiran['path'])))
                                                                        <a href="{{ asset('storage/' . $lampiran['path']) }}"
                                                                            download="{{ $lampiran['name'] ?? 'file' }}"
                                                                            class="btn btn-sm btn-outline-success me-1"
                                                                            title="Download">
                                                                            <i class="fas fa-download"></i>
                                                                        </a>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Row 3: Daftar Tujuan --}}
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-header py-2 rounded-top-3"
                                style="background:#e3f2fd;border-bottom:1px solid #bbdefb;">
                                <i class="fas fa-id-card me-2 text-primary"></i>
                                <span class="fw-semibold">Daftar Tujuan</span>
                            </div>
                            <div class="card-body">
                                <div class="info-row d-flex flex-column flex-sm-row">
                                    <div class="info-label">Kepada</div>
                                    <div class="info-value">
                                        <pre style="font-family: Public Sans, sans-serif">{{ $undangan->tujuan }}</pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Catatan --}}
                @if ($undangan->status != 'approve' && $undangan->catatan)
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card border-0 shadow-sm rounded-3">
                                <div class="card-header py-2 rounded-top-3"
                                    style="background:#fff3cd;border-bottom:1px solid #ffeeba;">
                                    <i class="fa fa-sticky-note me-2 text-warning"></i>
                                    <span class="fw-semibold">Catatan</span>
                                </div>
                                <div class="card-body">
                                    <textarea class="form-control" rows="4" readonly>{{ $undangan->catatan }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                {{-- Form Approval (hanya tampil jika status pending atau correction) --}}
                @if ($undangan->status === 'pending' && $undangan->nama_bertandatangan == Auth::user()->fullname)
                    <form id="approvalForm" method="POST"
                        action="{{ route('undangan.updateStatus', $undangan->id_undangan) }}">
                        @csrf
                        @method('PUT')

                        {{-- Row 4: Pengesahan dan Catatan --}}
                        <div class="row mb-4">
                            <div class="col-md-12" id="pengesahanCol">
                                <div class="card border-0 shadow-sm rounded-3 h-100">
                                    <div class="card-header py-2 rounded-top-3"
                                        style="background:#e3f2fd;border-bottom:1px solid #bbdefb;">
                                        <i class="fas fa-signature text-primary me-1"></i>
                                        <span class="fw-semibold">Pengesahan</span>
                                        <span style="color: red; font-size: 12px;">*</span>
                                    </div>
                                    <div class="card-body d-flex align-items-center justify-content-center">
                                        <div class="d-flex gap-4">
                                            <div class="form-check">
                                                <input type="radio" class="form-check-input approval-checkbox"
                                                    id="approve" name="status" value="approve">
                                                <label class="form-check-label" for="approve">Diterima</label>
                                            </div>

                                            <div class="form-check">
                                                <input type="radio" class="form-check-input approval-checkbox"
                                                    id="reject" name="status" value="reject">
                                                <label class="form-check-label" for="reject">Ditolak</label>
                                            </div>

                                            <div class="form-check">
                                                <input type="radio" class="form-check-input approval-checkbox"
                                                    id="correction" name="status" value="correction">
                                                <label class="form-check-label" for="correction">Dikoreksi</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6" id="catatanCol" style="display:none;">
                                <div class="card border-0 shadow-sm rounded-3 h-100">
                                    <div class="card-header py-2 rounded-top-3"
                                        style="background:#fff3cd;border-bottom:1px solid #ffeeba;">
                                        <i class="fa fa-sticky-note me-2 text-warning"></i>
                                        <span class="fw-semibold">Catatan</span>
                                        <span style="color: red; font-size: 12px;">*</span>
                                    </div>
                                    <div class="card-body d-flex flex-column">
                                        <textarea id="catatan" name="catatan" class="form-control flex-grow-1" rows="4"
                                            placeholder="Berikan Catatan"></textarea>
                                        <small id="catatanError" class="text-danger mt-1" style="display:none;">Catatan
                                            wajib diisi</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Row 5: Konfirmasi Daftar Penerima (hanya tampil saat approve) --}}
                        <div class="row mb-4" id="tujuanDivisiRow" style="display:none;">
                            <div class="col-md-12 mb-3">
                                <div class="card border-0 shadow-sm rounded-3">
                                    <div class="card-header py-2 rounded-top-3"
                                        style="background:#e3f2fd;border-bottom:1px solid #bbdefb;">
                                        <i class="fas fa-id-card me-2 text-primary"></i>
                                        <span class="fw-semibold">Konfirmasi Daftar Penerima</span>
                                        <label for="isi"
                                            style="color: #FF000080; font-size: 10px; margin-left: 5px;">
                                            *Berikut adalah daftar divisi tujuan yang akan menerima undangan.
                                        </label>
                                    </div>
                                    <div class="card-body">
                                        <div class="info-row d-flex flex-column flex-sm-row">
                                            <div class="info-label">Kepada</div>
                                            <div class="info-value">
                                                <pre style="font-family: Public Sans, sans-serif">{{ $undangan->tujuan }}</pre>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ACTION --}}
                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('undangan.terkirim') }}" class="btn rounded-3"
                                style="background:#fff;color:#0d6efd;border:1px solid #0d6efd;">Batal</a>
                            <button type="button" class="btn btn-primary rounded-3" id="submitBtn">Kirim</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('.approval-checkbox');
            const catatanCol = document.getElementById('catatanCol');
            const catatanInput = document.getElementById('catatan');
            const tujuanDivisiRow = document.getElementById('tujuanDivisiRow');
            const submitBtn = document.getElementById('submitBtn');
            let statusValue = null;

            // Radio button logic
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    checkboxes.forEach(cb => {
                        if (cb !== this) cb.checked = false;
                    });
                    statusValue = this.value;
                    const pengesahanCol = document.getElementById('pengesahanCol');

                    if (statusValue === 'approve') {
                        catatanCol.style.display = 'none';
                        catatanInput.required = false;
                        tujuanDivisiRow.style.display = 'flex';
                        // Pengesahan tetap full width untuk approve
                        pengesahanCol.className = 'col-md-12';
                    } else if (statusValue === 'reject' || statusValue === 'correction') {
                        catatanCol.style.display = 'block';
                        catatanInput.required = true;
                        tujuanDivisiRow.style.display = 'none';
                        // Pengesahan menjadi setengah width untuk reject/correction
                        pengesahanCol.className = 'col-md-6';
                    } else {
                        catatanCol.style.display = 'none';
                        catatanInput.required = false;
                        tujuanDivisiRow.style.display = 'none';
                        pengesahanCol.className = 'col-md-12';
                    }
                });
            });

            // Submit button logic
            if (submitBtn) {
                submitBtn.addEventListener('click', function() {
                    if (!statusValue) {

                        alert('Pilih status pengesahan terlebih dahulu!');
                        return;
                    }
                    // Untuk approve, tetap submit dan tampilkan modal sukses (biarkan reload)
                    if (statusValue === 'approve') {
                        document.getElementById('approvalForm').submit();
                        showNotification('Pengesahan undangan rapat berhasil', 'success');
                        setTimeout(function() {
                            window.location.href = "{{ route('undangan.terkirim') }}";
                        }, 1000);
                    } else {
                        // Validasi catatan untuk reject/correction
                        if ((statusValue === 'reject' || statusValue === 'correction') && catatanInput.value
                            .trim() === '') {
                            document.getElementById('catatanError').style.display = 'block';
                            catatanInput.focus();
                            return; // stop proses submit & redirect
                        }
                        showNotification('Penolakan undangan rapat berhasil', 'success');
                        // Kalau lolos validasi, submit lalu redirect
                        document.getElementById('approvalForm').submit();
                        setTimeout(function() {
                            window.location.href = "{{ route('undangan.terkirim') }}";
                        }, 1000);
                    }
                });
            }

            // Note: Success messages are handled by the respective action pages, not here
        });

        function showNotification(message, type) {
            // Implement sesuai dengan library notifikasi yang digunakan
            // Contoh menggunakan SweetAlert atau library lain
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: type === 'success' ? 'Berhasil!' : 'Error!',
                    text: message,
                    icon: type,
                    showConfirmButton: false
                });
            } else {
                // Fallback alert
                alert(message);
            }
        }
    </script>

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const diterimaCheckbox = document.getElementById('approve');
            const tindakLanjutSelect = document.getElementById('nextAction');
            const formPengiriman = document.getElementById('formPengiriman');

            function togglePengiriman() {
                if (diterimaCheckbox && tindakLanjutSelect && formPengiriman) {
                    if (diterimaCheckbox.checked && tindakLanjutSelect.value === 'dilanjutkan') {
                        formPengiriman.style.display = 'block';
                    } else {
                        formPengiriman.style.display = 'none';
                        const posisiPenerima = document.getElementById('posisi_penerima');
                        const divisiPenerima = document.getElementById('divisi_penerima');
                        if (posisiPenerima) posisiPenerima.value = '';
                        if (divisiPenerima) divisiPenerima.value = '';
                    }
                }
            }

            if (diterimaCheckbox) diterimaCheckbox.addEventListener('change', togglePengiriman);
            if (tindakLanjutSelect) tindakLanjutSelect.addEventListener('change', togglePengiriman);

            const formPengiriman = document.getElementById('formPengiriman');
            if (formPengiriman) {
                formPengiriman.style.display = 'none';
            }
        });
    </script>
@endsection
