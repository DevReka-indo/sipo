<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memo</title>

    <style>
        @page {
            margin-top: 120px;
            margin-bottom: 120px;
            margin-left: 0;
            margin-right: 0;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            padding: 0;
            line-height: 1.5;
        }

        header {
            position: fixed;
            top: -100px;
            left: 0;
            right: 0;
            width: 100%;
        }

        footer {
            position: fixed;
            bottom: -120px;
            left: 0;
            right: 0;
            width: 100%;
        }

        main {
            margin-top: 0px;
            margin-bottom: 0px;
            text-align: center;
        }

        .content {
            width: 100%;
            margin: auto;
            text-align: center;
        }

        .memo-title {
            text-align: center;
            justify-content: center;
            font-size: 26px;
            font-weight: bold;
            color: black;
        }

        .letter {
            margin-left: 2cm;
            margin-right: 2cm;
            background-color: #ffffff;
            position: relative;
            z-index: 1;
        }

        .header1 tr td:first-child {
            width: 20%;
        }

        .header2 table {
            margin-top: 15px;
            border-collapse: collapse;
            width: 100%;
            table-layout: fixed;
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

        .fill {
            margin-top: 5px;
            width: 95%;
            margin: 0 auto;
        }

        .fill p {
            text-align: left;
            line-height: 1.5;
        }

        /* Tabel sistem (contoh: kategori barang) */
        .fill table {
            border-collapse: collapse;
            width: 100%;
        }

        .fill table th,
        .fill table td {
            border: 1px solid black;
            padding: 5px;
        }

        .fill table th:first-child,
        .fill table td:first-child {
            width: 5%;
            min-width: 40px;
        }

        .fill table th:nth-child(3),
        .fill table td:nth-child(3),
        .fill table th:nth-child(4),
        .fill table td:nth-child(4) {
            width: 10%;
            min-width: 100px;
        }

        /* =========================
           CSS LAMA TinyMCE (DIBIARKAN) - sebagian dinonaktifkan
           ========================= */

        /* ===== NONAKTIFKAN: ini mengunci semua tabel dalam div jadi seragam =====
        .fill div table {
            border-collapse: collapse !important;
            width: 100% !important;
            table-layout: auto !important;
            margin: 10px 0 !important;
        }

        .fill div table td {
            width: auto !important;
        }
        ===== END NONAKTIF ===== */

        .fill div table th,
        .fill div table td {
            border: 1px solid #000 !important;
            padding: 8px !important;
            word-wrap: break-word !important;
            overflow-wrap: break-word !important;
            hyphens: auto !important;
            vertical-align: top !important;
            min-width: 80px !important;
            max-width: 200px !important;
        }

        .fill div table th {
            background-color: #f5f5f5 !important;
            font-weight: bold !important;
            text-align: inherit !important;
        }

        /* Khusus untuk tabel dengan banyak kolom */
        .fill div table:has(th:nth-child(4)) th,
        .fill div table:has(td:nth-child(4)) td {
            font-size: 11px !important;
            padding: 6px !important;
            max-width: 150px !important;
        }

        .fill div table:has(th:nth-child(5)) th,
        .fill div table:has(td:nth-child(5)) td {
            font-size: 10px !important;
            padding: 4px !important;
            max-width: 120px !important;
        }

        .fill div table td p,
        .fill div table th p {
            margin: 0 !important;
            padding: 0 !important;
            line-height: 1.5 !important;
        }

        .fill div {
            overflow-x: auto !important;
        }

        .fill div table td,
        .fill div table th {
            white-space: normal !important;
            word-break: break-word !important;
        }

        .fill div table:has(th:nth-child(6)) th,
        .fill div table:has(td:nth-child(6)) td {
            font-size: 9px !important;
            padding: 3px !important;
            max-width: 100px !important;
        }

        .fill div table:has(th:nth-child(8)) th,
        .fill div table:has(td:nth-child(8)) td {
            font-size: 8px !important;
            padding: 2px !important;
            max-width: 80px !important;
        }

        .pdf-mode .fill div table {
            page-break-inside: avoid !important;
        }

        .pdf-mode .fill div table th,
        .pdf-mode .fill div table td {
            page-break-inside: avoid !important;
        }

        div[style*="text-align: justify"] table,
        .contents table,
        .isi-memo table {
            border-collapse: collapse !important;
            width: 100% !important;
            table-layout: auto !important;
            margin: 10px 0 !important;
            font-size: 12px !important;
        }

        div[style*="text-align: justify"] table th,
        div[style*="text-align: justify"] table td,
        .contents table th,
        .contents table td,
        .isi-memo table th,
        .isi-memo table td {
            border: 1px solid #000 !important;
            padding: 6px !important;
            word-wrap: break-word !important;
            overflow-wrap: break-word !important;
            hyphens: auto !important;
            vertical-align: top !important;
        }

        table:not([class]) {
            border-collapse: collapse !important;
            width: 100% !important;
            margin: 10px 0 !important;
        }

        table:not([class]) th,
        table:not([class]) td {
            border: 1px solid #000 !important;
            padding: 8px !important;
            word-wrap: break-word !important;
            vertical-align: top !important;
        }

        table:not([class]) th {
            background-color: #f5f5f5 !important;
            font-weight: bold !important;
        }

        /* ===== NONAKTIFKAN: ini membuang width kolom dari user (TinyMCE) =====
        table[style*="width"] {
            width: 100% !important;
        }

        td[style*="width"],
        th[style*="width"] {
            width: auto !important;
            min-width: 80px !important;
            max-width: 200px !important;
        }
        ===== END NONAKTIF ===== */

        table tr td:nth-child(n+5),
        table tr th:nth-child(n+5) {
            font-size: 10px !important;
            padding: 4px !important;
            max-width: 120px !important;
        }

        table tr td:nth-child(n+7),
        table tr th:nth-child(n+7) {
            font-size: 9px !important;
            padding: 3px !important;
            max-width: 100px !important;
        }

        table * {
            word-break: break-word !important;
            overflow-wrap: break-word !important;
            hyphens: auto !important;
        }

        div[style*="width: 600px"] table {
            width: 100% !important;
            max-width: 100% !important;
        }

        .fill table[style] {
            width: 100% !important;
            border-collapse: collapse !important;
            table-layout: auto !important;
        }

        .fill td[style],
        .fill th[style] {
            border: 1px solid #000 !important;
            padding: 6px !important;
            word-wrap: break-word !important;
            vertical-align: top !important;
        }

        .fill table th[style*="text-align: center"],
        .fill table td[style*="text-align: center"],
        table:not([class]) th[style*="text-align: center"],
        table:not([class]) td[style*="text-align: center"] {
            text-align: center !important;
        }

        .fill table th[style*="text-align: left"],
        .fill table td[style*="text-align: left"],
        table:not([class]) th[style*="text-align: left"],
        table:not([class]) td[style*="text-align: left"] {
            text-align: left !important;
        }

        .fill table th[style*="text-align: right"],
        .fill table td[style*="text-align: right"],
        table:not([class]) th[style*="text-align: right"],
        table:not([class]) td[style*="text-align: right"] {
            text-align: right !important;
        }

        .fill table th[style*="text-align: justify"],
        .fill table td[style*="text-align: justify"],
        table:not([class]) th[style*="text-align: justify"],
        table:not([class]) td[style*="text-align: justify"] {
            text-align: justify !important;
        }

        .fill table th:not([style*="text-align"]),
        table:not([class]) th:not([style*="text-align"]) {
            text-align: left;
        }

        .fill table td:not([style*="text-align"]),
        table:not([class]) td:not([style*="text-align"]) {
            text-align: left;
        }

        @media print {
            .fill table {
                font-size: 11px !important;
            }

            .fill table td,
            .fill table th {
                padding: 4px !important;
                font-size: 11px !important;
                line-height: 1.5 !important;
            }
        }

        .fill table tr:has(> :nth-child(4)) td,
        .fill table tr:has(> :nth-child(4)) th {
            font-size: 11px !important;
            padding: 5px !important;
        }

        .fill table tr:has(> :nth-child(6)) td,
        .fill table tr:has(> :nth-child(6)) th {
            font-size: 10px !important;
            padding: 4px !important;
        }

        .fill table tr:has(> :nth-child(8)) td,
        .fill table tr:has(> :nth-child(8)) th {
            font-size: 9px !important;
            padding: 3px !important;
        }

        .contents {
            text-align: justify;
        }

        .signature {
            margin-top: 5%;
            text-align: left !important;
            width: fit-content;
            margin-left: auto;
            margin-right: 3%;
        }

        .signature p {
            text-align: center;
            margin: 0;
        }

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

        .view-mode .header1,
        .view-mode .header2 {
            position: relative;
            top: 150px;
            left: 50%;
            transform: translateX(-50%);
            width: 40%;
            background-color: white;
            padding: 10px;
            text-align: left;
            z-index: 1000;
        }

        .view-mode .header2 {
            top: 6.5cm;
            width: 38.5%;
        }

        .view-mode .fill {
            position: relative;
            width: 95%;
            margin-left: auto;
            margin-right: auto;
            text-align: justify;
        }

        .view-mode .collab {
            position: relative;
            margin-top: 5.2cm;
            width: 95%;
            margin-left: auto;
            margin-right: auto;
            text-align: justify;
            overflow-y: auto;
            max-height: calc(100vh - 13cm);
        }

        .pdf-mode header img,
        .pdf-mode footer img,
        .pdf-mode .content {
            width: 100%;
        }

        /* =========================================================
           FIX FINAL: Tabel TinyMCE mengikuti ukuran user
           ========================================================= */

        /* Wajib lebih spesifik agar menang dari rule lama */
        .fill .editor-content table {
            border-collapse: collapse !important;
            width: 100% !important;
            table-layout: fixed !important;     /* mPDF lebih patuh pada width kolom */
            margin: 10px 0 !important;
        }

        /* Jika editor menghasilkan colgroup/col width, biarkan */
        .fill .editor-content colgroup,
        .fill .editor-content col {
            width: auto;
        }

        .fill .editor-content col[style*="width"] {
            width: revert !important;
        }

        /* Jika editor menghasilkan width per cell, biarkan (jangan di-auto-kan) */
        .fill .editor-content td[style*="width"],
        .fill .editor-content th[style*="width"] {
            width: revert !important;
            min-width: 0 !important;
            max-width: none !important;
        }

        /* Tampilan tetap rapi */
        .fill .editor-content td,
        .fill .editor-content th {
            border: 1px solid #000 !important;
            padding: 6px !important;
            vertical-align: top !important;
            white-space: normal !important;
            overflow-wrap: break-word !important;
            word-break: break-word !important;
        }

        /* Matikan efek rule global nth-child pada tabel editor */
        .fill .editor-content table tr td:nth-child(n+5),
        .fill .editor-content table tr th:nth-child(n+5),
        .fill .editor-content table tr td:nth-child(n+7),
        .fill .editor-content table tr th:nth-child(n+7) {
            font-size: inherit !important;
            padding: inherit !important;
            max-width: none !important;
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
        <div class="content">
            <h3 class="memo-title">Memo</h3>

            <div class="letter">
                <table class="header1">
                    @if ($memo->tgl_dibuat != null)
                        <tr>
                            <td>Tanggal</td>
                            <td>:</td>
                            <td>{{ $memo->tgl_dibuat->translatedFormat('d F Y') }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td>Nomor</td>
                        <td>:</td>
                        <td>{{ $memo->nomor_memo }}</td>
                    </tr>
                    <tr>
                        <td>Perihal</td>
                        <td>:</td>
                        <td><b>{{ $memo->judul }}</b></td>
                    </tr>
                </table>

                <div class="header2">
                    <table>
                        <tr>
                            <th style="text-align: left; vertical-align: top;">
                                Dari :
                                {{ $memo->user->unit->name_unit ?? ($memo->user->section->name_section ?? ($memo->user->department->name_department ?? ($memo->user->divisi->nm_divisi ?? ($memo->user->director->name_director ?? ' ')))) }}
                            </th>
                            <th style="text-align: left; vertical-align: top;">
                                Kepada :
                                @if (count($tujuanNames) === 1)
                                    <span style="display: inline;">{{ $tujuanNames[0] }}</span>
                                @else
                                    <ol style="margin: 0; padding-left: 20px;">
                                        @foreach ($tujuanNames as $name)
                                            <li>{{ $name }}</li>
                                        @endforeach
                                    </ol>
                                @endif
                            </th>
                        </tr>
                    </table>
                </div>

                <div class="collab">
                    <div class="fill">
                        <!-- FIX: wrapper khusus untuk konten TinyMCE -->
                        <div class="editor-content"
                             style="text-align: justify; width: 100%; max-width: 100%; overflow-x: auto; line-height: 1.5;">
                            {!! $memo->isi_memo !!}
                        </div>

                        {{--
                        @if ($memo->kategoriBarang && $memo->kategoriBarang->isNotEmpty())
                            <table>
                                <tr>
                                    <th>No</th>
                                    <th>Barang</th>
                                    <th>Qty</th>
                                    <th>Satuan</th>
                                </tr>
                                @foreach ($memo->kategoriBarang as $index => $barang)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $barang->barang }}</td>
                                        <td>{{ $barang->qty }}</td>
                                        <td>{{ $barang->satuan }}</td>
                                    </tr>
                                @endforeach
                            </table>
                        @endif
                        --}}
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
                    @endphp

                    <table style="width: 100%; table-layout: fixed; border-collapse: collapse;">
                        <tr>
                            <td style="width: 60%;"></td>
                            <td style="width: 40%; text-align: center; vertical-align: top; padding: 10px; border: none;">
                                <p style="text-align: center; margin-bottom: 5px;"><b>Hormat kami,</b></p>

                                @if ($isDirektur)
                                    <p style="text-align: center; margin: 0; font-weight: bold;">
                                        {{ optional($manager->director)->name_director }}
                                    </p>
                                @else
                                    <p style="text-align: center; margin: 0; font-weight: bold;">
                                        {{ preg_replace('/^\([A-Z]+\)\s*/', '', $manager->position->nm_position) }}
                                        {{ $bagian }}
                                    </p>
                                @endif

                                @if (!empty($memo->qr_approved_by))
                                    <div style="margin: 10px 0; text-align: center;">
                                        <img src="data:image/png;base64,{{ $memo->qr_approved_by }}" width="150">
                                    </div>
                                @else
                                    <br>
                                @endif

                                <p style="margin: 0; text-align: center;">
                                    <b><u>{{ $memo->nama_bertandatangan }}</u></b>
                                </p>
                            </td>
                        </tr>
                    </table>

                    @php
                        $tembusanList = explode(';', $memo->tembusan ?? '');
                    @endphp

                    @if ($memo->tembusan)
                        <div class="tembusan" style="margin-top: 50px">
                            <table>
                                <tr>
                                    <td style="text-align: left; vertical-align: top;">
                                        Tembusan :
                                        @foreach ($tembusanList as $tembusan)
                                            <p style="margin: 0;">{{ $tembusan }}</p>
                                        @endforeach
                                    </td>
                                </tr>
                            </table>
                        </div>
                    @endif

                    <div style="clear: both;"></div>
                </div>
            </div>
        </div>
    </main>
</body>

</html>
