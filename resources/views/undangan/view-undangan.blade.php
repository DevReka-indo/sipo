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
                            <a href="{{ route('dashboard') }}" class="text-decoration-none text-primary">Beranda</a>
                            <span class="mx-2 text-muted">/</span>
                            <a href="{{ route('undangan.terkirim') }}" class="text-decoration-none text-primary">Undangan
                                Rapat</a>
                            <span class="mx-2 text-muted">/</span>
                            <span class="text-muted">Detail Undangan Rapat</span>
                        </div>
                    </div>
                </div>

                <div class="row ">
                    {{-- Kolom kiri: Informasi Detail Memo --}}
                    <div class="col-md-6 mb-3">
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-header py-2 rounded-top-3"
                                style="background:#e3f2fd;border-bottom:1px solid #bbdefb;">
                                <i class="fa fa-file-alt me-2 text-primary"></i>
                                <span class="fw-semibold">Informasi Detail Undangan rapat</span>
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
                                        {{ \Carbon\Carbon::parse($undangan->tgl_rapat)->translatedFormat('l, d F Y') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Kolom kanan: Kepada --}}
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
                                    <div class="info-label">File</div>
                                    <div class="info-value">
                                        <a href="{{ route('view-undanganPDF', $undangan->id_undangan) }}" target="_blank"
                                            rel="noopener noreferrer" class="btn btn-sm btn-custom me-2 rounded-2">
                                            <i class="fa fa-eye me-1"></i> Lihat
                                        </a>

                                        @if ($undangan->status == 'approve')
                                            <a href="{{ route('cetakundangan', ['id' => $undangan->id_undangan]) }}"
                                                class="btn btn-sm btn-custom rounded-2 me-2">
                                                <i class="fa fa-download me-1"></i> Unduh
                                            </a>
                                        @endif

                                        <a href="{{ route('disposisi.create', [
                                            'document_type' => 'undangan',
                                            'document_id' => $undangan->id_undangan,
                                        ]) }}"
                                            class="btn btn-sm btn-primary rounded-2">
                                            <i class="fas fa-paper-plane me-1"></i> Disposisi
                                        </a>
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
                </div> {{-- /row --}}

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
                                        @forelse (($tujuanDisplayList ?? []) as $index => $item)
                                            <p class="m-0">{{ $index + 1 }}. {{ $item }}</p>
                                        @empty
                                            -
                                        @endforelse
                                    </div>
                                </div>

                                @if (!empty($tembusanList ?? []))
                                    <div class="info-row d-flex flex-column flex-sm-row">
                                        <div class="info-label">Tembusan</div>
                                        <div class="info-value">
                                            @foreach ($tembusanList as $index => $item)
                                                <p class="m-0">{{ $index + 1 }}. {{ $item }}</p>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if (($canViewBcc ?? false) && !empty($bccDisplayList ?? []))
                                    <div class="info-row d-flex flex-column flex-sm-row">
                                        <div class="info-label">BCC</div>
                                        <div class="info-value">
                                            @foreach ($bccDisplayList as $index => $item)
                                                <p class="m-0">{{ $index + 1 }}. {{ $item }}</p>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
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

                {{-- Form Approval (khusus manager approver) --}}
                @php
                    $isManagerApprover =
                        auth()->user()->role_id_role == 3 &&
                        ($undangan->manager_user_id
                            ? (int) $undangan->manager_user_id === (int) auth()->id()
                            : (string) $undangan->nama_bertandatangan === (string) auth()->user()->fullname);
                @endphp
                @if ($undangan->status === 'pending' && $isManagerApprover)
                    <form id="approvalForm" method="POST"
                        action="{{ route('undangan.updateStatus', $undangan->id_undangan) }}">
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
                                                @forelse (($tujuanDisplayList ?? []) as $index => $item)
                                                    <p class="m-0">{{ $index + 1 }}. {{ $item }}</p>
                                                @empty
                                                    -
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

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
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('.approval-checkbox');
            const catatanCol = document.getElementById('catatanCol');
            const catatanInput = document.getElementById('catatan');
            const tujuanDivisiRow = document.getElementById('tujuanDivisiRow');
            const submitBtn = document.getElementById('submitBtn');
            const approvalForm = document.getElementById('approvalForm');
            let statusValue = null;
            let isSubmitting = false;

            if (!approvalForm) return;

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
                        pengesahanCol.className = 'col-md-12';
                    } else if (statusValue === 'reject' || statusValue === 'correction') {
                        catatanCol.style.display = 'block';
                        catatanInput.required = true;
                        tujuanDivisiRow.style.display = 'none';
                        pengesahanCol.className = 'col-md-6';
                    } else {
                        catatanCol.style.display = 'none';
                        catatanInput.required = false;
                        tujuanDivisiRow.style.display = 'none';
                        pengesahanCol.className = 'col-md-12';
                    }
                });
            });

            if (submitBtn) {
                submitBtn.addEventListener('click', function() {
                    if (isSubmitting) return;
                    if (!statusValue) {
                        alert('Pilih status pengesahan terlebih dahulu!');
                        return;
                    }
                    if ((statusValue === 'reject' || statusValue === 'correction') && catatanInput.value
                        .trim() ===
                        '') {
                        document.getElementById('catatanError').style.display = 'block';
                        catatanInput.focus();
                        return;
                    }

                    isSubmitting = true;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Memproses...';
                    approvalForm.submit();
                });
            }
        });
    </script>
@endpush
