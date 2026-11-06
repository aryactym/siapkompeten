<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Surat Pengantar Blanko BNSP</title>
    <style>
        @page { margin: 18mm 20mm; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color: #111; font-size: 10.5px; line-height: 1.5; }
        h1 { font-size: 13px; margin: 0 0 2px; text-align: center; text-transform: uppercase; }
        h2 { font-size: 11px; margin: 0 0 12px; text-align: center; font-weight: normal; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .meta td { vertical-align: top; padding: 2px 0; }
        .meta td:first-child { width: 22%; font-weight: bold; }
        .bordered th, .bordered td { border: 1px solid #333; padding: 4px 6px; vertical-align: top; }
        .bordered th { background: #eeeeee; font-size: 9px; text-transform: uppercase; }
        .center { text-align: center; }
        .muted { color: #555; }
        .signatures { margin-top: 40px; }
        .signatures td { width: 50%; text-align: center; vertical-align: top; }
        .signature-line { border-top: 1px solid #333; margin: 56px 16px 0; padding-top: 4px; }
    </style>
</head>
<body>
    <h1>Surat Pengantar Blanko Sertifikat Kompetensi</h1>
    <h2>Kepada: Badan Nasional Sertifikasi Profesi (BNSP)</h2>

    <table class="meta">
        <tr>
            <td>Lembaga</td>
            <td>{{ $institution?->name ?? '—' }}</td>
        </tr>
        <tr>
            <td>Nomor Lisensi</td>
            <td>{{ $institution?->nomor_lisensi ?? '—' }}</td>
        </tr>
        <tr>
            <td>Periode</td>
            <td>{{ \Carbon\Carbon::parse($dari)->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($sampai)->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td>Tanggal Surat</td>
            <td>{{ now()->translatedFormat('d F Y') }}</td>
        </tr>
    </table>

    <p>
        Dengan hormat, bersama ini kami sampaikan rekap pelaksanaan asesmen kompetensi dan penerbitan sertifikat
        pada periode di atas, sebagai bahan laporan ke BNSP.
    </p>

    <table class="bordered">
        <thead>
            <tr>
                <th style="width:5%" class="center">No</th>
                <th>Skema Sertifikasi</th>
                <th class="center" style="width:12%">Asesi</th>
                <th class="center" style="width:14%">Kompeten</th>
                <th class="center" style="width:16%">Sertifikat Terbit</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rekap as $index => $baris)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>
                        {{ $baris['skema']->nama_skema }}
                        <div class="muted small">{{ $baris['skema']->kode_skema }}</div>
                    </td>
                    <td class="center">{{ $baris['asesi'] }}</td>
                    <td class="center">{{ $baris['kompeten'] }}</td>
                    <td class="center">{{ $baris['sertifikat'] }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="2" class="fw-bold">Total</td>
                <td class="center fw-bold">{{ collect($rekap)->sum('asesi') }}</td>
                <td class="center fw-bold">{{ collect($rekap)->sum('kompeten') }}</td>
                <td class="center fw-bold">{{ collect($rekap)->sum('sertifikat') }}</td>
            </tr>
        </tbody>
    </table>

    <p class="muted small">
        Demikian surat pengantar ini kami sampaikan. Atas perhatian dan kerjasamanya, kami ucapkan terima kasih.
    </p>

    <table class="signatures">
        <tr>
            <td>
                <div class="signature-line"></div>
                <div>Manajer Sertifikasi</div>
                <div class="muted small">{{ $institution?->name ?? '' }}</div>
            </td>
            <td>
                <div class="signature-line"></div>
                <div>Komite Teknis</div>
            </td>
        </tr>
    </table>
</body>
</html>
