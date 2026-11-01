<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>FR.AK.01 · {{ $pendaftaran->no_pendaftaran }}</title>
    <style>
        @page { margin: 14mm 12mm; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color: #111; font-size: 10px; }
        h1 { font-size: 13px; margin: 0 0 2px; text-align: center; text-transform: uppercase; }
        h2 { font-size: 10.5px; margin: 0 0 12px; text-align: center; font-weight: normal; }
        h3 { font-size: 10.5px; margin: 12px 0 4px; background: #eeeeee; padding: 3px 6px; }
        table { width: 100%; border-collapse: collapse; }
        .meta td { vertical-align: top; padding: 2px 0; }
        .meta td:first-child { width: 34%; font-weight: bold; }
        .items th, .items td { border: 1px solid #333; padding: 4px 6px; vertical-align: top; }
        .items th { background: #eeeeee; font-size: 9px; text-transform: uppercase; }
        .muted { color: #555; }
        .signatures { margin-top: 30px; }
        .signatures td { width: 33%; text-align: center; vertical-align: top; }
        .signature-line { border-top: 1px solid #333; margin: 48px 12px 0; padding-top: 4px; }
    </style>
</head>
<body>
    <h1>FR.AK.01 · Checklist Verifikasi Portofolio</h1>
    <h2>{{ $pendaftaran->skema->nama_skema }}</h2>

    @php $a = $pendaftaran->asesi; $u = $a->user; @endphp

    <h3>A. Data Asesi</h3>
    <table class="meta">
        <tr><td>No. Pendaftaran</td><td>{{ $pendaftaran->no_pendaftaran }}</td></tr>
        <tr><td>Nama Asesi</td><td>{{ $u->name ?? '—' }}</td></tr>
        <tr><td>No. Registrasi</td><td>{{ $a->no_registrasi ?? '—' }}</td></tr>
        <tr><td>Skema</td><td>{{ $pendaftaran->skema->nama_skema }} ({{ $pendaftaran->skema->kode_skema }})</td></tr>
        <tr><td>LSP</td><td>{{ $institution->name ?? '—' }}</td></tr>
    </table>

    <h3>B. Checklist Dokumen Persyaratan</h3>
    <table class="items">
        <thead>
            <tr>
                <th style="width: 5%;">No.</th>
                <th style="width: 45%;">Dokumen Persyaratan</th>
                <th style="width: 15%;">Status</th>
                <th style="width: 35%;">Catatan Verifikator</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 0; @endphp
            @foreach ($pendaftaran->skema->persyaratans as $persyaratan)
                @php
                    $dokumen = $pendaftaran->dokumens->firstWhere('persyaratan_id', $persyaratan->id);
                    $no++;
                @endphp
                <tr>
                    <td>{{ $no }}</td>
                    <td>{{ $persyaratan->nama }}</td>
                    <td>{{ $dokumen ? strtoupper(str_replace('_', ' ', $dokumen->status)) : 'BELUM DIUNGGAH' }}</td>
                    <td>{{ $dokumen?->catatan ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h3>C. Pernyataan Verifikator</h3>
    <p class="muted">
        Saya menyatakan bahwa portofolio asesi telah diverifikasi sesuai persyaratan skema dan hasilnya tercatat pada tabel di atas.
    </p>

    <table class="signatures">
        <tr>
            <td>
                <div class="signature-line">Asesor / Verifikator</div>
                <div>{{ auth()->user()->name ?? '' }}</div>
            </td>
            <td>
                <div class="signature-line">Asesi</div>
                <div>{{ $u->name ?? '' }}</div>
            </td>
            <td>
                <div class="signature-line">Tanggal</div>
                <div>{{ now()->translatedFormat('d F Y') }}</div>
            </td>
        </tr>
    </table>
</body>
</html>
