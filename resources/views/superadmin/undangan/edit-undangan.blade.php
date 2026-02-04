@extends('layouts.app')

@section('title', 'Edit Undangan Rapat')

@section('content')
    <div class="container-fluid px-4 py-0 mt-0">
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-body py-3">
                <h3 class="fw-bold mb-3">Edit Undangan Rapat</h3>
                {{-- Breadcrumb --}}
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="bg-white border rounded-2 px-3 py-2 w-100 d-flex align-items-center">
                            <a href="{{ route('superadmin.dashboard') }}" class="text-decoration-none text-primary">Beranda</a>
                            <span class="mx-2 text-muted">/</span>
                            <a href="{{ route('superadmin.undangan.index') }}" class="text-decoration-none text-primary">Undangan
                                Rapat</a>
                            <span class="mx-2 text-muted">/</span>
                            <span class="text-muted">Edit Undangan Rapat</span>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Formulir Edit Undangan Rapat</div>
                    </div>
                    <div class="card-body">
                        <form method="POST" id="addUndanganForm"
                            action="{{ route('undangan/update', $undangan->id_undangan) }}"
                            onsubmit="console.log('FORM DIKIRIM'); return true;">
                            @csrf
                            @method('PUT')

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Tanggal Surat <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control"
                                        value="{{ $undangan->tgl_dibuat->format('Y-m-d') }}" disabled>
                                    <input type="hidden" name="tgl_dibuat"
                                        value="{{ $undangan->tgl_dibuat->format('Y-m-d') }}">
                                    <input type="hidden" name="tgl_disahkan"
                                        value="{{ $undangan->tgl_disahkan ? $undangan->tgl_disahkan : '' }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Seri Surat</label>
                                    <input type="text" class="form-control" name="seri_surat"
                                        value="{{ $undangan->seri_surat }}" readonly>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nomor Surat</label>
                                    <input type="text" class="form-control" name="nomor_undangan"
                                        value="{{ $undangan->nomor_undangan }}" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Perihal <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('judul') is-invalid @enderror"
                                        name="judul" value="{{ old('judul', $undangan->judul) }}" required>
                                    @error('judul')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="kepada" class="form-label">
                                            <i class="fas fa-user text-primary me-1"></i>
                                            Kepada <span class="text-danger">* Pilih user atau struktur, semua user
                                                di bawah struktur akan otomatis terpilih</span>
                                        </label>
                                        <div class="border rounded p-2" style="max-height: 300px; overflow-y: auto;">
                                            <div style="font-size: small" class="form-label" id="org-tree"></div>
                                            <style>
                                                #org-tree .jstree-anchor {
                                                    color: #1f4178;
                                                    font-weight: 500;
                                                }
                                            </style>
                                            <small id="tujuanError" class="text-danger" style="display:none;">Minimal pilih
                                                satu tujuan!</small>
                                        </div>
                                        <!-- Tujuan Container - Moved here for better organization -->
                                        <div id="tujuan-container"></div>
                                        @error('tujuan')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Added section for displaying selected recipients -->
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

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Tanggal Rapat <span class="text-danger">*</span></label>
                                    <input type="date" name="tgl_rapat"
                                        class="form-control @error('tgl_rapat') is-invalid @enderror"
                                        value="{{ old('tgl_rapat', optional(\Carbon\Carbon::parse($undangan->tgl_rapat))->format('Y-m-d')) }}"
                                        required>
                                    @error('tgl_rapat')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Waktu Rapat <span class="text-danger">*</span></label>
                                    <div class="d-flex">
                                        <input type="text" name="waktu_mulai"
                                            class="form-control me-2 @error('waktu_mulai') is-invalid @enderror"
                                            value="{{ old('waktu_mulai', $undangan->waktu_mulai) }}"
                                            placeholder="Waktu Mulai" required>
                                        <span class="fw-bold mt-2">s/d</span>
                                        <input type="text" name="waktu_selesai"
                                            class="form-control ms-2 @error('waktu_selesai') is-invalid @enderror"
                                            value="{{ old('waktu_selesai', $undangan->waktu_selesai) }}"
                                            placeholder="Waktu Selesai" required>
                                    </div>
                                    @error('waktu_mulai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @error('waktu_selesai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Tempat Rapat <span class="text-danger">*</span></label>
                                <input type="text" name="tempat"
                                    class="form-control @error('tempat') is-invalid @enderror"
                                    value="{{ old('tempat', $undangan->tempat) }}" required>
                                @error('tempat')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nama yang Bertanda Tangan <span
                                        class="text-danger">*</span></label>
                                {{-- Hidden input untuk memastikan data terkirim --}}
                                <input type="hidden" name="nama_bertandatangan"
                                    value="{{ $undangan->nama_bertandatangan }}">
                                <select name="nama_bertandatangan" id="nama_bertandatangan" class="form-control"
                                    disabled>
                                    @foreach ($managers as $manager)
                                        <option value="{{ $manager->firstname . ' ' . $manager->lastname }}"
                                            {{ old('nama_bertandatangan', $undangan->nama_bertandatangan) == $manager->firstname . ' ' . $manager->lastname ? 'selected' : '' }}>
                                            {{ $manager->firstname . ' ' . $manager->lastname }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('nama_bertandatangan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Agenda -->
                            <div class="form-group mb-3">
                                <label for="isi_undangan" class="form-label">
                                    <i class="fas fa-edit text-primary me-1"></i>
                                    Agenda <span class="text-danger">*</span>
                                </label>
                                <div class="tinymce-wrapper" id="tinymce-agenda-container">
                                    <textarea class="form-control @error('isi_undangan') is-invalid @enderror" id="isi_undangan" name="isi_undangan"
                                        rows="10" placeholder="Tulis agenda disini..." required>{{ old('isi_undangan', $undangan->isi_undangan) }}</textarea>
                                </div>
                                @error('isi_undangan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="card-action d-flex justify-content-between">
                                <a href="{{ route('superadmin.undangan.index') }}" class="btn btn-danger">Batal</a>
                                <button type="submit" class="btn btn-success" id="submitBtn">
                                    <span class="spinner-border spinner-border-sm d-none" role="status"
                                        aria-hidden="true"></span>
                                    Simpan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // TinyMCE Initialization
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof tinymce === 'undefined') {
                console.error('TinyMCE not loaded!');
                return;
            }

            var wrapper = document.getElementById('tinymce-agenda-container');
            if (wrapper) wrapper.classList.add('loading');

            var loadingTimeout = setTimeout(function() {
                if (wrapper && wrapper.classList.contains('loading')) {
                    wrapper.classList.remove('loading');
                }
            }, 10000);

            try {
                tinymce.init({
                    selector: '#isi_undangan',
                    height: 450,
                    menubar: 'edit view insert format tools table',
                    plugins: [
                        'advlist', 'autolink', 'lists', 'link', 'table', 'code',
                        'wordcount', 'paste', 'searchreplace', 'fullscreen', 'help', 'nonbreaking'
                    ],
                    toolbar: [
                        'undo redo | formatselect | bold italic underline | forecolor backcolor',
                        'alignleft aligncenter alignright | bullist numlist | outdent indent',
                        'link table | code fullscreen'
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
                    entity_encoding: 'raw',
                    keep_styles: true,

                    // Konfigurasi agar Enter dan Shift+Enter menghasilkan jarak yang sama (BR tag)
                    forced_root_block: '',
                    force_br_newlines: true,
                    force_p_newlines: false,
                    end_container_on_empty_block: true,

                    content_css: 'data:text/css;charset=UTF-8,' + encodeURIComponent(`
                        body {
                            line-height: 1.2 !important;
                            margin: 0;
                            padding: 8px;
                        }
                        p {
                            margin: 0 !important;
                            line-height: 1.2 !important;
                            padding: 0 !important;
                            display: block !important;
                        }
                        br {
                            line-height: 1.2 !important;
                            display: block !important;
                            content: "" !important;
                            margin: 0 !important;
                            padding: 0 !important;
                        }
                        div {
                            margin: 0 !important;
                            line-height: 1.2 !important;
                            padding: 0 !important;
                        }
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
                            if (e.keyCode === 13) { // Enter key
                                if (!e.shiftKey) {
                                    e.preventDefault();
                                    editor.execCommand('InsertLineBreak');
                                }
                            }
                        });

                        editor.on('init', function() {
                            console.log('TinyMCE isi_undangan ready');
                            if (loadingTimeout) clearTimeout(loadingTimeout);
                            if (wrapper) wrapper.classList.remove('loading');
                        });
                    }
                });
            } catch (error) {
                console.error('Error initializing TinyMCE:', error);
                if (wrapper) wrapper.classList.remove('loading');
            }
        });

        $(function() {
            const selectedTujuan = @json($tujuanArray);

            $('#org-tree').jstree({
                'core': {
                    'data': @json($jsTreeData)
                },
                'plugins': ['checkbox', 'search']
            }).on('ready.jstree', function(e, data) {
                // Check the saved user checkboxes
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
            }).on('changed.jstree', function(e, data) {
                updateSelectedRecipients(data);
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
        });

        // Form submission handler
        $('#addUndanganForm').on('submit', function(e) {
            e.preventDefault(); // Prevent default submission first

            const submitBtn = $('#submitBtn');
            const spinner = submitBtn.find('.spinner-border');
            const tujuanError = $('#tujuanError');
            const tujuanContainer = $('#tujuan-container');

            // Show loading state
            submitBtn.prop('disabled', true);
            spinner.removeClass('d-none');

            // Clear previous tujuan inputs
            tujuanContainer.html('');

            const selected = $('#org-tree').jstree('get_selected', true);
            const userIds = selected
                .filter(node => node.id.startsWith('user-'))
                .map(node => node.id.replace('user-', ''));

            // Validate that at least one recipient is selected
            if (userIds.length === 0) {
                tujuanError.text("Minimal pilih satu tujuan!");
                tujuanError.show();

                // Reset button state
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');

                // Scroll to error
                tujuanError[0].scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });

                return false;
            }

            // Hide error if validation passes
            tujuanError.hide();

            // Add tujuan inputs
            userIds.forEach(userId => {
                tujuanContainer.append(`<input type="hidden" name="tujuan[]" value="${userId}">`);
            });

            // Log form data for debugging
            console.log('Form Data:', new FormData(this));
            console.log('Selected User IDs:', userIds);

            // Submit the form
            this.submit();
        });
    </script>
@endpush
