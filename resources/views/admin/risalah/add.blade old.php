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
            </div>

            <div class="row">
                <div class="col-md-12">
                    <form action="{{ route('risalah.store') }}" method="POST" enctype="multipart/form-data"
                        id="risalahForm">
                        @csrf
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Formulir Tambah Risalah</h4>
                            </div>

                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="tanggal_surat" class="form-label">
                                            <i class="fas fa-calendar-alt text-primary me-1"></i>
                                            Tanggal Surat <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" name="tgl_dibuat" class="form-control"
                                            value="{{ date('Y-m-d') }}" required>
                                        <input type="hidden" name="tgl_disahkan">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="seri_surat" class="form-label">
                                            <i class="fas fa-hashtag text-primary me-1"></i>
                                            Seri Surat <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" name="seri_surat"
                                            placeholder="Contoh: 001" required value="{{ old('seri_surat') }}">
                                        <input type="hidden" name="pembuat" value="{{ auth()->user()->id }}">
                                        <input type="hidden" name="risalah_id_risalah" value="{{ $risalah->id_risalah }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label for="nomor_surat" class="form-label">
                                            <i class="fas fa-file-alt text-primary me-1"></i>
                                            Nomor Surat <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" name="nomor_risalah"
                                            placeholder="Contoh: RIS-62/REKA/LOG/X/2025" required
                                            value="{{ old('nomor_risalah') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="perihal" class="form-label">
                                            <i class="fas fa-tag text-primary me-1"></i>
                                            Judul <span class="text-danger">*</span>
                                        </label>
                                        <select name="judul" id="judul" class="form-select select2" required>
                                            <option value="" disabled selected>Pilih Judul</option>
                                            @foreach ($undangan as $u)
                                                <option value="{{ $u->judul }}" data-tempat="{{ $u->tempat }}"
                                                    data-waktu_mulai="{{ $u->waktu_mulai }}"
                                                    data-waktu_selesai="{{ $u->waktu_selesai }}"
                                                    data-nama_ttd="{{ $u->nama_bertandatangan }}">
                                                    {{ $u->judul }}
                                                </option>
                                            @endforeach
                                        </select>
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
                                        <input type="text" name="tempat" id="tempat" class="form-control" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="waktu" class="form-label">
                                            <i class="fas fa-clock text-primary me-1"></i>
                                            Waktu Rapat <span class="text-danger">*</span>
                                        </label>
                                        <div class="d-flex align-items-center">
                                            <input type="text" name="waktu_mulai" id="waktu_mulai"
                                                class="form-control me-2" placeholder="waktu mulai" required>
                                            <span class="fw-bold">s/d</span>
                                            <input type="text" name="waktu_selesai" id="waktu_selesai"
                                                class="form-control ms-2" placeholder="waktu selesai" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="nama_bertandatangan" class="form-label">
                                            <i class="fas fa-signature text-primary me-1"></i>
                                            Nama yang Bertanda Tangan <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" name="nama_bertandatangan" id="nama_bertandatangan"
                                            class="form-control" readonly required>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="lampiran" class="form-label">
                                                <i class="fas fa-paperclip text-primary me-1"></i>
                                                Lampiran
                                            </label>
                                            <input type="file"
                                                class="form-control @error('lampiran') is-invalid @enderror"
                                                id="lampiran" name="lampiran[]" accept=".pdf.jpg,.jpeg,.png" multiple>
                                            <small class="form-text text-muted">
                                                Format yang diizinkan: PDF, JPG, JPEG, PNG (Max: 2MB)
                                            </small>
                                            @error('lampiran')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div id="risalahContainer" class="mt-4">
                                    <!-- Dynamic content will be added here -->
                                </div>

                                <button type="button" class="btn btn-primary mt-3" id="tambahRisalahBtn">
                                    <i class="bi bi-plus-circle me-1"></i> Tambah Risalah Baru
                                </button>

                                <div id="risalahAlert" class="mt-2 text-danger" style="display:none;"></div>
                            </div>
                            <div class="card-footer d-flex justify-content-end">
                                <a href="{{ route('admin.risalah.index') }}"
                                    class="btn btn-outline-primary me-2">Batal</a>
                                <button type="submit" id="submitBtn" class="btn btn-primary">Simpan</button>
                            </div>
                        </div>
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
            const judulSelect = document.getElementById('judul');
            if (judulSelect) {
                judulSelect.addEventListener('change', function() {
                    const selected = this.options[this.selectedIndex];
                    const tempat = document.getElementById('tempat');
                    const waktuMulai = document.getElementById('waktu_mulai');
                    const waktuSelesai = document.getElementById('waktu_selesai');
                    const namaTtd = document.getElementById('nama_bertandatangan');

                    if (tempat) tempat.value = selected.dataset.tempat || '';
                    if (waktuMulai) waktuMulai.value = selected.dataset.waktu_mulai || '';
                    if (waktuSelesai) waktuSelesai.value = selected.dataset.waktu_selesai || '';
                    if (namaTtd) namaTtd.value = selected.dataset.nama_ttd || '';
                });
            }

            // Fungsi untuk update nomor otomatis
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
