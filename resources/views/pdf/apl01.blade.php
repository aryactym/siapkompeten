<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>APL-01 · {{ $pendaftaran->no_pendaftaran }}</title>
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
        .bordered th, .bordered td { border: 1px solid #333; padding: 4px 6px; vertical-align: top; }
        .bordered th { background: #eeeeee; font-size: 9px; text-transform: uppercase; }
        .muted { color: #555; }
        .signatures { margin-top: 30px; }
        .signatures td { width: 33%; text-align: center; vertical-align: top; }
        .signature-line { border-top: 1px solid #333; margin: 48px 12px 0; padding-top: 4px; }
    </style>
</head>
<body>
    <h1>Formulir APL-01 · Permohonan Sertifikasi Kompetensi</h1>
    <h2>{{ $pendaftaran->skema->nama_skema }} ({{ $pendaftaran->skema->kode_skema }})</h2>

    @php $a = $pendaftaran->asesi; $u = $a->user; @endphp

    <h3>A. Data Pribadi</h3>
    <table class="meta">
        <tr><td>No. Pendaftaran</td><td>{{ $pendaftaran->no_pendaftaran }}</td></tr>
        <tr><td>Nama Lengkap</td><td>{{ $u->name ?? '—' }}</td></tr>
        <tr><td>NIK</td><td>{{ $u->nik ?? '—' }}</td></tr>
        <tr><td>Jenis Kelamin</td><td>{{ $a->jenis_kelamin === 'L' ? 'Laki-laki' : ($a->jenis_kelamin === 'P' ? 'Perempuan' : '—') }}</td></tr>
        <tr><td>Tempat / Tanggal Lahir</td><td>{{ $a->tempat_lahir ?? '—' }}{{ $a->tanggal_lahir ? ', '.$a->tanggal_lahir->translatedFormat('d F Y') : '' }}</td></tr>
        <tr><td>Kebangsaan</td><td>{{ $a->kebangsaan ?? '—' }}</td></tr>
        <tr><td>Alamat Rumah</td><td>{{ $a->alamat_rumah ?? '—' }}</td></tr>
        <tr><td>Kelurahan / Kecamatan</td><td>{{ $a->kelurahan ?? '—' }}</td></tr>
        <tr><td>Kota / Provinsi</td><td>{{ $a->city->name ?? '—' }} / {{ $a->province->name ?? '—' }}</td></tr>
        <tr><td>Kode Pos</td><td>{{ $a->kode_pos ?? '—' }}</td></tr>
        <tr><td>Telepon Rumah</td><td>{{ $a->telp_rumah ?? '—' }}</td></tr>
        <tr><td>Telepon Kantor/HP</td><td>{{ $a->telp_kantor ?? $u->phone ?? '—' }}</td></tr>
        <tr><td>Pendidikan Terakhir</td><td>{{ $a->pendidikan_terakhir ?? '—' }}</td></tr>
    </table>

    <h3>B. Data Pekerjaan Sekarang</h3>
    <table class="meta">
        <tr><td>Nama Instansi/Perusahaan</td><td>{{ $a->pekerjaan_instansi ?? '—' }}</td></tr>
        <tr><td>Jabatan</td><td>{{ $a->jabatan ?? '—' }}</td></tr>
        <tr><td>Alamat Kantor</td><td>{{ $a->alamat_kantor ?? '—' }}</td></tr>
    </table>

    <h3>C. Skema Sertifikasi yang Dimohon</h3>
    <table class="meta">
        <tr><td>Judul Skema</td><td>{{ $pendaftaran->skema->nama_skema }}</td></tr>
        <tr><td>Nomor Skema</td><td>{{ $pendaftaran->skema->nomor_skema ?? '—' }}</td></tr>
        <tr><td>Jenis Skema</td><td>{{ strtoupper($pendaftaran->skema->jenis) }}</td></tr>
        <tr><td>Level KKNI</td><td>{{ $pendaftaran->skema->level_kkni ?? '—' }}</td></tr>
        <tr><td>TUK</td><td>{{ $pendaftaran->jadwal?->tuk?->nama_tuk ?? '—' }}</td></tr>
        <tr><td>Tanggal Uji</td><td>{{ $pendaftaran->jadwal?->tanggal?->translatedFormat('d F Y') ?? '—' }}</td></tr>
    </table>

    <h3>D. Lembaga Sertifikasi</h3>
    <table class="meta">
        <tr><td>Nama LSP</td><td>{{ $institution->name ?? '—' }}</td></tr>
        <tr><td>Nomor Lisensi</td><td>{{ $institution->nomor_lisensi ?? '—' }}</td></tr>
        <tr><td>Alamat</td><td>{{ $institution->alamat ?? '—' }}</td></tr>
    </table>

    <h3>E. Pernyataan dan Tanda Tangan</h3>
    <p class="muted">
        Dengan ini saya menyatakan bahwa data yang saya berikan adalah benar dan saya bersedia mengikuti proses sertifikasi kompetensi sesuai ketentuan yang berlaku.
    </p>

    <table class="signatures">
        <tr>
            <td>
                <div class="signature-line">Asesi</div>
                <div>{{ $u->name ?? '' }}</div>
            </td>
            <td>
                <div class="signature-line">Admin LSP</div>
                <div>{{ auth()->user()->name ?? '' }}</div>
            </td>
            <td>
                <div class="signature-line">Tanggal</div>
                <div>{{ now()->translatedFormat('d F Y') }}</div>
            </td>
        </tr>
    </table>
</body>
</html>
