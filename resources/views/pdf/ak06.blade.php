<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>FR.AK.06 · {{ $skema->nama_skema }}</title>
    <style>
        @page { margin: 10mm; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color: #111; font-size: 8.5px; line-height: 1.35; }
        h1 { font-size: 12px; margin: 0 0 2px; text-align: center; text-transform: uppercase; }
        h2 { font-size: 9.5px; margin: 0 0 10px; text-align: center; font-weight: normal; }
        h3 { font-size: 9px; margin: 10px 0 4px; background: #eeeeee; padding: 3px 6px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .meta td { vertical-align: top; padding: 1px 0; }
        .meta td:first-child { width: 22%; font-weight: bold; }
        .bordered th, .bordered td { border: 1px solid #333; padding: 3px 4px; vertical-align: top; }
        .bordered th { background: #eeeeee; font-size: 8px; text-transform: uppercase; text-align: center; }
        .muted { color: #555; }
        .status-badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 8px; font-weight: bold; }
        .status-draf { background: #fff3cd; color: #856404; }
        .status-final { background: #d4edda; color: #155724; }
        .signatures { margin-top: 22px; }
        .signatures td { width: 50%; text-align: center; vertical-align: top; }
        .signature-line { border-top: 1px solid #333; margin: 42px 12px 0; padding-top: 4px; }
    </style>
</head>
<body>
    <h1>FR.AK.06 — Meninjau Proses Asesmen</h1>
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
            <td>Tanggal</td>
            <td>{{ $jadwal->tanggal?->translatedFormat('d F Y') ?? '—' }}</td>
        </tr>
        <tr>
            <td>Status Tinjauan</td>
            <td>
                <span class="status-badge {{ $tinjauan->isFinal() ? 'status-final' : 'status-draf' }}">
                    {{ $tinjauan->isFinal() ? 'FINAL' : 'DRAF' }}
                </span>
            </td>
        </tr>
    </table>

    <h3>Prosedur Asesmen terhadap Prinsip Asesmen</h3>
    <table class="bordered">
        <thead>
            <tr>
                <th style="width:22%">Aspek yang ditinjau</th>
                @foreach ($tinjauan::PRINSIP as $label)
                    <th>{{ $label }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($tinjauan::PROSES as $prosesKey => $prosesLabel)
                <tr>
                    <td><strong>{{ $prosesLabel }}</strong></td>
                    @foreach ($tinjauan::PRINSIP as $prinsipKey => $prinsipLabel)
                        <td>{{ $tinjauan->nilaiProses($prosesKey, $prinsipKey) ?: '—' }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
    <table class="meta">
        <tr>
            <td>Rekomendasi Peningkatan (Prosedur)</td>
            <td>{{ $tinjauan->rekomendasi_proses ?: '—' }}</td>
        </tr>
    </table>

    <h3>Konsistensi Keputusan Asesmen</h3>
    <table class="bordered">
        <thead>
            <tr>
                <th style="width:22%">Aspek yang ditinjau</th>
                @foreach ($tinjauan::DIMENSI as $label)
                    <th>{{ $label }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Bukti dari berbagai asesmen diperiksa untuk konsistensi dimensi kompetensi</strong></td>
                @foreach ($tinjauan::DIMENSI as $dimensiKey => $label)
                    <td>{{ $tinjauan->nilaiDimensi($dimensiKey) ?: '—' }}</td>
                @endforeach
            </tr>
        </tbody>
    </table>
    <table class="meta">
        <tr>
            <td>Rekomendasi Peningkatan (Dimensi)</td>
            <td>{{ $tinjauan->rekomendasi_dimensi ?: '—' }}</td>
        </tr>
        <tr>
            <td>Komentar</td>
            <td>{{ $tinjauan->komentar ?: '—' }}</td>
        </tr>
    </table>

    <table class="signatures">
        <tr>
            <td>
                <div class="signature-line"></div>
                <div>Lead Asesor/Asesor</div>
                <div>{{ $asesor?->nama_lengkap ?? '—' }}</div>
            </td>
            <td>
                <div class="signature-line"></div>
                <div>Tanggal &amp; Tanda Tangan</div>
                <div>{{ $tinjauan->finalized_at?->translatedFormat('d F Y') ?? '—' }}</div>
            </td>
        </tr>
    </table>
</body>
</html>
