<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>FR.MAPA.02 · {{ $pendaftaran->no_pendaftaran }}</title>
    <style>
        @page { margin: 14mm 12mm; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color: #111; font-size: 9.5px; line-height: 1.4; }
        h1 { font-size: 13px; margin: 0 0 2px; text-align: center; text-transform: uppercase; }
        h2 { font-size: 10.5px; margin: 0 0 12px; text-align: center; font-weight: normal; }
        h3 { font-size: 10.5px; margin: 12px 0 4px; background: #eeeeee; padding: 3px 6px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .meta td { vertical-align: top; padding: 2px 0; }
        .meta td:first-child { width: 34%; font-weight: bold; }
        .items th, .items td { border: 1px solid #333; padding: 4px 5px; vertical-align: top; font-size: 8.5px; }
        .items th { background: #eeeeee; text-transform: uppercase; }
        .items td.unit-cell { font-weight: bold; background: #f8f9fa; }
        .muted { color: #555; }
        .signatures { margin-top: 30px; }
        .signatures td { width: 33%; text-align: center; vertical-align: top; }
        .signature-line { border-top: 1px solid #333; margin: 48px 12px 0; padding-top: 4px; }
    </style>
</head>
<body>
    <h1>FR.MAPA.02 · Peta Instrumen Asesmen</h1>
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
            <td class="fw-semibold">Tanggal Rencana</td>
            <td>{{ $rencana->tanggal_asesmen?->format('d M Y') ?? '—' }}</td>
        </tr>
    </table>

    <h3>A. Peta Instrumen per Unit Kompetensi</h3>
    <p class="muted">
        Tabel berikut memetakan setiap KUK (Kriteria Unjuk Kerja) ke jenis instrumen, metode asesmen, dan bukti yang akan dikumpulkan.
    </p>

    @php $no = 0; @endphp
    @foreach ($rencana->units as $unit)
        <table class="items">
            <thead>
                <tr>
                    <th style="width:5%">No.</th>
                    <th>Unit Kompetensi</th>
                    <th>KUK</th>
                    <th style="width:18%">Jenis Instrumen</th>
                    <th style="width:18%">Metode</th>
                    <th style="width:20%">Bukti</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="unit-cell" rowspan="{{ $unit->petaInstrumens->isEmpty() ? 1 : $unit->petaInstrumens->count() }}">
                        {{ $unit->unitKompetensi->kode_unit }} · {{ $unit->unitKompetensi->nama_unit }}
                    </td>
                    @if ($unit->petaInstrumens->isEmpty())
                        <td colspan="5" class="text-muted">Belum ada instrumen yang dipetakan untuk unit ini.</td>
                    @else
                        @php $unitNo = 0; @endphp
                        @foreach ($unit->petaInstrumens as $peta)
                            <tr{{ $unitNo > 0 ? '' : '' }}>
                                <td class="text-center">{{ ++$no }}</td>
                                <td>{{ $peta->kuk->kode_kuk ?? $peta->kuk->id }} · {{ $peta->kuk->pernyataan_kuk }}</td>
                                <td>{{ $peta->jenis_instrumen ?? '—' }}</td>
                                <td>{{ $peta->metode ?? '—' }}</td>
                                <td>{{ $peta->bukti ?? '—' }}</td>
                            </tr>
                            @php $unitNo++; @endphp
                        @endforeach
                    @endif
                </tr>
            </tbody>
        </table>
    @endforeach

    @if ($rencana->units->isEmpty())
        <p class="muted">Belum ada unit kompetensi yang dimasukkan dalam rencana asesmen ini.</p>
    @endif

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

    <p class="muted mt-3">Dokumen ini merupakan peta instrumen asesmen (MAPA.02) yang disusun oleh asesor sebelum pelaksanaan asesmen.</p>
</body>
</html>
