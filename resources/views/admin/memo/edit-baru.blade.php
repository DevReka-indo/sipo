@extends('layouts.app')

@section('title', 'Edit Memo')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/text-tiny.css') }}">
@endpush

@section('content')
    <div class="container-fluid px-4 py-0 mt-0">
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-body py-3">

                <h3 class="fw-bold mb-3">Edit Memo</h3>

                {{-- Breadcrumb --}}
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="bg-white border rounded-2 px-3 py-2 w-100 d-flex align-items-center">
                            <a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-primary">Beranda</a>
                            <span class="mx-2 text-muted">/</span>
                            <a href="{{ route('admin.memo.index') }}" class="text-decoration-none text-primary">Memo</a>
                            <span class="mx-2 text-muted">/</span>
                            <span class="text-muted">Edit Memo</span>
                        </div>
                    </div>
                </div>

                {{-- Form --}}
                <form action="{{ route('memo/update-baru', $memo->id_memo) }}" id= "memoForm" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="card">
                        @if ($errors->any())
                            <div class="alert alert-danger mb-3">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="card-header">
                            <div class="card-title">Formulir Edit Memo</div>
                        </div>
                        <div class="card-body">
                            <div id="tujuan-container"></div>
                            {{-- Row 1: Tanggal Surat & Seri Tahunan Surat --}}
                            <div class="row mb-3">
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
                                <label for="kode_bagian" class="form-label">
                                    <i class="fas fa-code text-primary me-1"></i>
                                    Kode Bagian <span class="text-danger">*</span>
                                </label>

                                <select name="kode_bagian" id="kode_bagian"
                                    class="form-select @error('kode_bagian') is-invalid @enderror" required>
                                    <option value="">-- Pilih Kode Bagian --</option>

                                    @foreach ($bagianKerja as $bagian)
                                        <option value="{{ $bagian->kode_bagian }}"
                                            {{ $memo->kode_bagian == $bagian->kode_bagian ? 'selected' : '' }}>
                                            {{ $bagian->kode_bagian }} - {{ $bagian->nama_bagian }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('kode_bagian')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                            </div>
                            {{-- <div class="col-md-6">
                                    <label for="seri_surat" class="form-label">
                                        <i class="fas fa-hashtag text-primary me-1"></i>
                                        Seri Tahunan Surat <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="seri_surat" id="seri_surat" class="form-control"
                                        value="{{ $memo->seri_surat }}">
                                    <input type="hidden" name="divisi_id_divisi" value="1">
                                    <input type="hidden" name="pembuat" value="Admin Sistem">
                                </div> --}}
                        </div>

                        {{-- Row 2: Nomor Surat & Perihal --}}
                        <div class="row mb-3">
                            <!-- Tanggal Surat -->
                            <div class="col-md-6">
                                {{-- <div class="form-group"> --}}
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
                                {{-- </div> --}}
                            </div>

                            {{-- <div class="col-md-6">
                                    <label for="nomor_surat" class="form-label">
                                        <i class="fas fa-file-alt text-primary me-1"></i>
                                        Nomor Surat <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="nomor_memo" id="nomor_memo" class="form-control"
                                        value="{{ $memo->nomor_memo }}">
                                </div> --}}
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
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="tembusan" class="form-label">
                                    <i class="fas fa-user text-primary me-1"></i>
                                    Tembusan <span class="text-muted form-text" style="font-size: x-small;">(Kosongkan
                                        jika
                                        tidak ada.)</span>
                                </label>
                                <select name="tembusan[]" id="tembusan" class="select2" multiple="multiple">
                                    @foreach ($tembusan as $t)
                                        <option value="{{ $t['id'] }}"
                                            @if (in_array($t['id'], $selectedTembusan)) selected @endif>{{ $t['name'] }}</option>
                                    @endforeach
                                </select>

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
                        </div>







                    </div>

                    {{-- Section: Keperluan Barang (Disabled) --}}
                    {{-- @php
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
                                            <small class="text-muted">*Isi keperluan barang jika dibutuhkan</small>
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
                                    Grid Baris Barang
                                    <div id="kb_fields">
                                        <div id="barangContainer" class="d-grid gap-3">
                                            baris dibuat via JS (prefill dari PHP/DB)
                                        </div>
                                    </div>

                                    tampung id detail untuk update (sejajar dengan baris)
                                    <input type="hidden" name="deleted_detail_ids" id="deleted_detail_ids">
                                </div>
                            </div>
                        </div>

                        kirim data prefill dari PHP ke JS
                        <script>
                            window.kbPrefill = @json($prefillItems); // [{id, nama, qty, satuan}, ...]
                        </script>
                        =================== /Section: Keperluan Barang ===================
                        --}}

                    <!-- Action Buttons -->
                    <div class="form-group">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.memo.index') }}" class="btn rounded-3"
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

        // =========================
        // LAMPIRAN: pilih satu per satu, tampil sebagai list
        // =========================
        $(document).ready(function() {
            console.log('Document ready - checking lampiran elements');
            const lampiranInputContainer = document.getElementById('lampiran-input-container');
            const lampiranInput = document.getElementById('lampiran-input');
            const lampiranList = document.getElementById('lampiran-list');

            console.log('Elements found:', {
                container: !!lampiranInputContainer,
                input: !!lampiranInput,
                list: !!lampiranList
            });

            if (lampiranInputContainer && lampiranInput && lampiranList) {
                console.log('Lampiran dynamic initialized successfully');

                function createEmptyVisibleInput() {
                    const newInput = document.createElement('input');
                    newInput.type = 'file';
                    newInput.id = 'lampiran-input';
                    newInput.className = 'form-control';
                    newInput.setAttribute('accept', '.pdf,.jpg,.jpeg,.png');
                    newInput.addEventListener('change', handleLampiranChange);
                    lampiranInputContainer.innerHTML = '';
                    lampiranInputContainer.appendChild(newInput);
                }

                let isProcessing = false;

                function handleLampiranChange(e) {
                    const input = e.target;
                    if (!input.files || input.files.length === 0 || isProcessing) return;

                    isProcessing = true;
                    input.removeEventListener('change', handleLampiranChange);

                    const file = input.files[0];
                    const maxSize = 2 * 1024 * 1024;
                    if (file.size > maxSize) {
                        Swal.fire({
                            icon: 'error',
                            title: 'File Terlalu Besar',
                            text: 'Ukuran file tidak boleh lebih dari 2MB',
                            confirmButtonColor: '#1572e8'
                        });
                        input.value = '';
                        isProcessing = false;
                        return;
                    }

                    const itemWrapper = document.createElement('div');
                    itemWrapper.className =
                        'd-flex align-items-center justify-content-between mb-2 flex-wrap gap-2';

                    const infoWrapper = document.createElement('div');
                    infoWrapper.className = 'flex-grow-1';

                    const nameSpan = document.createElement('span');
                    nameSpan.textContent = file.name;

                    const progressOuter = document.createElement('div');
                    progressOuter.className = 'progress mt-1';
                    progressOuter.style.height = '4px';

                    const progressInner = document.createElement('div');
                    progressInner.className = 'progress-bar';
                    progressInner.style.width = '100%';
                    progressInner.setAttribute('aria-valuenow', '100');
                    progressInner.setAttribute('aria-valuemin', '0');
                    progressInner.setAttribute('aria-valuemax', '100');
                    progressInner.textContent = '';

                    progressOuter.appendChild(progressInner);
                    infoWrapper.appendChild(nameSpan);
                    infoWrapper.appendChild(progressOuter);

                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'btn btn-sm btn-outline-danger';
                    removeBtn.innerHTML = '<i class="fas fa-trash"></i>';

                    input.name = 'lampiran[]';
                    input.classList.add('d-none');
                    input.removeEventListener('change', handleLampiranChange);

                    itemWrapper.appendChild(infoWrapper);
                    itemWrapper.appendChild(removeBtn);
                    itemWrapper.appendChild(input);

                    lampiranList.appendChild(itemWrapper);

                    removeBtn.addEventListener('click', function() {
                        itemWrapper.remove();
                    });

                    createEmptyVisibleInput();

                    setTimeout(() => {
                        isProcessing = false;
                    }, 100);
                }

                lampiranInput.addEventListener('change', handleLampiranChange);
            } else {
                console.warn('Lampiran elements not found, skip lampiran dynamic init');
            }

            $('.delete-lampiran-existing').on('click', function() {
                const lampiranIndex = $(this).data('index');
                const fileName = $(this).data('name');
                const element = $(this).closest('.col-12');

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
                            url: `/memo/lampiran-existing/{{ $memo->id_memo }}/${lampiranIndex}`,
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
            });
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
                        'border': '1',
                        'cellpadding': '2'
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
                    forced_root_block: 'p', // Tetap pakai <p> sebagai container
                    force_br_newlines: false, // Jangan paksa BR di semua tempat
                    force_p_newlines: false, // Jangan paksa P di semua tempat
                    end_container_on_empty_block: false,
                    newline_behavior: 'linebreak', // Enter = <br>, Shift+Enter = <p> baru
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
                        // Enter = <br> (line break dalam paragraph yang sama)
                        // Shift+Enter = <p> baru (paragraph baru)
                        // ========== CUSTOM ENTER BEHAVIOR ==========
                        editor.on('keydown', function(e) {
                            if (e.keyCode === 13) { // Enter key
                                var node = editor.selection.getNode();
                                var inList = editor.dom.getParent(node, 'li,ol,ul');
                                var inTable = editor.dom.getParent(node, 'td,th');

                                // PENTING: Di dalam list atau table, biarkan TinyMCE default behavior
                                if (inList || inTable) {
                                    return; // Exit handler, biar TinyMCE yang handle
                                }

                                // Di luar list/table:
                                if (e.shiftKey) {
                                    // Shift+Enter = paragraph baru
                                    e.preventDefault();
                                    editor.execCommand('InsertParagraph');
                                    return false;
                                } else {
                                    // Enter = line break <br>
                                    e.preventDefault();
                                    editor.execCommand('InsertLineBreak');
                                    return false;
                                }
                            }

                            // Tab functionality
                            if (e.keyCode === 9) {
                                var node = editor.selection.getNode();
                                var inList = editor.dom.getParent(node, 'li,ol,ul');

                                // Di dalam list, biarkan default Tab behavior (indent/outdent)
                                if (inList) {
                                    return; // Biarkan TinyMCE handle indent list
                                }

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

        $(document).ready(function() {
            console.log('submit trigger');

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

            $.validator.addMethod('tinymceNotEmpty', function(value, element) {
                const editorId = $(element).attr('id');
                if (tinymce.get(editorId)) {
                    const content = tinymce.get(editorId).getContent({
                        format: 'text'
                    });
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
                    // nomor_surat: {
                    //     required: true
                    // },
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
                    if (tinymce.get('isi_surat')) {
                        tinymce.get('isi_surat').save();
                        var content = tinymce.get('isi_surat').getContent();
                    }

                    $('#tujuan-container').empty();
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

                    const submitBtn = $(form).find('button[type="submit"]');
                    const originalText = submitBtn.html();
                    submitBtn.html('<i class="fas fa-spinner fa-spin me-1"></i>Menyimpan...');
                    submitBtn.prop('disabled', true);
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
          <input type="text" class="form-control nomor-field" name="barang_nomor[]" value="${nomor}" readonly style="background:#edf2f7;">
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

            function renderWith(count, data = []) {
                container.innerHTML = '';
                for (let i = 0; i < count; i++) {
                    const wrap = document.createElement('div');
                    wrap.innerHTML = rowTemplate(i, data[i] || {});
                    container.appendChild(wrap.firstElementChild);
                }
                renumber();
            }

            jumlahInput.addEventListener('input', function(e) {
                let count = parseInt(e.target.value || '1', 10);
                if (isNaN(count) || count < 1) count = 1;
                const current = container.querySelectorAll('.kb-row').length;
                const snapshot = [];

                container.querySelectorAll('.kb-row').forEach(row => {
                    snapshot.push({
                        id: row.querySelector('input[name="detail_id[]"]').value,
                        nama: row.querySelector('input[name="barang_nama[]"]').value,
                        qty: row.querySelector('input[name="barang_qty[]"]').value,
                        satuan: row.querySelector('input[name="barang_satuan[]"]').value,
                    });
                });

                if (count === current) return;

                if (count > current) {
                    for (let i = current; i < count; i++) snapshot[i] = snapshot[i] || {
                        id: '',
                        nama: '',
                        qty: '',
                        satuan: ''
                    };
                } else {
                    snapshot.length = count;
                }
                renderWith(count, snapshot);
            });

            function setEnabled(enabled) {
                kbFields.style.display = enabled ? '' : 'none';
                jumlahInput.disabled = !enabled;
                container.querySelectorAll('input').forEach(inp => inp.disabled = !enabled);
            }

            kbYes.addEventListener('change', () => setEnabled(true));
            kbNo.addEventListener('change', () => setEnabled(false));

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
