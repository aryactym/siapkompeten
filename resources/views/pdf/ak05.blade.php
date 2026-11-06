<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>FR.AK.05 · {{ $skema->nama_skema }}</title>
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
    <h1>FR.AK.05 — Laporan Asesmen</h1>
    <h2>{{ $skema->nama_skema }} ({{ $skema->kode_skema }})</h2>

    <table class="meta">
        <tr>
            <td>TUK</td>
            <td>{{ $tuk?->nama_tuk ?? '—' }}</td>
        </tr>
        <tr>
            <td>Nama Asesor</td>
            <td>{{ $asesor?->nama_lengkap ?? '—' }}</td>
        </tr>
        <tr>
            <td>No. Register Asesor</td>
            <td>{{ $asesor?->no_register ?? '—' }}</td>
        </tr>
        <tr>
            <td>Tanggal</td>
            <td>{{ $jadwal->tanggal?->translatedFormat('d F Y') ?? '—' }}</td>
        </tr>
        <tr>
            <td>Status Laporan</td>
            <td>
                <span class="status-badge {{ $laporan->isFinal() ? 'status-final' : 'status-draf' }}">
                    {{ $laporan->isFinal() ? 'FINAL' : 'DRAF' }}
                </span>
            </td>
        </tr>
    </table>

    <table class="bordered">
        <thead>
            <tr>
                <th style="width:5%" class="center">No</th>
                <th>Nama Asesi</th>
                <th style="width:6%" class="center">K</th>
                <th style="width:6%" class="center">BK</th>
                <th style="width:38%">Keterangan (unit BK)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rekap as $baris)
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td>{{ $baris['nama'] }}</td>
                    <td class="center">{{ $baris['rekomendasi'] === 'K' ? '✓' : '' }}</td>
                    <td class="center">{{ $baris['rekomendasi'] === 'BK' ? '✓' : '' }}</td>
                    <td>{{ $baris['keterangan'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="center muted">Belum ada asesi.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h3>Aspek Negatif dan Positif dalam Asesmen</h3>
    <table class="meta">
        <tr>
            <td>Aspek Positif</td>
            <td>{{ $laporan->aspek_positif ?: '—' }}</td>
        </tr>
        <tr>
            <td>Aspek Negatif</td>
            <td>{{ $laporan->aspek_negatif ?: '—' }}</td>
        </tr>
        <tr>
            <td>Pencatatan Penolakan Hasil Asesmen</td>
            <td>{{ $laporan->pencatatan_penolakan ?: '—' }}</td>
        </tr>
        <tr>
            <td>Saran Perbaikan</td>
            <td>{{ $laporan->saran_perbaikan ?: '—' }}</td>
        </tr>
        <tr>
            <td>Catatan</td>
            <td>{{ $laporan->catatan ?: '—' }}</td>
        </tr>
    </table>

    <table class="signatures">
        <tr>
            <td>
                <div class="signature-line"></div>
                <div>Asesor</div>
                <div>{{ $asesor?->nama_lengkap ?? '—' }}</div>
                <div class="muted small">{{ $asesor?->no_register ?? '' }}</div>
            </td>
            <td>
                <div class="signature-line"></div>
                <div>Tanggal</div>
                <div>{{ $laporan->finalized_at?->translatedFormat('d F Y') ?? '—' }}</div>
            </td>
        </tr>
    </table>
</body>
</html>
