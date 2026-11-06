<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>FR.VA · {{ $pendaftaran->no_pendaftaran }}</title>
    <style>
        @page { margin: 14mm 12mm; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color: #111; font-size: 9.5px; line-height: 1.4; }
        h1 { font-size: 13px; margin: 0 0 2px; text-align: center; text-transform: uppercase; }
        h2 { font-size: 10.5px; margin: 0 0 12px; text-align: center; font-weight: normal; }
        h3 { font-size: 10px; margin: 12px 0 4px; background: #eeeeee; padding: 3px 6px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .meta td { vertical-align: top; padding: 2px 0; }
        .meta td:first-child { width: 30%; font-weight: bold; }
        .bordered th, .bordered td { border: 1px solid #333; padding: 4px 5px; vertical-align: top; }
        .bordered th { background: #eeeeee; font-size: 8.5px; text-transform: uppercase; }
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
    <h1>FR.VA — Memberikan Kontribusi dalam Validasi Asesmen</h1>
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
            <td>{{ $asesor?->nama_lengkap ?? '—' }}</td>
        </tr>
        <tr>
            <td>TUK</td>
            <td>{{ $pendaftaran->jadwal?->tuk?->nama_tuk ?? '—' }}</td>
        </tr>
        <tr>
            <td>Status Validasi</td>
            <td>
                <span class="status-badge {{ $validasi->isFinal() ? 'status-final' : 'status-draf' }}">
                    {{ $validasi->isFinal() ? 'FINAL' : 'DRAF' }}
                </span>
                @if ($validasi->keputusan)
                    — {{ \App\Models\ValidasiAsesmen::KEPUTUSAN_LABELS[$validasi->keputusan] ?? $validasi->keputusan }}
                @endif
            </td>
        </tr>
        <tr>
            <td>Difinalkan Pada</td>
            <td>{{ $validasi->finalized_at?->translatedFormat('d F Y H:i') ?? '—' }}</td>
        </tr>
    </table>

    <h3>Aspek Validasi</h3>
    <table class="bordered">
        <tbody>
            <tr>
                <th style="width:22%">Validitas</th>
                <td>{{ $validasi->aspek_validitas ?: '—' }}</td>
            </tr>
            <tr>
                <th>Reliabilitas</th>
                <td>{{ $validasi->aspek_reliabilitas ?: '—' }}</td>
            </tr>
            <tr>
                <th>Keadilan</th>
                <td>{{ $validasi->aspek_keadilan ?: '—' }}</td>
            </tr>
            <tr>
                <th>Kesesuaian</th>
                <td>{{ $validasi->aspek_kesesuaian ?: '—' }}</td>
            </tr>
            <tr>
                <th>Keputusan</th>
                <td>{{ $validasi->keputusan ? (\App\Models\ValidasiAsesmen::KEPUTUSAN_LABELS[$validasi->keputusan] ?? $validasi->keputusan) : '—' }}</td>
            </tr>
            <tr>
                <th>Catatan</th>
                <td>{{ $validasi->catatan ?: '—' }}</td>
            </tr>
        </tbody>
    </table>

    <table class="signatures">
        <tr>
            <td>
                <div class="signature-line"></div>
                <div>Komite Teknis</div>
                <div>{{ $validasi->validator?->name ?? '—' }}</div>
            </td>
            <td>
                <div class="signature-line"></div>
                <div>Tanggal</div>
                <div>{{ $validasi->finalized_at?->translatedFormat('d F Y') ?? '—' }}</div>
            </td>
        </tr>
    </table>
</body>
</html>
