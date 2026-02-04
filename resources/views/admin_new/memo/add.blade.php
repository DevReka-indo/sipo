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
                                <form action="{{ route('memo-admin.store') }}" method="POST" enctype="multipart/form-data"
                                    id="memoForm">
                                    @csrf
                                    <div id="tujuan-container"></div>
                                    <div class="row">
                                        <!-- Nomor Surat -->
                                        <div class="col-md-12">
                                            <div class="form-group">
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
                                            </div>
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
                                                <input type="date"
                                                    class="form-control @error('tgl_dibuat') is-invalid @enderror"
                                                    id="tanggal_surat" name="tgl_dibuat"
                                                    value="{{ old('tgl_dibuat', date('Y-m-d')) }}" required>
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
                                                <input type="text"
                                                    class="form-control @error('judul') is-invalid @enderror" id="perihal"
                                                    name="judul" value="{{ old('judul') }}"
                                                    placeholder="Masukkan perihal surat" required>
                                                @error('judul')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>



                                    <div class="row">
                                        <!-- Nama yang Bertanda Tangan -->
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
                                                @error('nama_bertandatangan')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- Lampiran -->
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="lampiran" class="form-label">
                                                    <i class="fas fa-paperclip text-primary me-1"></i>
                                                    Lampiran
                                                </label>
                                                <input type="file"
                                                    class="form-control @error('lampiran') is-invalid @enderror"
                                                    id="lampiran" name="lampiran[]" accept=".pdf, .png, .jpeg, .jpg"
                                                    multiple>
                                                <small class="form-text text-muted">
                                                    Format yang diizinkan: PDF, PNG, JPEG, JPG (Max: 2MB)
                                                </small>
                                                @error('lampiran')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    {{-- Pembuat --}}
                                    <input type="hidden" name="pembuat" value="{{ auth()->user()->id }}">
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
                                                <div id="orgTreeError" class="form-control text-danger"
                                                    style="display:none;"></div>

                                                <div class="col-md-12">
                                                    <div class="border rounded p-2"
                                                        style="max-height: 300px; overflow-y: auto;">
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

                                    <!-- Isi Surat -->
                                    <div class="form-group">
                                        <label for="isi_memo" class="form-label">
                                            <i class="fas fa-edit text-primary me-1"></i>
                                            Isi Surat <span class="text-danger">*</span>
                                        </label>
                                        <textarea class="form-control @error('isi_memo') is-invalid @enderror" id="isi_memo" name="isi_memo" rows="10"
                                            placeholder="Tulis isi surat di sini..." required>{{ old('isi_memo') }}</textarea>
                                        @error('isi_memo')
                                            <div class="invalid-feedback">{{ $message }}</div>
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
                                    <div class="row mb-2">
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

                                    </div>

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
                                required: true
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
                            judul: {
                                required: "Perihal harus diisi"
                            },
                            kepada: {
                                required: "Kepada harus diisi"
                            },
                            manager_user_id: {
                                required: "Nama yang bertanda tangan harus diisi"
                            },
                            isi_memo: {
                                required: "Surat harus diisi"
                            },
                            jumlah_kolom: {
                                required: "Jumlah kategori harus diisi"
                            }
                        },
                        errorElement: 'span',
                        errorPlacement: function(error, element) {
                            error.addClass('invalid-feedback');
                            element.closest('.form-group').append(error);
                        },
                        highlight: function(element, errorClass, validClass) {
                            $(element).addClass('is-invalid');
                        },
                        unhighlight: function(element, errorClass, validClass) {
                            $(element).removeClass('is-invalid');
                        },
                        submitHandler: function(form) {
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

                //SUMMERNOTE
                $(document).ready(function() {
                    $('#isi_memo').summernote({
                        height: 300,
                        tabsize: 4,
                        placeholder: 'Tulis isi surat di sini...',
                        toolbar: [
                            ['style', ['style']],
                            ['font', ['bold', 'italic', 'underline', 'clear']],
                            ['fontname', ['fontname']],
                            ['fontsize', ['fontsize']],
                            ['color', ['color']],
                            ['para', ['ul', 'ol', 'paragraph']],
                            ['table', ['table']],
                            ['insert', ['link', 'picture']],
                            ['view', ['fullscreen', 'codeview', 'help']]
                        ],
                        callbacks: {
                            onInit: function() {
                                // Set focus to editor after initialization
                                let editor = $(this).next('.note-editor').find('.note-editable');

                                // Handle Tab key for indentation
                                editor.on('keydown', function(e) {
                                    if (e.key === 'Tab') {
                                        e.preventDefault();
                                        e.stopPropagation();

                                        let selection = window.getSelection();
                                        let range = selection.getRangeAt(0);

                                        if (e.shiftKey) {
                                            // Shift + Tab = outdent
                                            document.execCommand('outdent', false, null);
                                        } else {
                                            // Tab = indent
                                            document.execCommand('indent', false, null);
                                        }

                                        // Prevent default tab behavior
                                        return false;
                                    }
                                });
                            },
                            onChange: function(contents, $editable) {
                                // Update the original textarea value
                                $('#isi_memo').val(contents);
                            }
                        }
                    });
                });
            </script>
        @endpush
