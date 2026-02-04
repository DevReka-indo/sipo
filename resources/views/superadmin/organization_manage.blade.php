@extends('layouts.app')

@section('title', 'Manajemen Struktur Organisasi')

@section('content')

    <head>
        <meta charset="UTF-8">
        <title>Struktur Organisasi</title>
        <link rel="stylesheet" href="https://fperucic.github.io/treant-js/Treant.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/treant-js/1.0/Treant.css">
        <!-- Link to external CSS file -->
        <link rel="stylesheet" href="{{ asset('assets/css/organization-structure.css') }}">

        <script src="https://cdnjs.cloudflare.com/ajax/libs/treant-js/1.0/Treant.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.3.0/raphael.min.js"></script>
    </head>

    <div class="container-fluid px-4 py-0 mt-0">
        <div class="card shadow-sm border-0">
            <div class="card-body py-3">
                <!-- Back Button -->
                <div class="back-button">
                    <a href="{{ route('superadmin.dashboard') }}"><img src="/img/user-manage/Vector_back.png"
                            alt=""></a>
                </div>
                <h3 class="fw-bold mb-3">Manajemen Struktur Organisasi</h3>
                {{-- Breadcrumb --}}
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="bg-white border rounded-2 px-3 py-2 w-100 d-flex align-items-center">
                            <a href="{{ route('superadmin.dashboard') }}"
                                class="text-decoration-none text-primary">Beranda</a>
                            <span class="text-muted ms-1">/ Pengaturan / Manajemen Struktur Organisasi</span>
                        </div>
                    </div>
                </div>

                <form method="GET" action="{{ route('organization.manageOrganization') }}"
                    class="search-filter d-flex gap-2">
                </form>



                <!-- Wrapper untuk elemen di luar card -->
                <div class="col-12 col-md-auto">
                    {{-- Button Zoom --}}
                    <div class="treant-zoom-controls">
                        <button class="btn btn-light" style="box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);font-size:14px;font-weight:bold" onclick="zoomTreant(1.1)">+</button>
                        <button class="btn btn-light" style="box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);font-size:14px;font-weight:bold"onclick="zoomTreant(0.9)">−</button>
                        <button class="btn btn-light" style="box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);font-size:14px;font-weight:bold" onclick="resetZoom()">Reset</button>
                        <!-- Add User Button to Open Modal -->
                        <button type="button" class="btn btn-black rounded-3" data-bs-toggle="modal"
                            data-bs-target="#addUserModal">+ Tambah Struktur
                            Organisasi</button>
                    </div>

                    {{-- TREANT JS BUAT STO --}}
                    <div id="struktur-org">
                        <div id="zoom-target">
                            <!-- Treant di render disini -->
                            <div id="tree-container"></div>
                        </div>
                    </div>

                    <script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.2.7/raphael.min.js"></script>
                    <script src="https://fperucic.github.io/treant-js/Treant.min.js"></script>

                    {{-- CEK LOG ERROR KIRIM DATA
                    <pre>{{ json_encode($mainDirector, JSON_PRETTY_PRINT) }}</pre> --}}

                    {{-- SCRIPT TREANT JS BUAT STO --}}
                    <script>
                        var chart_config = {
                            chart: {
                                container: "#tree-container",
                                connectors: {
                                    type: 'step'
                                },
                                node: {
                                    HTMLclass: 'nodeExample1',
                                    useHtml: true
                                },
                                nodeAlign: "BOTTOM",
                                levelSeparation: 50,
                                siblingSeparation: 50,
                                subtreeSeparation: 100
                            },
                            nodeStructure: @json($formatDirector)
                        };

                        let treantScale = 1;

                        function applyZoom() {
                            const zoomTarget = document.getElementById('zoom-target');
                            zoomTarget.style.transform = `scale(${treantScale})`;
                            zoomTarget.style.transformOrigin = '0 0';
                        }

                        function zoomTreant(factor) {
                            // Untuk + dan -
                            treantScale *= factor;
                            applyZoom();
                        }

                        function resetZoom() {
                            treantScale = 1;
                            applyZoom();
                            scrollToCenter();
                        }

                        new Treant(chart_config, function() {
                            console.log("Treant finished rendering");
                            applyZoom(); // apply initial zoom
                        });
                    </script>
                    <script>
                        document.addEventListener("DOMContentLoaded", function() {
                            const interval = setInterval(() => {
                                const container = document.querySelector("#struktur-org");
                                const zoomTarget = document.querySelector("#zoom-target");

                                if (container && zoomTarget) {
                                    const scrollLeft = (zoomTarget.scrollWidth * parseFloat(getComputedStyle(zoomTarget)
                                        .transform.split(',')[0].replace('matrix(', '')) / 2) - (container.clientWidth /
                                        2);
                                    const scrollTop = 0;

                                    container.scrollLeft = scrollLeft;
                                    container.scrollTop = scrollTop;

                                    clearInterval(interval);
                                }
                            }, 100);
                        });
                    </script>
                </div> <!--Penutup col 12-->
            </div> <!--Penutup Card Body Py-3-->
        </div> <!--Penutup card show sm border 0-->
    </div> <!--Penutup container fluid-->

    <!-- Modal Tambah Struktur Organisasi -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="{{ route('organization-manage/add') }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addUserModalLabel">Tambah Struktur Organisasi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">

                        <div class="mb-3">
                            <label for="type" class="form-label">Jenis Struktur</label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="">-- Pilih --</option>
                                <option value="Director">Direktur</option>
                                <option value="Divisi">Divisi</option>
                                <option value="Department">Departemen</option>
                                <option value="Section">Bagian</option>
                                <option value="Unit">Unit</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="parent_id" class="form-label">Parent Struktur</label>
                            <select class="form-select" id="parent_id" name="parent_id">
                                <option value="">-- Pilih induk struktur --</option>
                                @php
                                    function renderOrgOptions($node, $level = 0)
                                    {
                                        $indent = str_repeat('&nbsp;', $level * 4);
                                        if (isset($node->name_director)) {
                                            echo "<option value='director-{$node->id_director}'>{$indent}Direktur: {$node->name_director}</option>";
                                        } elseif (isset($node->nm_divisi)) {
                                            echo "<option value='divisi-{$node->id_divisi}'>{$indent}--> Divisi: {$node->nm_divisi}</option>";
                                        } elseif (isset($node->name_department)) {
                                            echo "<option value='department-{$node->id_department}'>{$indent}-----> Departemen: {$node->name_department}</option>";
                                        } elseif (isset($node->name_section)) {
                                            echo "<option value='section-{$node->id_section}'>{$indent}--------> Bagian: {$node->name_section}</option>";
                                        } elseif (isset($node->name_unit)) {
                                            echo "<option value='unit-{$node->id_unit}'>{$indent}-----------> Unit: {$node->name_unit}</option>";
                                        }

                                        if (isset($node->subDirectors)) {
                                            foreach ($node->subDirectors as $subDir) {
                                                renderOrgOptions($subDir, $level + 1);
                                            }
                                        }
                                        if (isset($node->divisi)) {
                                            foreach ($node->divisi as $div) {
                                                renderOrgOptions($div, $level + 1);
                                            }
                                        }
                                        if (isset($node->department)) {
                                            if (isset($node->name_director)) {
                                                foreach ($node->department->whereNull('divisi_id_divisi') as $dept) {
                                                    renderOrgOptions($dept, $level + 1);
                                                }
                                            }
                                            if (isset($node->nm_divisi)) {
                                                foreach ($node->department as $dept) {
                                                    renderOrgOptions($dept, $level + 1);
                                                }
                                            }
                                        }
                                        if (isset($node->section)) {
                                            foreach ($node->section as $sec) {
                                                renderOrgOptions($sec, $level + 1);
                                            }
                                        }
                                        if (isset($node->unit)) {
                                            if (
                                                isset($node->name_department) &&
                                                $node->unit->whereNull('section_id_section')
                                            ) {
                                                foreach ($node->unit->whereNull('section_id_section') as $unit) {
                                                    renderOrgOptions($unit, $level + 1);
                                                }
                                            }
                                            if (isset($node->name_section)) {
                                                foreach ($node->unit as $unit) {
                                                    renderOrgOptions($unit, $level + 1);
                                                }
                                            }
                                        }
                                    }
                                    if ($mainDirector) {
                                        renderOrgOptions($mainDirector);
                                    }
                                @endphp
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Struktur</label>
                            <input type="text" class="form-control" id="name" name="name" required
                                placeholder="Masukkan nama struktur...">
                        </div>

                        <div class="mb-3">
                            <label for="kode" class="form-label">Kode Struktur</label>
                            <input type="text" class="form-control" id="kode" name="kode"
                                placeholder="Masukkan kode struktur...">
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Struktur Organisasi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="type" id="editType">
                        <input type="hidden" name="id" id="editId">
                        <div class="mb-3">
                            <label for="editName" class="form-label">Nama</label>
                            <input type="text" class="form-control" id="editName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="editKode" class="form-label">Kode</label>
                            <input type="text" class="form-control" id="editKode" name="kode">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const zoomLevel = 0.8; // Set your desired default zoom here (e.g., 0.8 = 80%)

            const waitForTreant = setInterval(() => {
                const nodeTree = document.querySelector('#struktur-org .Treant .node-tree');
                if (nodeTree) {
                    nodeTree.style.transform = `scale(${zoomLevel})`;
                    nodeTree.style.transformOrigin = 'top left';
                    clearInterval(waitForTreant);
                }
            }, 100);
        });
    </script>
    <script>
        let treantScale = 1;

        function zoomTreant(factor) {

            if (factor === 1) {
                treantScale = 1;
            } else {
                treantScale *= factor;
                treantScale = Math.max(0.2, Math.min(treantScale, 3));
            }

            const treantContent = document.querySelector("#struktur-org");
            if (treantContent) {
                treantContent.style.transform = 'scale(' + treantScale + ')';
                treantContent.style.transformOrigin = '0 0';
                console.log("Zoom clicked", factor);

            }
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const editModal = document.getElementById('editModal');
            editModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const type = button.getAttribute('data-type');
                const id = button.getAttribute('data-id');
                const name = button.getAttribute('data-name');
                const kode = button.getAttribute('data-kode');

                editModal.querySelector('#editType').value = type;
                editModal.querySelector('#editId').value = id;
                editModal.querySelector('#editName').value = name;
                editModal.querySelector('#editKode').value = kode;

                editModal.querySelector('#editForm').action = `/organization/${type}/${id}`;
            });
        });

        function confirmDelete(url) {
            Swal.fire({
                title: 'Anda yakin?',
                text: "Semua data di bawahnya juga akan dihapus!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    }).then(res => {
                        if (res.ok) {
                            location.reload();
                        } else {
                            Swal.fire('Gagal!', 'Tidak dapat menghapus data.', 'error');
                        }
                    });
                }
            });
        }
    </script>

@endsection
