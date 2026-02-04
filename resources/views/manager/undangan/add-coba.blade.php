@extends('layouts.app')

@section('title', 'Tambah Undangan Rapat')

@push('scripts')

    @section('content')
        <div class="container-fluid px-4 py-0 mt-0">
            <div class="card shadow-sm border-0">
                <div class="card-body py-3">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="fw-bold mb-0">Tambah Undangan</h3>
                    </div>

                    {{-- Breadcrumb --}}
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="bg-white border rounded-2 px-3 py-2 w-100 d-flex align-items-center">
                                <a href="{{ route('manager.dashboard') }}" class="text-decoration-none text-primary">Beranda</a>
                                <span class="mx-2 text-muted">/</span>
                                <a href="{{ route('undangan.manager') }}" class="text-decoration-none text-primary">Undangan</a>
                                <span class="mx-2 text-muted">/</span>
                                <span class="text-muted">Tambah Undangan</span>
                            </div>
                        </div>
                    </div>


                    <!-- Form Card -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h4 class="card-title mb-0">
                                            <i class="fas fa-plus-circle text-primary me-2"></i>
                                            Form Tambah Undangan
                                        </h4>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('undangan-superadmin.store') }}" method="POST"
                                        enctype="multipart/form-data" id="addUndanganForm">
                                        @csrf

                                        {{-- ROW 1: Tanggal Surat | Seri Tahunan Surat --}}
                                        <div class="row g-3">
                                            {{-- Kode Bagian Kerja --}}
                                            <div class="col-md-6">

                                                {{-- <div class="form-group"> --}}
                                                    <label for="kode_bagian" class="form-label">
                                                        <i class="fas fa-building text-primary me-1"></i>
                                                        Kode Bagian Kerja <span class="text-danger">*</span>
                                                    </label>

                                                    <select name="kode_bagian" id="kode_bagian"
                                                        class="form-control @error('kode_bagian') is-invalid @enderror"
                                                        required>
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
                                                {{-- </div> --}}
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label mb-1">
                                                    <i class="fas fa-calendar-alt text-primary me-1"></i> Tanggal Surat <span
                                                        class="text-danger">*</span>
                                                </label>
                                                <input type="date"
                                                    class="form-control @error('tgl_dibuat') is-invalid @enderror"
                                                    id="tgl_dibuat" name="tgl_dibuat"
                                                    value="{{ old('tgl_dibuat', date('Y-m-d')) }}" readonly>
                                                @error('tgl_dibuat')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <input type="hidden" name="tgl_disahkan">
                                            <input type="hidden" name="catatan">
                                            <input type="hidden" name="kode" value="{{ $kode }}">
                                            <input type="hidden" name="pembuat" value="{{ auth()->user()->id }}">
                                        </div>

                                        {{-- ROW 2: Nomor Surat | Perihal --}}
                                        <div class="row g-3 mt-1">
                                            {{-- <div class="col-md-6">
                                                <label class="form-label mb-1">
                                                    <i class="fas fa-calendar-alt text-primary me-1"></i> Kepada <span
                                                        class="text-danger">*</span>
                                                </label>
                                                <input type="text" class="form-control @error('kepada') is-invalid @enderror"
                                                    id="kepada" name="kepada" value="{{ old('kepada') }}"
                                                    placeholder="Tulis tujuan surat" required>
                                                @error('kepada')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div> --}}

                                            <div class="col-md-6">
                                                <label class="form-label mb-1">
                                                    <i class="fas fa-tag text-primary me-1"></i> Perihal <span
                                                        class="text-danger">*</span>
                                                </label>
                                                <input type="text" class="form-control @error('judul') is-invalid @enderror"
                                                    id="judul" name="judul" value="{{ old('judul') }}"
                                                    placeholder="Masukkan perihal surat" required>
                                                @error('judul')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        {{-- ROW 3: Kepada (Full width, tetap sejajar tepi) --}}
                                        <div class="row g-3 mt-1">
                                            <div class="col-12">
                                                <label class="form-label mb-1">
                                                    <i class="fas fa-user text-primary me-1"></i> Pilih Peserta Undangan
                                                    <span class="text-danger">*</span>
                                                    <span class="text-danger" style="font-size: x-small"> Pilih user atau
                                                        struktur, semua user di bawah struktur akan otomatis terpilih</span>
                                                </label>

                                                <div class="border rounded p-2" style="max-height:300px;overflow-y:auto;">
                                                    <div id="org-tree" class="form-label" style="font-size:small;"></div>
                                                    <style>
                                                        #org-tree .jstree-anchor {
                                                            color: #1f4178;
                                                            font-weight: 500
                                                        }
                                                    </style>
                                                    <small id="tujuanError" class="text-danger" style="display:none;">Minimal
                                                        pilih satu tujuan!</small>
                                                </div>
                                                <div id="tujuan-container"></div>

                                                {{-- daftar penerima terpilih --}}
                                                <div id="selected-section" style="display:none;">
                                                    <label class="form-label mt-2" style="font-size:small;">Daftar
                                                        Penerima:</label>
                                                    <div class="border rounded p-2" style="max-height:300px;overflow-y:auto;">
                                                        <ul id="selected-recipients"
                                                            style="font-size:small;padding-left:15px;margin:0;list-style:none;counter-reset:item;">
                                                        </ul>
                                                    </div>
                                                    <style>
                                                        #selected-recipients li {
                                                            margin-bottom: .2em
                                                        }

                                                        #selected-recipients li:before {
                                                            content: counter(item) ". ";
                                                            counter-increment: item;
                                                            font-weight: 700
                                                        }
                                                    </style>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- ROW 4: Tanggal Rapat | Waktu (input-group) | Tempat --}}
                                        <div class="row g-3 mt-1">
                                            <div class="col-md-4">
                                                <label class="form-label mb-1">
                                                    <i class="fas fa-calendar-check text-primary me-1"></i> Tanggal Rapat <span
                                                        class="text-danger">*</span>
                                                </label>
                                                <input type="date" name="tgl_rapat" id="tgl_rapat"
                                                    class="form-control @error('tgl_rapat') is-invalid @enderror"
                                                    value="{{ old('tgl_rapat') }}" required>
                                                @error('tgl_rapat')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label mb-1">
                                                    <i class="fas fa-clock text-primary me-1"></i> Waktu Rapat <span
                                                        class="text-danger">*</span>
                                                </label>
                                                <div class="input-group">
                                                    <input type="text" name="waktu_mulai" id="waktu_mulai"
                                                        class="form-control @error('waktu_mulai') is-invalid @enderror"
                                                        placeholder="09.00" value="{{ old('waktu_mulai') }}">
                                                    <span class="input-group-text">s/d</span>
                                                    <input type="text" name="waktu_selesai" id="waktu_selesai"
                                                        class="form-control @error('waktu_selesai') is-invalid @enderror"
                                                        placeholder="Selesai" value="{{ old('waktu_selesai') }}">
                                                </div>
                                                @error('waktu_mulai')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                                @error('waktu_selesai')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label mb-1">
                                                    <i class="fas fa-map-marker-alt text-primary me-1"></i> Tempat Rapat <span
                                                        class="text-danger">*</span>
                                                </label>
                                                <input type="text" name="tempat" id="tempat"
                                                    class="form-control @error('tempat') is-invalid @enderror"
                                                    placeholder="Ruang Rapat" value="{{ old('tempat') }}">
                                                @error('tempat')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- ROW 5: Nama TTD | Lampiran -->
                                        <div class="row g-3 align-items-start mt-1">

                                            <!-- Nama yang Bertanda Tangan -->
                                            <div class="col-md-6">
                                                <label for="manager_user_id" class="form-label">
                                                    <i class="fas fa-signature text-primary me-1"></i>
                                                    Nama yang Bertanda Tangan <span class="text-danger">*</span>
                                                </label>

                                                <select name="manager_user_id" id="manager_user_id" class="form-control">
                                                    <option value="" disabled selected>--Pilih--</option>
                                                    @foreach ($managers as $manager)
                                                        @php
                                                            preg_match(
                                                                '/\((.*?)\)/',
                                                                $manager->position->nm_position,
                                                                $matches,
                                                            );
                                                            $kode_position =
                                                                $matches[1] ?? $manager->position->nm_position;
                                                        @endphp
                                                        <option value="{{ $manager->id }}">
                                                            ({{ $kode_position }})
                                                            {{ $manager->firstname }}{{ $manager->lastname ? ' ' . $manager->lastname : '' }}
                                                        </option>
                                                    @endforeach

                                                </select>
                                                <input type="hidden" name="nama_bertandatangan" id="namaBertandatangan">
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
                                        </div>

                                        {{-- ROW 6: Agenda (full) --}}
                                        <div class="row g-3 mt-1">
                                            <div class="col-12">
                                                <label class="form-label mb-1">
                                                    <i class="fas fa-edit text-primary me-1"></i> Agenda <span
                                                        class="text-danger">*</span>
                                                </label>

                                                <div class="tinymce-wrapper" id="tinymce-agenda-container">
                                                    <textarea class="form-control @error('isi_undangan') is-invalid @enderror" id="isi_undangan" name="isi_undangan"
                                                        rows="10" required>{{ old('isi_undangan') }}</textarea>
                                                </div>
                                                <small class="form-text text-muted mt-1">
                                                    <i class="fas fa-info-circle text-info me-1"></i>
                                                    Kamu bisa menggunakan bullet, penomoran, atau tabel untuk merapikan agenda
                                                    rapat.
                                                </small>

                                                @error('isi_undangan')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        {{-- ACTION --}}
                                        <div class="d-flex justify-content-end gap-2 mt-3">
                                            <a href="{{ route('undangan.manager') }}" class="btn rounded-3"
                                                style="background:#fff;color:#0d6efd;border:1px solid #0d6efd;">Batal</a>
                                            <button type="submit" id="submitBtn"
                                                class="btn btn-primary rounded-3">Kirim</button>
                                        </div>
                                    </form>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            @endsection
            <script>
                // Debug: Check if libraries are loaded
                console.log('jQuery loaded:', typeof jQuery !== 'undefined');
                console.log('JSTree loaded:', typeof jQuery.fn.jstree !== 'undefined');
                console.log('JSTree data:', @json(json_decode($jsTreeData)));

                $(document).ready(function() {
                    console.log('Document ready, initializing JSTree...');

                    // Check if JSTree data exists
                    var treeData = @json(json_decode($jsTreeData));
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
                                    'dots': true
                                }
                            },
                            'plugins': ['checkbox']
                        }).on('ready.jstree', function() {
                            console.log('JSTree initialized successfully');
                        }).on('changed.jstree', function(e, data) {
                            console.log('JSTree selection changed:', data.selected);

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

                    // Form submission validation dengan prevent double click
                    $('#addUndanganForm').on('submit', function(e) {
                        console.log('Form submitting...');

                        // Cek jika tombol sudah disabled (sudah diklik sebelumnya)
                        if ($('#submitBtn').prop('disabled')) {
                            console.log('Button already disabled, preventing duplicate submission');
                            e.preventDefault();
                            return false;
                        }

                        const selected = $('#org-tree').jstree('get_selected', true);
                        const userIds = selected
                            .filter(node => node.id.startsWith('user-'))
                            .map(node => node.id.replace('user-', ''));

                        console.log('Form validation - User IDs:', userIds);

                        // Validate at least one recipient is selected
                        if (userIds.length === 0) {
                            $('#tujuanError').text("Minimal pilih satu tujuan!");
                            $('#tujuanError').show();

                            // Scroll to error
                            $('#tujuanError')[0].scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });

                            e.preventDefault();
                            return false;
                        }

                        // Jika validasi berhasil, disable tombol dan ubah text dengan loading
                        $('#submitBtn').prop('disabled', true)
                            .html(
                                '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Mengirim...'
                            );

                        console.log('Form submission allowed, processing...');

                        // Form akan disubmit secara normal
                        return true;
                    });

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
                        }

                        // Pasang handler pertama kali
                        lampiranInput.addEventListener('change', handleLampiranChange);
                    } else {
                        console.warn('Lampiran elements not found, skip lampiran dynamic init');
                    }
                    // =========================

                });

                function showNotification(message, type) {
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

                    @if (session('error'))
                        showNotification("{{ session('error') }}", "error");
                    @endif
                });

// ==========================
// TinyMCE Initialization - Undangan/Agenda
// Enter = BR, Shift+Enter = P
// ==========================
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing TinyMCE for Agenda...');

    if (typeof tinymce === 'undefined') {
        console.error('TinyMCE not loaded! Check if CDN is accessible.');
        var wrapper = document.getElementById('tinymce-agenda-container');
        var textarea = document.getElementById('isi_undangan');
        if (wrapper && textarea) {
            wrapper.classList.remove('loading');
            textarea.style.display = 'block';
            var notice = document.createElement('div');
            notice.className = 'alert alert-danger mt-2';
            notice.innerHTML =
                '<i class="fas fa-exclamation-triangle"></i> TinyMCE tidak dapat dimuat. Pastikan koneksi internet stabil.';
            textarea.parentNode.insertBefore(notice, textarea.nextSibling);
        }
        return;
    }

    console.log('TinyMCE version:', tinymce.majorVersion + '.' + tinymce.minorVersion);

    var wrapper = document.getElementById('tinymce-agenda-container');
    if (wrapper) {
        wrapper.classList.add('loading');
    }

    var loadingTimeout = setTimeout(function() {
        if (wrapper && wrapper.classList.contains('loading')) {
            console.warn('TinyMCE loading timeout - using fallback');
            wrapper.classList.remove('loading');
            var textarea = document.getElementById('isi_undangan');
            if (textarea) {
                textarea.style.display = 'block';
                textarea.classList.add('form-control');
                var notice = document.createElement('div');
                notice.className = 'alert alert-info mt-2';
                notice.innerHTML =
                    '<i class="fas fa-info-circle"></i> Editor loading timeout. Menggunakan editor teks sederhana.';
                textarea.parentNode.insertBefore(notice, textarea.nextSibling);
            }
        }
    }, 10000);

    try {
        tinymce.init({
            selector: '#isi_undangan',
            height: 500,
            placeholder: 'Tulis agenda rapat di sini...',
            menubar: 'edit view insert format tools table',
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'table', 'code',
                'wordcount', 'paste', 'searchreplace', 'fullscreen', 'help', 'nonbreaking'
            ],

            // ========== TOOLBAR DENGAN FONT & VERTICAL ALIGN ==========
            toolbar: [
                'undo redo | fontfamily fontsize | bold italic underline | forecolor backcolor',
                'alignleft aligncenter alignright alignjustify | bullist numlist | outdent indent',
                'link image table tablecellvalign | tabAlign | nonbreaking | code fullscreen | help'
            ],

            // Font Family Options
            font_family_formats: 'Arial=arial,helvetica,sans-serif; Calibri=calibri,sans-serif; Times New Roman=times new roman,times,serif; Courier New=courier new,courier,monospace; Verdana=verdana,geneva,sans-serif; Georgia=georgia,palatino,serif; Tahoma=tahoma,arial,helvetica,sans-serif',

            // Font Size Options
            font_size_formats: '8pt 9pt 10pt 11pt 12pt 14pt 16pt 18pt 20pt 24pt 28pt 32pt 36pt',

            branding: false,
            promotion: false,
            statusbar: false,

            // ========== KONFIGURASI TABLE UNTUK WYSIWYG ==========
            table_resize_bars: true,
            table_column_resizing: 'preservetable',
            table_use_colgroups: true,
            object_resizing: true,
            table_advtab: true,
            table_cell_advtab: true,
            table_toolbar: 'tableprops tabledelete | tableinsertrowbefore tableinsertrowafter tabledeleterow | tableinsertcolbefore tableinsertcolafter tabledeletecol | tablecellvalign',

            table_default_attributes: {
                'border': '1'
            },
            table_default_styles: {
                'border-collapse': 'collapse',
                'width': '100%'
            },
            table_cell_default_styles: {
                'border': '1px solid #000',
                'padding': '8px',
                'word-wrap': 'break-word',
                'vertical-align': 'top'
            },

            // Preserve width attributes dan colgroup
            extended_valid_elements: 'table[border|style|class|width],colgroup,col[style|width],td[style|colspan|rowspan|width],th[style|colspan|rowspan|width]',
            valid_children: '+body[style],+table[colgroup]',
            // ========== END KONFIGURASI TABLE ==========

            paste_data_images: true,
            paste_word_valid_elements: "b,strong,i,em,h1,h2,h3,h4,h5,h6,p,ol,ul,li,a[href],span,color,font-size,font-color,font-family,mark,table,tr,td,th,div,colgroup,col",
            paste_retain_style_properties: "all",
            entity_encoding: 'raw',
            keep_styles: true,

            formats: {
                alignleft: {
                    selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img',
                    styles: {
                        textAlign: 'left'
                    }
                },
                aligncenter: {
                    selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img',
                    styles: {
                        textAlign: 'center'
                    }
                },
                alignright: {
                    selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img',
                    styles: {
                        textAlign: 'right'
                    }
                },
                alignjustify: {
                    selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img',
                    styles: {
                        textAlign: 'justify'
                    }
                }
            },

            indent_use_margin: true,

            // ========== KONFIGURASI ENTER = BR (BUKAN P BARU) ==========
            forced_root_block: 'p',
            force_br_newlines: false,
            force_p_newlines: false,
            end_container_on_empty_block: false,
            newline_behavior: 'linebreak',
            // ========== END KONFIGURASI ENTER ==========

            content_style: `
                body {
                    font-family: Arial, Helvetica, sans-serif !important;
                    font-size: 12pt;
                }
            `,
            content_css: 'data:text/css;charset=UTF-8,' + encodeURIComponent(`
                body {
                    line-height: 1.5 !important;
                    margin: 0;
                    padding: 8px;
                    font-family: arial, helvetica, sans-serif;
                    font-size: 12pt;
                }
                p {
                    margin: 0 !important;
                    line-height: 1.5 !important;
                    padding: 0 !important;
                    display: block !important;
                }
                br {
                    line-height: 1.5 !important;
                    display: block !important;
                    content: "" !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }
                div {
                    margin: 0 !important;
                    line-height: 1.5 !important;
                    padding: 0 !important;
                }
                .tab-space {
                    display: inline-block;
                    width: 40px;
                    text-align: center;
                }
                .tab-right {
                    display: inline-block;
                    min-width: 40px;
                    text-align: right;
                }
                .tab-formatted {
                    white-space: pre;
                    font-family: 'Courier New', monospace;
                    tab-size: 8;
                }
                table {
                    margin: 0.3em 0 !important;
                    line-height: 1.5 !important;
                    border-collapse: collapse !important;
                }
                table p {
                    margin: 0 !important;
                    line-height: 1.5 !important;
                }
                td, th {
                    padding: 8px !important;
                    line-height: 1.5 !important;
                    border: 1px solid #000 !important;
                    vertical-align: top !important;
                }
                ul, ol {
                    margin: 0.3em 0 !important;
                    padding-left: 2em !important;
                    line-height: 1.5 !important;
                }
                li {
                    line-height: 1.5 !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }
            `),

            quickbars_selection_toolbar: 'bold italic underline | tabAlign | alignleft aligncenter alignright',
            quickbars_insert_toolbar: 'quickimage quicktable | hr pagebreak',

            setup: function(editor) {
                editor.on('change keyup', function() {
                    editor.save();
                    $('#isi_undangan').trigger('input');
                });

                editor.on('PastePostProcess', function(e) {
                    var allElements = e.node.querySelectorAll('*');
                    allElements.forEach(function(el) {
                        if (el.tagName.toLowerCase() !== 'p') {
                            el.style.lineHeight = '1.5';
                        }
                    });
                });

                // ========== CUSTOM ENTER BEHAVIOR ==========
                editor.on('keydown', function(e) {
                    if (e.keyCode === 13) { // Enter key
                        var node = editor.selection.getNode();
                        var inList = editor.dom.getParent(node, 'li,ol,ul');
                        var inTable = editor.dom.getParent(node, 'td,th');

                        if (inList || inTable) {
                            return;
                        }

                        if (e.shiftKey) {
                            e.preventDefault();
                            editor.execCommand('InsertParagraph');
                            return false;
                        } else {
                            e.preventDefault();
                            editor.execCommand('InsertLineBreak');
                            return false;
                        }
                    }

                    // Tab functionality
                    if (e.keyCode === 9) {
                        e.preventDefault();
                        if (e.shiftKey) {
                            var content = editor.selection.getContent();
                            if (content.includes('&nbsp;')) {
                                var newContent = content.replace(/^(&nbsp;){1,8}/, '');
                                editor.selection.setContent(newContent);
                            } else {
                                editor.execCommand('Outdent');
                            }
                        } else {
                            var tabSpaces =
                                '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                            editor.insertContent(tabSpaces);
                        }
                    }
                });

                editor.ui.registry.addButton('tabAlign', {
                    text: 'Tab Align',
                    tooltip: 'Insert tab spaces for alignment',
                    onAction: function() {
                        var tabSpaces =
                            '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                        editor.insertContent(tabSpaces);
                    }
                });

                editor.ui.registry.addMenuButton('tablecellvalign', {
                    text: 'V-Align',
                    tooltip: 'Vertical Alignment',
                    fetch: function(callback) {
                        var items = [{
                                type: 'menuitem',
                                text: 'Top',
                                onAction: function() {
                                    editor.execCommand(
                                        'mceTableApplyCellStyle',
                                        false, {
                                            'vertical-align': 'top'
                                        });
                                }
                            },
                            {
                                type: 'menuitem',
                                text: 'Middle',
                                onAction: function() {
                                    editor.execCommand(
                                        'mceTableApplyCellStyle',
                                        false, {
                                            'vertical-align': 'middle'
                                        });
                                }
                            },
                            {
                                type: 'menuitem',
                                text: 'Bottom',
                                onAction: function() {
                                    editor.execCommand(
                                        'mceTableApplyCellStyle',
                                        false, {
                                            'vertical-align': 'bottom'
                                        });
                                }
                            }
                        ];
                        callback(items);
                    }
                });

                editor.on('init', function() {
                    console.log('TinyMCE editor initialized successfully');

                    if (loadingTimeout) {
                        clearTimeout(loadingTimeout);
                    }
                    var wrapper = document.getElementById('tinymce-agenda-container');
                    if (wrapper) {
                        wrapper.classList.remove('loading');
                    }
                });

                editor.on('LoadError', function(e) {
                    console.error('TinyMCE load error:', e);
                });

                editor.on('SetupEditor', function(e) {
                    console.log('TinyMCE setup completed for editor:', e.editor.id);
                });
            }
        });
    } catch (error) {
        console.error('Error initializing TinyMCE (full config):', error);
        console.log('Attempting simple TinyMCE configuration...');

        try {
            tinymce.init({
                selector: '#isi_undangan',
                height: 400,
                menubar: false,
                plugins: ['lists', 'table'],
                toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | bullist numlist | table',
                branding: false,
                init_instance_callback: function(editor) {
                    console.log('Simple TinyMCE loaded successfully');
                    if (loadingTimeout) clearTimeout(loadingTimeout);
                    var wrapper = document.getElementById('tinymce-agenda-container');
                    if (wrapper) wrapper.classList.remove('loading');
                }
            });
        } catch (simpleError) {
            console.error('Even simple TinyMCE failed:', simpleError);
            var wrapper = document.getElementById('tinymce-agenda-container');
            var textarea = document.getElementById('isi_undangan');
            if (wrapper) wrapper.classList.remove('loading');
            if (loadingTimeout) clearTimeout(loadingTimeout);
            if (textarea) {
                textarea.style.display = 'block';
                textarea.style.minHeight = '400px';
                textarea.style.width = '100%';
                textarea.classList.add('form-control');
                var notice = document.createElement('div');
                notice.className = 'alert alert-warning mt-2';
                notice.innerHTML =
                    '<i class="fas fa-exclamation-triangle"></i> Editor canggih gagal dimuat. Menggunakan editor teks sederhana.';
                textarea.parentNode.insertBefore(notice, textarea.nextSibling);
            }
        }
    }
});
            </script>
        @endpush
