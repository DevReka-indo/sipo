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
                            <a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-primary">Beranda</a>
                            <span class="mx-2 text-muted">/</span>
                            <a href="{{ route('admin.undangan.index') }}" class="text-decoration-none text-primary">Undangan
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
                                    <label class="form-label">
                                        <i class="fas fa-calendar text-primary me-1"></i>
                                        Tanggal Surat <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" class="form-control"
                                        value="{{ $undangan->tgl_dibuat->format('Y-m-d') }}" disabled>
                                    <input type="hidden" name="tgl_dibuat"
                                        value="{{ $undangan->tgl_dibuat->format('Y-m-d') }}">
                                    <input type="hidden" name="tgl_disahkan"
                                        value="{{ $undangan->tgl_disahkan ? $undangan->tgl_disahkan : '' }}">
                                </div>
                                {{-- <div class="col-md-6">
                                    <label class="form-label">
                                        <i class="fas fa-hashtag text-primary me-1"></i>
                                        Seri Tahunan Surat
                                    </label>
                                    <input type="text" class="form-control" name="seri_surat"
                                        value="{{ $undangan->seri_surat }}">
                                </div> --}}
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <i class="fas fa-file-alt text-primary me-1"></i>
                                        Nomor Surat
                                    </label>
                                    <input type="text" class="form-control" name="nomor_undangan"
                                        value="{{ $undangan->nomor_undangan }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <i class="fas fa-tag text-primary me-1"></i>
                                        Perihal <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('judul') is-invalid @enderror"
                                        name="judul" value="{{ old('judul', $undangan->judul) }}" required>
                                    @error('judul')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="kepada" class="form-label">
                                        <i class="fas fa-user text-primary me-1"></i>
                                        Kepada <span class="text-danger">*</span>
                                    </label>
                                    <span class="text-danger" style="font-size: x-small">Pilih user atau struktur, semua
                                        user
                                        di bawah struktur akan otomatis terpilih</span>
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

                            <div class="row mb-3">
                                <div class="col-12">
                                    <!-- Added section for displaying selected recipients -->
                                    <div style="display: none;" id="selected-section">
                                        <label style="font-size: small;" class="form-label">
                                            <i class="fas fa-list text-primary me-1"></i>
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

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <i class="fas fa-calendar-day text-primary me-1"></i>
                                        Tanggal Rapat <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" name="tgl_rapat"
                                        class="form-control @error('tgl_rapat') is-invalid @enderror"
                                        value="{{ old('tgl_rapat', optional(\Carbon\Carbon::parse($undangan->tgl_rapat))->format('Y-m-d')) }}"
                                        required>
                                    @error('tgl_rapat')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <i class="fas fa-clock text-primary me-1"></i>
                                        Waktu Rapat <span class="text-danger">*</span>
                                    </label>
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
                                <label class="form-label">
                                    <i class="fas fa-map-marker-alt text-primary me-1"></i>
                                    Tempat Rapat <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="tempat"
                                    class="form-control @error('tempat') is-invalid @enderror"
                                    value="{{ old('tempat', $undangan->tempat) }}" required>
                                @error('tempat')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-signature text-primary me-1"></i>
                                    Nama yang Bertanda Tangan <span class="text-danger">*</span>
                                </label>
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
                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="isi_undangan" class="form-label">
                                        <i class="fas fa-edit text-primary me-1"></i>
                                        Agenda <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control @error('isi_undangan') is-invalid @enderror" id="isi_undangan" name="isi_undangan"
                                        rows="10" placeholder="Tulis agenda disini..." required>{{ old('isi_undangan', $undangan->isi_undangan) }}</textarea>
                                    @error('isi_undangan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="form-group">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.undangan.index') }}" class="btn rounded-3"
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
    </div>
    </div>
@endsection

@push('scripts')
    <script>
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
