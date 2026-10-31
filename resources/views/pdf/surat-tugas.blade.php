<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Surat Tugas Asesor</title>
    <style>
        @page { margin: 18mm 16mm; }
        body { font-family: 'Times New Roman', DejaVu Serif, serif; color: #111; font-size: 12px; line-height: 1.6; }
        .kop { text-align: center; border-bottom: 3px double #111; padding-bottom: 10px; margin-bottom: 22px; }
        .kop h1 { margin: 0; font-size: 18px; letter-spacing: 1px; }
        .kop h2 { margin: 2px 0 0; font-size: 13px; font-weight: normal; }
        h3 { text-align: center; text-transform: uppercase; text-decoration: underline; margin: 22px 0 14px; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin: 12px 0; }
        td { padding: 3px 6px; vertical-align: top; }
        td.label { width: 170px; }
        .signature { margin-top: 48px; width: 45%; margin-left: 55%; text-align: center; }
        .signature-line { border-top: 1px solid #111; margin-top: 54px; padding-top: 4px; }
    </style>
</head>
<body>
    <div class="kop">
        <h1>{{ \App\Support\TenantContext::institution()?->name ?? config('app.name') }}</h1>
        <h2>Lembaga Sertifikasi Profesi</h2>
        <div>{{ \App\Support\TenantContext::institution()?->alamat }}</div>
    </div>

    <h3>Surat Tugas Asesor Kompetensi</h3>

    <p>Yang bertanda tangan di bawah ini menugaskan:</p>

    <table>
        <tr><td class="label">Nama</td><td>: {{ $penugasan->asesor->nama_lengkap }}</td></tr>
        <tr><td class="label">No. Register Asesor</td><td>: {{ $penugasan->asesor->no_register }}</td></tr>
        <tr><td class="label">Peran</td><td>: Asesor {{ ucfirst($penugasan->peran) }}</td></tr>
    </table>

    <p>Untuk melaksanakan asesmen pada kegiatan berikut:</p>

    <table>
        <tr><td class="label">Event</td><td>: {{ $jadwal->event->nama }}</td></tr>
        <tr><td class="label">Skema Sertifikasi</td><td>: {{ $jadwal->skema->nama_skema }}</td></tr>
        <tr><td class="label">TUK</td><td>: {{ $jadwal->tuk->nama_tuk }}</td></tr>
        <tr><td class="label">Tanggal</td><td>: {{ $jadwal->tanggal->translatedFormat('d F Y') }}</td></tr>
        <tr><td class="label">Waktu</td><td>: {{ substr($jadwal->jam_mulai, 0, 5) }}–{{ substr($jadwal->jam_selesai, 0, 5) }} WIB</td></tr>
        <tr><td class="label">Nomor Surat Tugas</td><td>: {{ $jadwal->no_surat_tugas ?: '—' }}</td></tr>
    </table>

    <p>
        Demikian surat tugas ini dibuat untuk dilaksanakan dengan penuh tanggung jawab dan
        sesuai ketentuan yang berlaku.
    </p>

    <div class="signature">
        <div>{{ $jadwal->tanggal->translatedFormat('d F Y') }}</div>
        <div>Ketua LSP,</div>
        <div class="signature-line">{{ \App\Support\TenantContext::institution()?->nama_ketua ?: '.................................' }}</div>
    </div>
</body>
</html>
