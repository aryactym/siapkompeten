<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>FR.MAPA.01 · {{ $pendaftaran->no_pendaftaran }}</title>
    <style>
        @page { margin: 14mm 12mm; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color: #111; font-size: 10px; line-height: 1.4; }
        h1 { font-size: 13px; margin: 0 0 2px; text-align: center; text-transform: uppercase; }
        h2 { font-size: 10.5px; margin: 0 0 12px; text-align: center; font-weight: normal; }
        h3 { font-size: 10.5px; margin: 12px 0 4px; background: #eeeeee; padding: 3px 6px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .meta td { vertical-align: top; padding: 2px 0; }
        .meta td:first-child { width: 34%; font-weight: bold; }
        .bordered th, .bordered td { border: 1px solid #333; padding: 4px 5px; vertical-align: top; }
        .bordered th { background: #eeeeee; font-size: 9px; text-transform: uppercase; }
        .muted { color: #555; }
        .signatures { margin-top: 30px; }
        .signatures td { width: 33%; text-align: center; vertical-align: top; }
        .signature-line { border-top: 1px solid #333; margin: 48px 12px 0; padding-top: 4px; }
        .status-badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 8.5px; font-weight: bold; }
        .status-draf { background: #fff3cd; color: #856404; }
        .status-final { background: #d4edda; color: #155724; }
    </style>
</head>
<body>
    <h1>FR.MAPA.01 · Rencana Asesmen</h1>
    <h2>{{ $skema->nama_skema }} ({{ $skema->kode_skema }})</h2>

    <table class="meta">
        <tr>
            <td class="fw-semibold" style="width:34%">No. Pendaftaran</td>
            <td>{{ $pendaftaran->no_pendaftaran }}</td>
        </tr>
        <tr>
            <td class="fw-semibold">Asesi</td>
            <td>{{ $pendaftaran->asesi->user->name ?? '—' }}</td>
        </tr>
        <tr>
            <td class="fw-semibold">No. Registrasi</td>
            <td>{{ $pendaftaran->asesi->no_registrasi ?? '—' }}</td>
        </tr>
        <tr>
            <td class="fw-semibold">Jadwal</td>
            <td>{{ $rencana->pendaftaran->jadwal?->tanggal?->translatedFormat('d M Y') ?? '—' }}</td>
        </tr>
        <tr>
            <td class="fw-semibold">Tanggal Rencana</td>
            <td>{{ $rencana->tanggal_asesmen?->format('d M Y') ?? '—' }}</td>
        </tr>
        <tr>
            <td class="fw-semibold">Pendekatan Asesmen</td>
            <td>{{ ucfirst(str_replace('_', ' ', $rencana->pendekatan)) }}</td>
        </tr>
        <tr>
            <td class="fw-semibold">Status Rencana</td>
            <td>
                <span class="status-badge {{ $rencana->isFinal() ? 'status-final' : 'status-draf' }}">
                    {{ $rencana->isFinal() ? 'FINAL' : 'DRAF' }}
                </span>
            </td>
        </tr>
    </table>

    <h3>A. Tujuan Asesmen</h3>
    <p class="muted">{{ $rencana->tujuan ?? '—' }}</p>

    <h3>B. Ruang Lingkup Asesmen</h3>
    <p class="muted">{{ $rencana->ruang_lingkup ?? '—' }}</p>

    @if ($rencana->catatan)
        <h3>C. Catatan</h3>
        <p class="muted">{{ $rencana->catatan }}</p>
    @endif

    <h3>D. Pendampingan Asesor</h3>
    <table class="meta">
        <tr>
            <td class="fw-semibold">Asesor</td>
            <td>{{ $rencana->asesor?->nama_lengkap ?? '—' }}</td>
        </tr>
        <tr>
            <td class="fw-semibold">No. Register</td>
            <td>{{ $rencana->asesor?->no_register ?? '—' }}</td>
        </tr>
    </table>

    <table class="signatures mt-4">
        <tr>
            <td>
                <div class="signature-line"></div>
                <div>Asesor</div>
                <div>{{ $rencana->asesor?->nama_lengkap ?? '' }}</div>
                <div>{{ now()->format('d/m/Y') }}</div>
            </td>
            <td>
                <div class="signature-line"></div>
                <div>Tanggal</div>
                <div>{{ now()->format('d/m/Y') }}</div>
            </td>
            <td>
                <div class="signature-line"></div>
                <div>Keterangan</div>
                <div>—</div>
            </td>
        </tr>
    </table>

    <p class="muted mt-3">Dokumen ini merupakan rencana asesmen (MAPA.01) yang disusun oleh asesor sebelum pelaksanaan asesmen.</p>
</body>
</html>
