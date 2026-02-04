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
                                <a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-primary">Beranda</a>
                                <span class="mx-2 text-muted">/</span>
                                <a href="{{ route('admin.undangan.index') }}"
                                    class="text-decoration-none text-primary">Undangan</a>
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
                                    <!-- Form -->
                                    <form action="{{ route('undangan-superadmin.store') }}" method="POST"
                                        enctype="multipart/form-data" id="addUndanganForm">
                                        @csrf
                                        <div class="row">
                                            <!-- Nomor Surat -->
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="nomor_undangan" class="form-label">
                                                        <i class="fas fa-file-alt text-primary me-1"></i>
                                                        Nomor Surat <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text"
                                                        class="form-control @error('nomor_undangan') is-invalid @enderror"
                                                        id="nomor_undangan" name="nomor_undangan"
                                                        placeholder="Contoh: 218.23/REKA/GEN/LOG/X/2025" required
                                                        value="{{ old('nomor_undangan') }}">
                                                    @error('nomor_undangan')
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
                                                        id="tgl_dibuat" name="tgl_dibuat"
                                                        value="{{ old('tgl_dibuat', date('Y-m-d')) }}">
                                                    @error('tgl_dibuat')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <!-- Perihal -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="judul" class="form-label">
                                                        <i class="fas fa-tag text-primary me-1"></i>
                                                        Perihal <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text"
                                                        class="form-control @error('perihal') is-invalid @enderror"
                                                        id="judul" name="judul" value="{{ old('judul') }}"
                                                        placeholder="Masukkan perihal surat" required>
                                                    @error('judul')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <input type="hidden" name="tgl_disahkan">
                                            <input type="hidden" name="catatan">
                                            <!-- Seri Tahunan Surat -->
                                            <div class="col-md-6">
                                                {{-- <div class="form-group">
                                                    <label for="seri_surat" class="form-label">
                                                        <i class="fas fa-hashtag text-primary me-1"></i>
                                                        Seri Tahunan Surat <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text"
                                                        class="form-control @error('seri_surat') is-invalid @enderror"
                                                        id="seri_surat" name="seri_surat" placeholder="Contoh: 001"
                                                        value="{{ old('seri_surat') }}">
                                                    @error('seri_surat')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div> --}}
                                            </div>
                                            <input type="hidden" name="kode" value="{{ $kode }}">
                                            <input type="hidden" name="pembuat" value="{{ auth()->user()->id }}">
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-12">
                                                <div class="form-group">
                                                    <label for="kepada" class="form-label">
                                                        <i class="fas fa-user text-primary me-1"></i>
                                                        Kepada
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <small class="text-danger" style="font-size: x-small"> Pilih user atau
                                                        struktur, semua user
                                                        di bawah struktur akan otomatis terpilih</small>
                                                    <div class="border rounded p-2"
                                                        style="max-height: 300px; overflow-y: auto;">
                                                        <div style="font-size: small" class="form-label" id="org-tree">
                                                        </div>
                                                        <style>
                                                            #org-tree .jstree-anchor {
                                                                color: #1f4178;
                                                                font-weight: 500;
                                                            }
                                                        </style>
                                                        <small id="tujuanError" class="text-danger"
                                                            style="display:none;">Minimal pilih satu tujuan!</small>
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
                                                    <div class="border rounded p-2"
                                                        style="max-height: 300px; overflow-y: auto;">
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

                                        <div class="row mb-3">
                                            <!-- Tanggal Undangan -->
                                            <div class ="col-md-4">
                                                <div class="form-group">
                                                    <label for="tgl_rapat" class="form-label">
                                                        <i class="fas fa-calendar-check text-primary me-1"></i>
                                                        Tanggal Rapat <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="date" name="tgl_rapat" id="tgl_rapat"
                                                        class="form-control" value="{{ old('tgl_rapat') }}"
                                                        placeholder="Tanggal Rapat">
                                                    @error('tgl_rapat')
                                                        <div class="form-control text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <!-- Waktu -->
                                            <div class ="col-md-4">
                                                <div class="form-group">
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
                                                </div>
                                                @error('waktu_mulai')
                                                    <div class="form-control text-danger">{{ $message }}</div>
                                                @enderror
                                                @error('waktu_selesai')
                                                    <div class="form-control text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <!-- Tempat -->
                                            <div class ="col-md-4">
                                                <div class="form-group">
                                                    <label for="tempat" class="form-label"> <i
                                                            class="fas fa-map-marker-alt text-primary me-1"></i>
                                                        Tempat Rapat <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text" name="tempat" id="tempat" class="form-control"
                                                        placeholder="Ruang Rapat" value="{{ old('tempat') }}">
                                                    @error('tempat')
                                                        <div class="form-control text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <!-- Nama yang Beratanda Tangan -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="nama_bertandatangan" class="form-label">
                                                        <i class="fas fa-signature text-primary me-1"></i>
                                                        Nama yang Bertanda Tangan <span class="text-danger">*</span>
                                                    </label>
                                                    <select name="manager_user_id" id="manager_user_id" class="form-control"
                                                        required>
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
                                            <!-- Lampiran -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="lampiran" class="form-label">
                                                        <i class="fas fa-paperclip text-primary me-1"></i>
                                                        Lampiran
                                                    </label>

                                                    <div id="lampiran-wrapper">
                                                        <div class="lampiran-item mb-2">
                                                            <div class="input-group">
                                                                <input type="file"
                                                                    class="form-control @error('lampiran') is-invalid @enderror"
                                                                    name="lampiran[]" accept=".pdf,.jpg,.jpeg,.png">

                                                                {{-- Tombol tambah baris lampiran --}}
                                                                <button type="button"
                                                                    class="btn btn-outline-secondary btn-add-lampiran">
                                                                    <i class="fas fa-plus"></i>
                                                                </button>

                                                                {{-- Tombol hapus baris lampiran (disembunyikan jika hanya 1 baris) --}}
                                                                <button type="button"
                                                                    class="btn btn-outline-danger btn-remove-lampiran d-none">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </div>

                                                            <small class="text-muted d-block mt-1">
                                                                Nama File: <span class="file-name">Belum ada file
                                                                    dipilih</span>
                                                            </small>
                                                        </div>
                                                    </div>

                                                    <small class="form-text text-muted">
                                                        Format yang diizinkan: PDF, JPG, JPEG, PNG (Max: 2MB)
                                                    </small>

                                                    @error('lampiran')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                        </div>

                                        <!-- Agenda -->
                                        <div class="form-group">
                                            <label for="isi_undangan" class="form-label">
                                                <i class="fas fa-edit text-primary me-1"></i>
                                                Agenda <span class="text-danger">*</span>
                                            </label>
                                            <textarea class="form-control @error('isi_undangan') is-invalid @enderror" id="isi_undangan" name="isi_undangan"
                                                rows="10" placeholder="Tulis agenda disini test..." required>{{ old('isi_undangan') }}</textarea>
                                            @error('isi_undangan')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Action Buttons -->
                                        <div class="form-group">
                                            <div class="d-flex justify-content-end gap-2">
                                                <a href="{{ route('admin.undangan.index') }}" class="btn rounded-3"
                                                    style="background-color:#fff; color:#0d6efd; border:1px solid #0d6efd;">
                                                    Batal
                                                </a>
                                                <button type="submit" id="submitBtn" class="btn btn-primary rounded-3">
                                                    Kirim
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
                                    'dots': true,

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

                                        // --- LAMPIRAN DINAMIS (+) & TAMPIL NAMA FILE ---

                    function updateLampiranButtons() {
                        let items = $('#lampiran-wrapper .lampiran-item');

                        items.each(function(index) {
                            let removeBtn = $(this).find('.btn-remove-lampiran');

                            // Jika hanya 1 baris, tombol hapus disembunyikan
                            if (items.length === 1) {
                                removeBtn.addClass('d-none');
                            } else {
                                removeBtn.removeClass('d-none');
                            }
                        });
                    }

                    // Tambah baris lampiran
                    $('#lampiran-wrapper').on('click', '.btn-add-lampiran', function() {
                        let currentItem = $(this).closest('.lampiran-item');
                        let newItem = currentItem.clone();

                        // Reset input & nama file
                        let input = newItem.find('input[type="file"]');
                        input.val('');
                        newItem.find('.file-name').text('Belum ada file dipilih');

                        $('#lampiran-wrapper').append(newItem);

                        updateLampiranButtons();
                    });

                    // Hapus baris lampiran
                    $('#lampiran-wrapper').on('click', '.btn-remove-lampiran', function() {
                        $(this).closest('.lampiran-item').remove();
                        updateLampiranButtons();
                    });

                    // Tampilkan nama file yang dipilih
                    $('#lampiran-wrapper').on('change', 'input[type="file"]', function() {
                        if (this.files && this.files.length > 0) {
                            $(this)
                                .closest('.lampiran-item')
                                .find('.file-name')
                                .text(this.files[0].name);
                        } else {
                            $(this)
                                .closest('.lampiran-item')
                                .find('.file-name')
                                .text('Belum ada file dipilih');
                        }
                    });

                    // Set awal: hanya 1 baris, tombol hapus disembunyikan
                    updateLampiranButtons();

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
            </script>
        @endpush
