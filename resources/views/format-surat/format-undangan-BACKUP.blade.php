<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Undangan rapat</title>

    <style>
        /* ====== FIX UTAMA: SAMAKAN DENGAN MEMO ====== */
        @page {
            margin-top: 120px;
            margin-bottom: 120px;
            margin-left: 0;
            margin-right: 0;
        }

        body {
            font-family: Arial, "Noto Color Emoji", "Apple Color Emoji", "Segoe UI Emoji", sans-serif;
            font-size: 12px;
            padding: 0;
            margin: 0;
            line-height: 1.5;
        }

        header {
            position: fixed;
            top: -100px;
            left: 0;
            right: 0;
            width: 100%;
            z-index: 0;
        }

        footer {
            position: fixed;
            bottom: -120px;
            left: 0;
            right: 0;
            width: 100%;
            z-index: 0;
        }

        main {
            margin-top: 0;
            margin-bottom: 0;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .content {
            width: 100%;
            margin: 0;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .letter {
            margin-left: 2cm;
            margin-right: 2cm;
            background-color: #ffffff;
            position: relative;
            z-index: 1;
        }

        /* ====== KHUSUS HALAMAN 1 (PDF): NAIKKAN KONTEN, HEADER TETAP ====== */
        .first-page-adjust {
            display: none;
        }

        .pdf-mode .first-page-adjust {
            display: block;
            height: 0;
            margin-top: -85px; /* <-- UBAH INI: -15px / -20px / -30px */
        }

        /* ====== KOMPONEN UTAMA ====== */
        .date {
            text-align: right;
            width: 89%;
            margin-right: 20px;
            margin-top: 10%;
            justify-items: end;
        }

        .header1 tr td:first-child {
            width: 20%;
        }

        .header1 tr td {
            line-height: 1.2;
        }

        .header2 table {
            margin-top: 15px;
            border-collapse: collapse;
            width: 100%;
            table-layout: auto;
        }

        .header2 th {
            width: 50%;
            border-top: 3px solid black;
            border-bottom: 3px solid black;
            text-align: left;
            font-weight: normal;
            padding: 10px;
            word-wrap: break-word;
            overflow: hidden;
        }

        .header2 th+th {
            border-left: 3px solid black;
        }

        .header2 td {
            padding: 0;
            margin: 0;
            text-align: left;
            white-space: nowrap;
        }

        .header2 td:first-child {
            width: 1%;
            text-align: left;
            padding-right: 10px;
        }

        .fill {
            margin-top: 1px;
            width: 95%;
            margin: 0 auto;
        }

        .fill p {
            text-align: left;
            line-height: 1.5;
        }

        .fill table {
            border-collapse: collapse;
            width: 100%;
            table-layout: fixed;
            background-color: white;
            margin-left: 20px;
        }

        .fill table td {
            border: none;
            text-align: left;
            padding: 0;
            vertical-align: top;
        }

        .fill table tr td:first-child {
            width: 15%;
        }

        .fill table tr td:nth-child(2) {
            width: 3%;
            text-align: center;
        }

        .fill table tr td:nth-child(3) {
            width: 82%;
        }

        .contents {
            text-align: justify;
        }

        /* Agenda cell styling */
        .agenda-cell {
            text-align: justify !important;
            padding-right: 20px !important;
            word-wrap: break-word !important;
            word-break: break-word !important;
            white-space: normal !important;
            line-height: 1.5 !important;
            page-break-inside: auto !important;
        }

        /* ====== SIGNATURE ====== */
        .signature-position {
            text-align: center;
            margin: 10px 0 !important;
            font-weight: bold;
        }

        .signature-name {
            margin: 15px 0 0 0 !important;
            text-align: center;
            font-weight: bold;
        }

        /* QR Code container */
        .qr-container {
            margin: 12px 0 8px 0 !important;
            text-align: center;
            page-break-inside: avoid;
            padding: 5px 0;
        }

        .qr-container img {
            max-width: 150px;
            height: auto;
        }

        /* ====== VIEW MODE (PREVIEW) ====== */
        .view-mode header img,
        .view-mode footer img,
        .view-mode .content {
            width: 50%;
            margin: auto;
        }

        .view-mode header,
        .view-mode footer {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            position: fixed;
            left: 0;
            z-index: 100;
        }

        .view-mode {
            overflow: hidden;
        }

        .view-mode header img {
            display: block;
            margin: 0 auto;
            width: 50%;
        }

        .view-mode .header1 {
            position: fixed;
            top: 150px;
            left: 50%;
            transform: translateX(-50%);
            width: 40%;
            background-color: white;
            padding: 0;
            text-align: left;
            z-index: 1000;
        }

        .view-mode .header2 {
            position: relative;
            padding: 0;
            width: 39.5%;
            text-align: left;
        }

        .view-mode .fill {
            position: relative;
            width: 100%;
            margin-left: auto;
            margin-right: auto;
            text-align: justify;
            padding: 0;
        }

        .view-mode .collab {
            position: relative;
            margin-top: 1cm;
            width: 100%;
            margin-left: auto;
            margin-right: auto;
            text-align: justify;
            overflow-y: visible !important;
            max-height: none !important;
        }

        /* ====== PDF MODE ====== */
        .pdf-mode header img,
        .pdf-mode footer img,
        .pdf-mode .content {
            width: 100%;
        }

        .pdf-mode .date {
            text-align: right;
            width: 89%;
        }

        .pdf-mode .header2 {
            margin-left: 2.5px;
        }

        .pdf-mode .header2 h4,
        .pdf-mode .header2 p {
            text-align: left;
            margin-left: 0;
        }

        .pdf-mode .fill {
            position: relative;
            width: 100%;
            margin-left: auto;
            margin-right: auto;
            text-align: justify;
            padding: 0;
            margin-top: 0;
        }

        .pdf-mode .collab {
            position: relative;
            width: 100%;
            margin-left: 2.5px;
            margin-right: auto;
            text-align: justify;
            overflow-y: visible !important;
            max-height: none !important;
            padding: 0;
            margin-top: 0;
        }

        .header2 h4,
        .header2 p,
        .header2 table td {
            line-height: 1.5;
        }

        /* PRINT MEDIA QUERIES */
        @media print {
            .agenda-cell {
                page-break-inside: auto !important;
            }
        }

        /* CLEAR FLOAT UTILITY */
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
    </style>
</head>

<body class="{{ isset($isPdf) && $isPdf ? 'pdf-mode' : 'view-mode' }}">
    @php
        $status = strtolower((string) ($docStatus ?? ''));
        $needsWatermark = in_array($status, ['reject', 'correction', 'pending'], true);

        $file = match ($status) {
            'reject' => public_path('assets/img/rejected-rotate-stamp.png'),
            'correction' => public_path('assets/img/oncorrection-rotate-stamp.png'),
            'pending' => public_path('assets/img/onprogress-rotate-stamp.png'),
            default => null,
        };

        $wmBase64 =
            $needsWatermark && $file && file_exists($file)
                ? 'data:image/png;base64,' . base64_encode(file_get_contents($file))
                : null;
    @endphp

    @if ($needsWatermark && $wmBase64)
        <style>
            /* Overlay full-page di atas semua konten */
            ._wm_overlay {
                position: fixed;
                inset: 0;
                z-index: 9999;
                opacity: 0.4;
                pointer-events: none;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            ._wm_overlay img {
                width: 100%;
                height: 100%;
                object-fit: contain;
            }
        </style>

        <div class="_wm_overlay">
            <img src="{{ $wmBase64 }}" alt="watermark">
        </div>
    @endif

    <header>
        @if (isset($headerImage))
            <img src="{{ $headerImage }}" width="100%">
        @endif
    </header>

    <footer>
        @if (isset($footerImage))
            <img src="{{ $footerImage }}" width="100%">
        @endif
    </footer>

    <main>
        <!-- KHUSUS HALAMAN 1: angkat konten tanpa menggeser header -->
        <div class="first-page-adjust"></div>

        <div class="content no-page-break ttd">
            <div class="date">
                <p>Madiun, {{ $undangan->tgl_dibuat?->translatedFormat('d F Y') }}</p>
            </div>
            <br>

            <div class="letter">
                <table class="header1">
                    <tr>
                        <td>Nomor</td>
                        <td>:</td>
                        <td>{{ trim($undangan->nomor_undangan) }}</td>
                    </tr>
                    <tr>
                        <td>Perihal</td>
                        <td>:</td>
                        <td><b>{{ trim($undangan->judul) }}</b></td>
                    </tr>
                </table>

                <div class="collab">
                    <div class="header2">
                        <div class="agenda-cell" style="margin-top: 6px;">
                            {!! $undangan->isi_undangan !!}
                        </div>
                    </div>

                    @php
                        $bagian =
                            optional($manager->unit)->name_unit ??
                            (optional($manager->section)->name_section ??
                                (optional($manager->department)->name_department ??
                                    (optional($manager->divisi)->nm_divisi ??
                                        optional($manager->director)->name_director)));

                        $isDirektur =
                            is_null($manager->divisi_id_divisi) &&
                            is_null($manager->department_id_department) &&
                            is_null($manager->section_id_section) &&
                            is_null($manager->unit_id_unit);

                        $agendaLength = strlen($cleanTag ?? '');
                        $tujuanCount = count($tujuanUsers ?? []);
                        $totalContent = $agendaLength + $tujuanCount * 60;
                        $needsPageBreak = $totalContent > 1500 || $tujuanCount > 12 || $agendaLength > 800;
                    @endphp

                    <table style="width: 100%; table-layout: fixed; border-collapse: collapse;">
                        <tr>
                            <td style="width: 60%;"></td>
                            <td style="width: 40%; text-align: center; vertical-align: top; padding: 10px; border: none;">
                                <p style="text-align: center; margin-bottom: 5px;"><b>Hormat kami,</b></p>

                                @if ($isDirektur)
                                    <p class="signature-position">
                                        {{ optional($manager->director)->name_director }}
                                    </p>
                                @else
                                    <p class="signature-position">
                                        {{ preg_replace('/^\([A-Z]+\)\s*/', '', $manager->position->nm_position) }}
                                        {{ $bagian }}
                                    </p>
                                @endif

                                @if (!empty($undangan->qr_approved_by))
                                    <div class="qr-container">
                                        <img src="data:image/png;base64,{{ $undangan->qr_approved_by }}" width="150" alt="QR Code">
                                    </div>
                                @endif

                                <p class="signature-name"><u>{{ $undangan->nama_bertandatangan }}</u></p>
                            </td>
                        </tr>
                    </table>

                </div>
            </div>
        </div>
    </main>
</body>

</html>
