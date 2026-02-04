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
            font-size: 12;
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

        /* View Mode Styles */
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

        /* PDF Mode Styles */
        .pdf-mode header img,
        .pdf-mode footer img,
        .pdf-mode .content {
            width: 100%;
        }

        /* ============================================
           TinyMCE Editor Content - Table Styling
           ============================================ */

        /* Tabel dari TinyMCE - biarkan inline width bekerja */
        .fill .editor-content table {
            border-collapse: collapse !important;
            margin: 10px 0;
            /* JANGAN set width atau table-layout di sini */
        }

        /* Cell styling - HANYA visual, bukan layout */
        .fill .editor-content td,
        .fill .editor-content th {
            padding: 8px 10px;
            vertical-align: top;
            white-space: normal;
            overflow-wrap: break-word;
            word-break: break-word;
            line-height: 1.5;
        }

        /* Border handling - respect user setting */
        .fill .editor-content table[border="0"] td,
        .fill .editor-content table[border="0"] th {
            border: none;
        }

        .fill .editor-content table:not([border="0"]) td,
        .fill .editor-content table:not([border="0"]) th {
            border: 1px solid #000;
        }

        /* Background untuk header cells */
        .fill .editor-content th {
            background-color: #f5f5f5;
            font-weight: bold;
        }

        /* Paragraph dalam cell - no margin */
        .fill .editor-content td p,
        .fill .editor-content th p {
            margin: 0;
            line-height: 1.5;
        }

        /* Watermark Overlay */
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
                    @php
                        $isiMemo = $memo->isi_memo;

                        // Jika mode PDF, convert colgroup ke inline width di td
                        if (isset($isPdf) && $isPdf) {
                            $isiMemo = preg_replace_callback(
                                '/<table([^>]*)>(.*?)<\/table>/is',
                                function($tableMatch) {
                                    $tableAttrs = $tableMatch[1];
                                    $tableContent = $tableMatch[2];
                                    $widths = [];

                                    // Extract width dari colgroup
                                    if (preg_match('/<colgroup>(.*?)<\/colgroup>/is', $tableContent, $colgroupMatch)) {
                                        // Ambil semua width dari <col style="width: ...">
                                        preg_match_all('/<col[^>]*style="[^"]*width:\s*([^;"]+)[^"]*"[^>]*>/i', $colgroupMatch[1], $widthMatches);
                                        if (!empty($widthMatches[1])) {
                                            $widths = array_map('trim', $widthMatches[1]);
                                        }

                                        // Hapus colgroup dari table content
                                        $tableContent = preg_replace('/<colgroup>.*?<\/colgroup>/is', '', $tableContent);
                                    }

                                    // Jika ada width yang di-extract, apply ke setiap row
                                    if (!empty($widths)) {
                                        $tableContent = preg_replace_callback(
                                            '/<tr([^>]*)>(.*?)<\/tr>/is',
                                            function($rowMatch) use ($widths) {
                                                $rowAttrs = $rowMatch[1];
                                                $rowContent = $rowMatch[2];
                                                $cellIndex = 0;

                                                // Apply width ke setiap td/th
                                                $rowContent = preg_replace_callback(
                                                    '/<(td|th)([^>]*)>/i',
                                                    function($cellMatch) use ($widths, &$cellIndex) {
                                                        $tag = $cellMatch[1];
                                                        $attrs = $cellMatch[2];

                                                        // Hitung colspan untuk skip cells
                                                        $colspan = 1;
                                                        if (preg_match('/colspan\s*=\s*["\']?(\d+)["\']?/i', $attrs, $colspanMatch)) {
                                                            $colspan = (int)$colspanMatch[1];
                                                        }

                                                        // Apply width jika ada
                                                        if (isset($widths[$cellIndex])) {
                                                            $width = $widths[$cellIndex];

                                                            // Cek apakah sudah ada style attribute
                                                            if (preg_match('/style\s*=\s*"([^"]*)"/i', $attrs, $styleMatch)) {
                                                                $existingStyle = $styleMatch[1];

                                                                // Cek apakah sudah ada width di style
                                                                if (!preg_match('/width\s*:/i', $existingStyle)) {
                                                                    $newStyle = rtrim($existingStyle, '; ') . '; width: ' . $width . ';';
                                                                    $attrs = preg_replace('/style\s*=\s*"[^"]*"/i', 'style="' . $newStyle . '"', $attrs);
                                                                }
                                                            } else {
                                                                // Tambah style baru
                                                                $attrs .= ' style="width: ' . $width . ';"';
                                                            }
                                                        }

                                                        // Increment index berdasarkan colspan
                                                        $cellIndex += $colspan;

                                                        return '<' . $tag . $attrs . '>';
                                                    },
                                                    $rowContent
                                                );

                                                return '<tr' . $rowAttrs . '>' . $rowContent . '</tr>';
                                            },
                                            $tableContent
                                        );
                                    }

                                    return '<table' . $tableAttrs . '>' . $tableContent . '</table>';
                                },
                                $isiMemo
                            );
                        }
                    @endphp

                    <div class="fill">
                        <div class="editor-content"
                             style="text-align: justify; width: 100%; max-width: 100%; overflow-x: auto; line-height: 1.5;">
                            {!! $isiMemo !!}
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
