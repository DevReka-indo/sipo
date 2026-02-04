<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan</title>
    <link rel="stylesheet" href="{{ public_path('assets/css/format-cetakLaporan.css') }}">
</head>

<body class="{{ isset($isPdf) && $isPdf ? 'pdf-mode' : 'view-mode' }}">
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
        <div class="container">
            <div class="memo-title">
                LAPORAN MEMO

            </div>
            <div class="letter">
                <div class="row">
                    <div class="col-md-12">
                        <div class="fill">
                            <h3 style="text-align: left;">Laporan Tgl.
                                {{ \Carbon\Carbon::parse($tgl_awal)->format('d-m-Y') }} /
                                {{ \Carbon\Carbon::parse($tgl_akhir)->format('d-m-Y') }}</h3>
                            <table class="header1">
                                <tr style="background-color: #92C5FF99;">
                                    <th>NO</th>
                                    <th>DATA MASUK</th>
                                    <th>DATA DISAHKAN</th>
                                    <th>NAMA DOKUMEN</th>
                                    <th>NO DOKUMEN</th>
                                    <th>PIC</th>
                                </tr>
                                @foreach ($memos as $index => $laporan)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $laporan->tgl_dibuat ? $laporan->tgl_dibuat->format('d-m-Y') : '-' }}
                                        </td>
                                        <td>{{ $laporan->tgl_disahkan ? $laporan->tgl_disahkan->format('d-m-Y') : '-' }}
                                        </td>
                                        <td>{{ $laporan->judul ?? '-' }}</td>
                                        <td>{{ $laporan->nomor_memo ?? '-' }}</td>
                                        <td>
                                            {{-- @if ($laporan->status == 'reject')
                                        <span class="badge bg-danger">Ditolak</span>
                                        @elseif ($laporan->status == 'pending')
                                        <span class="badge bg-info">Diproses</span>
                                        @elseif ($laporan->status == 'correction')
                                        <span class="badge bg-warning">Dikoreksi</span>
                                        @else
                                        <span class="badge bg-success">Diterima</span>
                                        @endif --}}
                                            {{ App\Models\User::where('id', $laporan->pembuat)->first()->fullname ?? '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @if (isset($manager))
                <div>
                    {{-- PENGECEKAN APAKAH DIA DIREKTUR --}}
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
                    <div style="width: 40%; margin-left: auto; text-align: left; line-height: 1; margin-top: 10px;">
                        {{-- MENAMPILKAN POSISI DARI TABEL POSITION --}}
                        @if ($isDirektur)
                            <p style="text-align: center; margin: 0; font-weight: bold;">
                                {{ optional($manager->director)->name_director }}
                            </p>
                        @else
                            {{-- MENAMPILKAN POSISI DARI TABEL POSITION SERTA ASAL UNIT/SECTION/DEPARTMENT/DIVISI --}}
                            <p style="text-align: center; margin: 0; font-weight: bold;">
                                {{ preg_replace('/^\([A-Z]+\)\s*/', '', $manager->position->nm_position) }}
                                {{ $bagian }}
                            </p>
                        @endif
                        <div style="margin: 40px 0;"></div>
                        {{-- NAMA BERTANDA TANGAN --}}
                        <p style="margin: 0; text-align: center;">
                            <b><u>{{ $manager->firstname }}{{ $manager->lastname ? ' ' . $manager->lastname : '' }}</u></b>
                        </p>
                    </div>
                    <div style="clear: both;"></div>

                </div>
            @endif
        </div>
    </main>
</body>

</html>
