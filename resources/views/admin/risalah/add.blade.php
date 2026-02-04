@extends('layouts.app')

@section('title', 'Tambah Risalah Rapat')

@section('content')
    <div class="container-fluid px-4 py-0 mt-0">
        <div class="card shadow-sm border-0">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="fw-bold mb-0">Tambah Risalah</h3>
                </div>

                {{-- Breadcrumb --}}
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="bg-white border rounded-2 px-3 py-2 w-100 d-flex align-items-center">
                            <a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-primary">Beranda</a>
                            <span class="mx-2 text-muted">/</span>
                            <a href="{{ route('admin.risalah.index') }}"
                                class="text-decoration-none text-primary">Risalah</a>
                            <span class="mx-2 text-muted">/</span>
                            <span class="text-muted">Tambah Risalah</span>
                        </div>
                    </div>
                </div>


                <div class="row">
                    <div class="col-md-12">
                        @if ($errors->any())
                            <div class="alert alert-danger mb-3">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <form action="{{ route('risalah.store') }}" method="POST" enctype="multipart/form-data"
                            id="risalahForm">
                            @csrf
                            <div class="card">
                                <div class="card-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h4 class="card-title mb-0">
                                            <i class="fas fa-plus-circle text-primary me-2"></i>
                                            Form Tambah Risalah Rapat
                                        </h4>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <div class="row g-3">
                                        {{-- Kode Bagian Kerja --}}
                                        <div class="form-group">
                                            <label for="kode_bagian" class="form-label">
                                                <i class="fas fa-building text-primary me-1"></i>
                                                Kode Bagian Kerja <span class="text-danger">*</span>
                                            </label>

                                            <select name="kode_bagian" id="kode_bagian"
                                                class="form-control @error('kode_bagian') is-invalid @enderror" required>
                                                <option value="">-- Pilih Bagian Kerja --</option>

                                                @foreach ($bagianKerja as $bk)
                                                    <option value="{{ $bk->kode_bagian }}"
                                                        {{ old('kode_bagian') == $bk->kode_bagian ? 'selected' : '' }}>
                                                        {{ $bk->kode_bagian }} — {{ $bk->nama_bagian ?? '' }}
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
                                                value="{{ date('Y-m-d') }}" required>
                                            <input type="hidden" name="tgl_disahkan">
                                        </div>
                                        {{-- <div class="col-md-6">
                                        <label for="seri_surat" class="form-label">
                                            <i class="fas fa-hashtag text-primary me-1"></i>
                                            Seri Surat <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" name="seri_surat"
                                            placeholder="Contoh: 001" value="{{ old('seri_surat') }}">
                                        <input type="hidden" name="pembuat" value="{{ auth()->user()->id }}">
                                        <input type="hidden" name="risalah_id_risalah" value="{{ $risalah->id_risalah }}">
                                    </div> --}}
                                        <input type="hidden" name="pembuat" value="{{ auth()->user()->id }}">
                                        <input type="hidden" name="risalah_id_risalah" value="{{ $risalah->id_risalah }}">

                                        <div class="col-md-6">
                                            <label for="perihal" class="form-label">
                                                <i class="fas fa-tag text-primary me-1"></i>
                                                Judul <span class="text-danger">*</span>
                                            </label>
                                            <select name="judul" id="judul" class="select2" required>
                                                <option value="" disabled selected>Pilih Judul</option>
                                                @foreach ($undangan as $u)
                                                    <option value="{{ $u->judul }}" data-tempat="{{ $u->tempat }}"
                                                        data-waktu_mulai="{{ $u->waktu_mulai }}"
                                                        data-waktu_selesai="{{ $u->waktu_selesai }}"
                                                        data-tujuan="{{ $u->tujuan }}"
                                                        data-fullname='@json($users)'
                                                        data-id="{{ $u->id_undangan }}" data-self-id="{{ $self->id }}"
                                                        data-self-name="{{ $self->fullname }}">
                                                        {{ $u->judul }}
                                                @endforeach
                                            </select>
                                            {{-- <link
                                                href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css"
                                                rel="stylesheet" />
                                            <link
                                                href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
                                                rel="stylesheet" />

                                            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                                            <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.full.min.js"></script>

                                            <script>
                                                $(function() {
                                                    $('#judul').select2();
                                                });
                                                $('#judul').select2({
                                                    theme: "bootstrap-5",
                                                    placeholder: "Pilih Judul",
                                                    allowClear: true,
                                                    width: "100%"
                                                });
                                            </script> --}}
                                        </div>

                                        <div class="col-md-6">
                                            <label for="agenda" class="form-label">
                                                <i class="fas fa-edit text-primary me-1"></i>
                                                Agenda <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" name="agenda" class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="tempat" class="form-label"> <i
                                                    class="fas fa-map-marker-alt text-primary me-1"></i>
                                                Tempat Rapat <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" name="tempat" id="tempat" class="form-control"
                                                required>
                                        </div>

                                        <!-- Waktu -->
                                        <div class="col-md-6">
                                            <label class="form-label">
                                                <i class="fas fa-clock text-primary me-1"></i>
                                                Waktu Rapat <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <input type="text" name="waktu_mulai" id="waktu_mulai"
                                                    class="form-control" placeholder="09.00" required>
                                                <span class="input-group-text">s/d</span>
                                                <input type="text" name="waktu_selesai" id="waktu_selesai"
                                                    class="form-control" placeholder="Selesai" required>
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
                                        </div>

                                        <div class="col-md-6">
                                            <label for="pemimpin_acara" class="form-label">
                                                <i class="fas fa-signature text-primary me-1"></i>
                                                Pemimpin Acara <span class="text-danger">*</span>
                                            </label>
                                            <select name="pemimpin_acara" id="pemimpin_acara" class="select2" required>
                                                <option value="" disabled selected>
                                                    --Pilih Pemimpin Acara--
                                                </option>
                                                @foreach ($users as $user)
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
                                                <option value="" disabled selected>--Pilih Notulis Acara--</option>
                                                @foreach ($users as $user)
                                                    <option value="{{ $user->id }}">{{ $user->fullname }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                    </div>

                                    <div id="risalahContainer" class="mt-4">
                                        <!-- Dynamic content will be added here -->
                                    </div>

                                    <button type="button" class="btn btn-primary mt-3 w-100" id="tambahRisalahBtn">
                                        <i class="bi bi-plus-circle me-1"></i> Tambah Isi Risalah
                                    </button>

                                    <div id="risalahAlert" class="mt-2 text-danger" style="display:none;"></div>
                                </div>
                                <div class="card-footer d-flex justify-content-end">
                                    <a href="{{ route('admin.risalah.index') }}"
                                        class="btn btn-outline-primary me-2">Batal</a>
                                    <button type="submit" id="submitBtn" class="btn btn-primary">Simpan</button>
                                </div>
                            </div>
                            <input type="hidden" id="with_undangan" name="with_undangan">
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal untuk tambah risalah (jika diperlukan) -->
        <div class="modal fade" id="modalAddRisalah" tabindex="-1" aria-labelledby="modalAddRisalahLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalAddRisalahLabel">Tambah Risalah</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Modal content for adding risalah -->
                        <p>Modal content untuk tambah risalah...</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="button" class="btn btn-primary">Simpan Risalah</button>
                    </div>
                </div>
            </div>
        </div>

    @endsection

    @push('scripts')
        <script>
            // Script dengan double click prevention yang lebih robust
            document.addEventListener('DOMContentLoaded', function() {
                console.log('DOM Content Loaded - Script dimulai');

                // Autofill dari dropdown judul
                $('#judul').select2({
                    theme: "bootstrap-5",
                    placeholder: "Pilih Judul",
                    allowClear: true,
                    width: "100%"
                });
                const judulSelect = document.getElementById('judul');
                if (judulSelect) {

                    $('#judul').on('change', function() {
                        const selected = $(this).find(":selected");

                        $("#tempat").val(selected.data("tempat") || "");
                        $("#waktu_mulai").val(selected.data("waktu_mulai") || "");
                        $("#waktu_selesai").val(selected.data("waktu_selesai") || "");
                        $("#with_undangan").val(selected.attr("data-id") || "");


                        const tujuanIds = (selected.data("tujuan") || "")
                            .toString()
                            .split(";")
                            .map(v => parseInt(v.trim()))
                            .filter(Boolean);
                        console.log('Tujuan IDs:', tujuanIds);
                        const rawMap = selected.data("fullname") || {};
                        const userMap = Object.fromEntries(
                            Object.entries(rawMap).map(([name, id]) => [id, name])
                        );



                        const tujuanNames = tujuanIds.map(id => ({
                            id: id,
                            name: userMap[id] || "Unknown User"
                        }));

                        const selfId = parseInt(selected.data("self-id"));
                        const selfName = selected.data("self-name");

                        const UserDewe = tujuanNames.find(user => user.id === selfId);

                        if (!UserDewe) {
                            if (selfId && selfName) {
                                tujuanNames.push({
                                    id: selfId,
                                    name: selfName
                                });
                            }
                        }

                        // // update dropdown pemimpin + notulis
                        // function populateSelect(selectID, items) {
                        //     let $select = $(selectID);
                        //     $select.empty().append("<option value='' disabled selected>Pilih</option>");

                        //     items.forEach(item => {
                        //         $select.append(new Option(item.name, item.id));
                        //     });

                        //     // refresh select2
                        //     $select.trigger('change.select2');
                        // }

                        // populateSelect("#pemimpin_acara", tujuanNames);
                        // populateSelect("#notulis_acara",
                        //     tujuanNames);
                    });

                }

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

                //Fungsi untuk update nomor otomatis

                function updateNomor() {
                    const rows = document.querySelectorAll('.isi-surat-row');
                    rows.forEach((row, index) => {
                        const noInput = row.querySelector('.no-auto');
                        if (noInput) {
                            noInput.value = index + 1;
                        }
                    });
                    console.log(`Nomor diupdate untuk ${rows.length} baris`);
                }

                // Event delegation untuk tombol hapus
                document.addEventListener('click', function(e) {
                    if (e.target && (e.target.classList.contains('hapus-risalah-btn') || e.target.closest(
                            '.hapus-risalah-btn'))) {
                        e.preventDefault();
                        const button = e.target.classList.contains('hapus-risalah-btn') ? e.target : e.target
                            .closest('.hapus-risalah-btn');
                        const row = button.closest('.isi-surat-row');
                        if (row) {
                            row.remove();
                            updateNomor();
                            console.log('Baris risalah berhasil dihapus');
                        }
                    }
                });

                // Tombol tambah risalah baru
                const tambahRisalahBtn = document.getElementById('tambahRisalahBtn');
                if (tambahRisalahBtn) {
                    console.log('✓ Tombol tambah risalah ditemukan');

                    tambahRisalahBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        console.log('Tombol tambah risalah diklik!');

                        const risalahContainer = document.getElementById('risalahContainer');
                        if (!risalahContainer) {
                            console.error('Container risalah tidak ditemukan!');
                            return;
                        }

                        // Buat elemen baru dengan inline CSS
                        const newRow = document.createElement('div');
                        newRow.className = 'isi-surat-row row mb-3 g-2 border p-3 rounded';
                        newRow.style.alignItems = 'stretch';

                        newRow.innerHTML = `
                <div class="col-md-1" style="display: flex; flex-direction: column;">
                    <label class="form-label">No.</label>
                    <input type="text" class="form-control no-auto" name="nomor[]" readonly style="flex: 1;">
                </div>
                <div class="col-md-2" style="display: flex; flex-direction: column;">
                    <label class="form-label">Topik</label>
                    <textarea class="form-control" name="topik[]" placeholder="Topik" rows="2" required style="flex: 1; resize: vertical;"></textarea>
                </div>
                <div class="col-md-2" style="display: flex; flex-direction: column;">
                    <label class="form-label">Pembahasan</label>
                    <textarea class="form-control" name="pembahasan[]" placeholder="Pembahasan" rows="2" required style="flex: 1; resize: vertical;"></textarea>
                </div>
                <div class="col-md-2" style="display: flex; flex-direction: column;">
                    <label class="form-label">Tindak Lanjut</label>
                    <textarea class="form-control" name="tindak_lanjut[]" placeholder="Tindak Lanjut" rows="2" required style="flex: 1; resize: vertical;"></textarea>
                </div>
                <div class="col-md-2" style="display: flex; flex-direction: column;">
                    <label class="form-label">Target</label>
                    <textarea class="form-control" name="target[]" placeholder="Target" rows="2" required style="flex: 1; resize: vertical;"></textarea>
                </div>
                <div class="col-md-2" style="display: flex; flex-direction: column;">
                    <label class="form-label">PIC</label>
                    <textarea class="form-control" name="pic[]" placeholder="PIC" rows="2" required style="flex: 1; resize: vertical;"></textarea>
                </div>
                <div class="col-md-1" style="display: flex; align-items: center; justify-content: center; min-height: 80px;">
                    <button type="button" class="btn btn-danger btn-sm hapus-risalah-btn" style="margin: auto;">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;

                        risalahContainer.appendChild(newRow);
                        updateNomor();
                        console.log('✓ Risalah baru berhasil ditambahkan');
                    });
                } else {
                    console.error('❌ Tombol tambah risalah tidak ditemukan!');
                }


                // =========================
                // LAMPIRAN: pilih satu per satu, tampil sebagai list
                // =========================
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

                    function handleLampiranChange(e) {
                        const input = e.target;
                        if (!input.files || input.files.length === 0) return;

                        const file = input.files[0];
                        const maxSize = 2 * 1024 * 1024; // 2MB
                        if (file.size > maxSize) {
                            Swal.fire({
                                icon: 'error',
                                title: 'File Terlalu Besar',
                                text: 'Ukuran file tidak boleh lebih dari 2MB',
                                confirmButtonColor: '#1572e8'
                            });
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
                    }

                    // Pasang handler pertama kali
                    lampiranInput.addEventListener('change', handleLampiranChange);
                } else {
                    console.warn('Lampiran elements not found, skip lampiran dynamic init');
                }
                // =========================


                // IMPROVED: Form validation dan submit dengan robust double click prevention
                const risalahForm = document.getElementById('risalahForm');
                const submitBtn = document.getElementById('submitBtn');

                if (risalahForm && submitBtn) {
                    console.log('✓ Form risalah dan submit button ditemukan');

                    risalahForm.addEventListener('submit', function(e) {
                        console.log('Form submit event triggered');

                        // STEP 1: Cek jika tombol sudah disabled (mencegah double click)
                        if (submitBtn.disabled) {
                            console.log('❌ Button already disabled, preventing duplicate submission');
                            e.preventDefault();
                            e.stopPropagation();
                            return false;
                        }

                        const jumlahRisalah = document.querySelectorAll('.isi-surat-row').length;
                        const risalahAlert = document.getElementById('risalahAlert');

                        console.log('Jumlah risalah:', jumlahRisalah);

                        // STEP 2: Validasi form
                        if (jumlahRisalah < 1) {
                            e.preventDefault();
                            if (risalahAlert) {
                                risalahAlert.style.display = 'block';
                                risalahAlert.innerText = 'Minimal harus mengisi 1 risalah rapat!';

                                // Scroll ke error message
                                risalahAlert.scrollIntoView({
                                    behavior: 'smooth',
                                    block: 'center'
                                });
                            }
                            console.log('❌ Validasi gagal: belum ada risalah');
                            return false;
                        }

                        // STEP 3: Validasi berhasil - disable button dan tampilkan loading
                        console.log('✓ Validasi berhasil, memulai submit...');

                        // Hide error message
                        if (risalahAlert) {
                            risalahAlert.style.display = 'none';
                        }

                        // Disable button dengan loading state
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = `
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                Menyimpan...
            `;

                        console.log('✓ Form disubmit dengan spinner loading');

                        // Form akan disubmit secara normal
                        return true;
                    });

                    // Tambahan: Reset button jika ada error dari server (page reload dengan error)
                    window.addEventListener('load', function() {
                        // Cek apakah ada error message dari server
                        const errorElements = document.querySelectorAll(
                            '.alert-danger, .invalid-feedback, .error, .text-danger');
                        if (errorElements.length > 0) {
                            console.log('Terdeteksi error dari server, reset submit button');
                            setTimeout(function() {
                                if (submitBtn.disabled) {
                                    submitBtn.disabled = false;
                                    submitBtn.innerHTML = 'Simpan';
                                    console.log('✓ Submit button direset karena ada error');
                                }
                            }, 500);
                        }
                    });

                } else {
                    console.error('❌ Form risalah atau submit button tidak ditemukan!');
                }

                // Debug info
                setTimeout(function() {
                    console.log('=== DEBUG INFO ===');
                    console.log('Tombol tambah:', document.getElementById('tambahRisalahBtn') ? '✓' : '❌');
                    console.log('Container:', document.getElementById('risalahContainer') ? '✓' : '❌');
                    console.log('Form:', document.getElementById('risalahForm') ? '✓' : '❌');
                    console.log('Submit button:', document.getElementById('submitBtn') ? '✓' : '❌');
                    console.log('==================');
                }, 500);
            });
        </script>
    @endpush
