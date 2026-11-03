<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>FR.AK.04 · {{ $pendaftaran->no_pendaftaran }}</title>
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
    <h1>FR.AK.04 · Berita Acara Asesmen</h1>
    <h2>{{ $pendaftaran->skema->nama_skema }}</h2>

    @php $a = $pendaftaran->asesi; $u = $a->user; @endphp

    <h3>A. Data Asesmen</h3>
    <table class="meta">
        <tr><td>No. Pendaftaran</td><td>{{ $pendaftaran->no_pendaftaran }}</td></tr>
        <tr><td>Nama Asesi</td><td>{{ $u->name ?? '—' }}</td></tr>
        <tr><td>No. Registrasi</td><td>{{ $a->no_registrasi ?? '—' }}</td></tr>
        <tr><td>Skema</td><td>{{ $pendaftaran->skema->nama_skema }} ({{ $pendaftaran->skema->kode_skema }})</td></tr>
        <tr><td>TUK</td><td>{{ $pendaftaran->jadwal?->tuk?->nama_tuk ?? '—' }}</td></tr>
        <tr><td>Tanggal Asesmen</td><td>{{ $pendaftaran->jadwal?->tanggal?->translatedFormat('d F Y') ?? '—' }}</td></tr>
        <tr><td>Waktu</td><td>{{ $pendaftaran->jadwal?->jam_mulai }} – {{ $pendaftaran->jadwal?->jam_selesai }}</td></tr>
        <tr><td>Asesor</td><td>{{ $pendaftaran->jadwal?->asesors->first()?->asesor?->user?->name ?? '—' }}</td></tr>
    </table>

    <h3>B. Keputusan Asesmen</h3>
    <table class="meta">
        <tr><td>Rekomendasi</td><td>{{ $hasil ? strtoupper(str_replace('_', ' ', $hasil->rekomendasi)) : '—' }}</td></tr>
        <tr><td>Persen Kompeten</td><td>{{ $hasil ? number_format($hasil->persen_kompeten, 2).'%' : '—' }}</td></tr>
        <tr><td>Status</td><td>{{ $hasil ? strtoupper($hasil->status) : '—' }}</td></tr>
        <tr><td>Finalisasi</td><td>{{ $hasil?->finalized_at?->translatedFormat('d F Y H:i') ?: '—' }}</td></tr>
        <tr><td>Umpan Balik Diberikan</td><td>{{ $hasil?->umpan_balik_diberikan ? 'Ya' : 'Tidak' }}</td></tr>
    </table>

    @if ($hasil && $hasil->units->isNotEmpty())
        <h3>C. Keputusan per Unit</h3>
        <table class="items">
            <thead>
                <tr>
                    <th style="width: 12%;">Kode Unit</th>
                    <th style="width: 58%;">Judul Unit</th>
                    <th style="width: 30%;">Keputusan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($hasil->units as $hasilUnit)
                    <tr>
                        <td>{{ $hasilUnit->unit?->kode_unit ?? '—' }}</td>
                        <td>{{ $hasilUnit->unit?->judul_unit ?? '—' }}</td>
                        <td>{{ $hasilUnit->keputusan ? ucwords(str_replace('_', ' ', $hasilUnit->keputusan)) : '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h3>D. Pernyataan dan Tanda Tangan</h3>
    <p class="muted">
        Berita acara asesmen ini dibuat sebagai bukti pelaksanaan asesmen kompetensi sesuai ketentuan BNSP.
    </p>

    <table class="signatures">
        <tr>
            <td>
                @php $asesorUser = $pendaftaran->jadwal?->asesors->first()?->asesor?->user; @endphp
                @if ($asesorUser?->signature_path && file_exists(public_path('storage/' . $asesorUser->signature_path)))
                    <div style="margin: 20px 0;">
                        <img src="{{ public_path('storage/' . $asesorUser->signature_path) }}" 
                             alt="Tanda Tangan Asesor" 
                             style="max-height: 60px; max-width: 120px;">
                    </div>
                @else
                    <div class="signature-line"></div>
                @endif
                <div>Asesor</div>
                <div>{{ $asesorUser?->name ?? '' }}</div>
            </td>
            <td>
                @if ($u->signature_path && file_exists(public_path('storage/' . $u->signature_path)))
                    <div style="margin: 20px 0;">
                        <img src="{{ public_path('storage/' . $u->signature_path) }}" 
                             alt="Tanda Tangan Asesi" 
                             style="max-height: 60px; max-width: 120px;">
                    </div>
                @else
                    <div class="signature-line"></div>
                @endif
                <div>Asesi</div>
                <div>{{ $u->name ?? '' }}</div>
            </td>
            <td>
                <div class="signature-line"></div>
                <div>Tanggal</div>
                <div>{{ now()->translatedFormat('d F Y') }}</div>
            </td>
        </tr>
    </table>
</body>
</html>
