<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Berita Acara Pleno · {{ $skema->nama_skema }}</title>
    <style>
        @page { margin: 14mm 12mm; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color: #111; font-size: 9.5px; line-height: 1.4; }
        h1 { font-size: 13px; margin: 0 0 2px; text-align: center; text-transform: uppercase; }
        h2 { font-size: 10.5px; margin: 0 0 12px; text-align: center; font-weight: normal; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .meta td { vertical-align: top; padding: 2px 0; }
        .meta td:first-child { width: 30%; font-weight: bold; }
        .bordered th, .bordered td { border: 1px solid #333; padding: 4px 5px; vertical-align: top; }
        .bordered th { background: #eeeeee; font-size: 8.5px; text-transform: uppercase; }
        .center { text-align: center; }
        .muted { color: #555; }
        .status-badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 8.5px; font-weight: bold; }
        .status-draf { background: #fff3cd; color: #856404; }
        .status-final { background: #d4edda; color: #155724; }
        .signatures { margin-top: 26px; }
        .signatures td { width: 50%; text-align: center; vertical-align: top; }
        .signature-line { border-top: 1px solid #333; margin: 48px 12px 0; padding-top: 4px; }
    </style>
</head>
<body>
    <h1>Berita Acara Pleno Asesmen</h1>
    <h2>{{ $skema->nama_skema }} ({{ $skema->kode_skema }})</h2>

    <table class="meta">
        <tr>
            <td>Nomor BA</td>
            <td>{{ $pleno->nomor_ba ?: '—' }}</td>
        </tr>
        <tr>
            <td>Tanggal Pleno</td>
            <td>{{ $pleno->tanggal?->translatedFormat('d F Y') ?? '—' }}</td>
        </tr>
        <tr>
            <td>TUK</td>
            <td>{{ $tuk?->nama_tuk ?? '—' }}</td>
        </tr>
        <tr>
            <td>Asesor</td>
            <td>{{ $asesor?->nama_lengkap ?? '—' }}</td>
        </tr>
        <tr>
            <td>Keputusan Pleno</td>
            <td>{{ $pleno->keputusan === 'disetujui' ? 'Disetujui' : ($pleno->keputusan === 'ditolak' ? 'Ditolak' : '—') }}</td>
        </tr>
        <tr>
            <td>Status</td>
            <td>
                <span class="status-badge {{ $pleno->isFinal() ? 'status-final' : 'status-draf' }}">
                    {{ $pleno->isFinal() ? 'FINAL' : 'DRAF' }}
                </span>
            </td>
        </tr>
    </table>

    <table class="bordered">
        <thead>
            <tr>
                <th style="width:5%" class="center">No</th>
                <th>Nama Asesi</th>
                <th style="width:12%" class="center">Kompeten</th>
                <th style="width:12%" class="center">Belum Kompeten</th>
                <th style="width:30%">Catatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pleno->pesertas as $index => $peserta)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ $peserta->pendaftaran?->asesi?->user?->name ?? '—' }}</td>
                    <td class="center">{{ $peserta->keputusan === 'kompeten' ? '✓' : '' }}</td>
                    <td class="center">{{ $peserta->keputusan === 'belum_kompeten' ? '✓' : '' }}</td>
                    <td>{{ $peserta->catatan ?: '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="meta">
        <tr>
            <td>Catatan Pleno</td>
            <td>{{ $pleno->catatan ?: '—' }}</td>
        </tr>
    </table>

    <table class="signatures">
        <tr>
            <td>
                <div class="signature-line"></div>
                <div>Komite Teknis</div>
            </td>
            <td>
                <div class="signature-line"></div>
                <div>Manajer Sertifikasi</div>
            </td>
        </tr>
    </table>
</body>
</html>
