<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>FR.AK.03 · {{ $pendaftaran->no_pendaftaran }}</title>
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
        .signatures { margin-top: 30px; }
        .signatures td { width: 50%; text-align: center; vertical-align: top; }
        .signature-line { border-top: 1px solid #333; margin: 48px 12px 0; padding-top: 4px; }
    </style>
</head>
<body>
    <h1>FR.AK.03 — Umpan Balik dan Catatan Asesmen</h1>
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
            <td>No. Registrasi</td>
            <td>{{ $pendaftaran->asesi->no_registrasi ?? '—' }}</td>
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
            <td>Tanggal Asesmen</td>
            <td>{{ $pendaftaran->jadwal?->tanggal?->translatedFormat('d F Y') ?? '—' }}</td>
        </tr>
        <tr>
            <td>Dikirim Pada</td>
            <td>{{ $umpanBalik->disubmit_pada?->translatedFormat('d F Y H:i') ?? '—' }}</td>
        </tr>
    </table>

    <table class="bordered">
        <thead>
            <tr>
                <th style="width:5%" class="center">No</th>
                <th>Komponen</th>
                <th style="width:8%" class="center">Ya</th>
                <th style="width:8%" class="center">Tidak</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($komponen as $key => $pernyataan)
                @php
                    $jawaban = $umpanBalik->jawaban($key);
                @endphp
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td>{{ $pernyataan }}</td>
                    <td class="center">{{ $jawaban === \App\Models\UmpanBalikAsesmen::JAWABAN_YA ? '✓' : '' }}</td>
                    <td class="center">{{ $jawaban === \App\Models\UmpanBalikAsesmen::JAWABAN_TIDAK ? '✓' : '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="meta">
        <tr>
            <td>Rekap</td>
            <td>{{ $rekap['ya'] }} Ya · {{ $rekap['tidak'] }} Tidak · {{ $rekap['total'] }} komponen</td>
        </tr>
        <tr>
            <td>Catatan/Komentar Lainnya</td>
            <td>{{ $umpanBalik->catatan ?: '—' }}</td>
        </tr>
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
                <div>Asesor</div>
                <div>{{ $asesor?->nama_lengkap ?? '—' }}</div>
            </td>
        </tr>
    </table>
</body>
</html>
