<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>FR.AK.02 · {{ $pendaftaran->no_pendaftaran }}</title>
    <style>
        @page { margin: 16mm 14mm; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color: #111; font-size: 10.5px; }
        h1 { font-size: 14px; margin: 0 0 3px; text-align: center; text-transform: uppercase; }
        h2 { font-size: 11.5px; margin: 0 0 14px; text-align: center; font-weight: normal; }
        h3 { font-size: 11.5px; margin: 16px 0 6px; }
        table { width: 100%; border-collapse: collapse; }
        .meta td { vertical-align: top; padding: 2px 0; width: 50%; }
        .items th, .items td { border: 1px solid #333; padding: 5px 6px; text-align: left; vertical-align: top; }
        .items th { background: #eeeeee; font-size: 9.5px; text-transform: uppercase; }
        .muted { color: #555; }
        .summary { margin-top: 14px; border: 1px solid #333; padding: 8px 10px; }
        .signatures { margin-top: 34px; }
        .signatures td { width: 33%; text-align: center; vertical-align: top; }
        .signature-line { border-top: 1px solid #333; margin: 52px 14px 0; padding-top: 4px; }
    </style>
</head>
<body>
    <h1>FR.AK.02 — Rekaman Asesmen Kompetensi</h1>
    <h2>{{ $pendaftaran->skema->nama_skema }}</h2>

    <table class="meta">
        <tr>
            <td>
                <div><strong>No. Pendaftaran:</strong> {{ $pendaftaran->no_pendaftaran }}</div>
                <div><strong>Asesi:</strong> {{ $pendaftaran->asesi->user->name ?? '—' }}</div>
                <div><strong>No. Registrasi:</strong> {{ $pendaftaran->asesi->no_registrasi ?? '—' }}</div>
            </td>
            <td>
                <div><strong>Status:</strong> {{ strtoupper($hasil->status) }}</div>
                <div><strong>Asesor:</strong> {{ $hasil->asesor->user->name ?? '—' }}</div>
                <div><strong>Final:</strong> {{ $hasil->finalized_at?->translatedFormat('d F Y H:i') ?: '—' }}</div>
            </td>
        </tr>
    </table>

    @foreach ($units as $unit)
        <h3>{{ $unit->kode_unit }} · {{ $unit->judul_unit }}</h3>

        <table class="items">
            <thead>
                <tr>
                    <th style="width: 30%;">Metode Asesmen</th>
                    <th style="width: 12%;">Digunakan</th>
                    <th style="width: 28%;">Bukti</th>
                    <th style="width: 15%;">Pencapaian</th>
                    <th style="width: 15%;">Keputusan</th>
                </tr>
            </thead>
            <tbody>
                @php $hasilUnit = $hasilUnits->get($unit->id); @endphp
                @if ($hasilUnit?->metodes->isNotEmpty())
                    @foreach ($hasilUnit->metodes as $hasilMetode)
                        <tr>
                            <td>{{ \App\Models\HasilAsesmenMetode::METODE_LABELS[$hasilMetode->metode] ?? ucfirst(str_replace('_', ' ', $hasilMetode->metode)) }}</td>
                            <td>{{ $hasilMetode->digunakan ? 'Ya' : '—' }}</td>
                            <td class="muted">
                                {{ $hasilMetode->bukti_langsung ? 'Langsung' : '' }}
                                {{ $hasilMetode->bukti_tidak_langsung ? '· Tidak langsung' : '' }}
                                {{ $hasilMetode->bukti_tambahan ? '· Tambahan' : '' }}
                            </td>
                            <td>{{ $hasilMetode->pencapaian ? 'Ya' : '—' }}</td>
                            <td>{{ $hasilMetode->keputusan ? ucwords(str_replace('_', ' ', $hasilMetode->keputusan)) : '—' }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="5" class="text-secondary text-center">Tidak ada metode asesmen</td>
                    </tr>
                @endif
                <tr>
                    <td colspan="4"><strong>Keputusan unit</strong></td>
                    <td><strong>{{ $hasilUnit?->keputusan ? ucwords(str_replace('_', ' ', $hasilUnit->keputusan)) : '—' }}</strong></td>
                </tr>
            </tbody>
        </table>

        <table class="items" style="margin-top: 8px;">
            <thead>
                <tr>
                    <th style="width: 5%;">No.</th>
                    <th style="width: 35%;">Elemen Kompetensi</th>
                    <th style="width: 20%;">Metode</th>
                    <th style="width: 12%;">Tercapai</th>
                    <th style="width: 14%;">4C</th>
                </tr>
            </thead>
            <tbody>
                @php $no = 0; @endphp
                @foreach ($unit->elemenKompetensis as $elemen)
                    @php
                        $kukElemen = $elemen->kriteriaUnjukKerjas->pluck('id');
                        $hasilKukElemen = $hasilKuks->filter(fn ($hk) => in_array($hk->kuk_id, $kukElemen->all()));
                        $tercapaiCount = $hasilKukElemen->filter(fn ($k) => $k->tercapai === true)->count();
                        $elemenTercapai = $tercapaiCount === $kukElemen->count() && $kukElemen->count() > 0;
                        $metodeDisplay = !empty($metodeDefault) ? \App\Models\HasilAsesmenMetode::METODE_LABELS[$metodeDefault[0]] ?? ucfirst(str_replace('_', ' ', $metodeDefault[0])) : '—';
                        $no++;
                    @endphp
                    <tr>
                        <td>{{ $no }}</td>
                        <td>{{ $elemen->nama_elemen }}</td>
                        <td class="muted small">{{ $metodeDisplay }}</td>
                        <td>{{ $elemenTercapai ? 'Ya' : 'Tidak' }}</td>
                        <td class="muted">
                            @if ($hasilKukElemen->count() > 0)
                                {{ $hasilKukElemen->first()?->valid ? 'V' : '-' }}{{ $hasilKukElemen->first()?->autentik ? 'A' : '-' }}{{ $hasilKukElemen->first()?->terkini ? 'T' : '-' }}{{ $hasilKukElemen->first()?->memadai ? 'M' : '-' }}
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach

    <div class="summary">
        <div><strong>Persentase KUK tercapai:</strong> {{ number_format($hasil->persen_kompeten, 2, ',', '.') }}%</div>
        <div>
            <strong>Rekomendasi:</strong>
            {{ $hasil->rekomendasi === \App\Models\HasilAsesmen::REKOMENDASI_DIREKOMENDASIKAN
                ? 'DIREKOMENDASIKAN (KOMPETEN)'
                : ($hasil->rekomendasi ? 'TIDAK DIREKOMENDASIKAN' : '—') }}
        </div>
        <div><strong>Umpan balik diberikan:</strong> {{ $hasil->umpan_balik_diberikan ? 'Ya' : 'Belum' }}</div>
    </div>

    <table class="signatures">
        <tr>
            <td>
                <div>Asesi,</div>
                <div class="signature-line">{{ $pendaftaran->asesi->user->name ?? '—' }}</div>
            </td>
            <td>
                <div>Asesor,</div>
                <div class="signature-line">{{ $hasil->asesor->user->name ?? '________________' }}</div>
            </td>
            <td>
                <div>Ketua LSP,</div>
                <div class="signature-line">________________</div>
            </td>
        </tr>
    </table>
</body>
</html>
