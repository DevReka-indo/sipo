@extends('layouts.app')

@section('title', 'Detail Risalah Rapat')

@section('content')
    <div class="container-fluid px-4 py-0 mt-0">
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-body py-3">
                <h3 class="fw-bold mb-3">Detail Risalah Rapat</h3>

                {{-- Breadcrumb --}}
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="bg-white border rounded-2 px-3 py-2 w-100 d-flex align-items-center">
                            <a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-primary">Beranda</a>
                            <span class="mx-2 text-muted">/</span>
                            <a href="{{ route('admin.risalah.index') }}" class="text-decoration-none text-primary">Risalah
                                Rapat</a>
                            <span class="mx-2 text-muted">/</span>
                            <span class="text-muted">Detail Risalah Rapat</span>
                        </div>
                    </div>
                </div>

                {{-- Row 1: Informasi Nomor Seri & Detail --}}
                <div class="row">
                    {{-- Kolom kiri --}}
                    <div class="col-md-6 mb-3">
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-header py-2 rounded-top-3"
                                style="background:#e3f2fd;border-bottom:1px solid #bbdefb;">
                                <i class="fa fa-file-alt me-2 text-primary"></i>
                                <span class="fw-semibold">Informasi Risalah</span>
                            </div>
                            <div class="card-body">
                                <div class="info-row d-flex flex-column flex-sm-row">
                                    <div class="info-label">No Dokumen</div>
                                    <div class="info-value">{{ $risalah->nomor_risalah ?? '-' }}</div>
                                </div>
                                {{-- <div class="info-row d-flex flex-column flex-sm-row">
                                    <div class="info-label">Seri Tahunan Surat</div>
                                    <div class="info-value">{{ $risalah->seri_surat ?? '-' }}</div>
                                </div> --}}
                                <div class="info-row d-flex flex-column flex-sm-row">
                                    <div class="info-label">Perihal</div>
                                    <div class="info-value">{{ $risalah->judul ?? '-' }}</div>
                                </div>
                                <div class="info-row d-flex flex-column flex-sm-row">
                                    <div class="info-label">Hari, Tanggal</div>
                                    <div class="info-value">
                                        @if (!empty($risalah->tgl_dibuat))
                                            {{ \Carbon\Carbon::parse($risalah->tgl_dibuat)->translatedFormat('l, d F Y') }}
                                        @else
                                            -
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Kolom kanan --}}
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
                                        {{ optional($risalah->user)->firstname ? $risalah->user->firstname . ' ' . $risalah->user->lastname : 'N/A' }}
                                    </div>
                                </div>

                                <div class="info-row d-flex flex-column flex-sm-row">
                                    <div class="info-label">Status</div>
                                    <div class="info-value">
                                        @switch($risalah->status)
                                            @case('reject')
                                                <span class="badge bg-danger px-3 py-2">Ditolak</span>
                                            @break

                                            @case('pending')
                                                <span class="badge bg-info px-3 py-2">Diproses</span>
                                            @break

                                            @case('correction')
                                                <span class="badge bg-warning px-3 py-2">Dikoreksi</span>
                                            @break

                                            @default
                                                <span class="badge bg-success px-3 py-2">Diterima</span>
                                        @endswitch
                                    </div>
                                </div>

                                <div class="info-row d-flex flex-column flex-sm-row">
                                    <div class="info-label">Pengesahan</div>
                                    <div class="info-value">
                                        {{ $risalah->tgl_disahkan ? \Carbon\Carbon::parse($risalah->tgl_disahkan)->format('d-m-Y') : '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Row 2: Informasi Detail Risalah --}}
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-header py-2 rounded-top-3"
                                style="background:#e3f2fd;border-bottom:1px solid #bbdefb;">
                                <i class="fas fa-calendar-alt me-2 text-primary"></i>
                                <span class="fw-semibold">Informasi Detail Risalah</span>
                            </div>
                            <div class="card-body">
                                <div class="info-row d-flex flex-column flex-sm-row">
                                    <div class="info-label">No Dokumen</div>
                                    <div class="info-value">{{ $risalah->nomor_risalah ?? '-' }}</div>
                                </div>

                                <div class="info-row d-flex flex-column flex-sm-row">
                                    <div class="info-label">Pengirim</div>
                                    <div class="info-value">
                                        {{ optional(optional($risalah->user)->department)->kode_department ??
                                            (optional(optional($risalah->user)->divisi)->kode_divisi ?? '-') }}
                                    </div>
                                </div>

                                <div class="info-row d-flex flex-column flex-sm-row">
                                    <div class="info-label">Perihal</div>
                                    <div class="info-value">{{ $risalah->judul ?? '-' }}</div>
                                </div>

                                <div class="info-row d-flex flex-column flex-sm-row">
                                    <div class="info-label">Tanggal Risalah</div>
                                    <div class="info-value">
                                        @if (!empty($risalah->tgl_dibuat))
                                            {{ \Carbon\Carbon::parse($risalah->tgl_dibuat)->translatedFormat('l, d F Y') }}
                                        @else
                                            -
                                        @endif
                                    </div>
                                </div>

                                <div class="info-row d-flex flex-column flex-sm-row">
                                    <div class="info-label">File</div>
                                    <div class="info-value">
                                        <a onclick="window.location.href='{{ route('view-risalahPDF', $risalah->id_risalah) }}'"
                                            class="btn btn-sm btn-custom me-2 rounded-2"
                                            title="Lihat surat dan lampiran dalam format PDF">
                                            <i class="fa fa-eye me-1"></i> Lihat
                                        </a>

                                        <a onclick="window.location.href='{{ route('cetakrisalah', ['id' => $risalah->id_risalah]) }}'"
                                            class="btn btn-sm btn-custom rounded-2"
                                            title="Unduh surat dan lampiran dalam format PDF">
                                            <i class="fa fa-download me-1"></i> Unduh
                                        </a>

                                    </div>
                                </div>
                                @if ($lampiranData)
                                    <div class="info-row d-flex flex-column flex-sm-row">
                                        <div class="info-label">Lampiran</div>
                                        <div class="info-value w-100">
                                            <div class="d-flex align-items-center justify-content-between mb-3">
                                                <span class="fw-semibold">Daftar Lampiran</span>
                                                <a href="{{ route('download-semua-lampiran-risalah', $risalah->id_risalah) }}"
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
                @if ($risalah->status != 'approve' && $risalah->catatan)
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card border-0 shadow-sm rounded-3">
                                <div class="card-header py-2 rounded-top-3"
                                    style="background:#fff3cd;border-bottom:1px solid #ffeeba;">
                                    <i class="fa fa-sticky-note me-2 text-warning"></i>
                                    <span class="fw-semibold">Catatan</span>
                                </div>
                                <div class="card-body">
                                    <textarea class="form-control" rows="4" readonly>{{ $risalah->catatan }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                {{-- Row 3: Daftar Tujuan --}}
                @if ($tujuanUsernames)
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
                                            <pre style="font-family: Public Sans, sans-serif">{{ $tujuanUsernames ?? ($risalah->tujuan ?? '-') }}</pre>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                @endif

                {{-- Form Approval (tampil jika pending/reject/correction) --}}
                @if (in_array($risalah->status, ['pending']) && $risalah->nama_pemimpin_acara == Auth::user()->fullname)
                    <form id="approvalForm" method="POST"
                        action="{{ route('risalah.updateStatus', $risalah->id_risalah) }}">
                        @csrf
                        @method('PUT')

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

                            {{-- Catatan (muncul jika reject/correction) --}}
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
                                            placeholder="Berikan Catatan">{{ old('catatan', $risalah->catatan) }}</textarea>
                                        <small id="catatanError" class="text-danger mt-1" style="display:none;">Catatan
                                            wajib diisi</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Konfirmasi daftar penerima () --}}
                        {{-- <div class="row mb-4" id="tujuanDivisiRow" style="display:none;">
                            <div class="col-md-12 mb-3">
                                <div class="card border-0 shadow-sm rounded-3">
                                    <div class="card-header py-2 rounded-top-3"
                                        style="background:#e3f2fd;border-bottom:1px solid #bbdefb;">
                                        <i class="fas fa-id-card me-2 text-primary"></i>
                                        <span class="fw-semibold">Konfirmasi Daftar Penerima</span>
                                        <label class="ms-1" style="color:#FF000080;font-size:10px;">
                                            *Berikut adalah daftar tujuan yang akan menerima risalah.
                                        </label>
                                    </div>
                                    <div class="card-body">
                                        <div class="info-row d-flex flex-column flex-sm-row">
                                            <div class="info-label">Kepada</div>
                                            <div class="info-value">
                                                <pre style="font-family: Public Sans, sans-serif">{{ $tujuanUsernames ?? ($risalah->tujuan ?? '-') }}</pre>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> --}}

                        {{-- ACTION --}}
                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('admin.risalah.index') }}" class="btn rounded-3"
                                style="background:#fff;color:#0d6efd;border:1px solid #0d6efd;">Batal</a>
                            <button type="button" class="btn btn-primary rounded-3" id="submitBtn">Kirim</button>
                        </div>
                    </form>
                @endif

                {{-- errors --}}
                @if ($errors->any())
                    <div class="alert alert-danger mt-3">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

            </div>
        </div>
    </div>



@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const radios = document.querySelectorAll('.approval-checkbox');
            const catatanCol = document.getElementById('catatanCol');
            const catatanInput = document.getElementById('catatan');
            const tujuanDivisiRow = document.getElementById('tujuanDivisiRow');
            const submitBtn = document.getElementById('submitBtn');
            const pengesahanCol = document.getElementById('pengesahanCol');
            const risalahId = @json($risalah->id_risalah); // penting utk key localStorage
            let statusValue = null;

            // === Toggle area catatan/tujuan ===
            radios.forEach(r => {
                r.addEventListener('change', function() {
                    statusValue = this.value;

                    if (statusValue === 'approve') {
                        catatanCol.style.display = 'none';
                        tujuanDivisiRow.style.display = 'flex';
                        if (catatanInput) catatanInput.required = false;
                        pengesahanCol.className = 'col-md-12';
                    } else if (statusValue === 'reject' || statusValue === 'correction') {
                        catatanCol.style.display = 'block';
                        tujuanDivisiRow.style.display = 'none';
                        if (catatanInput) catatanInput.required = true;
                        pengesahanCol.className = 'col-md-6';
                    } else {
                        catatanCol.style.display = 'none';
                        tujuanDivisiRow.style.display = 'none';
                        if (catatanInput) catatanInput.required = false;
                        pengesahanCol.className = 'col-md-12';
                    }
                });
            });

            // === Validasi & submit ===
            if (submitBtn) {
                submitBtn.addEventListener('click', function() {
                    if (!statusValue) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Peringatan',
                            text: 'Pilih status pengesahan terlebih dahulu!'
                        });
                        return;
                    }
                    if ((statusValue === 'reject' || statusValue === 'correction') && catatanInput &&
                        catatanInput.value.trim() === '') {
                        document.getElementById('catatanError').style.display = 'block';
                        catatanInput.focus();
                        return;
                    }

                    localStorage.setItem(`risalah-status-${risalahId}`, statusValue);

                    document.getElementById('approvalForm').submit();
                });
            }

            const successMessage = @json(session('success'));
            const errorMessage = @json(session('error'));
            const hasErrors = @json($errors->any());

            if (hasErrors) {
                localStorage.removeItem(`risalah-status-${risalahId}`);
            }

            if (errorMessage) {
                Swal.fire({
                    icon: 'error',
                    title: 'Ditolak',
                    text: errorMessage,
                    confirmButtonText: 'OK'
                });
                localStorage.removeItem(`risalah-status-${risalahId}`);
                return;
            }

            if (successMessage) {
                const last = localStorage.getItem(`risalah-status-${risalahId}`);
                let cfg;

                if (last === 'approve') {
                    cfg = {
                        icon: 'success',
                        title: 'Sukses',
                        text: 'Risalah berhasil disahkan.'
                    };
                } else if (last === 'reject') {
                    cfg = {
                        icon: 'error',
                        title: 'Ditolak',
                        text: 'Risalah ditolak.'
                    };
                } else if (last === 'correction') {
                    cfg = {
                        icon: 'warning',
                        title: 'Perlu Koreksi',
                        text: 'Risalah memerlukan koreksi.'
                    };
                } else {
                    cfg = {
                        icon: 'success',
                        title: 'Berhasil',
                        text: successMessage
                    };
                }

                Swal.fire({
                    ...cfg,
                    confirmButtonText: 'Kembali ke Halaman Risalah',
                    confirmButtonColor: '#1E4178',
                    allowOutsideClick: false
                }).then(() => {
                    window.location.href = "{{ route('admin.risalah.index') }}";
                });

                localStorage.removeItem(`risalah-status-${risalahId}`);
            }
        });
    </script>
@endpush
