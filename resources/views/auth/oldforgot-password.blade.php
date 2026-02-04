@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
            padding: 20px;
        }

        .contact-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        .contact-grid {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 30px;
        }

        .contact-card {
            background: white;
            padding: 30px;
            width: 300px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .icon {
            font-size: 40px;
            margin-bottom: 10px;
        }
    </style>


    <div class="container mb-5 pb-5">
        <div class="container">
            <div class="header">
                <h1>🎧 Hubungi Helpdesk</h1>
                <p>Tim dukungan teknis kami siap membantu Anda mengatasi berbagai masalah dan pertanyaan. Pilih metode
                    komunikasi yang paling nyaman untuk Anda.</p>
            </div>

            <div class="emergency-banner">
                <div class="icon">🚨</div>
                <h3>Dukungan Darurat 24/7</h3>
                <p>Untuk masalah yang membutuhkan penanganan segera, hubungi hotline darurat kami</p>
                <div style="font-size:1.3rem;font-weight:700;margin-top:10px;">📞 +62 851 5661 3540</div>
            </div>

            <div class="contact-grid">

                <div class="contact-card">
                    <div class="icon">📧</div>
                    <h3>Email</h3>
                    <div class="details">
                        <div class="primary-contact">helpdesk@perusahaan.com</div>
                        <div class="secondary-info">Respon dalam 2-4 jam<br>(Hari kerja)</div>
                        <a href="mailto:helpdesk@perusahaan.com" class="action-btn">Kirim Email</a>
                    </div>
                </div>


                <div class="contact-card">
                    <div class="icon">📱</div>
                    <h3>WhatsApp</h3>
                    <div class="details">
                        <div class="primary-contact">+62 851-5661-3540</div>
                        <div class="secondary-info">Chat & Voice Message<br>Aktif pada jam kerja</div>
                        <a href="https://wa.me/6285156613540" target="_blank" class="action-btn">Chat WhatsApp</a>
                    </div>
                </div>
            </div>

            <div class="info-cards">
                <div class="info-card">
                    <div class="icon">⏰</div>
                    <h3>Jam Operasional</h3>
                    <ul class="hours-list">
                        <li><span>Senin - Jumat</span> <span>07:30 - 16:30</span></li>
                        <li><span>Sabtu</span> <span>08:00 - 12:00</span></li>
                        <li><span>Minggu</span> <span>08:00 - 12:00</span></li>
                        <li><span>Darurat</span> <span>24/7</span></li>
                    </ul>
                </div>

                <div class="info-card">
                    <div class="icon">🏢</div>
                    <h3>Kantor Pusat</h3>
                    <p>Jl. Candi Sewu No.30, Madiun Lor, Kec. Manguharjo<br>Kota Madiun, Jawa Timur 63122<br>Indonesia</p>
                    <div style="margin-top:15px;">
                        <strong>Kunjungan:</strong><br>Senin - Jumat<br>07:30 - 16:30
                    </div>
                </div>
            </div>
        </div>

    @endsection
    @push('scripts')
        <script>
            // Tag body to scope background override for this page only
            document.addEventListener('DOMContentLoaded', () => {
                document.body.classList.add('forgot-pw');
            });
            // Animasi delay on-load
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('.contact-card').forEach((c, i) => c.style.animationDelay = `${i*0.1}s`);
                document.querySelectorAll('.info-card').forEach((c, i) => c.style.animationDelay = `${0.8+(i*0.1)}s`);
            });

            // Status online berdasarkan jam operasional
            function updateStatus() {
                const now = new Date();
                const h = now.getHours();
                const d = now.getDay();
                const dot = document.querySelector('.status-dot');
                const txt = document.querySelector('.status-indicator span');
                let online = false;
                if (d >= 1 && d <= 5) online = h >= 8 && h < 20; // Senin-Jumat
                else if (d === 6) online = h >= 9 && h < 17; // Sabtu
                else if (d === 0) online = h >= 10 && h < 16; // Minggu
                if (dot && txt) {
                    dot.style.background = online ? '#10b981' : '#ef4444';
                    txt.textContent = online ? 'Tim Online' : 'Tim Offline';
                }
            }
            updateStatus();
            setInterval(updateStatus, 60000);
        </script>
    @endpush
