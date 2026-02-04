@extends('layouts.app')

@section('title', 'Tambah Memo')

@section('content')
    <div class="container-fluid px-4 py-0 mt-0">
        <div class="card shadow-sm border-0">
            <div class="card-body py-3">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="fw-bold mb-0">Tambah Memo</h3>
                </div>

                {{-- Breadcrumb --}}
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="bg-white border rounded-2 px-3 py-2 w-100 d-flex align-items-center">
                            <a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-primary">Beranda</a>
                            <span class="mx-2 text-muted">/</span>
                            <a href="{{ route('admin.memo.index') }}" class="text-decoration-none text-primary">Memo</a>
                            <span class="mx-2 text-muted">/</span>
                            <span class="text-muted">Tambah Memo</span>
                        </div>
                    </div>
                </div>


                <!-- Form Card -->
                <div class="row">
                    <div class="col-12">
                        @if ($errors->any())
                            <div class="alert alert-danger mb-3">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="card">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h4 class="card-title mb-0">
                                        <i class="fas fa-plus-circle text-primary me-2"></i>
                                        Form Tambah Memo
                                    </h4>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Form -->
                                <form action="{{ route('admin-memo.store') }}" method="POST" enctype="multipart/form-data"
                                    id="memoForm">
                                    @csrf
                                    <div id="tujuan-container"></div>
                                    <div class="row">
                                        @if ($parentMemo)
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="memo_feedback" class="form-label">
                                                        <i class="fas fa-file-alt text-primary me-1"></i>
                                                        Merujuk Pada Nomor Surat <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text"
                                                        class="form-control @error('memo_feedback') is-invalid @enderror"
                                                        id="memo_feedback" name="memo_feedback"
                                                        value="{{ $parentMemo->nomor_memo }}" readonly>
                                                </div>
                                            </div>
                                            <!-- Nomor Surat -->
                                            <div class="col-md-6">
                                            @elseif(!$parentMemo)
                                                <div class="col-md-12">
                                        @endif
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
                                        {{-- <div class="form-group">
                                            <label for="nomor_memo" class="form-label">
                                                <i class="fas fa-file-alt text-primary me-1"></i>
                                                Nomor Surat <span class="text-danger">*</span>
                                            </label>
                                            <input type="text"
                                                class="form-control @error('nomor_memo') is-invalid @enderror"
                                                id="nomor_memo" name="nomor_memo" value="{{ old('nomor_memo') }}"
                                                placeholder="Contoh: 218.23/REKA/GEN/LOG/X/2025" required>
                                            @error('nomor_memo')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div> --}}
                                    </div>
                            </div>

                            <div class="row">
                                <!-- Tanggal Surat -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tanggal_surat" class="form-label">
                                            <i class="fas fa-calendar-alt text-primary me-1"></i>
                                            Tanggal Surat <span class="text-danger">*</span>
                                        </label>

                                        <input type="date" class="form-control @error('tgl_dibuat') is-invalid @enderror"
                                            id="tanggal_surat" name="tgl_dibuat"
                                            value="{{ old('tgl_dibuat', now()->format('Y-m-d')) }}" readonly>

                                        @error('tgl_dibuat')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <!-- Seri Tahunan Surat -->
                                {{-- <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="seri_surat" class="form-label">
                                                    <i class="fas fa-hashtag text-primary me-1"></i>
                                                    Seri Tahunan Surat <span class="text-danger">*</span>
                                                </label>
                                                <input type="text"
                                                    class="form-control @error('seri_surat') is-invalid @enderror"
                                                    id="seri_surat" name="seri_surat" value="{{ old('seri_surat') }}"
                                                    placeholder="Contoh: 001">
                                                @error('seri_surat')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div> --}}

                                <!-- Perihal -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="perihal" class="form-label">
                                            <i class="fas fa-tag text-primary me-1"></i>
                                            Perihal <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control @error('judul') is-invalid @enderror"
                                            id="perihal" name="judul" value="{{ old('judul') }}"
                                            placeholder="Masukkan perihal surat" required>
                                        @error('judul')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>



                            <div class="row">
                                <!-- Nama yang Beratanda Tangan -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nama_bertandatangan" class="form-label">
                                            <i class="fas fa-signature text-primary me-1"></i>
                                            Nama yang Bertanda Tangan <span class="text-danger">*</span>
                                        </label>
                                        <select name="manager_user_id" id="manager_user_id" class="form-control">
                                            <option value="" disabled selected style="text-align: left;">
                                                --Pilih--</option>
                                            @foreach ($managers as $manager)
                                                @php
                                                    preg_match(
                                                        '/\((.*?)\)/',
                                                        $manager->position->nm_position,
                                                        $matches,
                                                    );
                                                    $kode_position = $matches[1] ?? $manager->position->nm_position;
                                                @endphp
                                                <option value="{{ $manager->id }}">
                                                    ({{ $kode_position }})
                                                    {{ $manager->firstname }}{{ $manager->lastname ? ' ' . $manager->lastname : '' }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <input type="hidden" name="nama_bertandatangan" id="namaBertandatangan">
                                        @error('nama_bertandatangan')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
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



                            </div>
                            {{-- Pembuat --}}
                            <input type="hidden" name="pembuat" value="{{ auth()->user()->id }}">
                            <input type="hidden" name="divisi_id_divisi" value="{{ auth()->user()->divisi_id ?? '' }}">
                            <input type="hidden" name="catatan" value="">
                            <input type="hidden" name="tgl_disahkan" value="">
                            <input type="hidden" name="seri_surat" value="">
                            <!-- Kepada -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="kepada" class="form-label">
                                            <i class="fas fa-user text-primary me-1"></i>
                                            Kepada <span class="text-danger">*</span>

                                        </label>
                                        <small class="text-danger form-text" style="font-size: x-small;">Cukup
                                            pilih
                                            Divisi /
                                            Departemen / Bagian / Unit / Karyawan yang dituju.</small>
                                        <div id="orgTreeError" class="form-control text-danger" style="display:none;">
                                        </div>

                                        <div class="col-md-12">
                                            <div class="border rounded p-2" style="max-height: 300px; overflow-y: auto;">
                                                <div style=" font-size: small;" id="org-tree"></div>
                                            </div>
                                            @push('scripts')
                                                <script>
                                                    $(function() {
                                                        $('#org-tree').jstree({
                                                            'core': {
                                                                'data': @json(json_decode($jsTreeData))
                                                            },
                                                            'plugins': ["checkbox", "search"],
                                                            'checkbox': {
                                                                'three_state': false,
                                                                'cascade': 'none'
                                                            }
                                                        }).on('ready.jstree', function(e, data) {
                                                            // hide checkboxes for top-level nodes
                                                            $('#org-tree li').each(function() {
                                                                var node = data.instance.get_node(this.id);
                                                                if (node && node.parent === "#") {
                                                                    // hide checkbox using CSS
                                                                    $(this).find('.jstree-checkbox').css('display', 'none');
                                                                }
                                                            });
                                                        }).on('changed.jstree', function(e, data) {
                                                            document.getElementById('errorTujuan').style.display = 'none';
                                                            let sortOrder = ['div', 'dept', 'section', 'unit', 'user'];
                                                            let selectedNodes = data.instance.get_selected(true)
                                                                .sort((a, b) => {
                                                                    let aType = a.id.split('-')[0]; // prefix before "-"
                                                                    let bType = b.id.split('-')[0];
                                                                    return sortOrder.indexOf(aType) - sortOrder.indexOf(bType);
                                                                });

                                                            let list = $('#selected-recipients');
                                                            let section = $('#selected-section');
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
                                            <div style="display: none;" id="selected-section">
                                                <label style="font-size: small;" class="form-label">
                                                    Tujuan Terpilih:
                                                </label>
                                                <div class="border rounded p-2"
                                                    style="max-height: 300px; overflow-y: auto;">
                                                    <ul id="selected-recipients"
                                                        style="font-size: small; padding-left: 15px; margin: 0;">
                                                    </ul>
                                                </div>
                                            </div>
                                            <div style="display: none; font-size: small" id="errorTujuan"
                                                class="form-control text-danger">
                                                Minimal pilih satu tujuan!
                                            </div>
                                        </div>
                                        @error('tujuan[]')
                                            <div class="form-control text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                            </div>
                            <div class="form-group">
                                <div class="col-md-6">
                                    <label for="tembusan" class="form-label">
                                        <i class="fas fa-user text-primary me-1"></i>
                                        Tembusan <span class="text-muted form-text" style="font-size: x-small;">(Kosongkan
                                            jika
                                            tidak ada.)</span>
                                    </label>
                                    <select name="tembusan[]" id="tembusan" class="select2" multiple="multiple">
                                        @foreach ($tembusan as $t)
                                            <option value="{{ $t['id'] }}">{{ $t['name'] }}</option>
                                        @endforeach
                                    </select>

                                </div>
                            </div>
                            {{-- <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="nama_bertandatangan" class="form-label">
                                                    <i class="fas fa-user text-primary me-1"></i>
                                                    Cc <span class="text-danger">*</span>
                                                </label>
                                                <select name="cc_user_id" id="cc_user_id" class="select2">
                                                    <option value="" disabled selected style="text-align: left;">
                                                        --Pilih--</option>

                                                </select>

                                                <input type="hidden" name="nama_bertandatangan" id="namaBertandatangan">
                                                @error('nama_bertandatangan')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div> --}}
                            <!-- Isi Surat -->
                            <div class="form-group">
                                <label for="isi_surat" class="form-label">
                                    <i class="fas fa-edit text-primary me-1"></i>
                                    Isi Surat <span class="text-danger">*</span>
                                </label>

                                <div class="tinymce-wrapper" id="tinymce-container">
                                    <textarea class="form-control @error('isi_memo') is-invalid @enderror" id="isi_surat" name="isi_memo" rows="10"
                                        placeholder="Tulis isi surat di sini..." required>{{ old('isi_memo') }}</textarea>
                                </div>
                                <small class="form-text text-muted mt-2">
                                    <i class="fas fa-info-circle text-info me-1"></i>
                                    <strong>Tips untuk Tabel:</strong>
                                    <ul class="mb-0 ps-3 mt-1">
                                        <li>Gunakan fitur <strong>Table</strong> di toolbar editor untuk membuat
                                            tabel yang rapi</li>
                                        <li>Hindari tabel dengan terlalu banyak kolom (maksimal 6-7 kolom agar tidak
                                            terpotong)</li>
                                        <li>Gunakan text yang singkat dan jelas dalam setiap sel tabel</li>
                                    </ul>
                                </small>
                                @error('isi_memo')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            @php
                                use Illuminate\Support\Str;

                                // Collect only errors for barang, qty, satuan (including array indices)
                                $barangQtySatuanErrors = collect($errors->getMessages())
                                    ->filter(function ($messages, $key) {
                                        return Str::startsWith($key, ['barang', 'qty', 'satuan']);
                                    })
                                    ->flatten() // turn arrays of messages into a single list
                                    ->unique(); // remove duplicates
                            @endphp

                            <div class="col-md-12" style="align-content: center;" id="errorKategoriBarang">
                                @if ($barangQtySatuanErrors->isNotEmpty())
                                    <div class="col-md-6 alert alert-danger">
                                        <ul>
                                            @foreach ($barangQtySatuanErrors as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>


                            <!-- Keperluan Barang + Tambah Kategori Barang) -->
                            {{-- <div class="row mb-2">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <div class="rounded p-2"
                                                    style="background-color:#e3f2fd; border:1px solid #bbdefb;">
                                                    <!-- Header card -->
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <label class="mb-0"
                                                            style="color:#1E4178; font-weight:600; font-size:14px;">Keperluan
                                                            Barang</label>
                                                        <label class="mb-0" style="color:#e91e63; font-size:12px;">*Isi
                                                            keperluan barang jika dibutuhkan</label>
                                                    </div>

                                                    <!-- Divider tipis -->
                                                    <div style="height:8px;"></div>

                                                    <!-- Bar Tambah Kategori Barang (tetap compact) -->
                                                    <div class="d-flex align-items-center px-2 py-0"
                                                        style="background:#fff; border:1px solid #e0e0e0; border-radius:8px; height:34px;">
                                                        <label class="mb-0 me-2"
                                                            style="color:#1E4178; font-weight:600; font-size:12px;">Tambah
                                                            Kategori Barang</label>
                                                        <div class="ms-auto d-flex align-items-center" style="gap:10px;">
                                                            <label class="form-check d-flex align-items-center m-0"
                                                                style="gap:4px;">
                                                                <input class="form-check-input m-0" type="radio"
                                                                    name="opsi" id="ya" value="ya"
                                                                    onclick="toggleKategoriBarang()"
                                                                    style="width:12px;height:12px;">
                                                                <span class="form-check-label m-0"
                                                                    style="color:#1E4178; font-size:12px;">Ya</span>
                                                            </label>
                                                            <label class="form-check d-flex align-items-center m-0"
                                                                style="gap:4px;">
                                                                <input class="form-check-input m-0" type="radio"
                                                                    name="opsi" id="tidak" value="tidak" checked
                                                                    onclick="toggleKategoriBarang()"
                                                                    style="width:12px;height:12px;">
                                                                <span class="form-check-label m-0"
                                                                    style="color:#1E4178; font-size:12px;">Tidak</span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <!-- Input jumlah kategori barang (hidden by default) -->
                                                    <div id="jumlahKategoriContainer" class="mt-2"
                                                        style="display:none;">
                                                        <label for="jumlah_kolom" class="form-label"
                                                            style="color:#1E4178; font-weight:600;">Jumlah Kategori
                                                            Barang</label>
                                                        <input type="number" min="1" class="form-control"
                                                            id="jumlah_kolom" name="jumlah_kolom"
                                                            placeholder="Masukkan jumlah kategori barang yang ingin diinput"
                                                            min="1" max="40">

                                                    </div>

                                                    <!-- Kategori Barang (auto-generated by jumlah_kolom) -->
                                                    <div id="kategoriBarangContainer" class="mt-3"
                                                        style="display:none;"></div>

                                                </div>
                                            </div>
                                        </div>
                                    </div> --}}

                            <!-- Action Buttons -->
                            <div class="form-group">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.memo.index') }}"
                                        class="btn btn-outline-secondary rounded-3">
                                        Batal
                                    </a>
                                    <button type="submit" id="submitBtn" class="btn btn-primary rounded-3">
                                        Simpan
                                    </button>
                                </div>
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
            document.addEventListener('DOMContentLoaded', function() {
                console.log('DOM Content Loaded - Script dimulai');


                $('#tembusan').select2({
                    theme: "bootstrap-5",
                    placeholder: "Pilih Tembusan Memo",
                    allowClear: true,
                    width: "100%"
                });
                $(document).ready(function() {
                    console.log("Select2 loaded?", typeof $.fn.select2);
                });
            });


            // ===================================================================
            // TinyMCE Initialization - Enter = BR, Shift+Enter = P
            // ===================================================================
            document.addEventListener('DOMContentLoaded', function() {
                console.log('DOM loaded, initializing TinyMCE...');

                if (typeof tinymce === 'undefined') {
                    console.error('TinyMCE not loaded! Check if CDN is accessible.');
                    var wrapper = document.getElementById('tinymce-container');
                    var textarea = document.getElementById('isi_surat');
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

                var wrapper = document.getElementById('tinymce-container');
                if (wrapper) {
                    wrapper.classList.add('loading');
                }

                var loadingTimeout = setTimeout(function() {
                    if (wrapper && wrapper.classList.contains('loading')) {
                        console.warn('TinyMCE loading timeout - using fallback');
                        wrapper.classList.remove('loading');
                        var textarea = document.getElementById('isi_surat');
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
                        selector: '#isi_surat',
                        height: 500,
                        placeholder: 'Tulis isi surat di sini...',
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
                                $('#isi_surat').trigger('input');
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
                                var wrapper = document.getElementById('tinymce-container');
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
                            selector: '#isi_surat',
                            height: 400,
                            menubar: false,
                            plugins: ['lists', 'table'],
                            toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | bullist numlist | table',
                            branding: false,
                            init_instance_callback: function(editor) {
                                console.log('Simple TinyMCE loaded successfully');
                                if (loadingTimeout) clearTimeout(loadingTimeout);
                                var wrapper = document.getElementById('tinymce-container');
                                if (wrapper) wrapper.classList.remove('loading');
                            }
                        });
                    } catch (simpleError) {
                        console.error('Even simple TinyMCE failed:', simpleError);
                        var wrapper = document.getElementById('tinymce-container');
                        var textarea = document.getElementById('isi_surat');
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

            $('#memoForm').on('submit', function(e) {
                const selectedNodes = $('#org-tree').jstree('get_selected', true);
                if (selectedNodes.length === 0) {
                    e.preventDefault(); // stop here
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
                        const content = tinymce.get(editorId).getContent({
                            format: 'text'
                        });
                        return content.trim().length > 0;
                    }
                    return value.trim().length > 0;
                }, 'Isi surat harus diisi');

                $.validator.addMethod('tinymceMinLength', function(value, element, param) {
                    const editorId = $(element).attr('id');
                    if (tinymce.get(editorId)) {
                        const content = tinymce.get(editorId).getContent({
                            format: 'text'
                        });
                        return content.trim().length >= param;
                    }
                    return value.trim().length >= param;
                }, function(param) {
                    return 'Isi surat minimal ' + param + ' karakter';
                });

                // Validator untuk memastikan konten tidak hanya berisi tag kosong
                $.validator.addMethod('tinymceNotEmpty', function(value, element) {
                    const editorId = $(element).attr('id');
                    if (tinymce.get(editorId)) {
                        const content = tinymce.get(editorId).getContent({
                            format: 'text'
                        });
                        // Cek jika konten hanya berisi whitespace atau tag kosong
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
                            required: false
                        },
                        nomor_memo: {
                            required: true
                        },
                        judul: {
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
                            tinymceNotEmpty: true,
                            tinymceMinLength: 20
                        }
                    },
                    messages: {
                        tanggal_surat: {
                            required: "Tanggal surat harus diisi"
                        },
                        seri_surat: {
                            required: "Seri tahunan surat harus diisi"
                        },
                        nomor_memo: {
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
                            tinymceNotEmpty: "Isi surat tidak boleh kosong",
                            tinymceMinLength: "Isi surat minimal 20 karakter"
                        },
                        jumlah_kolom: {
                            required: "Jumlah kategori harus diisi"
                        }
                    },
                    errorElement: 'span',
                    errorPlacement: function(error, element) {
                        error.addClass('invalid-feedback');
                        if (element.attr('id') === 'isi_surat') {
                            // Untuk TinyMCE, letakkan error setelah wrapper
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
                            // Pastikan semua formatting tersimpan
                            tinymce.get('isi_surat').save();

                            // Optional: Clean up excessive nbsp sequences jika diperlukan
                            var content = tinymce.get('isi_surat').getContent();
                            // Anda bisa menambahkan cleanup di sini jika diperlukan
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
                        //console.log("Submitting form with tujuan:", $('#tujuan-container').html());
                        // Submit form
                        form.submit();
                    }
                });

                // File size validation


                // Auto-generate nomor surat if needed
                // $('#seri_surat').on('blur', function() {
                //     const seriSurat = $(this).val();
                //     const currentYear = new Date().getFullYear();

                //     if (seriSurat && !$('#nomor_memo').val()) {
                //         const nomorSurat = `${seriSurat}/MEMO/${currentYear}`;
                //         $('#nomor_memo').val(nomorSurat);
                //     }
                // });
                // Initialize jumlah kategori visibility on page load
                toggleKategoriBarang();
                // Dropdown handlers for autofill
                $(document).on('click', '.pilih-kepada', function(e) {
                    e.preventDefault();
                    const val = $(this).data('value');
                    $('#kepada').val(val).trigger('input');
                });
                $(document).on('click', '.pilih-ttd', function(e) {
                    console.log("triger pilih ttd1");
                    e.preventDefault();
                    console.log("triger pilih ttd 2");
                    const namaField = document.getElementById('namaBertandatangan');
                    const val = $(this).data('value');
                    namaField.value = val;
                    console.log("Updated via .pilih-ttd:", $('#namaBertandatangan').val());

                });
                $(document).on('change', '#manager_user_id', function() {
                    console.log("triger manageruserid");
                    // Get selected option text (excluding the code in parentheses if you want just the name)
                    const text = $(this).find("option:selected").text().trim();

                    // Or, if you want only the name part (without position code):
                    const cleaned = text.replace(/\(.*?\)\s*/, '');

                    $('#namaBertandatangan').val(cleaned);
                    console.log("Updated via #manager_user_id:", $('#namaBertandatangan').val());

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

            });
            //Maximum kategori barang = 40
            window.addEventListener('DOMContentLoaded', function() {
                var jumlahKategoriInput = document.getElementById('jumlah_kolom');

                if (!jumlahKategoriInput) return;

                let max = parseInt(jumlahKategoriInput.max, 10);
                let min = parseInt(jumlahKategoriInput.min, 10);

                // Custom validation messages
                jumlahKategoriInput.addEventListener('invalid', function() {
                    if (!this.value) {
                        this.setCustomValidity('Kolom ini wajib diisi.');
                    } else {
                        this.setCustomValidity('');
                    }
                });

                jumlahKategoriInput.addEventListener('input', function() {
                    let val = parseInt(this.value, 10);

                    if (!isNaN(val)) {
                        if (val > max) {
                            this.value = max;
                        } else if (val < min) {
                            this.value = min;
                        }
                    }
                    this.setCustomValidity(''); // clear error once user types
                });
            });


            // Show/hide input jumlah kategori barang based on radio selection
            function toggleKategoriBarang() {
                const yesRadio = document.getElementById('ya');
                const container = document.getElementById('jumlahKategoriContainer');
                const jumlahInput = document.getElementById('jumlah_kolom');
                const kategoriContainer = document.getElementById('kategoriBarangContainer');
                let jumlahKolomError = document.getElementById('jumlah_kolom-error');

                if (!container) return;
                if (yesRadio && yesRadio.checked) {
                    container.style.display = 'block';
                    jumlahInput.required = true;
                    // Show kategori container only if jumlah already filled
                    if (jumlahInput && jumlahInput.value && parseInt(jumlahInput.value) > 0) {
                        kategoriContainer.style.display = 'block';

                        renderKategoriBarang(parseInt(jumlahInput.value));
                    }
                } else {

                    container.style.display = 'none';
                    if (jumlahKolomError) {
                        jumlahKolomError.style.display = 'none';
                    }
                    jumlahInput.required = false;
                    jumlahInput.value = '';


                    let validator = $("#memoForm").validate();
                    $(jumlahInput).rules('remove', 'required');
                    // mark it as valid so error disappe   ars
                    validator.successList.push(jumlahInput);

                    if (jumlahInput) jumlahInput.value = '';
                    if (kategoriContainer) {
                        kategoriContainer.innerHTML = '';
                        kategoriContainer.style.display = 'none';
                    }
                }
            }

            // Render dynamic inputs for kategori barang
            function renderKategoriBarang(jumlah) {
                const target = document.getElementById('kategoriBarangContainer');
                if (!target) return;
                if (!jumlah || jumlah < 1) {
                    target.innerHTML = '';
                    target.style.display = 'none';
                    return;
                }

                let html = '';
                for (let i = 0; i < jumlah; i++) {
                    const nomor = i + 1;
                    html += `
        <div class="row g-3 mb-2">
            <div class="col-md-3">
                <div class="form-group">
                    <label class="form-label">Nomor</label>
                    <input type="text" class="form-control" name="nomor[]" value="${nomor}" readonly>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="form-label">Barang</label>
                    <input type="text" class="form-control" name="barang[]" placeholder="Masukkan barang">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="form-label">Qty</label>
                    <input type="number" class="form-control" name="qty[]" placeholder="Masukkan jumlah" min="1">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="form-label">Satuan</label>
                    <input type="text" class="form-control" name="satuan[]" placeholder="Masukkan satuan">
                </div>
            </div>
        </div>`;
                }
                target.innerHTML = html;
                target.style.display = 'block';
            }

            // Re-render when jumlah_kolom changes
            document.addEventListener('DOMContentLoaded', function() {
                const jumlahInput = document.getElementById('jumlah_kolom');
                if (!jumlahInput) return;

                const max = parseInt(jumlahInput.max, 10) || 40;
                const min = parseInt(jumlahInput.min, 10) || 1;

                // Handle browser's invalid event
                jumlahInput.addEventListener('invalid', function() {
                    this.setCustomValidity('Kolom ini wajib diisi.');
                });

                jumlahInput.addEventListener('input', function() {
                    let val = parseInt(this.value, 10);

                    if (this.value !== '') {
                        if (val > max) {
                            val = max;
                            this.value = max;
                        } else if (val < min) {
                            val = min;
                            this.value = min;
                        }
                    }

                    this.setCustomValidity('');

                    if (!isNaN(val) && val >= min && val <= max) {
                        renderKategoriBarang(val);
                    } else {
                        renderKategoriBarang(0);
                    }
                });
            });
            $(function() {
                $('#lampiran').on('change', function() {
                    console.log('file terupload');
                    const file = this.files[0];
                    const maxSize = 2 * 1024 * 1024; // 2MB

                    if (!file) return;

                    // Check file size
                    if (file.size > maxSize) {
                        Swal.fire({
                            icon: 'error',
                            title: 'File Terlalu Besar',
                            text: 'Ukuran file tidak boleh lebih dari 2MB',
                            confirmButtonColor: '#1572e8'
                        });
                        console.log('file terlalu besar');
                        this.value = '';
                        return;
                    }
                    reader.readAsArrayBuffer(file.slice(0, 5));
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


                @if (session('error') === 'Tidak ada karyawan di dalam tujuan dokumen.')
                    showNotification("Gagal membuat memo. Tidak ada karyawan pada bagian kerja yang dituju.", "error");
                @elseif (session('error'))
                    showNotification("{{ session('error') }}", "error");
                @endif
            });
        </script>
    @endpush
