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
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12;
            padding: 0;
            line-height: 1.5;
        }

        .pdf-mode,
        .pdf-mode * {
            font-family: 'DejaVu Sans', sans-serif !important;
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
            margin-bottom: 20px;
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

        .header2 {
            page-break-inside: auto;
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
            vertical-align: top;
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

        .pdf-mode header img,
        .pdf-mode footer img,
        .pdf-mode .content {
            width: 100%;
        }

        .fill .editor-content table {
            border-collapse: collapse !important;
            margin: 10px 0;
        }

        .fill .editor-content td,
        .fill .editor-content th {
            padding: 8px 10px;
            vertical-align: top;
            white-space: normal;
            overflow-wrap: break-word;
            word-break: break-word;
            line-height: 1.5;
        }

        .fill .editor-content table[border="0"] td,
        .fill .editor-content table[border="0"] th {
            border: none;
        }

        .fill .editor-content table:not([border="0"]) td,
        .fill .editor-content table:not([border="0"]) th {
            border: 1px solid #000;
        }

        .fill .editor-content th {
            background-color: #f5f5f5;
            font-weight: bold;
        }

        .fill .editor-content td p,
        .fill .editor-content th p {
            margin: 0;
            line-height: 1.5;
        }

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

        /* =========================
           Tambahan untuk PDF rapi
           ========================= */

        .collab {
            width: 100%;
            page-break-inside: auto;
        }

        .signature-block {
            width: 38%;
            margin-left: auto;
            margin-right: 0;
            margin-top: 24px;
            text-align: center;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .signature-block p {
            margin: 0;
            text-align: center;
        }

        .signature-role {
            margin-top: 5px;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .signature-qr {
            margin: 10px 0;
            text-align: center;
            min-height: 90px;
        }

        .signature-qr img {
            display: inline-block;
        }

        .attachment-block {
            margin-top: 24px;
            text-align: left;
            page-break-inside: auto;
            break-inside: auto;
        }

        .attachment-title {
            font-weight: bold;
            margin-bottom: 6px;
        }

        .attachment-list {
            margin: 0;
            padding-left: 20px;
        }

        .attachment-list li {
            margin: 0 0 3px 0;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .attachment-paragraph {
            margin: 0 0 3px 0;
            text-align: left;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .no-gap {
            margin-top: 12px;
        }

        .header-list {
            margin: 5px 0 0 0;
            padding-left: 20px;
        }

        .header-list li {
            margin: 0 0 2px 0;
        }

        .clearfix {
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
                {{-- Header --}}
                <table class="header">
                    @if ($memo->tgl_dibuat != null)
                        <tr style="vertical-align: top">
                            <td style="padding-right: 16px">Tanggal</td>
                            <td style="padding-right: 8px">:</td>
                            <td>{{ $memo->tgl_dibuat->translatedFormat('d F Y') }}</td>
                        </tr>
                    @endif
                    <tr style="vertical-align: top">
                        <td style="padding-right: 16px">Nomor</td>
                        <td style="padding-right: 8px">:</td>
                        <td>{{ $memo->nomor_memo }}</td>
                    </tr>
                    <tr style="vertical-align: top">
                        <td style="padding-right: 16px">Perihal</td>
                        <td style="padding-right: 8px">:</td>
                        <td><b>{{ $memo->judul }}</b></td>
                    </tr>
                </table>

                <div class="header2">
                    <table>
                        <tr>
                            <th>
                                Dari :
                                @if ($manager)
                                    {{ $manager->unit->name_unit ??
                                        ($manager->section->name_section ??
                                            ($manager->department->name_department ??
                                                ($manager->divisi->nm_divisi ?? ($manager->director->name_director ?? $memo->nama_bertandatangan)))) }}
                                @else
                                    {{ $memo->nama_bertandatangan ?? '-' }}
                                @endif
                            </th>
                            <th>
                                @if ($tujuanTerlampir ?? false)
                                    Kepada : <em>(penerima dan tembusan surat terlampir)</em>
                                @else
                                    Kepada :
                                    @if (!empty($tujuanList ?? []))
                                        <ol class="header-list">
                                            @foreach ($tujuanList as $name)
                                                <li>{{ $name }}</li>
                                            @endforeach
                                        </ol>
                                    @else
                                        <span>-</span>
                                    @endif
                                @endif
                            </th>
                        </tr>
                    </table>
                </div>

                <div class="collab">
                    @php
                        $isiMemo = $memo->isi_memo;

                        if (isset($isPdf) && $isPdf) {
                            $isiMemo = preg_replace_callback(
                                '/<table([^>]*)>(.*?)<\/table>/is',
                                function ($tableMatch) {
                                    $tableAttrs = $tableMatch[1];
                                    $tableContent = $tableMatch[2];
                                    $widths = [];

                                    if (preg_match('/<colgroup>(.*?)<\/colgroup>/is', $tableContent, $colgroupMatch)) {
                                        preg_match_all(
                                            '/<col[^>]*style="[^"]*width:\s*([^;"]+)[^"]*"[^>]*>/i',
                                            $colgroupMatch[1],
                                            $widthMatches,
                                        );
                                        if (!empty($widthMatches[1])) {
                                            $widths = array_map('trim', $widthMatches[1]);
                                        }

                                        $tableContent = preg_replace(
                                            '/<colgroup>.*?<\/colgroup>/is',
                                            '',
                                            $tableContent,
                                        );
                                    }

                                    if (!empty($widths)) {
                                        $tableContent = preg_replace_callback(
                                            '/<tr([^>]*)>(.*?)<\/tr>/is',
                                            function ($rowMatch) use ($widths) {
                                                $rowAttrs = $rowMatch[1];
                                                $rowContent = $rowMatch[2];
                                                $cellIndex = 0;

                                                $rowContent = preg_replace_callback(
                                                    '/<(td|th)([^>]*)>/i',
                                                    function ($cellMatch) use ($widths, &$cellIndex) {
                                                        $tag = $cellMatch[1];
                                                        $attrs = $cellMatch[2];

                                                        $colspan = 1;
                                                        if (
                                                            preg_match(
                                                                '/colspan\s*=\s*["\']?(\d+)["\']?/i',
                                                                $attrs,
                                                                $colspanMatch,
                                                            )
                                                        ) {
                                                            $colspan = (int) $colspanMatch[1];
                                                        }

                                                        if (isset($widths[$cellIndex])) {
                                                            $width = $widths[$cellIndex];

                                                            if (
                                                                preg_match(
                                                                    '/style\s*=\s*"([^"]*)"/i',
                                                                    $attrs,
                                                                    $styleMatch,
                                                                )
                                                            ) {
                                                                $existingStyle = $styleMatch[1];

                                                                if (!preg_match('/width\s*:/i', $existingStyle)) {
                                                                    $newStyle =
                                                                        rtrim($existingStyle, '; ') .
                                                                        '; width: ' .
                                                                        $width .
                                                                        ';';
                                                                    $attrs = preg_replace(
                                                                        '/style\s*=\s*"[^"]*"/i',
                                                                        'style="' . $newStyle . '"',
                                                                        $attrs,
                                                                    );
                                                                }
                                                            } else {
                                                                $attrs .= ' style="width: ' . $width . ';"';
                                                            }
                                                        }

                                                        $cellIndex += $colspan;

                                                        return '<' . $tag . $attrs . '>';
                                                    },
                                                    $rowContent,
                                                );

                                                return '<tr' . $rowAttrs . '>' . $rowContent . '</tr>';
                                            },
                                            $tableContent,
                                        );
                                    }

                                    return '<table' . $tableAttrs . '>' . $tableContent . '</table>';
                                },
                                $isiMemo,
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

                    {{-- Signature dibuat div biasa, bukan table --}}
                    <div class="signature-block">
                        <p><b>Hormat kami,</b></p>

                        @if ($isDirektur)
                            <p class="signature-role">
                                {{ optional($manager->director)->name_director }}
                            </p>
                        @else
                            <p class="signature-role">
                                {{ preg_replace('/^\([A-Z]+\)\s*/', '', $manager->position->nm_position) }}
                                {{ $bagian }}
                            </p>
                        @endif

                        <div class="signature-qr">
                            @if (!empty($memo->qr_approved_by))
                                <img src="data:image/png;base64,{{ $memo->qr_approved_by }}" width="150">
                            @else
                                <br><br><br><br>
                            @endif
                        </div>

                        <p>
                            <b><u>{{ $memo->nama_bertandatangan }}</u></b>
                        </p>
                    </div>


                    @if ($tujuanTerlampir ?? false)
                        <div class="attachment-block">
                            <div class="attachment-title">Kepada :</div>
                            <ol class="attachment-list">
                                @foreach ($tujuanList ?? [] as $name)
                                    <li>{{ $name }}</li>
                                @endforeach
                            </ol>
                        </div>
                    @endif

                    @if (!empty($tembusanList ?? []))
                        <div class="attachment-block {{ $tujuanTerlampir ?? false ? 'no-gap' : '' }}">
                            <div class="attachment-title">Tembusan :</div>
                            <ol class="attachment-list">
                                @foreach ($tembusanList as $tembusan)
                                    <li>{{ $tembusan }}</li>
                                @endforeach
                            </ol>
                        </div>
                    @endif

                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
    </main>
</body>

</html>
