@php
    /** @var \App\Models\Ia04Asesmen $ia04 */
    $check = '✓';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>FR.IA.04 · {{ $pendaftaran->no_pendaftaran }}</title>
    <style>
        @page { margin: 14mm 12mm; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color: #111; font-size: 9.5px; line-height: 1.4; }
        h1 { font-size: 13px; margin: 0 0 2px; text-align: center; text-transform: uppercase; }
        h2 { font-size: 10.5px; margin: 0 0 12px; text-align: center; font-weight: normal; }
        h3 { font-size: 10px; margin: 14px 0 4px; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .meta td { vertical-align: top; padding: 2px 0; }
        .meta td:first-child { width: 30%; font-weight: bold; }
        .bordered th, .bordered td { border: 1px solid #333; padding: 4px 5px; vertical-align: top; }
        .bordered th { background: #eeeeee; font-size: 8.5px; text-transform: uppercase; }
        .box { border: 1px solid #333; padding: 6px 8px; min-height: 34px; margin-bottom: 8px; }
        .box-label { font-weight: bold; font-size: 9px; text-transform: uppercase; margin-bottom: 2px; }
        .center { text-align: center; }
        .muted { color: #555; }
        .signatures { margin-top: 26px; }
        .signatures td { width: 33%; text-align: center; vertical-align: top; }
        .signature-line { border-top: 1px solid #333; margin: 46px 10px 0; padding-top: 4px; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <h1>FR.IA.04.A — Daftar Instruksi Terstruktur</h1>
    <h2>Penjelasan Proyek Singkat / Kegiatan Terstruktur Lainnya</h2>

    <table class="meta">
        <tr><td>Skema Sertifikasi</td><td>{{ $skema->nama_skema }} ({{ $skema->kode_skema }})</td></tr>
        <tr><td>Nomor Skema</td><td>{{ $skema->nomor_skema ?? '—' }}</td></tr>
        <tr><td>TUK</td><td>{{ $pendaftaran->jadwal?->tuk?->nama_tuk ?? '—' }}</td></tr>
        <tr><td>Nama Asesor</td><td>{{ $asesor?->nama_lengkap ?? '—' }}</td></tr>
        <tr><td>Nama Asesi</td><td>{{ $pendaftaran->asesi->user->name ?? '—' }}</td></tr>
        <tr><td>Tanggal</td><td>{{ $pendaftaran->jadwal?->tanggal?->translatedFormat('d F Y') ?? '—' }}</td></tr>
        <tr><td>Judul Kegiatan</td><td>{{ $ia04->judul_kegiatan ?: '—' }}</td></tr>
        <tr><td>Status</td><td>{{ $ia04->isFinal() ? 'FINAL' : 'DRAF' }}</td></tr>
    </table>

    <h3>Kelompok Pekerjaan</h3>
    <table class="bordered">
        <thead>
            <tr>
                <th style="width:6%" class="center">No</th>
                <th style="width:22%">Kode Unit</th>
                <th>Judul Unit</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($units as $unit)
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td>{{ $unit->unitKompetensi?->kode_unit ?? '—' }}</td>
                    <td>{{ $unit->unitKompetensi?->judul_unit ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="center muted">Belum ada unit kompetensi.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="box">
        <div class="box-label">Skenario proyek singkat / kegiatan terstruktur lainnya</div>
        {!! nl2br(e($ia04->skenario ?: '—')) !!}
    </div>

    <div class="box">
        <div class="box-label">Hal yang harus disiapkan / dilakukan / dihasilkan</div>
        {!! nl2br(e($ia04->hal_dipersiapkan ?: '—')) !!}
    </div>
    <table class="meta">
        <tr><td>Waktu persiapan</td><td>{{ $ia04->waktu_persiapan ?: '—' }}</td></tr>
    </table>

    <div class="box">
        <div class="box-label">Hal yang perlu didemonstrasikan / dipresentasikan</div>
        {!! nl2br(e($ia04->hasil_dipresentasikan ?: '—')) !!}
    </div>
    <table class="meta">
        <tr><td>Waktu presentasi</td><td>{{ $ia04->waktu_presentasi ?: '—' }}</td></tr>
    </table>

    <div class="box">
        <div class="box-label">Umpan balik untuk asesi</div>
        {!! nl2br(e($ia04->umpan_balik ?: '—')) !!}
    </div>

    <table class="signatures">
        <tr>
            <td>
                <div class="signature-line"></div>
                <div>Asesi</div>
                <div>{{ $pendaftaran->asesi->user->name ?? '—' }}</div>
            </td>
            <td>
                <div class="signature-line"></div>
                <div>Asesor</div>
                <div>{{ $asesor?->nama_lengkap ?? '—' }}</div>
            </td>
            <td>
                <div class="signature-line"></div>
                <div>Supervisor (jika ada)</div>
                <div>&nbsp;</div>
            </td>
        </tr>
    </table>

    <div class="page-break"></div>

    <h1>FR.IA.04.B — Penilaian Proyek Singkat</h1>
    <h2>atau Kegiatan Terstruktur Lainnya</h2>

    <table class="meta">
        <tr><td>Skema Sertifikasi</td><td>{{ $skema->nama_skema }} ({{ $skema->kode_skema }})</td></tr>
        <tr><td>TUK</td><td>{{ $pendaftaran->jadwal?->tuk?->nama_tuk ?? '—' }}</td></tr>
        <tr><td>Judul Kegiatan Terstruktur</td><td>{{ $ia04->judul_kegiatan ?: '—' }}</td></tr>
        <tr><td>Nama Asesor</td><td>{{ $asesor?->nama_lengkap ?? '—' }}</td></tr>
        <tr><td>Nama Asesi</td><td>{{ $pendaftaran->asesi->user->name ?? '—' }}</td></tr>
        <tr><td>Tanggal</td><td>{{ $pendaftaran->jadwal?->tanggal?->translatedFormat('d F Y') ?? '—' }}</td></tr>
    </table>

    <table class="bordered">
        <thead>
            <tr>
                <th style="width:5%" class="center">No</th>
                <th style="width:22%">Lingkup Penyajian</th>
                <th>Pertanyaan / Tanggapan</th>
                <th style="width:7%" class="center">Ya</th>
                <th style="width:7%" class="center">Tdk</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($ia04->items as $item)
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td>{{ $item->lingkup ?: '—' }}</td>
                    <td>
                        <div><strong>Pertanyaan:</strong> {{ $item->pertanyaan }}</div>
                        <div><strong>Tanggapan:</strong> {{ $item->tanggapan ?: '—' }}</div>
                    </td>
                    <td class="center">{{ $item->pencapaian === true ? $check : '' }}</td>
                    <td class="center">{{ $item->pencapaian === false ? $check : '' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="center muted">Belum ada butir penilaian.</td></tr>
            @endforelse
        </tbody>
    </table>

    <table class="meta">
        <tr>
            <td>Rekomendasi Asesor</td>
            <td>
                Asesi direkomendasikan:
                <strong>{{ \App\Models\Ia04Asesmen::REKOMENDASI_LABELS[$ia04->rekomendasi] ?? '—' }}</strong>
            </td>
        </tr>
    </table>

    <table class="signatures">
        <tr>
            <td>
                <div class="signature-line"></div>
                <div>Asesi</div>
                <div>{{ $pendaftaran->asesi->user->name ?? '—' }}</div>
            </td>
            <td>
                <div class="signature-line"></div>
                <div>Asesor</div>
                <div>{{ $asesor?->nama_lengkap ?? '—' }}</div>
            </td>
            <td>
                <div class="signature-line"></div>
                <div>No. Reg Asesor</div>
                <div>{{ $asesor?->no_register ?? '—' }}</div>
            </td>
        </tr>
    </table>
</body>
</html>
