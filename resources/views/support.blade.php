@extends('layouts.auth')

@section('title', 'Pusat Bantuan & Dukungan - SIPO')

@section('content')
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
            padding: 20px;
        }

        h1,
        h2,
        h3 {
            font-weight: 700;
            color: #000 !important;
            /* Judul jadi hitam */
        }

        .emergency-banner h3 {
            color: white !important;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header p {
            color: #555;
        }

        .contact-grid,
        .info-cards,
        .faq-list {
            margin-top: 20px;
        }

        /* === CARDS LEBIH LEBAR === */
        .contact-card,
        .info-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            text-align: center;
            width: 380px;
            /* lebih lebar */
            margin: 0 auto;
        }

        .contact-grid {
            display: flex;
            gap: 15px;
            /* jarak antar card diperkecil */
            flex-wrap: wrap;
            justify-content: center;
        }

        .info-cards {
            display: flex;
            gap: 15px;
            /* jarak antar card diperkecil */
            flex-wrap: wrap;
            justify-content: center;
        }

        .icon {
            font-size: 40px;
            margin-bottom: 15px;
        }

        /* === FAQ SPACING === */
        .faq-item {
            width: 100%;
            max-width: 700px;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            text-align: left;
            margin: 15px auto;
            /* diberi jarak antar FAQ */
        }

        .faq-item h4 {
            font-weight: 700;
        }

        .faq-item p {
            color: #555;
            margin-top: 5px;
        }

        .action-btn {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            background: #2563eb;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
        }

        .action-btn:hover {
            background: #1d4ed8;
        }
    </style>



    <div class="container mb-5 pb-5">
        <div class="container">
            <div class="header">
                <h1>📞 Pusat Bantuan & Dukungan SIPO</h1>
                <p>
                    Selamat datang di halaman dukungan resmi SIPO (Sistem Informasi Persuratan Online).
                    Di sini Anda dapat menghubungi tim helpdesk, melihat FAQ, dan mendapatkan bantuan kapan pun Anda
                    membutuhkannya.
                </p>
            </div>

            <!-- EMERGENCY -->
            <div class="emergency-banner">
                <div class="icon">🚨</div>
                <h3>Dukungan Darurat 24/7</h3>
                <p>Untuk persoalan kritis yang membutuhkan penanganan segera, hubungi hotline di bawah ini:</p>
                <div style="font-size:1.4rem;font-weight:700;margin-top:10px;">📞 +62 851 5661 3540</div>
            </div>

            <!-- CONTACT CARDS -->
            <div class="contact-grid">

                <div class="contact-card">
                    <div class="icon">📧</div>
                    <h3>Email Resmi Bantuan</h3>
                    <div class="primary-contact" style="font-weight:700;">helpdeskit.reka@gmail.com</div>
                    <div class="secondary-info" style="color:#555;margin-top:10px;">
                        Respon dalam 2–4 jam<br>(Hari kerja)
                    </div>
                    <a href="mailto:helpdeskit.reka@gmail.com" class="action-btn">Kirim Email</a>
                </div>

                <div class="contact-card">
                    <div class="icon">📱</div>
                    <h3>WhatsApp Helpdesk</h3>
                    <div class="primary-contact" style="font-weight:700;">+62 851-5661-3540</div>
                    <div class="secondary-info" style="color:#555;margin-top:10px;">
                        Chat & Voice Message<br>Aktif pada jam kerja
                    </div>
                    <a href="https://wa.me/6285156613540" target="_blank" class="action-btn">Chat WhatsApp</a>
                </div>

            </div>

            <!-- INFORMATION CARDS -->
            <div class="info-cards">

                <div class="info-card">
                    <div class="icon">⏰</div>
                    <h3>Jam Operasional</h3>
                    <ul class="hours-list" style="list-style:none;padding:0;color:#444;">
                        <li><strong>Senin - Jumat:</strong> 07:30 - 16:30</li>
                        <li><strong>Sabtu:</strong> 08:00 - 12:00</li>
                        <li><strong>Minggu:</strong> 08:00 - 12:00</li>
                        <li><strong>Darurat:</strong> 24/7</li>
                    </ul>
                </div>

                <div class="info-card">
                    <div class="icon">🏢</div>
                    <h3>Kantor Pusat</h3>
                    <p style="color:#444;">
                        PT. Rekaindo Global Jasa<br>
                        Jl. Candi Sewu No. 30, Madiun Lor<br>
                        Kec. Manguharjo, Kota Madiun<br>
                        Jawa Timur 63122, Indonesia
                    </p>
                    <div style="margin-top:15px; color:#000;">
                        <strong>Kunjungan Kantor:</strong><br>
                        Senin - Jumat<br>
                        07:30 - 16:30
                    </div>
                </div>

            </div>

            <!-- FAQ -->
            <div class="faq-list mt-5">
                <h2 style="text-align:center;margin-bottom:20px;">❓ Pertanyaan yang Sering Diajukan</h2>

                <div class="faq-item">
                    <h4>1. Apa itu SIPO?</h4>
                    <p>SIPO adalah Sistem Informasi Persuratan Online yang digunakan untuk mengelola surat masuk, surat
                        keluar,
                        disposisi, dan alur persetujuan secara digital.</p>
                </div>

                <div class="faq-item">
                    <h4>2. Bagaimana cara mendapatkan akun?</h4>
                    <p>Akun dapat diberikan oleh administrator instansi/perusahaan Anda atau melalui permintaan resmi ke
                        helpdesk.</p>
                </div>

                <div class="faq-item">
                    <h4>3. Apa yang harus saya lakukan jika lupa kata sandi?</h4>
                    <p>Gunakan fitur <strong>Lupa Password</strong> pada halaman login atau hubungi tim helpdesk untuk reset
                        manual.</p>
                </div>

                <div class="faq-item">
                    <h4>4. Apakah SIPO bisa digunakan oleh semua perusahaan?</h4>
                    <p>Ya, SIPO bersifat umum dan dapat digunakan oleh instansi apa pun yang ingin mendigitalisasi sistem
                        persuratannya.</p>
                </div>

                <div class="faq-item">
                    <h4>5. Bagaimana cara mendapatkan SIPO di perusahaan?</h4>
                    <p>Silakan menghubungi kami untuk informasi lebih lanjut terkait pemasangan SIPO di perusahaan Anda.</p>
                </div>

                <div class="faq-item">
                    <h4>6. Bagaimana jika aplikasi mengalami error?</h4>
                    <p>Silakan menghubungi helpdesk melalui email atau WhatsApp. Jika darurat, gunakan hotline 24/7.</p>
                </div>

            </div>
        </div>

    @endsection

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                document.body.classList.add('support-page');
            });
        </script>
    @endpush
