<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Risalah {{ $risalah->nomor_risalah }} </title>
    <style>
        @page {
            margin-top: 20px;
            margin-bottom: 0px;
            margin-left: 0;
            margin-right: 0;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
            padding-top: 120px;
            padding-bottom: 100px;

        }

        .first-page-adjust {
            height: 0px;
            margin-top: -120px;
        }

        /* header/footer tetap */
        header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            width: 100%;
        }

        footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100%;
        }

        main {
            margin-top: 5px;
            margin-bottom: 10px;
            text-align: center;

        }

        .content {
            width: 100%;
            margin: auto;
            text-align: center;
        }

        /* blok info (Hari/Tempat/...) */
        .letter {
            margin-left: 2cm;
            margin-right: 2cm;
            background-color: #ffffff;
            line-height: 0.7cm;
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

        /* netralisir .header2 th agar <colgroup> tabel utama berlaku */
        .header2 table.fill th {
            width: auto !important;
        }

        .header2 table.fill col {
            width: auto !important;
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

        /* >>> sejajarkan tabel dengan .letter (2 cm kiri–kanan) <<< */
        .pdf-mode .header2 {
            margin: 0 2cm;
            /* sama seperti .letter */
            padding: 0;
            width: auto;
        }

        /* tabel inti */
        .fill {
            border-collapse: collapse;
            font-size: 11px;
            table-layout: fixed;
            /* kolom paten */
            width: 100%;
        }

        .fill th,
        .fill td {
            border: 1.5px solid black;
            padding: 6px;
            text-align: left;
            vertical-align: top;
            white-space: normal;
            /* boleh turun baris */
            word-wrap: break-word;
            /* dompdf */
            word-break: break-word;
            /* potong kata panjang */
        }

        .fill thead th {
            background-color: #f0f0f0;
            text-align: center;
            font-weight: bold;
        }

        .fill thead th:first-child {
            white-space: nowrap;
            text-align: center;
            font-size: 10px;
        }

        .fill td:first-child {
            white-space: nowrap;
            text-align: center;
        }


        .contents {
            text-align: justify;
            line-height: 0.7cm;
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

        /* view-mode (punyamu) */
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
            overflow-y: auto;
            max-height: calc(100vh - 9cm);
        }

        /* pdf-mode (punyamu) */
        .pdf-mode header img,
        .pdf-mode footer img,
        .pdf-mode .content {
            width: 100%;
        }

        .pdf-mode .date {
            text-align: center;
            width: 100%;
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
            overflow-y: auto;
            max-height: calc(100vh - 12cm);
            padding: 0;
            margin-top: 0;
        }

        .date {
            margin-top: 10%;
            display: flex;
            justify-content: center;
            text-align: center;
        }

        .title {
            font-size: 22px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .header1 tr td {
            line-height: 1.2;
        }

        .header2 h4,
        .header2 p,
        .header2 table td {
            line-height: 1.5;
        }


        /* ------------------------------------------------------------------
                                     Debug borders (testing only) - named colors

                                     - Global selectors so debug outlines appear even if body classes
                                         are stripped by the renderer. Each selector uses a distinct
                                         named CSS color (no hex codes) for easy visual testing.
                                     - Header and table colors are intentionally different and clear.
                                ------------------------------------------------------------------ */

        /* High-level page areas */
        /* header {
            outline: 3px solid red;
            border-color: red;
        }

        footer {
            outline: 3px solid blue;
            border-color: blue;
        }

        main {
            outline: 2px dashed green;
            border-color: green;
        }

        /* Content blocks */
        /* .content {
            outline: 2px solid orange;
            border-color: orange;
        }

        .letter {
            outline: 2px dotted purple;
            border-color: purple;
        } */

        /* Header sections (distinct from page header) */
        /* .header1 {
            outline: 2px solid teal;
            border-color: teal;
        }

        .header2 {
            outline: 2px solid navy;
            border-color: navy;
        }

        .header2 table {
            outline: 2px solid olive;
            border-color: olive;
        }

        .header2 th {
            outline: 2px solid maroon;
            border-color: maroon;
        }

        .header2 td {
            outline: 1px solid gray;
            border-color: gray;
        } */

        /* Main table */
        /* .fill {
            outline: 2px solid lime;
            border-color: lime;
        }

        .fill th {
            outline: 2px solid black;
            border-color: black;
        }

        .fill td {
            outline: 1px dashed pink;
            border-color: pink;
        } */

        /* Other areas */
        /* .collab {
            outline: 2px dashed brown;
            border-color: brown;
        }

        .date {
            outline: 2px solid yellow;
            border-color: yellow;
        }

        .title {
            outline: 2px solid gold;
            border-color: gold;
        }

        .signature {
            outline: 2px dashed silver;
            border-color: silver;
        } */

        /* Mode indicators */
        /* .view-mode {
            outline: 2px solid coral;
        }

        .pdf-mode {
            outline: 2px solid indigo;
        }  */
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
                /* di atas semua elemen */
                opacity: 0.4;
                /* ubah transparansi sesuai selera */
                pointer-events: none;
                /* biar gak ganggu elemen lain */
                display: flex;
                align-items: center;
                justify-content: center;
            }

            ._wm_overlay img {
                width: 100%;
                /* menutupi 1 halaman penuh */
                height: 100%;
                object-fit: contain;
                /* biar proporsional */
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

    {{-- <htmlpageheader name="pageHeader">
        <img src="{{ $headerImage }}" width="100%">
    </htmlpageheader>

    <htmlpagefooter name="pageFooter">
        <img src="{{ $footerImage }}" width="100%">
    </htmlpagefooter>

    <sethtmlpageheader name="pageHeader" value="on" />
    <sethtmlpagefooter name="pageFooter" value="on" /> --}}



    <main>
        <div class="first-page-adjust"></div>
        <div class="content">
            <div class="date">
                <div class="title">
                    <h5>Risalah Rapat<br>
                        {{ $risalah->agenda }}
                        <br>Nomor: {{ $risalah->nomor_risalah }}
                    </h5>
                </div>
            </div>

            <div class="letter">
                <table style="font-size: 12px; margin-bottom: 20px;">
                    <tr>
                        <td style="width: 100px;">Hari, tanggal</td>
                        <td style="width: 10px;">:</td>
                        <td>{{ $risalah->tgl_dibuat->translatedFormat('d F Y') }}</td>
                    </tr>
                    <tr>
                        <td>Waktu</td>
                        <td>:</td>
                        <td>
                            {{ $risalah->waktu_mulai }}
                            @if (preg_match('/^\d{1,2}(\.\d{1,2})?$/', $risalah->waktu_mulai))
                                WIB
                            @endif
                            s.d
                            {{ $risalah->waktu_selesai ?? 'selesai' }}
                            @if ($risalah->waktu_selesai && preg_match('/^\d{1,2}(\.\d{1,2})?$/', $risalah->waktu_selesai))
                                WIB
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Tempat</td>
                        <td>:</td>
                        <td>{{ $risalah->tempat }}</td>
                    </tr>

                    <tr>
                        <td>Agenda</td>
                        <td>:</td>
                        <td> {!! nl2br(e($risalah->agenda)) !!}
                        </td>

                    </tr>
                    <tr>
                        <td>Daftar Hadir</td>
                        <td>:</td>
                        <td>Daftar Hadir Terlampir</td>
                    </tr>
                    @if ($risalah->with_undangan)
                        <tr>
                            <td>Nomor Undangan</td>
                            <td>:</td>
                            <td>{{ $undangan->nomor_undangan }}</td>
                        </tr>
                    @endif
                </table>
            </div>

            <div class="collab">
                <div class="header2">
                    <table class="fill" style="page-break-inside: always;">
                        <!-- total 100% -->
                        <colgroup>
                            <col style="width:6%">
                            <col style="width:16%">
                            <col style="width:26%">
                            <col style="width:29%"> <!-- Tindak Lanjut paling lebar -->
                            <col style="width:13%">
                            <col style="width:10%">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Topik</th>
                                <th>Pembahasan</th>
                                <th>Tindak Lanjut</th>
                                <th>Target</th>
                                <th>PIC</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($risalah->risalahDetails as $index => $detail)
                                <tr>
                                    <td style="text-align: center;">{{ $index + 1 }}</td>
                                    <td>{{ $detail->topik }}</td>
                                    <td>
                                        @foreach (explode(';', $detail->pembahasan) as $poin)
                                            {!! nl2br(e(trim($poin))) !!}<br>
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach (explode(';', $detail->tindak_lanjut) as $poin)
                                            {!! nl2br(e(trim($poin))) !!}<br>
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach (explode(';', $detail->target) as $poin)
                                            {!! nl2br(e(trim($poin))) !!}<br>
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach (explode(';', $detail->pic) as $poin)
                                            {!! nl2br(e(trim($poin))) !!}<br>
                                        @endforeach
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{-- <table style="width:100%; text-align:center; align:center;">
                        <tr>
                            <td width="50%" style="text-align: center">
                                @php
                                    $namaJabatan = $pemimpin?->position?->nm_position;
                                @endphp
                                <p style="text-align:center">Pemimpin Acara</p>
                                <p style="margin:0; text-align:center;">
                                    {{ $namaJabatan }}
                                    {{ $pemimpin?->department?->name_department ?? ($userBertandatangan?->divisi?->nm_divisi ?? '-') }}
                                </p>
                                @if (!empty($risalah->qr_pemimpin_acara))
                                    <img src="data:image/png;base64,{{ $risalah->qr_pemimpin_acara }}" width="150" height="150">
                                @endif
                                <p style="margin:0; text-align:center;">{{ $risalah->nama_pemimpin_acara }}</p>
                            </td>

                            <td width="50%" style="text-align: center">
                                @php
                                    $namaJabatan = $notulis?->position?->nm_position;
                                @endphp
                                <p style="text-align: center">Notulis</p>
                                <p style="margin:0; text-align: center; ">
                                    {{ $namaJabatan }}
                                    {{ $notulis?->department?->name_department ?? '-' }}
                                </p>
                                @if (!empty($risalah->qr_notulis_acara))
                                    <img src="data:image/png;base64,{{ $risalah->qr_notulis_acara }}" width="150" height="150">
                                @endif
                                <p style="margin:0; text-align: center;">{{ $risalah->nama_notulis_acara }}</p>
                            </td>
                        </tr>
                    </table> --}}
                    <table style="width: 100%; table-layout: fixed; border-collapse: collapse;">
                        <tr>
                            <!-- Kolom Kiri: Notulis Acara -->
                            <td
                                style="width: 50%; text-align: center; vertical-align: top; padding: 10px; border: none;">
                                @php
                                    $jabatanNotulis = $notulis?->position?->nm_position;
                                    $departemenNotulis = $notulis?->department?->name_department ?? '-';
                                @endphp

                                <p style="margin: 0; text-align:center">Notulis</p>
                                <p style="margin: 4px 0; text-align:center">
                                    {{ $jabatanNotulis }} {{ $departemenNotulis }}
                                </p>

                                @if (!empty($risalah->qr_notulis_acara))
                                    <img src="data:image/png;base64,{{ $risalah->qr_notulis_acara }}" width="150"
                                        height="150" style="margin: 8px 0;">
                                @endif

                                <p style="margin: 4px 0; text-align:center">{{ $risalah->nama_notulis_acara }}</p>
                            </td>
                            <!-- Kolom Kanan: Pemimpin -->
                            <td
                                style="width: 50%; text-align: center; vertical-align: top; padding: 10px; border: none;">
                                @php
                                    $jabatanPemimpin = $pemimpin?->position?->nm_position;
                                    $departemenPemimpin =
                                        $pemimpin?->department?->name_department ??
                                        ($userBertandatangan?->divisi?->nm_divisi ?? '-');
                                @endphp

                                <p style="margin: 0; text-align:center">Pemimpin Acara</p>
                                <p style="margin: 4px 0;  text-align:center">
                                    {{ $jabatanPemimpin }} {{ $departemenPemimpin }}
                                </p>

                                @if (!empty($risalah->qr_pemimpin_acara))
                                    <img src="data:image/png;base64,{{ $risalah->qr_pemimpin_acara }}" width="150"
                                        height="150" style="margin: 8px 0;">
                                @endif

                                <p style="margin: 4px 0; text-align:center">{{ $risalah->nama_pemimpin_acara }}</p>
                            </td>



                        </tr>
                    </table>
                </div>
                <div style="clear: both;"></div>
            </div> <!-- /.header2 -->
        </div> <!-- /.collab -->
        </div>
    </main>
</body>

</html>
