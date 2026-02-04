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
                                            <div class="col-md-12">
                                                <label class="form-label mb-1">
                                                    <i class="fas fa-file-alt text-primary me-1"></i> Nomor Surat <span
                                                        class="text-danger">*</span>
                                                </label>
                                                <input type="text"
                                                    class="form-control @error('nomor_undangan') is-invalid @enderror"
                                                    id="nomor_undangan" name="nomor_undangan"
                                                    placeholder="Contoh: 218.23/REKA/GEN/LOG/X/2025"
                                                    value="{{ old('nomor_undangan') }}">
                                                @error('nomor_undangan')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            {{-- <div class="col-md-6">
                                                <label class="form-label mb-1">
                                                    <i class="fas fa-hashtag text-primary me-1"></i> Seri Tahunan Surat <span
                                                        class="text-danger">*</span>
                                                </label>
                                                <input type="text"
                                                    class="form-control @error('seri_surat') is-invalid @enderror"
                                                    id="seri_surat" name="seri_surat" placeholder="Contoh:001"
                                                    value="{{ old('seri_surat') }}">
                                                @error('seri_surat')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div> --}}
                                            <input type="hidden" name="tgl_disahkan">
                                            <input type="hidden" name="catatan">
                                            <input type="hidden" name="kode" value="{{ $kode }}">
                                            <input type="hidden" name="pembuat" value="{{ auth()->user()->id }}">
                                        </div>

                                        {{-- ROW 2: Nomor Surat | Perihal --}}
                                        <div class="row g-3 mt-1">
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
                                                    <i class="fas fa-user text-primary me-1"></i> Kepada
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
                                            <div class="col-md-6 d-flex flex-column">
                                                <label class="form-label">
                                                    <i class="fas fa-paperclip text-primary me-1"></i> Lampiran
                                                </label>

                                                <input type="file"
                                                    class="form-control @error('lampiran') is-invalid @enderror"
                                                    id="lampiran" name="lampiran[]" accept=".pdf,.jpg,.jpeg,.png" multiple>

                                                <!-- pastikan small di dalam col-md-6 -->
                                                <small class="text-muted mt-1">
                                                    Format yang diizinkan: PDF, JPG, JPEG, PNG (Max: 2MB)
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
                                                        rows="10" placeholder="Tulis agenda disini..." required>{{ old('isi_undangan') }}</textarea>
                                                </div>
                                                <small class="form-text text-muted mt-1">
                                                    <i class="fas fa-info-circle text-info me-1"></i>
                                                    Kamu bisa menggunakan bullet, penomoran, atau tabel untuk merapikan agenda rapat.
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
                // TinyMCE untuk Agenda (isi_undangan)
                // ==========================
                document.addEventListener('DOMContentLoaded', function() {
                    console.log('Init TinyMCE untuk isi_undangan (Tambah Undangan Manager)...');

                    if (typeof tinymce === 'undefined') {
                        console.error('TinyMCE tidak ditemukan. Pastikan script TinyMCE sudah dimuat di layout.');
                        return; // fallback: tetap pakai textarea biasa
                    }

                    try {
                        tinymce.init({
                            selector: '#isi_undangan',
                            height: 450,
                            menubar: 'edit view insert format tools table',
                            plugins: [
                                'advlist', 'autolink', 'lists', 'link', 'image', 'table', 'code',
                                'wordcount', 'paste', 'searchreplace', 'fullscreen', 'help', 'nonbreaking'
                            ],
                            toolbar: [
                                'undo redo | formatselect | bold italic underline | forecolor backcolor',
                                'alignleft aligncenter alignright alignjustify | bullist numlist | outdent indent',
                                'link image table | tabAlign | nonbreaking | code fullscreen | help'
                            ],
                            branding: false,
                            promotion: false,
                            statusbar: false,
                            table_default_attributes: {
                                'border': '1',
                                'style': 'border-collapse: collapse; width: 100%; table-layout: auto;'
                            },
                            table_default_styles: {
                                'border-collapse': 'collapse',
                                'width': '100%',
                                'table-layout': 'auto'
                            },
                            table_cell_default_attributes: {
                                'style': 'border: 1px solid #000; padding: 8px; word-wrap: break-word; vertical-align: top;'
                            },
                            table_cell_default_styles: {
                                'border': '1px solid #000',
                                'padding': '8px',
                                'word-wrap': 'break-word',
                                'vertical-align': 'top'
                            },
                            paste_data_images: true,
                            paste_word_valid_elements: "b,strong,i,em,h1,h2,h3,h4,h5,h6,p,ol,ul,li,a[href],span,color,font-size,font-color,font-family,mark,table,tr,td,th,div",
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

                            // Konfigurasi agar Enter dan Shift+Enter menghasilkan jarak yang sama (BR tag)
                            forced_root_block: '',
                            force_br_newlines: true,
                            force_p_newlines: false,
                            end_container_on_empty_block: true,

                            content_css: 'data:text/css;charset=UTF-8,' + encodeURIComponent(`
                                /* Global reset untuk line height yang rapat */
                                body {
                                    line-height: 1.2 !important;
                                    margin: 0;
                                    padding: 8px;
                                }

                                /* Paragraph dengan margin 0 agar jarak sama dengan BR */
                                p {
                                    margin: 0 !important;
                                    line-height: 1.2 !important;
                                    padding: 0 !important;
                                    display: block !important;
                                }

                                /* BR tag dengan line-height yang sama dengan paragraph */
                                br {
                                    line-height: 1.2 !important;
                                    display: block !important;
                                    content: "" !important;
                                    margin: 0 !important;
                                    padding: 0 !important;
                                }

                                /* Div styling */
                                div {
                                    margin: 0 !important;
                                    line-height: 1.2 !important;
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

                                /* Styling untuk preformatted text dengan tab */
                                .tab-formatted {
                                    white-space: pre;
                                    font-family: 'Courier New', monospace;
                                    tab-size: 8;
                                }

                                /* Table styling untuk spacing yang konsisten */
                                table {
                                    margin: 0.3em 0 !important;
                                    line-height: 1.2 !important;
                                }

                                table p {
                                    margin: 0 !important;
                                    line-height: 1.2 !important;
                                }

                                td, th {
                                    padding: 8px !important;
                                    line-height: 1.2 !important;
                                }

                                /* List styling */
                                ul, ol {
                                    margin: 0.3em 0 !important;
                                    padding-left: 2em !important;
                                    line-height: 1.2 !important;
                                }

                                li {
                                    line-height: 1.2 !important;
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
                                            el.style.lineHeight = '1.2';
                                        }
                                    });
                                });

                                editor.on('keydown', function(e) {
                                    // Enter key - paksa insert BR untuk kedua Enter dan Shift+Enter
                                    if (e.keyCode === 13) {
                                        e.preventDefault();
                                        editor.execCommand('InsertLineBreak');
                                        return false;
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
                                            var tabSpaces = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                                            editor.insertContent(tabSpaces);
                                        }
                                    }
                                });

                                editor.ui.registry.addButton('tabAlign', {
                                    text: 'Tab Align',
                                    tooltip: 'Insert tab spaces for alignment',
                                    onAction: function() {
                                        var tabSpaces = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                                        editor.insertContent(tabSpaces);
                                    }
                                });

                                editor.on('init', function() {
                                    console.log('TinyMCE isi_undangan (Tambah Undangan Manager) siap');
                                });
                            }
                        });
                    } catch (e) {
                        console.error('Gagal init TinyMCE isi_undangan:', e);
                    }
                });
            </script>
        @endpush
