<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>FR.APL.02 · {{ $pendaftaran->no_pendaftaran }}</title>
    <style>
        @page { margin: 16mm 14mm; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color: #111; font-size: 10.5px; }
        h1 { font-size: 14px; margin: 0 0 3px; text-align: center; text-transform: uppercase; }
        h2 { font-size: 11.5px; margin: 0 0 14px; text-align: center; font-weight: normal; }
        h3 { font-size: 11px; margin: 14px 0 6px; }
        table { width: 100%; border-collapse: collapse; }
        .meta td { vertical-align: top; padding: 2px 0; width: 50%; }
        .items th, .items td { border: 1px solid #333; padding: 5px 6px; text-align: left; vertical-align: top; }
        .items th { background: #eeeeee; font-size: 9.5px; text-transform: uppercase; }
        .muted { color: #555; }
        .signatures { margin-top: 34px; }
        .signatures td { width: 33%; text-align: center; vertical-align: top; }
        .signature-line { border-top: 1px solid #333; margin: 52px 14px 0; padding-top: 4px; }
    </style>
</head>
<body>
    <h1>Formulir FR.APL.02 · Asesmen Mandiri</h1>
    <h2>{{ $pendaftaran->skema->nama_skema }}</h2>

    <table class="meta">
        <tr>
            <td>
                <div><strong>No. Pendaftaran:</strong> {{ $pendaftaran->no_pendaftaran }}</div>
                <div><strong>Asesi:</strong> {{ $pendaftaran->asesi->user->name ?? '—' }}</div>
                <div><strong>No. Registrasi:</strong> {{ $pendaftaran->asesi->no_registrasi ?? '—' }}</div>
            </td>
            <td>
                <div><strong>Status:</strong> {{ str_replace('_', ' ', $penilaian->status) }}</div>
                <div><strong>Diserahkan:</strong> {{ $penilaian->diserahkan_pada?->translatedFormat('d F Y H:i') ?: '—' }}</div>
                <div><strong>Diverifikasi:</strong> {{ $penilaian->diverifikasi_pada?->translatedFormat('d F Y H:i') ?: '—' }}</div>
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th style="width: 5%;">No.</th>
                <th style="width: 47%;">Unit / Elemen / KUK</th>
                <th style="width: 12%;">Jawaban</th>
                <th style="width: 36%;">Bukti &amp; Verifikasi 4C</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 0; @endphp
            @foreach ($units as $unit)
                @foreach ($unit->elemenKompetensis as $elemen)
                    @foreach ($elemen->kriteriaUnjukKerjas as $kuk)
                        @php
                            $jawaban = $jawabans->get($kuk->id);
                            $no++;
                        @endphp
                        <tr>
                            <td>{{ $no }}</td>
                            <td>
                                <strong>{{ $unit->kode_unit }} · {{ $unit->judul_unit }}</strong><br>
                                <span class="muted">{{ $elemen->nama_elemen }}</span><br>
                                {{ $kuk->pernyataan_kuk }}
                            </td>
                            <td>{{ $jawaban?->jawaban_asesi ? 'Ya, mampu' : 'Belum mampu' }}</td>
                            <td>
                                <div><strong>Asesi:</strong> {{ $jawaban?->bukti_asesi ?: '—' }}</div>
                                <div><strong>Asesor:</strong> {{ $jawaban?->bukti_asesor ?: '—' }}</div>
                                <div class="muted">
                                    4C:
                                    @if ($jawaban)
                                        {{ $jawaban->valid ? 'Valid' : '—' }} ·
                                        {{ $jawaban->autentik ? 'Autentik' : '—' }} ·
                                        {{ $jawaban->terkini ? 'Terkini' : '—' }} ·
                                        {{ $jawaban->memadai ? 'Memadai' : '—' }}
                                    @else
                                        —
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            @endforeach
        </tbody>
    </table>

    <table class="signatures">
        <tr>
            <td>
                <div>Asesi,</div>
                <x-pdf.signature :model="$pendaftaran->asesi?->user" label="Asesi" :height="80" :width="150" :margin="30" />
                <div style="margin-top: 10px;">{{ $pendaftaran->asesi->user->name ?? '—' }}</div>
            </td>
            <td>
                <div>Asesor,</div>
                <x-pdf.signature :model="$penilaian?->asesor?->user" label="Asesor" :height="80" :width="150" :margin="30" />
                <div style="margin-top: 10px;">{{ $penilaian?->asesor?->user?->name ?? '________________' }}</div>
            </td>
            <td>
                <div>Ketua LSP,</div>
                <x-pdf.signature :model="$pendaftaran->institution" label="Ketua LSP" :height="80" :width="150" :margin="30" />
                <div style="margin-top: 10px;">{{ $pendaftaran->institution?->nama_ketua ?? '________________' }}</div>
            </td>
        </tr>
    </table>
</body>
</html>
