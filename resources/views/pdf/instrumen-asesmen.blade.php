<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $instrumen->label() }} · {{ $pendaftaran->no_pendaftaran }}</title>
    <style>
        @page { margin: 14mm 12mm; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color: #111; font-size: 9.5px; line-height: 1.4; }
        h1 { font-size: 13px; margin: 0 0 2px; text-align: center; text-transform: uppercase; }
        h2 { font-size: 10.5px; margin: 0 0 12px; text-align: center; font-weight: normal; }
        h3 { font-size: 10px; margin: 12px 0 4px; background: #eeeeee; padding: 3px 6px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .meta td { vertical-align: top; padding: 2px 0; }
        .meta td:first-child { width: 32%; font-weight: bold; }
        .bordered th, .bordered td { border: 1px solid #333; padding: 4px 5px; vertical-align: top; }
        .bordered th { background: #eeeeee; font-size: 8.5px; text-transform: uppercase; }
        .muted { color: #555; }
        .signatures { margin-top: 30px; }
        .signatures td { width: 50%; text-align: center; vertical-align: top; }
        .signature-line { border-top: 1px solid #333; margin: 48px 12px 0; padding-top: 4px; }
        .status-badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 8.5px; font-weight: bold; }
        .status-draf { background: #fff3cd; color: #856404; }
        .status-final { background: #d4edda; color: #155724; }
        .center { text-align: center; }
    </style>
</head>
<body>
    <h1>{{ $instrumen->label() }}</h1>
    <h2>{{ $skema->nama_skema }} ({{ $skema->kode_skema }})</h2>

    <table class="meta">
        <tr>
            <td>No. Pendaftaran</td>
            <td>{{ $pendaftaran->no_pendaftaran }}</td>
        </tr>
        <tr>
            <td>Nama Asesi</td>
            <td>{{ $pendaftaran->asesi->user->name ?? '—' }}</td>
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
            <td>No. Register Asesor</td>
            <td>{{ $asesor?->no_register ?? '—' }}</td>
        </tr>
        <tr>
            <td>Hari / Tanggal</td>
            <td>{{ $pendaftaran->jadwal?->tanggal?->translatedFormat('d F Y') ?? '—' }}</td>
        </tr>
        <tr>
            <td>TUK</td>
            <td>{{ $pendaftaran->jadwal?->tuk?->nama_tuk ?? '—' }}</td>
        </tr>
        <tr>
            <td>Status Instrumen</td>
            <td>
                <span class="status-badge {{ $instrumen->isFinal() ? 'status-final' : 'status-draf' }}">
                    {{ $instrumen->isFinal() ? 'FINAL' : 'DRAF' }}
                </span>
            </td>
        </tr>
    </table>

    @foreach ($instrumen->items->groupBy('unit_kompetensi_id') as $items)
        <h3>
            {{ $items->first()->unitKompetensi?->kode_unit ?? 'Unit' }}
            — {{ $items->first()->unitKompetensi?->judul_unit ?? 'Unit Kompetensi' }}
        </h3>
        <table class="bordered">
            <thead>
            <tr>
                <th style="width:5%" class="center">No</th>
                <th>Kriteria Unjuk Kerja</th>
                <th style="width:11%" class="center">Tercapai</th>
                <th style="width:11%" class="center">Belum</th>
                <th style="width:26%">Catatan</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($items as $item)
                <tr>
                    <td class="center">{{ $item->urutan }}</td>
                    <td>{{ $item->kuk?->pernyataan_kuk ?? '—' }}</td>
                    <td class="center">{{ $item->isTercapai() ? '✓' : '☐' }}</td>
                    <td class="center">{{ $item->hasil === \App\Models\InstrumenAsesmenItem::HASIL_BELUM_TERCAPAI ? '✓' : '☐' }}</td>
                    <td>{{ $item->catatan ?: '—' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endforeach

    <table class="meta">
        <tr>
            <td>Rekap</td>
            <td>
                {{ $rekap['total'] }} item ·
                {{ $rekap['tercapai'] }} tercapai ·
                {{ $rekap['belum_tercapai'] }} belum tercapai ·
                {{ $rekap['belum_dinilai'] }} belum dinilai
            </td>
        </tr>
        @if ($instrumen->catatan)
            <tr>
                <td>Catatan Umum</td>
                <td>{{ $instrumen->catatan }}</td>
            </tr>
        @endif
    </table>

    <table class="signatures">
        <tr>
            <td>
                <div class="signature-line"></div>
                <div>Asesor</div>
                <div>{{ $asesor?->nama_lengkap ?? '—' }}</div>
            </td>
            <td>
                <div class="signature-line"></div>
                <div>Asesi</div>
                <div>{{ $pendaftaran->asesi->user->name ?? '—' }}</div>
            </td>
        </tr>
    </table>

    @if ($instrumen->finalized_at)
        <p class="muted mt-3">
            Difinalkan pada {{ $instrumen->finalized_at->translatedFormat('d F Y H:i') }}.
        </p>
    @endif
</body>
</html>
