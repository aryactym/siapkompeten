<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>FR.AK.04 · {{ $pendaftaran->no_pendaftaran }}</title>
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
        .signatures { margin-top: 26px; }
        .signatures td { width: 50%; text-align: center; vertical-align: top; }
        .signature-line { border-top: 1px solid #333; margin: 48px 12px 0; padding-top: 4px; }
    </style>
</head>
<body>
    <h1>FR.AK.04 — Banding Asesmen</h1>
    <h2>{{ $skema->nama_skema }} ({{ $skema->kode_skema }})</h2>

    <table class="meta">
        <tr>
            <td>No. Pendaftaran</td>
            <td>{{ $pendaftaran->no_pendaftaran }}</td>
        </tr>
        <tr>
            <td>Nama Asesi</td>
            <td>{{ $asesiUser?->name ?? '—' }}</td>
        </tr>
        <tr>
            <td>Nama Asesor</td>
            <td>{{ $pendaftaran->jadwal?->asesors->first()?->nama_lengkap ?? '—' }}</td>
        </tr>
        <tr>
            <td>TUK</td>
            <td>{{ $pendaftaran->jadwal?->tuk?->nama_tuk ?? '—' }}</td>
        </tr>
        <tr>
            <td>Tanggal Asesmen</td>
            <td>{{ $pendaftaran->jadwal?->tanggal?->translatedFormat('d F Y') ?? '—' }}</td>
        </tr>
        <tr>
            <td>Status Banding</td>
            <td>{{ $banding::STATUS_LABELS[$banding->status] ?? $banding->status }}</td>
        </tr>
        <tr>
            <td>Diajukan Pada</td>
            <td>{{ $banding->created_at?->translatedFormat('d F Y H:i') ?? '—' }}</td>
        </tr>
        <tr>
            <td>Ditinjau Pada</td>
            <td>{{ $banding->ditinjau_pada?->translatedFormat('d F Y H:i') ?? '—' }}</td>
        </tr>
    </table>

    <table class="bordered">
        <thead>
            <tr>
                <th style="width:30%">Item</th>
                <th>Uraian</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Alasan banding (diajukan asesi)</td>
                <td>{{ $banding->alasan }}</td>
            </tr>
            <tr>
                <td>Keputusan peninjau</td>
                <td>{{ $banding->keputusan ?: '—' }}</td>
            </tr>
        </tbody>
    </table>

    <h2 style="text-align:left;font-size:10px;margin:8px 0 4px;">Riwayat Status</h2>
    <table class="bordered">
        <thead>
            <tr>
                <th style="width:20%">Waktu</th>
                <th style="width:20%">Status</th>
                <th>Catatan</th>
                <th style="width:20%">Oleh</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($banding->histories as $history)
                <tr>
                    <td>{{ $history->created_at?->translatedFormat('d F Y H:i') ?? '—' }}</td>
                    <td>{{ $banding::STATUS_LABELS[$history->status] ?? $history->status }}</td>
                    <td>{{ $history->catatan ?: '—' }}</td>
                    <td>{{ $history->pembuat?->name ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="signatures">
        <tr>
            <td>
                <div class="signature-line"></div>
                <div>Asesi</div>
                <div>{{ $asesiUser?->name ?? '—' }}</div>
            </td>
            <td>
                <div class="signature-line"></div>
                <div>Peninjau</div>
                <div>{{ $banding->peninjau?->name ?? '—' }}</div>
            </td>
        </tr>
    </table>
</body>
</html>
