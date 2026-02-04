@extends('layouts.app')

@section('title', 'Edit Risalah Rapat')

@section('content')
    <div class="container-fluid px-4 py-0 mt-0">
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-body py-3">

                <h3 class="fw-bold mb-3">Edit Risalah Rapat</h3>

                {{-- Breadcrumb --}}
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="bg-white border rounded-2 px-3 py-2 w-100 d-flex align-items-center">
                            <a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-primary">Beranda</a>
                            <span class="mx-2 text-muted">/</span>
                            <a href="{{ route('superadmin.risalah.index') }}"
                                class="text-decoration-none text-primary">Risalah
                                Rapat</a>
                            <span class="mx-2 text-muted">/</span>
                            <span class="text-muted">Edit Risalah</span>
                        </div>
                    </div>
                </div>

                {{-- Form --}}
                <form action="{{ route('risalah.update', $risalah->id_risalah) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header py-2 rounded-top-3"
                            style="background:#e3f2fd;border-bottom:1px solid #bbdefb;">
                            <i class="fa fa-edit me-2 text-primary"></i>
                            <span class="fw-semibold">Formulir Edit Risalah</span>
                        </div>
                        <div class="card-body">

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="tanggal_surat" class="form-label">
                                        <i class="fas fa-calendar-alt text-primary me-1"></i>
                                        Tanggal Surat <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" name="tgl_dibuat" class="form-control"
                                        value="{{ $risalah->tgl_dibuat->format('Y-m-d') }}" required>
                                    <input type="hidden" name="tgl_disahkan">
                                </div>
                                <div class="col-md-6">
                                    <label for="seri_surat" class="form-label">
                                        <i class="fas fa-hashtag text-primary me-1"></i>
                                        Seri Surat <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="seri_surat" id="seri_surat" class="form-control"
                                        value="{{ $risalah->seri_surat }}" readonly>
                                    <input type="hidden" name="divisi_id_divisi"
                                        value="{{ auth()->user()->divisi_id_divisi }}">
                                    <input type="hidden" name="pembuat"
                                        value="{{ auth()->user()->position->nm_position . ' ' . auth()->user()->role->nm_role }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="nomor_surat" class="form-label">
                                        <i class="fas fa-file-alt text-primary me-1"></i>
                                        Nomor Surat <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="nomor_risalah" id="nomor_risalah" class="form-control"
                                        value="{{ $risalah->nomor_risalah }}" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label for="perihal" class="form-label">
                                        <i class="fas fa-tag text-primary me-1"></i>
                                        Judul <span class="text-danger">*</span>
                                    </label>
                                    <select name="judul" id="judul" class="form-select select2" required>
                                        <option value="{{ $risalah->judul }}" selected>{{ $risalah->judul }}</option>
                                        @foreach ($undangan as $u)
                                            <option value="{{ $u->judul }}" data-tempat="{{ $u->tempat }}"
                                                data-waktu_mulai="{{ $u->waktu_mulai }}"
                                                data-waktu_selesai="{{ $u->waktu_selesai }}"
                                                data-nama_ttd="{{ $u->nama_bertandatangan }}">
                                                {{ $u->judul }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="agenda" class="form-label">
                                        <i class="fas fa-edit text-primary me-1"></i>
                                        Agenda <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="agenda" id="agenda" class="form-control"
                                        value="{{ $risalah->agenda }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="tempat" class="form-label"> <i
                                            class="fas fa-map-marker-alt text-primary me-1"></i>
                                        Tempat Rapat <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="tempat" id="tempat" class="form-control"
                                        value="{{ $risalah->tempat }}" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="waktu" class="form-label">
                                        <i class="fas fa-clock text-primary me-1"></i>
                                        Waktu Rapat <span class="text-danger">*</span>
                                    </label>
                                    <div class="d-flex align-items-center">
                                        <input type="text" name="waktu_mulai" id="waktu_mulai"
                                            class="form-control me-2" placeholder="Mulai"
                                            value="{{ $risalah->waktu_mulai }}" required>
                                        <span class="fw-bold">s/d</span>
                                        <input type="text" name="waktu_selesai" id="waktu_selesai"
                                            class="form-control ms-2" placeholder="Selesai"
                                            value="{{ $risalah->waktu_selesai }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="nama_bertandatangan" class="form-label">
                                        <i class="fas fa-signature text-primary me-1"></i>
                                        Nama yang Bertanda Tangan <span class="text-danger">*</span>
                                    </label>
                                    <select name="nama_bertandatangan" id="nama_bertandatangan"
                                        class="form-select select2" required>
                                        <option value="{{ $risalah->nama_bertandatangan }}" selected>
                                            {{ $risalah->nama_bertandatangan }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            {{-- Detail Risalah --}}
                            <div id="risalahContainer">
                                @if (!empty($risalah->risalahDetails) && $risalah->risalahDetails->isNotEmpty())
                                    @foreach ($risalah->risalahDetails as $detail)
                                        <div class="isi-surat-row row g-2 mb-2">
                                            <div class="col-md-1">
                                                <label>No</label>
                                                <textarea class="form-control no-auto" name="nomor[]" rows="2" readonly>{{ $detail->nomor }}</textarea>
                                            </div>
                                            <div class="col-md-2">
                                                <label>Topik</label>
                                                <textarea class="form-control" name="topik[]" rows="2">{{ $detail->topik }}</textarea>
                                            </div>
                                            <div class="col-md-3">
                                                <label>Pembahasan</label>
                                                <textarea class="form-control" name="pembahasan[]" rows="2">{{ $detail->pembahasan }}</textarea>
                                            </div>
                                            <div class="col-md-2">
                                                <label>Tindak Lanjut</label>
                                                <textarea class="form-control" name="tindak_lanjut[]" rows="2">{{ $detail->tindak_lanjut }}</textarea>
                                            </div>
                                            <div class="col-md-2">
                                                <label>Target</label>
                                                <textarea class="form-control" name="target[]" rows="2">{{ $detail->target }}</textarea>
                                            </div>
                                            <div class="col-md-2">
                                                <label>PIC</label>
                                                <textarea class="form-control" name="pic[]" rows="2">{{ $detail->pic }}</textarea>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>

                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="tambahIsiRisalahBtn">
                                    <i class="fa fa-plus me-1"></i> Tambah Isi Risalah
                                </button>
                            </div>

                        </div>
                        <div class="card-footer text-end">
                            <a href="{{ route('superadmin.risalah.index') }}" class="btn btn-outline-primary">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- Select2, Summernote, dll --}}
    <script src="https://cdn.jsdelivr.net/npm/summernote/dist/summernote-lite.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#judul').on('change', function() {
                const selected = $(this).find('option:selected');
                const tempat = selected.data('tempat');
                const waktuMulai = selected.data('waktu_mulai');
                const waktuSelesai = selected.data('waktu_selesai');
                const namaTTD = selected.data('nama_ttd');

                $('#tempat').val(tempat);
                $('#waktu_mulai').val(waktuMulai);
                $('#waktu_selesai').val(waktuSelesai);
                $('#nama_bertandatangan').val(namaTTD).trigger('change'); // untuk Select2
            });
        });

        $(document).ready(function() {
            $('.select2').select2({
                placeholder: "Pilih Nama",
                allowClear: true
            });
        });

        $(document).ready(function() {
            $('#dropdownMenuButton').on('change', function() {
                $(this).css('text-align', 'left');
                if ($(this).val() === null || $(this).val() === "") {
                    $(this).css('text-align', 'center');
                }
            });
        });

        $(document).ready(function() {
            $('.select2').select2({
                placeholder: "Pilih Nama",
                allowClear: true
            });
        });

        // Tambah & hapus isi risalah
        document.getElementById('tambahIsiRisalahBtn').addEventListener('click', function(event) {
            event.preventDefault();
            var risalahContainer = document.getElementById('risalahContainer');
            var newRow = document.createElement('div');
            newRow.classList.add('isi-surat-row', 'row', 'g-2', 'mb-2');

            newRow.innerHTML = `
                <div class="col-md-1">
                    <input type="text" class="form-control no-auto" name="nomor[]" readonly>
                </div>
                <div class="col-md-2">
                    <textarea class="form-control" name="topik[]" placeholder="Topik" rows="2"></textarea>
                </div>
                <div class="col-md-3">
                    <textarea class="form-control" name="pembahasan[]" placeholder="Pembahasan" rows="2"></textarea>
                </div>
                <div class="col-md-2">
                    <textarea class="form-control" name="tindak_lanjut[]" placeholder="Tindak Lanjut" rows="2"></textarea>
                </div>
                <div class="col-md-2">
                    <textarea class="form-control" name="target[]" placeholder="Target" rows="2"></textarea>
                </div>
                <div class="col-md-2 position-relative">
                    <textarea class="form-control" name="pic[]" placeholder="PIC" rows="2"></textarea>
                </div>
            `;

            risalahContainer.appendChild(newRow);
            updateNomor();
        });

        function updateNomor() {
            const nomorInputs = document.querySelectorAll('.isi-surat-row .no-auto');
            nomorInputs.forEach((input, index) => {
                input.value = index + 1;
            });
        }
    </script>
@endpush
