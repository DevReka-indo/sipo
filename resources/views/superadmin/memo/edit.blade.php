@extends('layouts.app')

@section('title', 'Edit Memo')

@section('content')
    <div class="container-fluid px-4 py-0 mt-0">
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-body py-3">

                <h3 class="fw-bold mb-3">Edit Memo</h3>

                {{-- Breadcrumb --}}
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="bg-white border rounded-2 px-3 py-2 w-100 d-flex align-items-center">
                            <a href="{{ route('superadmin.dashboard') }}"
                                class="text-decoration-none text-primary">Beranda</a>
                            <span class="mx-2 text-muted">/</span>
                            <a href="{{ route('superadmin.memo.index') }}"
                                class="text-decoration-none text-primary">Memo</a>
                            <span class="mx-2 text-muted">/</span>
                            <span class="text-muted">Edit Memo</span>
                        </div>
                    </div>
                </div>

                {{-- Form --}}
                <form action="{{ route('memo/update', $memo->id_memo) }}" id= "memoForm" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">Formulir Edit Memo</div>
                        </div>
                        <div class="card-body">
                            <div id="tujuan-container"></div>
                            {{-- Row 1: Tanggal Surat & Seri Surat --}}
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="tanggal_surat" class="form-label">
                                        <i class="fas fa-calendar-alt text-primary me-1"></i>
                                        Tanggal Surat <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" name="tgl_dibuat" class="form-control"
                                        value="{{ $memo->tgl_dibuat->format('Y-m-d') }}" required>
                                    <input type="hidden" name="tgl_disahkan">
                                </div>
                                <div class="col-md-6">
                                    <label for="seri_surat" class="form-label">
                                        <i class="fas fa-hashtag text-primary me-1"></i>
                                        Seri Surat <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="seri_surat" id="seri_surat" class="form-control"
                                        value="{{ $memo->seri_surat }}" readonly>
                                    <input type="hidden" name="divisi_id_divisi" value="1">
                                    <input type="hidden" name="pembuat" value="Admin Sistem">
                                </div>
                            </div>

                            {{-- Row 2: Nomor Surat & Perihal --}}
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="nomor_surat" class="form-label">
                                        <i class="fas fa-file-alt text-primary me-1"></i>
                                        Nomor Surat <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="nomor_memo" id="nomor_memo" class="form-control"
                                        value="{{ $memo->nomor_memo }}" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label for="perihal" class="form-label">
                                        <i class="fas fa-tag text-primary me-1"></i>
                                        Perihal <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('judul') is-invalid @enderror"
                                        id="perihal" name="judul" value="{{ $memo->judul }}"
                                        placeholder="Masukkan perihal surat" required>
                                    @error('judul')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Row 3: Nama yang Bertanda Tangan & Lampiran --}}
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="nama_bertandatangan" class="form-label">
                                        <i class="fas fa-signature text-primary me-1"></i>
                                        Nama yang Bertanda Tangan <span class="text-danger">*</span>
                                    </label>
                                    <input type="hidden" name="nama_bertandatangan" id="nama_bertandatangan"
                                        class="form-control" value="{{ $memo->nama_bertandatangan }}" required>
                                    <select name="nama_bertandatangan" id="nama_bertandatangan" class="form-control"
                                        value="$memo">
                                        <option value="{{ $memo->nama_bertandatangan }}" selected>
                                            {{ $memo->nama_bertandatangan }}
                                        </option>
                                    </select>
                                    @error('nama_bertandatangan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                {{-- <div class="col-md-6">
                                    <label for="lampiran" class="form-label">
                                        <i class="fas fa-paperclip text-primary me-1"></i>
                                        Lampiran
                                    </label>
                                    <input type="file" class="form-control @error('lampiran') is-invalid @enderror"
                                        id="lampiran" name="lampiran" accept=".pdf">
                                    <small class="form-text text-muted">
                                        Format yang diizinkan: PDF (Max: 2MB)
                                    </small>
                                    @error('lampiran')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div> --}}
                            </div>

                            {{-- Row 4: Kepada (Full Width) --}}
                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="kepada" class="form-label">
                                        <i class="fas fa-user text-primary me-1"></i>
                                        Kepada <span class="text-danger">*</span>
                                        <span class="text-danger" style="font-size: x-small;">Cukup pilih
                                            Divisi /
                                            Departemen / Bagian / Unit / Karyawan yang dituju.</span>
                                    </label>
                                    @push('scripts')
                                        <script>
                                            const tujuanNameArray = @json($tujuanArray);

                                            $(function() {
                                                $('#org-tree')
                                                    .jstree({
                                                        core: {
                                                            data: @json(json_decode($jsTreeData))
                                                        },
                                                        plugins: ["checkbox", "search"],
                                                        checkbox: {
                                                            keep_selected_style: false,
                                                            three_state: false,
                                                            cascade: "none",
                                                        },
                                                    })
                                                    // hide checkboxes for top-level nodes
                                                    .on('ready.jstree', function(e, data) {
                                                        $('#org-tree li').each(function() {
                                                            const node = data.instance.get_node(this.id);
                                                            if (node && node.parent === "#") {
                                                                $(this).find('.jstree-checkbox').hide();
                                                            }
                                                        });

                                                        // ✅ Auto-select tujuan nodes
                                                        const treeInstance = data.instance;
                                                        const allNodes = treeInstance.get_json('#', {
                                                            flat: true
                                                        });

                                                        tujuanNameArray.forEach(name => {
                                                            const foundNode = allNodes.find(node => node.text === name);
                                                            if (foundNode) {
                                                                treeInstance.check_node(foundNode.id);
                                                            }
                                                        });

                                                        // ✅ Open parents of all selected nodes
                                                        treeInstance.get_selected(true).forEach(node => {
                                                            let parentId = treeInstance.get_parent(node.id);
                                                            while (parentId && parentId !== "#") {
                                                                treeInstance.open_node(parentId);
                                                                parentId = treeInstance.get_parent(parentId);
                                                            }
                                                        });
                                                    })
                                                    .on('changed.jstree', function(e, data) {
                                                        document.getElementById("errorTujuan").style.display = "none";

                                                        const sortOrder = ["div", "dept", "section", "unit", "user"];
                                                        const selectedNodes = data.instance.get_selected(true).sort((a, b) => {
                                                            const aType = a.id.split("-")[0];
                                                            const bType = b.id.split("-")[0];
                                                            return sortOrder.indexOf(aType) - sortOrder.indexOf(bType);
                                                        });

                                                        const list = $("#selected-recipients");
                                                        const section = $("#selected-section");
                                                        list.empty();

                                                        if (selectedNodes.length) {
                                                            selectedNodes.forEach(node => {
                                                                list.append(`<li>${node.text}</li>`);
                                                            });
                                                            section.show();
                                                        } else {
                                                            section.hide();
                                                        }
                                                    });
                                            });
                                        </script>
                                    @endpush
                                    <div id="orgTreeError" class="form-control text-danger" style="display:none;"></div>

                                    <div class="border rounded p-2" style="max-height: 300px; overflow-y: auto;">
                                        <div style="font-size: small;" id="org-tree"></div>
                                    </div>

                                    <div style="display: none;" id="selected-section" class="mt-2">
                                        <label style="font-size: small;" class="form-label">
                                            Tujuan Terpilih:
                                        </label>
                                        <div class="border rounded p-2" style="max-height: 300px; overflow-y: auto;">
                                            <ul id="selected-recipients"
                                                style="font-size: small; padding-left: 15px; margin: 0;"></ul>
                                        </div>
                                    </div>

                                    <div style="display: none; font-size: small" id="errorTujuan"
                                        class="form-control text-danger mt-2">
                                        Minimal pilih satu tujuan!
                                    </div>

                                    @error('tujuan[]')
                                        <div class="form-control text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Hidden Fields --}}
                            <input type="hidden" name="pembuat" value="{{ auth()->user()->id }}">

                            {{-- Row 5: Isi Surat (Full Width) --}}
                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="isi_surat" class="form-label">
                                        <i class="fas fa-edit text-primary me-1"></i>
                                        Isi Surat <span class="text-danger">*</span>
                                    </label>
                                    <div class="tinymce-wrapper" id="tinymce-container">
                                        <textarea class="form-control @error('isi_memo') is-invalid @enderror" id="isi_surat" name="isi_memo" rows="10"
                                            placeholder="Tulis isi surat di sini..." required>{{ $memo->isi_memo }}</textarea>
                                    </div>
                                    @error('isi_memo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Section: Keperluan Barang --}}
                            @php
                                // Susun data prefill: prioritas old() (ketika validasi gagal), lalu dari DB ($memo->keperluanBarang)
                                $prefillItems = [];

                                if (old('barang_nama')) {
                                    foreach (old('barang_nama') as $i => $nama) {
                                        $prefillItems[] = [
                                            'id' => old("detail_id.$i"),
                                            'nama' => $nama,
                                            'qty' => old("barang_qty.$i"),
                                            'satuan' => old("barang_satuan.$i"),
                                        ];
                                    }
                                } elseif (!empty($memo) && ($memo->kategoriBarang ?? collect())->isNotEmpty()) {
                                    foreach ($memo->kategoriBarang as $it) {
                                        $prefillItems[] = [
                                            'id' => $it->id, // sesuaikan nama PK detail
                                            'nama' => $it->nama_barang, // sesuaikan kolom DB
                                            'qty' => $it->qty, // sesuaikan kolom DB
                                            'satuan' => $it->satuan, // sesuaikan kolom DB
                                        ];
                                    }
                                }

                                // Tentukan jumlah awal baris (min 1). Default 2 jika kosong.
                                $initialCount = max(1, count($prefillItems) ?: 2);
                            @endphp

                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="p-3 rounded-3 border" style="background:#e9f4ff;border-color:#cfe2ff;">

                                        @if ($memo->kategoriBarang && $memo->kategoriBarang->isNotEmpty())
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div class="fw-semibold" style="color:#1E4178;">Keperluan Barang</div>
                                                {{-- <small class="text-muted">*Isi keperluan barang jika dibutuhkan</small> --}}
                                            </div>
                                            @foreach ($memo->kategoriBarang as $index => $barang)
                                                <div class="row g-3 align-items-end kb-row">
                                                    <input type="hidden"
                                                        name="kategori_barang[{{ $index }}][id_kategori_barang]"
                                                        value="{{ $barang->id_kategori_barang }}">
                                                    <div class="col-lg-3 col-md-3">
                                                        <label class="form-label mb-1">Nomor</label>
                                                        <input type="text"
                                                            id="kategori_barang_{{ $index }}_nomor"
                                                            name="kategori_barang[{{ $index }}][nomor]"
                                                            class="form-control" value="{{ $barang->nomor }}" readonly>
                                                    </div>
                                                    <div class="col-lg-3 col-md-3">
                                                        <label class="form-label mb-1">Barang</label>
                                                        <input type="text"
                                                            id="kategori_barang_{{ $index }}_nama_barang"
                                                            name="kategori_barang[{{ $index }}][barang]"
                                                            class="form-control" value="{{ $barang->barang }}" required
                                                            oninvalid="this.setCustomValidity('Kolom ini wajib diisi.');"
                                                            oninput="this.setCustomValidity('');">
                                                    </div>
                                                    <div class="col-lg-3 col-md-3">
                                                        <label class="form-label mb-1">Qty</label>
                                                        <input type="number"
                                                            id="kategori_barang_{{ $index }}_qty"
                                                            name="kategori_barang[{{ $index }}][qty]"
                                                            class="form-control" value="{{ $barang->qty }}" required
                                                            oninvalid="this.setCustomValidity('Kolom ini wajib diisi.');"
                                                            oninput="this.setCustomValidity('');">
                                                    </div>
                                                    <div class="col-lg-3 col-md-3">
                                                        <label class="form-label mb-1">Satuan</label>
                                                        <input type="text"
                                                            id="kategori_barang_{{ $index }}_satuan"
                                                            name="kategori_barang[{{ $index }}][satuan]"
                                                            class="form-control" value="{{ $barang->satuan }}" required
                                                            oninvalid="this.setCustomValidity('Kolom ini wajib diisi.');"
                                                            oninput="this.setCustomValidity('');">
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                        {{-- Grid Baris Barang --}}
                                        <div id="kb_fields">
                                            <div id="barangContainer" class="d-grid gap-3">
                                                {{-- baris dibuat via JS (prefill dari PHP/DB) --}}
                                            </div>
                                        </div>

                                        {{-- tampung id detail untuk update (sejajar dengan baris) --}}
                                        <input type="hidden" name="deleted_detail_ids" id="deleted_detail_ids">
                                    </div>
                                </div>
                            </div>

                            {{-- kirim data prefill dari PHP ke JS --}}
                            <script>
                                window.kbPrefill = @json($prefillItems); // [{id, nama, qty, satuan}, ...]
                            </script>
                            {{-- =================== /Section: Keperluan Barang =================== --}}


                        </div>

                        <!-- Action Buttons -->
                        <div class="form-group">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('superadmin.memo.index') }}" class="btn rounded-3"
                                    style="background-color:#fff; color:#0d6efd; border:1px solid #0d6efd;">
                                    Batal
                                </a>
                                <button type="submit" id="submitBtn" class="btn btn-primary rounded-3">
                                    Simpan
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection



@push('scripts')
    <script>
        // TinyMCE Initialization
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, initializing TinyMCE...');

            if (typeof tinymce === 'undefined') {
                console.error('TinyMCE not loaded!');
                return;
            }

            var wrapper = document.getElementById('tinymce-container');
            if (wrapper) {
                wrapper.classList.add('loading');
            }

            var loadingTimeout = setTimeout(function() {
                if (wrapper && wrapper.classList.contains('loading')) {
                    wrapper.classList.remove('loading');
                }
            }, 10000);

            try {
                tinymce.init({
                    selector: '#isi_surat',
                    height: 500,
                    menubar: 'edit view insert format tools table',
                    plugins: [
                        'advlist', 'autolink', 'lists', 'link', 'image', 'table', 'code',
                        'wordcount', 'paste', 'searchreplace', 'fullscreen', 'help', 'nonbreaking'
                    ],
                    toolbar: [
                        'undo redo | formatselect | bold italic underline | forecolor backcolor',
                        'alignleft aligncenter alignright | bullist numlist | outdent indent',
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
                            styles: { textAlign: 'left' }
                        },
                        aligncenter: {
                            selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img',
                            styles: { textAlign: 'center' }
                        },
                        alignright: {
                            selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img',
                            styles: { textAlign: 'right' }
                        },
                        alignjustify: {
                            selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img',
                            styles: { textAlign: 'justify' }
                        }
                    },
                    indent_use_margin: true,

                    // Konfigurasi agar Enter dan Shift+Enter menghasilkan jarak yang sama (BR tag)
                    forced_root_block: '',
                    force_br_newlines: true,
                    force_p_newlines: false,
                    end_container_on_empty_block: true,

                    // Custom CSS untuk tab spacing dan line height yang rapat
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

                        .tab-formatted {
                            white-space: pre;
                            font-family: 'Courier New', monospace;
                            tab-size: 8;
                        }

                        /* Table styling */
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
                            $('#isi_surat').trigger('input');
                        });

                        // Cleanup setelah paste
                        editor.on('PastePostProcess', function(e) {
                            var allElements = e.node.querySelectorAll('*');
                            allElements.forEach(function(el) {
                                if (el.tagName.toLowerCase() !== 'p') {
                                    el.style.lineHeight = '1.2';
                                }
                            });
                        });

                        // Override Enter key agar sama dengan Shift+Enter (selalu BR tag)
                        editor.on('keydown', function(e) {
                            // Enter key - paksa insert BR seperti Shift+Enter
                            if (e.keyCode === 13) {
                                if (!e.shiftKey) {
                                    e.preventDefault();
                                    editor.execCommand('InsertLineBreak');
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
                            console.log('TinyMCE initialized');
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

        $('#memoForm').on('submit', function(e) {
            const selectedNodes = $('#org-tree').jstree('get_selected', true);
            if (selectedNodes.length === 0) {
                e.preventDefault();
                $('#errorTujuan').show()[0].scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
                return false;
            }
        });

        $(document).ready(function() {
            console.log('submit trigger');

            // Custom validator untuk TinyMCE
            $.validator.addMethod('tinymceRequired', function(value, element) {
                const editorId = $(element).attr('id');
                if (tinymce.get(editorId)) {
                    const content = tinymce.get(editorId).getContent({ format: 'text' });
                    return content.trim().length > 0;
                }
                return value.trim().length > 0;
            }, 'Isi surat harus diisi');

            $.validator.addMethod('tinymceNotEmpty', function(value, element) {
                const editorId = $(element).attr('id');
                if (tinymce.get(editorId)) {
                    const content = tinymce.get(editorId).getContent({ format: 'text' });
                    return content.trim().length > 0 && content.trim() !== '';
                }
                return value.trim().length > 0;
            }, 'Isi surat tidak boleh kosong');

            $('#memoForm').validate({
                ignore: [],
                rules: {
                    tanggal_surat: {
                        required: true
                    },
                    seri_surat: {
                        required: true
                    },
                    nomor_surat: {
                        required: true
                    },
                    perihal: {
                        required: true
                    },
                    kepada: {
                        required: true
                    },
                    manager_user_id: {
                        required: true
                    },
                    isi_memo: {
                        tinymceRequired: true,
                        tinymceNotEmpty: true
                    }
                },
                messages: {
                    tanggal_surat: {
                        required: "Tanggal surat harus diisi"
                    },
                    seri_surat: {
                        required: "Seri surat harus diisi"
                    },
                    nomor_surat: {
                        required: "Nomor surat harus diisi"
                    },
                    perihal: {
                        required: "Perihal harus diisi"
                    },
                    kepada: {
                        required: "Kepada harus diisi"
                    },
                    manager_user_id: {
                        required: "Nama yang beratanda tangan harus diisi"
                    },
                    isi_memo: {
                        tinymceRequired: "Isi surat harus diisi",
                        tinymceNotEmpty: "Isi surat tidak boleh kosong"
                    },
                    jumlah_kolom: {
                        required: "Jumlah kategori harus diisi"
                    }
                },
                errorElement: 'span',
                errorPlacement: function(error, element) {
                    error.addClass('invalid-feedback');
                    if (element.attr('id') === 'isi_surat') {
                        element.closest('.tinymce-wrapper').after(error);
                        element.closest('.tinymce-wrapper').addClass('has-error');
                    } else {
                        element.closest('.form-group').append(error);
                    }
                },
                highlight: function(element, errorClass, validClass) {
                    $(element).addClass('is-invalid');
                    if ($(element).attr('id') === 'isi_surat') {
                        $(element).closest('.tinymce-wrapper').addClass('has-error');
                    }
                },
                unhighlight: function(element, errorClass, validClass) {
                    $(element).removeClass('is-invalid');
                    if ($(element).attr('id') === 'isi_surat') {
                        $(element).closest('.tinymce-wrapper').removeClass('has-error');
                    }
                },
                submitHandler: function(form) {
                    // Sinkronisasi TinyMCE dengan textarea sebelum submit
                    if (tinymce.get('isi_surat')) {
                        tinymce.get('isi_surat').save();
                    }
                    // Clear existing tujuan[] inputs
                    $('#tujuan-container').empty();

                    // Get selected nodes
                    const selectedNodes = $('#org-tree').jstree('get_selected', true);

                    const tujuan = selectedNodes;
                    if (selectedNodes.length === 0) {
                        document.getElementById('errorTujuan').style.display = 'block';
                        document.getElementById('errorTujuan').scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        e.preventDefault();
                        submitBtn.prop('disabled', false).text('Simpan');
                        return false;
                    }
                    let sortOrder = ['div', 'dept', 'section', 'unit', 'user'];
                    selectedNodes.sort((a, b) => {
                        let aType = a.id.split('-')[0];
                        let bType = b.id.split('-')[0];
                        return sortOrder.indexOf(aType) - sortOrder.indexOf(bType);
                    });
                    tujuan.forEach(node => {
                        const nodeId = node.id
                        const nodeText = node.text;
                        $('#tujuan-container').append(
                            `<input type="hidden" name="tujuan[]" value="${node.id}">` +
                            `<input type="hidden" name="tujuanString[]" value="${node.text}">`
                        );
                    });

                    // Show loading state
                    const submitBtn = $(form).find('button[type="submit"]');
                    const originalText = submitBtn.html();
                    submitBtn.html('<i class="fas fa-spinner fa-spin me-1"></i>Menyimpan...');
                    submitBtn.prop('disabled', true);

                    console.log("Submitting form with tujuan:", $('#tujuan-container').html());

                    // Submit form normally
                    form.submit();
                }
            });
        });
        (function() {
            const jumlahInput = document.getElementById('jumlahKategori');
            const container = document.getElementById('barangContainer');
            const kbFields = document.getElementById('kb_fields');
            const kbYes = document.getElementById('kb_yes');
            const kbNo = document.getElementById('kb_no');

            const prefill = Array.isArray(window.kbPrefill) ? window.kbPrefill : [];

            function rowTemplate(idx, preset = {}) {
                const nomor = idx + 1;
                const id = preset.id ?? '';
                const barang = preset.nama ?? '';
                const qty = preset.qty ?? '';
                const satuan = preset.satuan ?? '';

                return `
      <div class="row g-3 align-items-end kb-row">
        <input type="hidden" name="detail_id[]" value="${id}">
        <div class="col-lg-3 col-md-3">
          <label class="form-label mb-1">Nomor</label>
          <input type="text" class="form-control nomor-field" name="barang_nomor[]" value="${nomor}" readonly
                 style="background:#edf2f7;">
        </div>
        <div class="col-lg-3 col-md-3">
          <label class="form-label mb-1">Barang</label>
          <input type="text" class="form-control" name="barang_nama[]" placeholder="Masukkan barang" value="${barang}">
        </div>
        <div class="col-lg-3 col-md-3">
          <label class="form-label mb-1">Qty</label>
          <input type="number" class="form-control" name="barang_qty[]" placeholder="Masukkan jumlah" min="0" value="${qty}">
        </div>
        <div class="col-lg-3 col-md-3">
          <label class="form-label mb-1">Satuan</label>
          <input type="text" class="form-control" name="barang_satuan[]" placeholder="Masukkan satuan" value="${satuan}">
        </div>
      </div>
    `;
            }

            function renumber() {
                container.querySelectorAll('.nomor-field').forEach((el, i) => el.value = i + 1);
            }

            // render tepat sejumlah 'count' dengan data preset (kalau ada)
            function renderWith(count, data = []) {
                container.innerHTML = '';
                for (let i = 0; i < count; i++) {
                    const wrap = document.createElement('div');
                    wrap.innerHTML = rowTemplate(i, data[i] || {});
                    container.appendChild(wrap.firstElementChild);
                }
                renumber();
            }

            // Saat user mengubah jumlah baris:
            jumlahInput.addEventListener('input', function(e) {
                let count = parseInt(e.target.value || '1', 10);
                if (isNaN(count) || count < 1) count = 1;

                const current = container.querySelectorAll('.kb-row').length;

                // Kumpulkan nilai yang sudah terisi agar tidak hilang saat ganti jumlah
                const snapshot = [];
                container.querySelectorAll('.kb-row').forEach(row => {
                    snapshot.push({
                        id: row.querySelector('input[name="detail_id[]"]').value,
                        nama: row.querySelector('input[name="barang_nama[]"]')
                            .value,
                        qty: row.querySelector('input[name="barang_qty[]"]').value,
                        satuan: row.querySelector('input[name="barang_satuan[]"]')
                            .value,
                    });
                });

                if (count === current) return;

                // jika diperbesar → tambah baris kosong di belakang
                if (count > current) {
                    for (let i = current; i < count; i++) snapshot[i] = snapshot[i] || {
                        id: '',
                        nama: '',
                        qty: '',
                        satuan: ''
                    };
                } else {
                    // jika diperkecil → potong dari belakang
                    snapshot.length = count;
                }

                renderWith(count, snapshot);
            });

            // Toggle aktif/nonaktif section
            function setEnabled(enabled) {
                kbFields.style.display = enabled ? '' : 'none';
                jumlahInput.disabled = !enabled;
                container.querySelectorAll('input').forEach(inp => inp.disabled = !enabled);
            }
            kbYes.addEventListener('change', () => setEnabled(true));
            kbNo.addEventListener('change', () => setEnabled(false));

            // INIT: jika ada data DB/old → pakai itu; kalau tidak ada → pakai jumlah bawaan
            if (prefill.length) {
                jumlahInput.value = prefill.length;
                renderWith(prefill.length, prefill);
            } else {
                renderWith(parseInt(jumlahInput.value || '1', 10));
            }
            setEnabled(true);
        })();
    </script>
@endpush
