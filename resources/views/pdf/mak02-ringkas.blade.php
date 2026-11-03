<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>FR.AK.02 Ringkas · {{ $pendaftaran->no_pendaftaran }}</title>
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
    <table style="margin-bottom: 16px;">
        <tr>
            <td style="width: 20%; text-align: center;">
                @if ($pendaftaran->institution?->logo_bnsp_path)
                    <img src="{{ public_path('storage/' . $pendaftaran->institution->logo_bnsp_path) }}" 
                         alt="Logo BNSP" 
                         style="max-height: 60px;">
                @endif
            </td>
            <td style="width: 60%; text-align: center;">
                <h1>FR.AK.02 — Rekaman Asesmen Kompetensi</h1>
                <h2>{{ $pendaftaran->skema->nama_skema }}</h2>
            </td>
            <td style="width: 20%; text-align: center;">
                @if ($pendaftaran->institution?->logo_lsp_path)
                    <img src="{{ public_path('storage/' . $pendaftaran->institution->logo_lsp_path) }}" 
                         alt="Logo LSP" 
                         style="max-height: 60px;">
                @endif
            </td>
        </tr>
    </table>

    <table class="meta">
        <tr>
            <td>
                <div><strong>No. Pendaftaran:</strong> {{ $pendaftaran->no_pendaftaran }}</div>
                <div><strong>Asesi:</strong> {{ $pendaftaran->asesi->user->name ?? '—' }}</div>
                <div><strong>Nama Skema:</strong> {{ $pendaftaran->skema->nama_skema ?? '—' }}</div>
                <div><strong>Nomor Skema:</strong> {{ $pendaftaran->skema->kode_skema ?? '—' }}</div>
            </td>
            <td>
                <div><strong>Status:</strong> {{ strtoupper($hasil->status) }}</div>
                <div><strong>Asesor:</strong> {{ $hasil->asesor->user->name ?? '—' }}</div>
                <div><strong>Tanggal Asesmen:</strong> {{ $pendaftaran->jadwal?->tanggal?->translatedFormat('d F Y') ?: '—' }}</div>
                <div><strong>Final:</strong> {{ $hasil->finalized_at?->translatedFormat('d F Y H:i') ?: '—' }}</div>
            </td>
        </tr>
    </table>

    {{-- METODE ASESMEN (ditampilkan sekali di atas) --}}
    @if (!empty($metodeDefault))
        <div style="margin: 14px 0; padding: 8px; border: 1px solid #ccc; background: #f9f9f9;">
            <strong style="font-size: 9px; text-transform: uppercase;">Metode Asesmen:</strong>
            <div style="margin-top: 4px; font-size: 9.5px;">
                {{ collect($metodeDefault)
                    ->map(fn ($m) => \App\Models\HasilAsesmenMetode::METODE_LABELS[$m] ?? ucfirst(str_replace('_', ' ', $m)))
                    ->join(', ') }}
            </div>
        </div>
    @endif

    {{-- UNIT KOMPETENSI (ringkas, tanpa detail elemen) --}}
    <table class="items">
        <thead>
            <tr>
                <th style="width: 10%;">No.</th>
                <th style="width: 10%;">Kode Unit</th>
                <th style="width: 50%;">Unit Kompetensi</th>
                <th style="width: 15%;">KUK Dinilai</th>
                <th style="width: 15%;">Keputusan</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 0; @endphp
            @foreach ($units as $unit)
                @php
                    $kukUnit = $unit->elemenKompetensis
                        ->flatMap(fn ($e) => $e->kriteriaUnjukKerjas)
                        ->pluck('id');
                    $kukUnitDinilai = $hasilKuks->filter(fn ($k) => in_array($k->kuk_id, $kukUnit->all()))
                        ->filter(fn ($k) => $k->tercapai !== null)->count();
                    $hasilUnit = $hasilUnits->get($unit->id);
                    $no++;
                @endphp
                <tr>
                    <td>{{ $no }}</td>
                    <td class="muted">{{ $unit->kode_unit }}</td>
                    <td>{{ $unit->judul_unit }}</td>
                    <td>{{ $kukUnitDinilai }} / {{ $kukUnit->count() }}</td>
                    <td>{{ $hasilUnit?->keputusan ? ucwords(str_replace('_', ' ', $hasilUnit->keputusan)) : '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <div><strong>Persentase KUK tercapai:</strong> {{ number_format($hasil->persen_kompeten, 2, ',', '.') }}%</div>
        <div>
            <strong>Rekomendasi:</strong>
            {{ $hasil->rekomendasi === \App\Models\HasilAsesmen::REKOMENDASI_DIREKOMENDASIKAN
                ? 'Kompeten'
                : 'Belum Kompeten' }}
        </div>
        <div><strong>Umpan balik diberikan:</strong> {{ $hasil->umpan_balik_diberikan ? 'Ya' : 'Belum' }}</div>
    </div>

    <table class="signatures" style="height: 150px;">
        <tr style="height: 100%;">
            <td style="vertical-align: bottom; padding-bottom: 10px;">
                <div>Asesi,</div>
                @if ($pendaftaran->asesi->user->signature_path)
                    <div style="margin: 20px 0;">
                        <img src="{{ public_path('storage/' . $pendaftaran->asesi->user->signature_path) }}" 
                             alt="Tanda Tangan Asesi" 
                             style="max-height: 60px; max-width: 150px;">
                    </div>
                @else
                    <div class="signature-line"></div>
                @endif
                <div style="margin-top: 8px; font-size: 9px;">{{ $pendaftaran->asesi->user->name ?? '—' }}</div>
            </td>
            <td style="vertical-align: bottom; padding-bottom: 10px;">
                <div>Asesor,</div>
                @if ($hasil->asesor->user->signature_path)
                    <div style="margin: 20px 0;">
                        <img src="{{ public_path('storage/' . $hasil->asesor->user->signature_path) }}" 
                             alt="Tanda Tangan Asesor" 
                             style="max-height: 60px; max-width: 150px;">
                    </div>
                @else
                    <div class="signature-line"></div>
                @endif
                <div style="margin-top: 8px; font-size: 9px;">{{ $hasil->asesor->user->name ?? '________________' }}</div>
            </td>
            <td style="vertical-align: bottom; padding-bottom: 10px;">
                <div>Ketua LSP,</div>
                @if ($pendaftaran->institution?->tanda_tangan_path)
                    <div style="margin: 20px 0;">
                        <img src="{{ public_path('storage/' . $pendaftaran->institution->tanda_tangan_path) }}" 
                             alt="Tanda Tangan Ketua LSP" 
                             style="max-height: 60px; max-width: 150px;">
                    </div>
                @else
                    <div class="signature-line"></div>
                @endif
                <div style="margin-top: 8px; font-size: 9px;">{{ $pendaftaran->institution?->nama_ketua ?? '________________' }}</div>
            </td>
        </tr>
    </table>
</body>
</html>
