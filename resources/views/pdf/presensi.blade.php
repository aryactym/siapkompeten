<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Daftar Hadir Peserta · {{ $jadwal->skema->nama_skema }}</title>
    <style>
        @page { margin: 16mm 14mm; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color: #111; font-size: 11px; }
        h1 { font-size: 15px; margin: 0 0 4px; text-align: center; text-transform: uppercase; }
        h2 { font-size: 12px; margin: 0 0 16px; text-align: center; font-weight: normal; }
        table { width: 100%; border-collapse: collapse; }
        .meta { margin-bottom: 10px; }
        .meta td { vertical-align: top; padding: 2px 0; width: 50%; }
        .muted { color: #555; }
        .participants { margin-top: 12px; }
        .participants th, .participants td { border: 1px solid #333; padding: 6px 8px; text-align: left; vertical-align: top; }
        .participants th { background: #eeeeee; font-size: 10px; text-transform: uppercase; }
        .signatures { margin-top: 36px; }
        .signatures td { width: 33%; text-align: center; vertical-align: top; }
        .signature-line { border-top: 1px solid #333; margin: 54px 16px 0; padding-top: 4px; }
        .footnote { margin-top: 18px; font-size: 9px; color: #555; }
    </style>
</head>
<body>
    <h1>Daftar Hadir Peserta Uji Kompetensi</h1>
    <h2>{{ $jadwal->skema->nama_skema }}</h2>

    <table class="meta">
        <tr>
            <td>
                <div><strong>Event:</strong> {{ $jadwal->event->nama }}</div>
                <div><strong>TUK:</strong> {{ $jadwal->tuk->nama_tuk }}</div>
                <div><strong>Alamat:</strong> {{ $jadwal->tuk->alamat ?: '—' }}</div>
            </td>
            <td>
                <div><strong>Tanggal:</strong> {{ $jadwal->tanggal->translatedFormat('d F Y') }}</div>
                <div><strong>Waktu:</strong> {{ substr($jadwal->jam_mulai, 0, 5) }}–{{ substr($jadwal->jam_selesai, 0, 5) }} WIB</div>
                <div><strong>Jumlah Peserta:</strong> {{ $jadwal->pendaftarans->count() }} orang</div>
            </td>
        </tr>
    </table>

    <div class="muted">
        <strong>Asesor:</strong>
        {{ $jadwal->asesors->map(fn ($asesor) => $asesor->nama_lengkap)->join(', ') ?: 'Belum ditugaskan' }}
    </div>

    <table class="participants">
        <thead>
            <tr>
                <th style="width: 28px;">No</th>
                <th>Nama Peserta</th>
                <th style="width: 110px;">No. Registrasi</th>
                <th style="width: 120px;">Tanda Tangan</th>
                <th style="width: 100px;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($jadwal->pendaftarans as $index => $pendaftaran)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $pendaftaran->asesi->user->name }}</td>
                    <td>{{ $pendaftaran->asesi->no_registrasi }}</td>
                    <td style="height: 30px;"></td>
                    <td></td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px;">Belum ada peserta terplotting.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="signatures">
        <tr>
            <td>
                <div>Asesor Kompetensi</div>
                <div class="signature-line">{{ $jadwal->asesors->first()?->nama_lengkap ?: '.................................' }}</div>
            </td>
            <td>
                <div>Perwakilan TUK</div>
                <div class="signature-line">.................................</div>
            </td>
            <td>
                <div>Panitia LSP</div>
                <div class="signature-line">.................................</div>
            </td>
        </tr>
    </table>

    <p class="footnote">
        Dicetak dari SiapKompeten pada {{ now()->translatedFormat('d F Y H:i') }} WIB.
        Data peserta dihitung real-time dari pendaftaran terplotting.
    </p>
</body>
</html>
