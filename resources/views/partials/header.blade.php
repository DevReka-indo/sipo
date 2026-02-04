<nav class="navbar navbar-expand-lg bg-white border-bottom shadow-sm">
    <div class="container-fluid">

        <!-- Sidebar Toggle (tampilkan di semua ukuran dulu, nanti bisa dibatasi lagi) -->
        {{-- <button type="button" class="toggle-sidebar" aria-label="Toggle sidebar">
            <i class="fa fa-bars"
                style="color:#BEA6EB;background:#E9E6FB;padding:10px;border-radius:10px;display:inline-block;"></i>
        </button> --}}
        <span class="toggle-sidebar" role="button" tabindex="0" aria-label="Toggle sidebar">
            <i class="fa fa-bars"
                style="color:#BEA6EB;background:#E9E6FB;padding:10px;border-radius:10px;display:inline-block;"></i>
        </span>


        <!-- Area kosong untuk layout -->
        <div class="flex-grow-1"></div>

        <!-- Header Icons -->
        <ul class="navbar-nav ms-auto align-items-center" style="gap:24px;">

            <!-- Notifikasi -->
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="notifDropdown" role="button"
                    data-bs-toggle="dropdown" aria-expanded="false"
                    style="background:#E9E6FB; padding:8px 12px; border-radius:20px;">
                    <i class="fa fa-bell" style="color:#BEA6EB;font-size:20px;"></i>
                    <span class="notification badge bg-danger" id="notif-count" style="display:none;">0</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="notifDropdown"
                    style="width:400px; max-height:400px; overflow-y:auto; overflow-x:hidden;">
                    <li class="dropdown-header fw-bold">Notifikasi</li>
                    <li>
                        <div id="notif-body" class="px-3 py-2 text-center text-muted">Memuat notifikasi...</div>
                    </li>
                </ul>
            </li>

            <!-- Profile & Settings -->
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button"
                    data-bs-toggle="dropdown" aria-expanded="false"
                    style="background:#E9E6EB; padding:10px 22px; border-radius:16px; display:flex; align-items:center; justify-content:center; gap:10px;">
                    @if (Auth::user()->profile_image)
                        <img src="data:image/png;base64,{{ Auth::user()->profile_image }}" alt="profile"
                            class="rounded-circle" style="width: 20px; height: 20px; object-fit: cover;">
                    @else
                        <i class="fa fa-user-circle" style="color:#BEA6EB;font-size:20px;"></i>
                    @endif
                    <i class="fa fa-cog" style="color:#56C7EB;font-size:20px;"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="profileDropdown"
                    style="min-width:260px;">
                    <li class="px-3 py-2">
                        <div class="fw-bold mb-1">Selamat Datang, {{ Auth::user()->firstname }}
                            {{ Auth::user()->lastname }}</div>
                        @if (Auth::user()->role_id_role == 1)
                            <div class="text-muted mb-2" style="font-size:14px;">Super Admin</div>
                        @else
                            <div class="text-muted mb-2" style="font-size:14px;">
                                {{ Auth::user()->position->nm_position }}</div>
                        @endif
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item" href="{{ route('edit-profile') }}"><i class="fas fa-user me-2"></i>
                            Profil</a></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="dropdown-item text-danger" type="submit"><i
                                    class="fas fa-sign-out-alt me-2"></i> Keluar</button>
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>

{{-- Toggle Sidebar + Backdrop --}}
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.querySelector('.toggle-sidebar');
            const sidebar = document.querySelector('.sidebar');
            const backdrop = document.querySelector('.sidebar-backdrop');
            const body = document.body;

            let hadMinimize = false;
            const isMobile = () => window.matchMedia('(max-width: 991.98px)').matches;

            function openSidebar() {
                hadMinimize = body.classList.contains('sidebar_minimize');
                if (hadMinimize) body.classList.remove('sidebar_minimize');
                sidebar?.classList.add('active');
                backdrop?.classList.add('show');
                document.documentElement.style.overflow = 'hidden';
                document.body.style.overflow = 'hidden';
            }

            function closeSidebar() {
                sidebar?.classList.remove('active');
                backdrop?.classList.remove('show');
                document.documentElement.style.overflow = '';
                document.body.style.overflow = '';
                if (hadMinimize && !isMobile()) body.classList.add('sidebar_minimize');
            }

            function toggleSidebar() {
                if (sidebar?.classList.contains('active')) {
                    closeSidebar();
                } else {
                    openSidebar();
                }
            }

            toggleBtn?.addEventListener('click', toggleSidebar);
            backdrop?.addEventListener('click', closeSidebar);

            // Tutup saat tekan ESC
            document.addEventListener('keydown', e => {
                if (e.key === 'Escape' && sidebar?.classList.contains('active')) closeSidebar();
            });

            // Tutup otomatis saat klik link di sidebar (hanya mobile)
            document.querySelectorAll('.sidebar a').forEach(a => {
                a.addEventListener('click', (e) => {
                    if (!isMobile()) return;

                    // Jika link adalah toggle collapse, jangan tutup sidebar
                    if (a.hasAttribute('data-bs-toggle') && a.getAttribute('data-bs-toggle') ===
                        'collapse') {
                        return;
                    }

                    // Jika link normal (href valid), tutup sidebar
                    if (a.getAttribute('href') && a.getAttribute('href') !== '#') {
                        closeSidebar();
                    }
                });
            });

            // Reset state kalau resize ke desktop
            window.addEventListener('resize', () => {
                if (!isMobile()) closeSidebar();
            });
        });
    </script>
@endpush
{{-- JS Notifikasi --}}
@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Variable untuk menyimpan count saat ini
            let currentCount = 0;

            loadNotif();
            loadNotifCount();

            // auto refresh tiap 15 detik
            setInterval(function() {
                loadNotif();
                loadNotifCount();
            }, 15000);

            // Mapping icon & warna berdasarkan judul
            function getNotifConfig(judul) {
                let lower = judul.toLowerCase();
                let icon = "fas fa-file";
                let bgColor = "secondary";

                if (lower.includes("risalah")) {
                    icon = "fas fa-clipboard-list";
                    if (lower.includes("tolak")) bgColor = "danger";
                    else if (lower.includes("koreksi")) bgColor = "warning";
                    else if (lower.includes("setuju") || lower.includes("masuk") || lower.includes("kirim"))
                        bgColor = "success";
                } else if (lower.includes("undangan")) {
                    icon = "fas fa-calendar-check";
                    if (lower.includes("tolak")) bgColor = "danger";
                    else if (lower.includes("koreksi")) bgColor = "warning";
                    else if (lower.includes("setuju") || lower.includes("masuk") || lower.includes("kirim"))
                        bgColor = "success";
                } else if (lower.includes("memo")) {
                    icon = "fas fa-file-alt";
                    if (lower.includes("tolak")) bgColor = "danger";
                    else if (lower.includes("revisi")) bgColor = "warning";
                    else if (lower.includes("setuju") || lower.includes("masuk") || lower.includes("kirim"))
                        bgColor = "success";
                } else if (lower.includes("surat")) {
                    icon = "fas fa-envelope";
                    bgColor = "warning";
                } else if (lower.includes("laporan")) {
                    icon = "fas fa-chart-bar";
                    bgColor = "danger";
                }

                return {
                    icon,
                    bgColor
                };
            }

            // format tanggal ke Indonesia (contoh: 26 Agustus 2025, 14:30)
            function formatTanggalIndo(dateString) {
                let date = new Date(dateString);
                return new Intl.DateTimeFormat('id-ID', {
                    day: '2-digit',
                    month: 'long',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                }).format(date);
            }

            // potong teks dengan ... jika panjang lebih dari limit
            function truncateText(text, limit = 50) {
                if (!text) return "-";
                return text.length > limit ? text.substring(0, limit - 3) + "..." : text;
            }

            // ambil daftar notifikasi
            function loadNotif() {
                fetch("{{ route('notifications.index') }}")
                    .then(response => response.json())
                    .then(data => {
                        let body = document.getElementById('notif-body');
                        if (data.notifications.length === 0) {
                            body.innerHTML =
                                `<div class="text-muted py-2 text-start">Tidak ada notifikasi</div>`;
                            return;
                        }

                        let html = "";
                        data.notifications.forEach(notif => {
                            let readClass = notif.dibaca == 0 ? "fw-bold" : "text-muted";
                            let {
                                icon,
                                bgColor
                            } = getNotifConfig(notif.judul);

                            // Tentukan background color berdasarkan status dibaca
                            let itemBgColor = notif.dibaca == 0 ?
                                'background: rgba(173, 216, 230, 0.3);' : 'background: transparent;';

                            html += `
                    <div class="dropdown-item d-flex align-items-center ${readClass} text-start notif-item"
                         data-id="${notif.id_notifikasi}"
                         data-read="${notif.dibaca}"
                         data-redirect_url="${notif.redirect_url}"
                         style="cursor: pointer; ${itemBgColor} border-radius: 8px; margin: 2px 0;padding: 12px;">
                        <div class="me-3">
                            <div class="bg-${bgColor} d-flex align-items-center justify-content-center"
                                style="width:36px; height:36px; border-radius:50%;">
                                <i class="${icon} text-white" style="font-size:18px;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 text-start">
                            <div>${notif.judul}</div>
                            <div class="small text-secondary">${truncateText(notif.judul_document, 50)}</div>
                            <small class="text-muted">${formatTanggalIndo(notif.updated_at)}</small>
                        </div>
                    </div>
                `;
                        });

                        body.innerHTML = html;

                        // tambahkan event listener ke setiap item notif
                        document.querySelectorAll('.notif-item').forEach(item => {
                            item.addEventListener('click', function(e) {
                                e.preventDefault();
                                let notifId = this.getAttribute('data-id');
                                let isRead = this.getAttribute('data-read');
                                let redirectUrl = this.getAttribute('data-redirect_url')

                                // Hanya mark as read jika belum dibaca
                                if (isRead == '0') {
                                    markNotifAsRead(notifId, this, redirectUrl);
                                } else {
                                    window.location.href = redirectUrl;
                                }
                            });
                        });
                    })
                    .catch(error => {
                        console.error('Error loading notifications:', error);
                        document.getElementById('notif-body').innerHTML =
                            '<div class="text-danger py-2">Error memuat notifikasi</div>';
                    });
            }

            // fungsi tandai satu notif dibaca
            function markNotifAsRead(id, element, redirectUrl) {
                fetch(`/notifications/${id}/tanda-dibaca`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {

                            // Update UI
                            element.classList.remove('fw-bold');
                            element.classList.add('text-muted');
                            element.setAttribute('data-read', '1');

                            updateCounterDirectly(-1);
                            setTimeout(() => loadNotifCount(), 50);

                            // Redirect after marking read
                            window.location.href = redirectUrl;
                        }
                    })
            }


            // Update counter secara langsung
            function updateCounterDirectly(change) {
                currentCount = Math.max(0, currentCount + change);
                let countEl = document.getElementById('notif-count');
                countEl.innerText = currentCount;
                countEl.style.display = currentCount > 0 ? 'inline-block' : 'none';
            }

            // ambil jumlah unread
            function loadNotifCount() {
                fetch("{{ route('notifications.count') }}")
                    .then(response => response.json())
                    .then(data => {
                        currentCount = data.count;
                        let countEl = document.getElementById('notif-count');
                        countEl.innerText = data.count;
                        countEl.style.display = data.count > 0 ? 'inline-block' : 'none';
                    })
                    .catch(error => {
                        console.error('Error loading notification count:', error);
                    });
            }
        });
    </script>
@endpush
