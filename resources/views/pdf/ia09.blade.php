<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>FR.IA.07 · {{ $pendaftaran->no_pendaftaran }}</title>
    <style>
        @page { margin: 14mm 12mm; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color: #111; font-size: 9.5px; line-height: 1.4; }
        h1 { font-size: 13px; margin: 0 0 2px; text-align: center; text-transform: uppercase; }
        h2 { font-size: 10.5px; margin: 0 0 12px; text-align: center; font-weight: normal; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .meta td { vertical-align: top; padding: 2px 0; }
        .meta td:first-child { width: 26%; font-weight: bold; }
        .bordered th, .bordered td { border: 1px solid #333; padding: 4px 5px; vertical-align: top; }
        .bordered th { background: #eeeeee; font-size: 8.5px; text-transform: uppercase; }
        .center { text-align: center; }
        .muted { color: #555; }
        .signatures { margin-top: 24px; }
        .signatures td { width: 50%; text-align: center; vertical-align: top; }
        .signature-line { border-top: 1px solid #333; margin: 48px 12px 0; padding-top: 4px; }
    </style>
</head>
<body>
    <h1>FR.IA.07 — Pertanyaan untuk Mendukung Observasi</h1>
    <h2>{{ $skema->nama_skema }} ({{ $skema->kode_skema }})</h2>

    <table class="meta">
        <tr><td>Nama Asesi</td><td>{{ $pendaftaran->asesi->user->name ?? '—' }}</td></tr>
        <tr><td>Nama Asesor</td><td>{{ $asesor?->nama_lengkap ?? '—' }}</td></tr>
        <tr><td>TUK</td><td>{{ $pendaftaran->jadwal?->tuk?->nama_tuk ?? '—' }}</td></tr>
        <tr><td>Tanggal</td><td>{{ $pendaftaran->jadwal?->tanggal?->translatedFormat('d F Y') ?? '—' }}</td></tr>
        <tr><td>Status</td><td>{{ $ia09->isFinal() ? 'FINAL' : 'DRAF' }}</td></tr>
    </table>

    <table class="bordered">
        <thead>
            <tr>
                <th style="width:5%" class="center">No</th>
                <th>Pertanyaan</th>
                <th style="width:8%" class="center">Ya</th>
                <th style="width:8%" class="center">Tidak</th>
                <th style="width:24%">Tanggapan Asesi</th>
                <th style="width:18%">Catatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($ia09->items as $item)
                <tr>
                    <td class="center">{{ $item->urutan }}</td>
                    <td>{{ $item->pertanyaan }}</td>
                    <td class="center">{{ $item->pencapaian === true ? '✓' : '' }}</td>
                    <td class="center">{{ $item->pencapaian === false ? '✓' : '' }}</td>
                    <td>{{ $item->tanggapan ?: '—' }}</td>
                    <td>{{ $item->catatan ?: '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="meta">
        <tr><td>Umpan balik untuk asesi</td><td>{{ $ia09->umpan_balik ?: '—' }}</td></tr>
    </table>

    <table class="signatures">
        <tr>
            <td>
                <div class="signature-line"></div>
                <div>Asesi</div>
                <div>{{ $pendaftaran->asesi->user->name ?? '—' }}</div>
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
