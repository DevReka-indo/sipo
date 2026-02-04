<div class="sidebar-logo">
    <div class="logo-header d-flex align-items-center justify-content-center p-3 pt-4 pb-4" style="padding:14px 16px;">
        <a href="{{ url('dashboard') }}" class="logo" style="display:block; width:100%; text-decoration:none;">
            <div
                style="
                    background:#fff;
                    padding:10px 14px;
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    overflow:hidden;
                    width:100%;
                ">
                <img src="/assets/img/Logo-SIPO-Text.svg" alt="SIPO"
                    style="display:block; max-width:100%; height:auto; max-height:76px; margin:0;" />
            </div>
        </a>
    </div>
</div>

<div class="sidebar-wrapper">
    <div class="sidebar-content">
        <ul class="nav nav-secondary" style="margin-top: 50px;">

            <li class="nav-section">
                <span class="sidebar-mini-icon">
                    <i class="fa fa-ellipsis-h"></i>
                </span>
                <h4 class="text-section">MENU</h4>
            </li>
            <li class="nav-item {{ request()->routeIs('counter-nomor-surat.*') ? 'active' : '' }}">
                <a href="{{ route('counter-nomor-surat.index') }}">
                    <i class="fas fa-sort-numeric-up"></i>
                    <p>Counter Nomor Surat</p>
                </a>
            </li>


            <!-- SUPERADMIN -->
            @if (Auth::user()->role->nm_role == 'superadmin')
                <li class="nav-item {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('superadmin.dashboard') }}" class="nav-link">
                        <i class="fas fa-home"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <!-- Memo -->
                <li class="nav-item {{ request()->routeIs('superadmin.memo.index') ? 'active' : '' }}">
                    <a href="{{ route('superadmin.memo.index') }}" class="nav-link">
                        <i class="fas fa-file-alt"></i>
                        <p>Memo</p>
                    </a>
                </li>

                <!-- Undangan Rapat -->
                <li class="nav-item {{ request()->routeIs('superadmin.undangan.index') ? 'active' : '' }}">
                    <a href="{{ route('superadmin.undangan.index') }}" class="nav-link">
                        <i class="fas fa-calendar-alt"></i>
                        <p>Undangan Rapat</p>
                    </a>
                </li>

                <!-- Risalah Rapat -->
                <li class="nav-item {{ request()->routeIs('superadmin.risalah.index') ? 'active' : '' }}">
                    <a href="{{ route('superadmin.risalah.index') }}" class="nav-link">
                        <i class="fas fa-clipboard-list"></i>
                        <p>Risalah Rapat</p>
                    </a>
                </li>
            @endif

            <!-- ADMIN -->
            @if (Auth::user()->role->nm_role == 'admin')
                <li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link">
                        <i class="fas fa-home"></i>
                        <p>Dashboard</p>
                    </a>
                </li>


                <!-- Memo (Lama) -->
                {{-- <li class="nav-item {{ request()->routeIs('admin.memo.index') ? 'active' : '' }}">
                    <a href="{{ route('admin.memo.index') }}" class="nav-link">
                        <i class="fas fa-file-alt"></i>
                        <p>Memo</p>
                    </a>
                </li> --}}

                <!-- Memo (Admin: Masuk/Keluar) -->
                <li
                    class="nav-item {{ request()->is('memo*') || request()->routeIs('admin.memo.terkirim', 'admin.memo.diterima') ? 'active' : '' }}">
                    <a data-bs-toggle="collapse" href="#memo-admin"
                        aria-expanded="{{ request()->is('memo*') || request()->routeIs('admin.memo.terkirim', 'admin.memo.diterima') ? 'true' : 'false' }}">
                        <i class="fas fa-file-alt"></i>
                        <p>Memo</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse {{ request()->is('memo*') || request()->routeIs('admin.memo.terkirim', 'admin.memo.diterima') ? 'show' : '' }}"
                        id="memo-admin">
                        <ul class="nav nav-collapse">
                            <li class="{{ request()->routeIs('admin.memo.terkirim') ? 'active' : '' }}">
                                <a href="{{ route('admin.memo.terkirim') }}">
                                    <span class="sub-item">Memo Keluar</span>
                                </a>
                            </li>
                            <li class="{{ request()->routeIs('admin.memo.diterima') ? 'active' : '' }}">
                                <a href="{{ route('admin.memo.diterima') }}">
                                    <span class="sub-item">Memo Masuk</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>


                <!-- Undangan Rapat (Lama) -->
                {{-- <li class="nav-item {{ request()->routeIs('admin.undangan.index') ? 'active' : '' }}">
                    <a href="{{ route('admin.undangan.index') }}" class="nav-link">
                        <i class="fas fa-calendar-alt"></i>
                        <p>Undangan Rapat</p>
                    </a>
                </li> --}}

                <!-- Undangan (Admin: Masuk/Keluar) -->
                <li
                    class="nav-item {{ request()->routeIs('admin.undangan.terkirim', 'admin.undangan.diterima') ? 'active' : '' }}">
                    <a data-bs-toggle="collapse" href="#undangan-admin">
                        <i class="fas fa-calendar-alt"></i>
                        <p>Undangan Rapat</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse {{ request()->routeIs('admin.undangan.terkirim', 'admin.undangan.diterima') ? 'show' : '' }}"
                        id="undangan-admin">
                        <ul class="nav nav-collapse">
                            <li class="{{ request()->routeIs('admin.undangan.terkirim') ? 'active' : '' }}">
                                <a href="{{ route('admin.undangan.terkirim') }}">
                                    <span class="sub-item">Undangan Keluar</span>
                                </a>
                            </li>
                            <li class="{{ request()->routeIs('admin.undangan.diterima') ? 'active' : '' }}">
                                <a href="{{ route('admin.undangan.diterima') }}">
                                    <span class="sub-item">Undangan Masuk</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- Risalah Rapat -->
                <li class="nav-item {{ request()->routeIs('admin.risalah.index') ? 'active' : '' }}">
                    <a href="{{ route('admin.risalah.index') }}" class="nav-link">
                        <i class="fas fa-clipboard-list"></i>
                        <p>Risalah Rapat</p>
                    </a>
                </li>
            @endif
            @if (Auth::user()->role->nm_role == 'superadmin' || Auth::user()->role->nm_role == 'admin')
                <!-- Arsip -->
                <li class="nav-item {{ request()->is('arsip*') ? 'active' : '' }}">
                    <a data-bs-toggle="collapse" href="#arsip">
                        <i class="fas fa-archive"></i>
                        <p>Arsip</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse {{ request()->is('arsip*') ? 'show' : '' }}" id="arsip">
                        <ul class="nav nav-collapse">
                            <li class="{{ request()->routeIs('arsip.memo') ? 'active' : '' }}">
                                <a href="{{ route('arsip.memo') }}">
                                    <span class="sub-item">Memo</span>
                                </a>
                            </li>
                            <li class="{{ request()->routeIs('arsip.undangan') ? 'active' : '' }}">
                                <a href="{{ route('arsip.undangan') }}">
                                    <span class="sub-item">Undangan Rapat</span>
                                </a>
                            </li>
                            <li class="{{ request()->routeIs('arsip.risalah') ? 'active' : '' }}">
                                <a href="{{ route('arsip.risalah') }}">
                                    <span class="sub-item">Risalah Rapat</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endif
            <!-- LAPORAN -->
            @if (Auth::user()->role->nm_role == 'superadmin')
                <!-- Laporan -->
                <li class="nav-item {{ request()->is('laporan*') ? 'active' : '' }}">
                    <a data-bs-toggle="collapse" href="#laporan">
                        <i class="fas fa-book"></i>
                        <p>Laporan</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse {{ request()->is('laporan*') ? 'show' : '' }}" id="laporan">
                        <ul class="nav nav-collapse">
                            <li class="{{ request()->routeIs('laporan-memo.superadmin') ? 'active' : '' }}">
                                <a href="{{ route('laporan-memo.superadmin') }}">
                                    <span class="sub-item">Memo</span>
                                </a>
                            </li>
                            <li class="{{ request()->routeIs('laporan-undangan.superadmin') ? 'active' : '' }}">
                                <a href="{{ route('laporan-undangan.superadmin') }}">
                                    <span class="sub-item">Undangan Rapat</span>
                                </a>
                            </li>
                            <li class="{{ request()->routeIs('laporan-risalah.superadmin') ? 'active' : '' }}">
                                <a href="{{ route('laporan-risalah.superadmin') }}">
                                    <span class="sub-item">Risalah Rapat</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endif

            <!-- MANAGER -->
            @if (Auth::user()->role->nm_role == 'manager')
                <li class="nav-item {{ request()->routeIs('manager.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('manager.dashboard') }}" class="nav-link">
                        <i class="fas fa-home"></i>
                        <p>Dashboard</p>
                    </a>
                </li>


                {{-- Memo --}}
                <li
                    class="nav-item
                    {{ request()->is('memo*') || request()->routeIs('memo.terkirim', 'memo.diterima') ? 'active' : '' }}">
                    <a data-bs-toggle="collapse" href="#memo">
                        <i class="fas fa-file-alt"></i>
                        <p>Memo</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse
                        {{ request()->is('memo*') || request()->routeIs('memo.terkirim', 'memo.diterima') ? 'show' : '' }}"
                        id="memo">
                        <ul class="nav nav-collapse">
                            <li class="{{ request()->routeIs('memo.terkirim') ? 'active' : '' }}">
                                <a href="{{ route('memo.terkirim') }}">
                                    <span class="sub-item">Memo Keluar</span>
                                </a>
                            </li>
                            <li class="{{ request()->routeIs('memo.diterima') ? 'active' : '' }}">
                                <a href="{{ route('memo.diterima') }}">
                                    <span class="sub-item">Memo Masuk</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>



                <!-- Undangan Rapat (Lama) -->
                {{-- <li class="nav-item {{ request()->routeIs('undangan.manager') ? 'active' : '' }}">
                    <a href="{{ route('undangan.manager') }}" class="nav-link">
                        <i class="fas fa-calendar-alt"></i>
                        <p>Undangan Rapat</p>
                    </a>
                </li> --}}

                <!-- Undangan (Manager: Masuk/Keluar) -->
                <li
                    class="nav-item {{ request()->is('undangan*') || request()->routeIs('undangan.terkirim', 'undangan.diterima') ? 'active' : '' }}">
                    <a data-bs-toggle="collapse" href="#undangan-manager"
                        aria-expanded="{{ request()->is('undangan*') || request()->routeIs('undangan.terkirim', 'undangan.diterima') ? 'true' : 'false' }}">
                        <i class="fas fa-calendar-alt"></i>
                        <p>Undangan Rapat</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse {{ request()->is('undangan*') || request()->routeIs('undangan.terkirim', 'undangan.diterima') ? 'show' : '' }}"
                        id="undangan-manager">
                        <ul class="nav nav-collapse">
                            <li class="{{ request()->routeIs('undangan.terkirim') ? 'active' : '' }}">
                                <a href="{{ route('undangan.terkirim') }}">
                                    <span class="sub-item">Undangan Keluar</span>
                                </a>
                            </li>
                            <li class="{{ request()->routeIs('undangan.diterima') ? 'active' : '' }}">
                                <a href="{{ route('undangan.diterima') }}">
                                    <span class="sub-item">Undangan Masuk</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- Risalah Rapat -->
                <li class="nav-item {{ request()->routeIs('risalah.manager') ? 'active' : '' }}">
                    <a href="{{ route('risalah.manager') }}" class="nav-link">
                        <i class="fas fa-clipboard-list"></i>
                        <p>Risalah Rapat</p>
                    </a>
                </li>

                <!-- Arsip -->
                <li class="nav-item {{ request()->is('arsip*') ? 'active' : '' }}">
                    <a data-bs-toggle="collapse" href="#arsip">
                        <i class="fas fa-archive"></i>
                        <p>Arsip</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse {{ request()->is('arsip*') ? 'show' : '' }}" id="arsip">
                        <ul class="nav nav-collapse">
                            <li class="{{ request()->routeIs('arsip.memo') ? 'active' : '' }}">
                                <a href="{{ route('arsip.memo') }}">
                                    <span class="sub-item">Memo</span>
                                </a>
                            </li>
                            <li class="{{ request()->routeIs('arsip.undangan') ? 'active' : '' }}">
                                <a href="{{ route('arsip.undangan') }}">
                                    <span class="sub-item">Undangan Rapat</span>
                                </a>
                            </li>
                            <li class="{{ request()->routeIs('arsip.risalah') ? 'active' : '' }}">
                                <a href="{{ route('arsip.risalah') }}">
                                    <span class="sub-item">Risalah Rapat</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endif






            <li class="nav-section">
                <span class="sidebar-mini-icon">
                    <i class="fa fa-ellipsis-h"></i>
                </span>
                <h4 class="text-section">LAINNYA</h4>
            </li>
            {{-- Pengaturan (SUPERADMIN & ADMIN) --}}
            @if (Auth::user()->role->nm_role == 'superadmin' || Auth::user()->role->nm_role == 'admin')
                <li
                    class="nav-item
                {{ request()->is('pengaturan*') ||
                request()->routeIs('data-perusahaan', 'user.manage', 'organization.manageOrganization')
                    ? 'active'
                    : '' }}">
                    <a data-bs-toggle="collapse" href="#pengaturan">
                        <i class="fas fa-cogs"></i>
                        <p>Pengaturan</p>
                        <span class="caret"></span>
                    </a>

                    <div class="collapse
                    {{ request()->is('pengaturan*') ||
                    request()->routeIs('data-perusahaan', 'user.manage', 'organization.manageOrganization')
                        ? 'show'
                        : '' }}"
                        id="pengaturan">
                        <ul class="nav nav-collapse">
                            <li class="{{ request()->routeIs('data-perusahaan') ? 'active' : '' }}">
                                <a href="{{ route('data-perusahaan') }}">
                                    <span class="sub-item">Data Perusahaan</span>
                                </a>
                            </li>
                            @if (Auth::user()->role->nm_role == 'superadmin')
                                <li class="{{ request()->routeIs('kode-bagian.index') ? 'active' : '' }}">
                                    <a href="{{ route('kode-bagian.index') }}">
                                        <span class="sub-item">Manajemen Kode Bagian Kerja</span>
                                    </a>
                                </li>
                                <li class="{{ request()->routeIs('user.manage') ? 'active' : '' }}">
                                    <a href="{{ route('user.manage') }}">
                                        <span class="sub-item">Manajemen Pengguna</span>
                                    </a>
                                </li>
                                <li
                                    class="{{ request()->routeIs('organization.manageOrganization') ? 'active' : '' }}">
                                    <a href="{{ route('organization.manageOrganization') }}">
                                        <span class="sub-item">Manajemen Struktur Organisasi</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </li>
            @endif

            <!-- Pemulihan Superadmin -->
            @if (Auth::user()->role->nm_role == 'superadmin')
                <li
                    class="nav-item
                    {{ request()->is('pemulihan*') || request()->routeIs('memo.backup', 'undangan.backup', 'risalah.backup')
                        ? 'active'
                        : '' }}">
                    <a data-bs-toggle="collapse" href="#pemulihan">
                        <i class="fas fa-recycle"></i>
                        <p>Pemulihan</p>
                        <span class="caret"></span>
                    </a>

                    <div class="collapse
                        {{ request()->is('pemulihan*') || request()->routeIs('memo.backup', 'undangan.backup', 'risalah.backup')
                            ? 'show'
                            : '' }}"
                        id="pemulihan">
                        <ul class="nav nav-collapse">
                            <li class="{{ request()->routeIs('memo.backup') ? 'active' : '' }}">
                                <a href="{{ route('memo.backup') }}">
                                    <span class="sub-item">Memo</span>
                                </a>
                            </li>
                            <li class="{{ request()->routeIs('undangan.backup') ? 'active' : '' }}">
                                <a href="{{ route('undangan.backup') }}">
                                    <span class="sub-item">Undangan Rapat</span>
                                </a>
                            </li>
                            <li class="{{ request()->routeIs('risalah.backup') ? 'active' : '' }}">
                                <a href="{{ route('risalah.backup') }}">
                                    <span class="sub-item">Risalah Rapat</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endif

            <!-- Info -->
            <li class="nav-item {{ request()->is('info') ? 'active' : '' }}">
                <a href="{{ route('info') }}">
                    <i class="fas fa-info-circle"></i>
                    <p>Info</p>
                </a>
            </li>
        </ul>
    </div>
</div>
