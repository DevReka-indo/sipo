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
                            <a href="{{ route('admin.risalah.index') }}" class="text-decoration-none text-primary">Risalah
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
                        @if ($errors->any())
                            <div class="alert alert-danger mb-3">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="card-header py-2 rounded-top-3"
                            style="background:#e3f2fd;border-bottom:1px solid #bbdefb;">
                            <i class="fa fa-edit me-2 text-primary"></i>
                            <span class="fw-semibold">Formulir Edit Risalah</span>
                        </div>

                        <div class="card-body">
                            @if ($errors->any())
                                <div class="alert alert-danger mb-3">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="row mb-3">
                                {{-- Kode Bagian Kerja --}}
                                <div class="col-md-6">
                                    <label for="kode_bagian" class="form-label">
                                        <i class="fas fa-building text-primary me-1"></i>
                                        Kode Bagian Kerja <span class="text-danger">*</span>
                                    </label>

                                    <select name="kode_bagian" id="kode_bagian"
                                        class="form-control @error('kode_bagian') is-invalid @enderror" required>
                                        <option value="">-- Pilih Bagian Kerja --</option>

                                        @foreach ($bagianKerja as $bk)
                                            <option value="{{ $bk->kode_bagian }}" <option value="{{ $bk->kode_bagian }}"
                                                {{ old('kode_bagian', $risalah->kode_bagian ?? '') == $bk->kode_bagian ? 'selected' : '' }}>
                                                {{ $bk->kode_bagian }} — {{ $bk->nama_bagian ?? '' }}
                                            </option>

                                            </option>
                                        @endforeach
                                    </select>

                                    @error('kode_bagian')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
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
                                    <label for="perihal" class="form-label">
                                        <i class="fas fa-tag text-primary me-1"></i>
                                        Judul <span class="text-danger">*</span>
                                    </label>
                                    @if ($risalah->with_undangan)
                                        <select name="judul" id="judul" class="form-select" required disabled>
                                            <option value="{{ $risalah->judul }}" selected>{{ $risalah->judul }}</option>
                                        </select>
                                        <input type="hidden" name="judul" value="{{ $risalah->judul }}">
                                    @else
                                        <input type="text" name="judul" id="judul" class="form-control" required
                                            value="{{ $risalah->judul }}">
                                    @endif
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
                                        <input type="text" name="waktu_mulai" id="waktu_mulai" class="form-control me-2"
                                            placeholder="Mulai" value="{{ $risalah->waktu_mulai }}" required>
                                        <span class="fw-bold">s/d</span>
                                        <input type="text" name="waktu_selesai" id="waktu_selesai"
                                            class="form-control ms-2" placeholder="Selesai"
                                            value="{{ $risalah->waktu_selesai }}" required>
                                    </div>
                                </div>
                                <!-- Lampiran -->
                                <div class="col-md-6">
                                    <label for="lampiran-input" class="form-label">
                                        <i class="fas fa-paperclip text-primary me-1"></i>
                                        Lampiran
                                    </label>

                                    {{-- Input utama yang selalu kosong, dipakai untuk memilih file satu per satu --}}
                                    <div id="lampiran-input-container" class="mb-2">
                                        <input type="file" id="lampiran-input"
                                            class="form-control @error('lampiran') is-invalid @enderror"
                                            accept=".pdf,.jpg,.jpeg,.png">
                                    </div>

                                    {{-- Daftar file yang sudah dipilih --}}
                                    <div id="lampiran-list" class="mt-2">
                                        {{-- Item file terpilih akan muncul di sini lewat JS --}}
                                    </div>

                                    <small class="form-text text-muted">
                                        Format yang diizinkan: PDF, JPG, JPEG, PNG (Max: 2MB).
                                        File akan dikirim saat Anda klik tombol <b>Simpan</b>.
                                    </small>
                                    @error('lampiran')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror

                                    {{-- Tampilan File Lampiran yang Sudah Diupload --}}
                                    @if (!empty($lampiranData) && is_array($lampiranData))
                                        <div class="mt-3">
                                            <label for="lampiran-input" class="form-label">
                                                <i class="fas fa-paperclip text-primary me-1"></i>
                                                File yang Sudah Diupload
                                            </label>
                                            <div class="row">
                                                @foreach ($lampiranData as $index => $lampiran)
                                                    <div class="col-12 mb-2">
                                                        <div class="border rounded p-2">
                                                            <div class="d-flex align-items-center justify-content-between">
                                                                <div class="flex-grow-1">
                                                                    <small class="text-truncate d-block"
                                                                        title="{{ $lampiran['name'] ?? 'File Lampiran' }}">
                                                                        <i class="fas fa-file text-primary me-1"></i>
                                                                        {{ $lampiran['name'] ?? 'File Lampiran ' . ($index + 1) }}
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
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-outline-danger delete-lampiran-existing"
                                                                        data-index="{{ $index }}"
                                                                        data-name="{{ $lampiran['name'] ?? 'File' }}"
                                                                        title="Hapus File">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                            <div class="progress mt-1" style="height: 2px;">
                                                                <div class="progress-bar bg-success" style="width: 100%;">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            @if (!$risalah->with_undangan)
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="kepada" class="form-label">
                                                <i class="fas fa-user text-primary me-1"></i>
                                                Pilih Peserta Acara
                                                <span class="text-danger">*</span>
                                            </label>
                                            <small class="text-danger" style="font-size: x-small"> Pilih user atau
                                                struktur, semua user
                                                di bawah struktur akan otomatis terpilih</small>
                                            <div class="border rounded p-2" style="max-height: 300px; overflow-y: auto;">
                                                <div style="font-size: small" class="form-label" id="org-tree">
                                                </div>
                                                <style>
                                                    #org-tree .jstree-anchor {
                                                        color: #1f4178;
                                                        font-weight: 500;
                                                    }
                                                </style>
                                                <small id="tujuanError" class="text-danger" style="display:none;">Minimal
                                                    pilih satu tujuan!</small>
                                            </div>
                                            <!-- ADD TUJUAN JSTREE CONTAINER -->
                                            <div id="tujuan-container"></div>
                                            @error('kepada')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class = "row mb-3">
                                    <div class="col-12">
                                        <div style="display: none;" id="selected-section">
                                            <label style="font-size: small;" class="form-label">
                                                Daftar Penerima:
                                            </label>
                                            <div class="border rounded p-2" style="max-height: 300px; overflow-y: auto;">
                                                <ul id="selected-recipients"
                                                    style="font-size: small; padding-left: 15px; margin: 0; counter-reset: item; list-style-type: none;">
                                                </ul>
                                                <style>
                                                    #selected-recipients li {
                                                        display: block;
                                                        margin-bottom: 0.2em;
                                                    }

                                                    #selected-recipients li:before {
                                                        content: counter(item, decimal) ". ";
                                                        counter-increment: item;
                                                        font-weight: bold;
                                                    }
                                                </style>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <input type="hidden" name="with_undangan" value="{{ $risalah->with_undangan }}">
                            @endif
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="pemimpin_acara" class="form-label">
                                        <i class="fas fa-signature text-primary me-1"></i>
                                        Pemimpin Acara <span class="text-danger">*</span>
                                    </label>
                                    <select name="pemimpin_acara" id="pemimpin_acara" class="select2" required>
                                        @if (!$risalah->pemimpin)
                                            <option value="" selected>Pilih Pemimpin Acara</option>
                                        @else
                                            <option value="{{ $risalah->pemimpin->id }}" selected>
                                                {{ $risalah->nama_pemimpin_acara }}
                                            </option>
                                        @endif
                                        @foreach ($users as $user)
                                            @if ($risalah->pemimpin && $user->id == $risalah->pemimpin->id)
                                                @continue;
                                            @endif
                                            <option value="{{ $user->id }}">{{ $user->fullname }}</option>
                                        @endforeach
                                    </select>

                                </div>
                                <div class="col-md-6">
                                    <label for="notulis_acara" class="form-label">
                                        <i class="fas fa-signature text-primary me-1"></i>
                                        Notulis <span class="text-danger">*</span>
                                    </label>
                                    <select name="notulis_acara" id="notulis_acara" class="select2" required>
                                        @if (!$risalah->notulis)
                                            <option value="" selected>
                                                Pilih Notulis Acara</option>
                                        @else
                                            <option value="{{ $risalah->notulis->id }}" selected>
                                                {{ $risalah->nama_notulis_acara }}</option>
                                        @endif
                                        @foreach ($users as $user)
                                            @if ($risalah->notulis && $user->id == $risalah->notulis->id)
                                                @continue;
                                            @endif
                                            <option value="{{ $user->id }}">{{ $user->fullname }}</option>
                                        @endforeach
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
                            <a href="{{ route('admin.risalah.index') }}" class="btn btn-outline-primary">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- jQuery dulu -->



    <script>
        document.addEventListener('DOMContentLoaded', function() {
            $('#pemimpin_acara').select2({
                theme: "bootstrap-5",
                placeholder: "Pilih Pemimpin Acara",
                allowClear: true,
                width: "100%"
            });
            $('#notulis_acara').select2({
                theme: "bootstrap-5",
                placeholder: "Pilih Notulis Acara",
                allowClear: true,
                width: "100%"
            });
            $(document).ready(function() {
                console.log("Select2 loaded?", typeof $.fn.select2);
            });
        });
    </script>
    {{-- <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script> --}}

    <!-- Summernote -->
    <script src="https://cdn.jsdelivr.net/npm/summernote/dist/summernote-lite.min.js"></script>
    <script>
        // $(document).ready(function() {


        $(document).ready(function() {
            $('#dropdownMenuButton').on('change', function() {
                $(this).css('text-align', 'left');
                if ($(this).val() === null || $(this).val() === "") {
                    $(this).css('text-align', 'center');
                }
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

        // =========================
        // LAMPIRAN: pilih satu per satu, tampil sebagai list
        // =========================
        $(document).ready(function() {
            const lampiranInputContainer = document.getElementById('lampiran-input-container');
            const lampiranInput = document.getElementById('lampiran-input');
            const lampiranList = document.getElementById('lampiran-list');

            if (lampiranInputContainer && lampiranInput && lampiranList) {
                console.log('Lampiran dynamic initialized');

                function createEmptyVisibleInput() {
                    const newInput = document.createElement('input');
                    newInput.type = 'file';
                    newInput.id = 'lampiran-input';
                    newInput.className = 'form-control';
                    newInput.setAttribute('accept', '.pdf,.jpg,.jpeg,.png');

                    // Pasang event handler lagi
                    newInput.addEventListener('change', handleLampiranChange);

                    // Bersihkan container dan pasang input baru
                    lampiranInputContainer.innerHTML = '';
                    lampiranInputContainer.appendChild(newInput);
                }

                let isProcessing = false; // Flag untuk mencegah duplikasi

                function handleLampiranChange(e) {
                    const input = e.target;
                    if (!input.files || input.files.length === 0 || isProcessing) return;

                    isProcessing = true; // Set flag untuk mencegah duplikasi
                    input.removeEventListener('change', handleLampiranChange);

                    const file = input.files[0];

                    // Validasi ukuran file (2MB)
                    const maxSize = 2 * 1024 * 1024; // 2MB
                    if (file.size > maxSize) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'File Terlalu Besar',
                                text: 'Ukuran file tidak boleh lebih dari 2MB',
                                confirmButtonColor: '#1572e8'
                            });
                        } else {
                            alert('Ukuran file tidak boleh lebih dari 2MB');
                        }
                        // Reset input tanpa membuat yang baru
                        input.value = '';
                        isProcessing = false;
                        return;
                    }

                    // Wrapper tiap file di list
                    const itemWrapper = document.createElement('div');
                    itemWrapper.className =
                        'd-flex align-items-center justify-content-between mb-2 flex-wrap gap-2';

                    // Bagian info file + progress
                    const infoWrapper = document.createElement('div');
                    infoWrapper.className = 'flex-grow-1';

                    const nameSpan = document.createElement('span');
                    nameSpan.textContent = file.name;

                    // Progress bar simple (indikator "siap diunggah")
                    const progressOuter = document.createElement('div');
                    progressOuter.className = 'progress mt-1';
                    progressOuter.style.height = '4px';

                    const progressInner = document.createElement('div');
                    progressInner.className = 'progress-bar';
                    progressInner.style.width = '100%';
                    progressInner.setAttribute('aria-valuenow', '100');
                    progressInner.setAttribute('aria-valuemin', '0');
                    progressInner.setAttribute('aria-valuemax', '100');
                    progressInner.textContent = ''; // biar tipis

                    progressOuter.appendChild(progressInner);
                    infoWrapper.appendChild(nameSpan);
                    infoWrapper.appendChild(progressOuter);

                    // Tombol hapus
                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'btn btn-sm btn-outline-danger';
                    removeBtn.innerHTML = '<i class="fas fa-trash"></i>';

                    // Pindahkan input asli ke dalam wrapper, jadikan hidden & beri name
                    input.name = 'lampiran[]';
                    input.classList.add('d-none');
                    input.removeEventListener('change', handleLampiranChange);

                    itemWrapper.appendChild(infoWrapper);
                    itemWrapper.appendChild(removeBtn);
                    itemWrapper.appendChild(input); // input tersembunyi tetap di DOM supaya ikut terkirim

                    lampiranList.appendChild(itemWrapper);

                    // Hapus item + input jika tombol hapus diklik
                    removeBtn.addEventListener('click', function() {
                        itemWrapper.remove();
                    });

                    // Buat input baru yang kosong untuk pilih file berikutnya
                    createEmptyVisibleInput();

                    // Reset flag setelah selesai
                    setTimeout(() => {
                        isProcessing = false;
                    }, 100);
                }

                // Pasang handler pertama kali
                lampiranInput.addEventListener('change', handleLampiranChange);
            } else {
                console.warn('Lampiran elements not found, skip lampiran dynamic init');
            }

            // Hapus lampiran yang sudah ada
            $('.delete-lampiran-existing').on('click', function() {
                const lampiranIndex = $(this).data('index');
                const fileName = $(this).data('name');
                const element = $(this).closest('.col-12');

                // Gunakan SweetAlert jika tersedia, jika tidak gunakan confirm biasa
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Konfirmasi Hapus',
                        text: `Apakah Anda yakin ingin menghapus file "${fileName}"?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Ya, Hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: `/risalah/lampiran-existing/{{ $risalah->id_risalah }}/${lampiranIndex}`,
                                type: 'DELETE',
                                data: {
                                    _token: $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(response) {
                                    element.remove();
                                    Swal.fire({
                                        title: 'Berhasil!',
                                        text: 'File berhasil dihapus.',
                                        icon: 'success',
                                        confirmButtonColor: '#1572e8'
                                    });
                                },
                                error: function(xhr) {
                                    Swal.fire({
                                        title: 'Gagal!',
                                        text: 'Terjadi kesalahan saat menghapus file.',
                                        icon: 'error',
                                        confirmButtonColor: '#1572e8'
                                    });
                                }
                            });
                        }
                    });
                } else {
                    // Fallback ke confirm biasa jika SweetAlert tidak tersedia
                    if (confirm(`Apakah Anda yakin ingin menghapus file "${fileName}"?`)) {
                        $.ajax({
                            url: `/risalah/lampiran-existing/{{ $risalah->id_risalah }}/${lampiranIndex}`,
                            type: 'DELETE',
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                element.remove();
                                alert('File berhasil dihapus.');
                            },
                            error: function(xhr) {
                                alert('Terjadi kesalahan saat menghapus file.');
                            }
                        });
                    }
                }
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            console.log("jQuery loaded?", typeof jQuery);
            console.log('Document ready, initializing JSTree...');

            // Check if JSTree data exists
            var treeData = @json(json_decode($jsTreeData));
            var selectedTujuan = @json($tujuanArray);
            if (!treeData || treeData.length === 0) {
                console.error('JSTree data is empty or invalid');
                $('#org-tree').html('<p class="text-danger">Data organisasi tidak tersedia</p>');
                return;
            }

            // Initialize JSTree with error handling
            try {
                $('#org-tree').jstree({
                    'core': {
                        'data': treeData,
                        'themes': {
                            //'responsive': true,
                            'dots': true,

                        }
                    },
                    'plugins': ['checkbox']
                }).on('ready.jstree', function(e, data) {
                    console.log('JSTree initialized successfully');
                    selectedTujuan.forEach(id => {
                        $('#org-tree').jstree('check_node', '#user-' + id);
                    });

                    // Initial display of selected recipients
                    updateSelectedRecipients(data);

                    // Auto expand nodes that have selected users
                    data.instance.get_selected(true).forEach(function(node) {
                        // Open parent nodes of selected users
                        let parentId = data.instance.get_parent(node.id);
                        while (parentId && parentId !== '#') {
                            data.instance.open_node(parentId);
                            parentId = data.instance.get_parent(parentId);
                        }
                    });
                    // Pre-select nodes based on existing tujuan

                }).on('changed.jstree', function(e, data) {
                    console.log('JSTree selection changed:', data.selected);
                    updateSelectedRecipients(data);
                    // Clear previous hidden inputs
                    $('#tujuan-container').empty();

                    let allSelectedNodes = data.instance.get_selected(true);
                    let selectedNodes = [];
                    let userIds = [];

                    console.log('All selected nodes:', allSelectedNodes);

                    allSelectedNodes.forEach(function(node) {
                        console.log('Processing node:', node);

                        // Check if node has 'fa fa-user' icon (which indicates it's a user)
                        if (node.icon && node.icon === 'fa fa-user') {
                            selectedNodes.push(node.text);
                            // Extract user ID from node.id (assuming format is 'user-{id}')
                            if (node.id.startsWith('user-')) {
                                userIds.push(node.id.replace('user-', ''));
                            }
                        }

                        // Auto expand selected nodes to show their children
                        if (data.instance.is_selected(node.id)) {
                            data.instance.open_node(node.id);
                        }
                    });

                    console.log('Selected users:', selectedNodes);
                    console.log('User IDs:', userIds);

                    // Add hidden inputs for form submission
                    userIds.forEach(function(userId) {
                        $('#tujuan-container').append(
                            '<input type="hidden" name="tujuan[]" value="' + userId + '">');
                    });

                    // Sort selectedNodes by position hierarchy
                    selectedNodes.sort(function(a, b) {
                        const positionOrder = {
                            'Direktur': 1,
                            'GM': 2,
                            'General Manager': 2,
                            'SM': 3,
                            'Senior Manager': 3,
                            'M': 4,
                            'Manager': 4,
                            'PJ SM': 5,
                            'Penanggung Jawab Senior Manager': 5,
                            'PJ M': 6,
                            'Penanggung Jawab Manager': 6,
                            'SPV': 7,
                            'Supervisor': 7,
                            'PJ SPV': 8,
                            'Penanggung Jawab Supervisor': 8,
                            'Staff': 9
                        };

                        const getPositionPriority = function(text) {
                            for (let pos in positionOrder) {
                                if (text.startsWith(pos)) {
                                    return positionOrder[pos];
                                }
                            }
                            return 999;
                        };

                        return getPositionPriority(a) - getPositionPriority(b);
                    });


                    // Update display list
                    let list = $('#selected-recipients');
                    let section = $('#selected-section');
                    list.empty();

                    if (selectedNodes.length) {
                        selectedNodes.forEach(function(name) {
                            list.append('<li>' + name + '</li>');
                        });
                        section.show();
                    } else {
                        section.hide();
                    }

                    // Hide error message if users are selected
                    if (userIds.length > 0) {
                        $('#tujuanError').hide();
                    }
                }).on('error.jstree', function(e, data) {
                    console.error('JSTree error:', data);
                });

            } catch (error) {
                console.error('JSTree initialization failed:', error);
                $('#org-tree').html('<p class="text-danger">Gagal memuat data organisasi. Error: ' + error.message +
                    '</p>');
            }
        });

        function updateSelectedRecipients(data) {
            let allSelectedNodes = data.instance.get_selected(true);
            let selectedNodes = [];

            allSelectedNodes.forEach(function(node) {
                // Check if node has 'fa fa-user' icon (which indicates it's a user)
                if (node.icon && node.icon === 'fa fa-user') {
                    selectedNodes.push(node.text);
                }

                // Auto expand selected nodes to show their children
                if (data.instance.is_selected(node.id)) {
                    data.instance.open_node(node.id);
                }
            });

            // Sort selectedNodes by position hierarchy
            selectedNodes.sort(function(a, b) {
                const positionOrder = {
                    'Direktur': 1,
                    'GM': 2,
                    'General Manager': 2,
                    'SM': 3,
                    'Senior Manager': 3,
                    'M': 4,
                    'Manager': 4,
                    'PJ SM': 5,
                    'Penanggung Jawab Senior Manager': 5,
                    'PJ M': 6,
                    'Penanggung Jawab Manager': 6,
                    'SPV': 7,
                    'Supervisor': 7,
                    'PJ SPV': 8,
                    'Penanggung Jawab Supervisor': 8,
                    'Staff': 9
                };

                const getPositionPriority = function(text) {
                    for (let pos in positionOrder) {
                        if (text.startsWith(pos)) {
                            return positionOrder[pos];
                        }
                    }
                    return 999;
                };

                return getPositionPriority(a) - getPositionPriority(b);
            });

            let list = $('#selected-recipients');
            let section = $('#selected-section');
            list.empty();

            if (selectedNodes.length) {
                selectedNodes.forEach(name => {
                    list.append(`<li>${name}</li>`);
                });
                section.show();
            } else {
                section.hide();
            }
        }
    </script>
@endpush
