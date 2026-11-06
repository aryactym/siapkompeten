<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>FR.IA.10 · {{ $pendaftaran->no_pendaftaran }}</title>
    <style>
        @page { margin: 14mm 12mm; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color: #111; font-size: 10px; line-height: 1.4; }
        h1 { font-size: 13px; margin: 0 0 2px; text-align: center; text-transform: uppercase; }
        h2 { font-size: 10.5px; margin: 0 0 12px; text-align: center; font-weight: normal; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .meta td { vertical-align: top; padding: 2px 0; }
        .meta td:first-child { width: 26%; font-weight: bold; }
        .muted { color: #555; }
        .signatures { margin-top: 30px; }
        .signatures td { width: 50%; text-align: center; vertical-align: top; }
        .signature-line { border-top: 1px solid #333; margin: 48px 12px 0; padding-top: 4px; }
    </style>
</head>
<body>
    <h1>FR.IA.10 — Klarifikasi Bukti Pihak Ketiga</h1>
    <h2>{{ $skema->nama_skema }} ({{ $skema->kode_skema }})</h2>
    <table class="meta">
        <tr><td>Nama Asesi</td><td>{{ $pendaftaran->asesi->user->name ?? '—' }}</td></tr>
        <tr><td>Nama Pihak Ketiga</td><td>{{ $ia10->nama_pihak_ketiga ?: '—' }}</td></tr>
        <tr><td>Jabatan</td><td>{{ $ia10->jabatan ?: '—' }}</td></tr>
        <tr><td>Instansi</td><td>{{ $ia10->instansi ?: '—' }}</td></tr>
        <tr><td>Nama Asesor</td><td>{{ $asesor?->nama_lengkap ?? '—' }}</td></tr>
        <tr><td>TUK</td><td>{{ $pendaftaran->jadwal?->tuk?->nama_tuk ?? '—' }}</td></tr>
        <tr><td>Tanggal</td><td>{{ $pendaftaran->jadwal?->tanggal?->translatedFormat('d F Y') ?? '—' }}</td></tr>
        <tr><td>Status</td><td>{{ $ia10->isFinal() ? 'FINAL' : 'DRAF' }}</td></tr>
    </table>
    <h2 style="text-align:left;font-size:10.5px;margin:8px 0 4px;">Klarifikasi</h2>
    <p>{{ $ia10->klarifikasi ?: '—' }}</p>
    <table class="signatures">
        <tr>
            <td>
                <div class="signature-line"></div>
                <div>Pihak Ketiga</div>
                <div>{{ $ia10->nama_pihak_ketiga ?? '—' }}</div>
            </td>
            <td>
                <div class="signature-line"></div>
                <div>Asesor</div>
                <div>{{ $asesor?->nama_lengkap ?? '—' }}</div>
            </td>
        </tr>
    </table>
</body>
</html>
