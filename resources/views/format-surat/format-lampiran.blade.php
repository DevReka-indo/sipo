<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        @page {
            margin: 0;
        }

        html,
        body {
            height: 100%;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .header {
            height: 28mm;
            width: 100%;
        }

        .footer {
            height: 20mm;
            width: 100%;
            position: fixed;
            bottom: 0;
            left: 0;
        }

        .content {
            box-sizing: border-box;
            padding-left: 15mm;
            padding-right: 15mm;
            padding-top: 6mm;
            /* a bit spacing under header */
            padding-bottom: 6mm;
            /* a bit spacing above footer */
            min-height: calc(297mm - 28mm - 20mm);
            display: flex;
            flex-direction: column;
            align-items: stretch;
            justify-content: flex-start;
        }

        .lampiran-label {
            font-weight: bold;
            font-size: 12pt;
            margin-bottom: 4mm;
        }

        .lampiran-img {
            width: 100%;
            max-width: 100%;
            /* ensure image fits inside content area without overlapping header/footer */
            max-height: calc(297mm - 28mm - 20mm - 12mm);
            object-fit: contain;
            /* show whole image by default */
            display: block;
            margin: 0 auto;
        }
    </style>
</head>

<body>
    @if (!empty($headerImage))
        <div class="header"><img src="{{ $headerImage }}"
                style="height:28mm;width:100%;object-fit:cover;display:block;" /></div>
    @else
        <div class="header"></div>
    @endif

    <div class="content">
        <div class="lampiran-label">Lampiran</div>
        @if (!empty($imageData))
            <img src="{{ $imageData }}" class="lampiran-img" />
        @else
            <div style="color:#666;">(Tidak ada gambar)</div>
        @endif
    </div>

    @if (!empty($footerImage))
        <div class="footer"><img src="{{ $footerImage }}"
                style="height:20mm;width:100%;object-fit:cover;display:block;" /></div>
    @else
        <div class="footer"></div>
    @endif
</body>

</html>
