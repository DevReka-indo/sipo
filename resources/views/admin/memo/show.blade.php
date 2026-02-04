@extends('layouts.app')

@section('title', 'Detail Memo')

@section('content')


    <div class="container-fluid px-4 py-0 mt-0">
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-body py-3">

                <h3 class="fw-bold mb-3">Detail Memo</h3>

                {{-- Breadcrumb --}}
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="bg-white border rounded-2 px-3 py-2 w-100 d-flex align-items-center">
                            <a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-primary">Beranda</a>
                            <span class="mx-2 text-muted">/</span>
                            <a href="{{ route('admin.memo.index') }}" class="text-decoration-none text-primary">Memo</a>
                            <span class="mx-2 text-muted">/</span>
                            <span class="text-muted">Detail Memo</span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    {{-- Kolom kiri: Informasi Detail Memo --}}
                    <div class="col-md-6 mb-3">
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-header py-2 rounded-top-3"
                                style="background:#e3f2fd;border-bottom:1px solid #bbdefb;">
                                <i class="fa fa-file-alt me-2 text-primary"></i>
                                <span class="fw-semibold">Informasi Detail Memo</span>
                            </div>
                            <div class="card-body">

                                @if ($memoRujukan)
                                    <div class="info-row d-flex flex-column flex-sm-row">
                                        <div class="info-label">Merujuk Memo</div>
                                        <div class="info-value">{{ $memoRujukan->nomor_memo }}</div>
                                    </div>
                                @endif

                                <div class="info-row d-flex flex-column flex-sm-row">
                                    <div class="info-label">No Surat</div>
                                    <div class="info-value">{{ $memo->nomor_memo }}</div>
                                </div>

                                <div class="info-row d-flex flex-column flex-sm-row">
                                    <div class="info-label">Perihal</div>
                                    <div class="info-value">{{ $memo->judul }}</div>
                                </div>

                                <div class="info-row d-flex flex-column flex-sm-row">
                                    <div class="info-label">Hari, Tanggal</div>
                                    <div class="info-value">{{ $memo->tgl_dibuat->translatedFormat('l, d F Y') }}</div>
                                </div>

                                <div class="info-row d-flex flex-column flex-sm-row">
                                    <div class="info-label">Kepada</div>
                                    <div class="info-value">
                                        @php
                                            use App\Models\User;

                                            $tujuanNames = explode(';', $memo->tujuan_string);
                                        @endphp

                                        @if (count($tujuanNames) === 1)
                                            {{ trim($tujuanNames[0]) }}
                                        @else
                                            <ol class="ol-clean">
                                                @foreach ($tujuanNames as $tujuan)
                                                    <li>{{ trim($tujuan) }}</li>
                                                @endforeach
                                            </ol>
                                        @endif
                                    </div>
                                </div>

                                @php
                                    $tembusanList = explode(';', $memo->tembusan ?? '');
                                    $tembusanList = array_filter($tembusanList, fn($t) => trim($t) !== '');
                                @endphp
                                @if (!empty($tembusanList))
                                    <div class="info-row d-flex flex-column flex-sm-row">
                                        <div class="info-label">Tembusan</div>
                                        <div class="info-value">
                                            @foreach ($tembusanList as $index => $tembusan)
                                                <p class="m-0">{{ $index + 1 }}. {{ $tembusan }}</p>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
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
                                    <div class="info-value">{{ $pembuat?->firstname }} {{ $pembuat?->lastname }}</div>
                                </div>

                                <div class="info-row d-flex flex-column flex-sm-row">
                                    <div class="info-label">Status</div>
                                    <div class="info-value">
                                        @if ($memo->kode != $divDeptKode)
                                            @if ($memo->final_status == 'reject')
                                                <span class="badge bg-danger">Ditolak</span>
                                            @elseif ($memo->final_status == 'pending')
                                                <span class="badge bg-info">Diproses</span>
                                            @elseif ($memo->final_status == 'correction')
                                                <span class="badge bg-warning">Dikoreksi</span>
                                            @else
                                                <span class="badge bg-success">Diterima</span>
                                            @endif
                                        @else
                                            @if ($memo->status == 'reject')
                                                <span class="badge bg-danger">Ditolak</span>
                                            @elseif ($memo->status == 'pending')
                                                <span class="badge bg-info">Diproses</span>
                                            @elseif ($memo->status == 'correction')
                                                <span class="badge bg-warning">Dikoreksi</span>
                                            @else
                                                <span class="badge bg-success">Diterima</span>
                                            @endif
                                        @endif
                                    </div>
                                </div>

                                <div class="info-row d-flex flex-column flex-sm-row">
                                    <div class="info-label">File</div>
                                    <div class="info-value">
                                        <a onclick="window.location.href='{{ route('view-memoPDF', $memo->id_memo) }}'""
                                            class="btn btn-sm btn-custom me-2 rounded-2">
                                            <i class="fa fa-eye me-1"></i> Lihat
                                        </a>
                                        <a onclick="window.location.href='{{ route('cetakmemo', ['id' => $memo->id_memo]) }}'"
                                            class="btn btn-sm btn-custom rounded-2">
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
                                                <a href="{{ route('download-semua-lampiran', $memo->id_memo) }}"
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

                {{-- Catatan --}}
                @if ($memo->catatan)
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card border-0 shadow-sm rounded-3">
                                <div class="card-header py-2 rounded-top-3"
                                    style="background:#fff3cd;border-bottom:1px solid #ffeeba;">
                                    <i class="fa fa-sticky-note me-2 text-warning"></i>
                                    <span class="fw-semibold">Catatan</span>
                                </div>
                                <div class="card-body">
                                    <textarea class="form-control" rows="4" readonly>{{ $memo->catatan }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($memo->status === 'approve')
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card border-0 shadow-sm rounded-3">
                                <div class="card-header py-2 rounded-top-3"
                                    style="background:#e3f2fd;border-bottom:1px solid #bbdefb;">
                                    <i class="fa fa-file-alt me-2 text-primary"></i>
                                    <span class="fw-semibold">Balasan Memo</span>
                                </div>
                                <div class="card-body">
                                    @if ($balasanMemos->isNotEmpty())
                                        <div class="table-responsive mt-3">
                                            <table class="table table-bordered custom-table-bagian">
                                                <thead>
                                                    <tr>
                                                        <th class="text-center" style="width:25%;">Nama Dokumen</th>
                                                        <th class="text-center" style="width:25%;">Dokumen</th>
                                                        <th class="text-center" style="width:10%;">Divisi</th>
                                                        <th class="text-center" style="width:10%;">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($balasanMemos as $balasanMemo)
                                                        <tr>
                                                            <td class="text-center">{{ $balasanMemo->judul ?? '-' }}</td>
                                                            <td class="text-center">{{ $balasanMemo->nomor_memo ?? '-' }}
                                                            </td>
                                                            <td class="text-center">{{ $balasanMemo->kode ?? '-' }}</td>
                                                            <td>
                                                                <div class="d-flex justify-content-center gap-2">
                                                                    <a onclick="window.location.href='{{ route('view-memoPDF', $balasanMemo->id_memo) }}'"
                                                                        class="btn btn-sm btn-custom me-2 rounded-2">
                                                                        <i class="fa fa-eye me-1"></i> Lihat Memo
                                                                    </a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        {{-- JIKA BELUM ADA MEMO BALASAN --}}
                                        <div class="d-flex justify-content-between align-items-center">

                                            {{-- Keterangan --}}
                                            <p class="mb-0">Belum ada memo balasan.</p>

                                            {{-- Tombol Balas Memo --}}
                                            <a href="{{ route('admin.memo.store2', ['reply_to' => $memo->id_memo]) }}"
                                                class="btn rounded-3 text-white"
                                                style="background-color:#1E4178; border-color:#1E4178;">
                                                <i class="fas fa-reply me-1"></i>Balas Memo
                                            </a>

                                        </div>
                                    @endif

                                </div>

                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
