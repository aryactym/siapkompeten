<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Dokumen Pre-Asesmen · {{ $pendaftaran->no_pendaftaran }}</title>
    <style>
        @page { margin: 14mm 12mm; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color: #111; font-size: 9.5px; line-height: 1.4; }
        h1 { font-size: 14px; margin: 0 0 2px; text-align: center; text-transform: uppercase; }
        h2 { font-size: 11px; margin: 14px 0 6px; text-align: center; font-weight: normal; }
        h3 { font-size: 10.5px; margin: 12px 0 5px; background: #eeeeee; padding: 3px 6px; border-bottom: 1px solid #999; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .meta td { vertical-align: top; padding: 2px 0; }
        .meta td:first-child { width: 30%; font-weight: bold; }
        .bordered th, .bordered td { border: 1px solid #333; padding: 4px 5px; vertical-align: top; }
        .bordered th { background: #eeeeee; font-size: 8.5px; text-transform: uppercase; }
        .muted { color: #555; font-size: 8.5px; }
        .signatures { margin-top: 30px; }
        .signatures td { width: 33%; text-align: center; vertical-align: top; }
        .signature-line { border-top: 1px solid #333; margin: 48px 12px 0; padding-top: 4px; }
        .alert-info { background: #e7f1ff; border: 1px solid #b3d4ff; padding: 8px; border-radius: 4px; }
        .form-check-inline { display: inline-flex; align-items: center; gap: 4px; margin-right: 10px; }
        .checkbox-small { width: 12px; height: 12px; }
        .radio-small { width: 12px; height: 12px; }
        .fw-semibold { font-weight: 600; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <h1>Dokumen Pre-Asesmen</h1>
    <h2>{{ $pendaftaran->skema->nama_skema }} ({{ $pendaftaran->skema->nomor_skema ?? '—' }})</h2>

    <table class="meta">
        <tr>
            <td class="fw-semibold" style="width:30%">No. Pendaftaran</td>
            <td>{{ $pendaftaran->no_pendaftaran }}</td>
        </tr>
        <tr>
            <td class="fw-semibold">Asesi</td>
            <td>{{ $pendaftaran->asesi->user->name ?? '—' }}</td>
        </tr>
        <tr>
            <td class="fw-semibold">Skema Sertifikasi</td>
            <td>{{ $data['nama_skema'] ?? $pendaftaran->skema->nama_skema ?? '—' }}</td>
        </tr>
        <tr>
            <td class="fw-semibold">Tanggal Setuju</td>
            <td>{{ $data['disetujui_tanggal'] ?? $persetujuan?->disetujui_pada?->format('d/m/Y') ?? '—' }}</td>
        </tr>
    </table>

    <!-- ===== AK.07 Penyesuaian ===== -->
    <h3>Form AK.07 — Penyesuaian yang Wajar dan Beralasan</h3>

    <table class="meta">
        <tr><td class="fw-semibold" style="width:30%">Nama Skema</td><td>{{ $data['nama_skema'] ?? $pendaftaran->skema->nama_skema ?? '—' }}</td></tr>
        <tr><td class="fw-semibold">Judul Skema</td><td>{{ $data['judul_skema'] ?? $pendaftaran->skema->nomor_skema ?? '—' }}</td></tr>
        <tr><td class="fw-semibold">Hari / Tanggal</td><td>{{ $data['hari_tanggal'] ?? $jadwal?->tanggal?->translatedFormat('d F Y') ?? '—' }}</td></tr>
        <tr><td class="fw-semibold">Waktu</td><td>{{ $data['waktu'] ?? substr($jadwal?->jam_mulai, 0, 5).'–'.substr($jadwal?->jam_selesai, 0, 5) ?? '—' }}</td></tr>
        <tr><td class="fw-semibold">Asesor</td><td>{{ $data['asesor'] ?? $jadwal?->asesors->first()?->nama_lengkap ?? '—' }}</td></tr>
        <tr><td class="fw-semibold">TUK</td><td>{{ $data['tuk'] ?? $jadwal?->tuk?->nama_tuk ?? '—' }}</td></tr>
        <tr><td class="fw-semibold">Jenis Bukti</td>
            <td>
                @php
                    $bukti = $data['jenis_bukti'] ?? [];
                    $buktiLabels = [
                        'portofolio' => 'Hasil Verifikasi Portofolio',
                        'reviu_produk' => 'Hasil Reviu Produk',
                        'observasi' => 'Hasil Observasi Langsung',
                        'kegiatan_terstruktur' => 'Hasil Kegiatan Terstruktur',
                        'tertulis' => 'Hasil Pertanyaan Tertulis',
                        'lisan' => 'Hasil Pertanyaan Lisan',
                        'wawancara' => 'Hasil Pertanyaan Wawancara',
                    ];
                @endphp
                @if (!empty($bukti))
                    @foreach ($bukti as $b) {{ $buktiLabels[$b] ?? $b }}<br> @endforeach
                @else
                    —
                @endif
            </td>
        </tr>
    </table>

    <p class="muted">
        Formulir ini dapat digunakan jika ada asesi yang mempunyai keterbatasan sehingga diperlukan penyesuaian yang wajar dan beralasan.
    </p>

    <div class="mb-3">
        <label class="fw-semibold small">Potensi Asesi</label>
        <div class="row g-2">
            @php
                $potensi = [
                    'Hasil pelatihan dan / atau pendidikan, dimana Kurikulum dan fasilitas praktek mampu telusur terhadap standar kompetensi',
                    'Hasil pelatihan dan / atau pendidikan, dimana kurikulum belum berbasis kompetensi.',
                    'Pekerja berpengalaman, dimana berasal dari industri/tempat kerja yang dalam operasionalnya mampu telusur dengan standar kompetensi',
                    'Pekerja berpengalaman, dimana berasal dari industri/tempat kerja yang dalam operasionalnya belum berbasis kompetensi.',
                    'Pelatihan / belajar mandiri atau otodidak.',
                ];
                $potensiSelected = array_map(fn ($v) => (string) $v, $data['potensi'] ?? []);
            @endphp
            @foreach ($potensi as $i => $t)
                <div class="col-12">
                    <div class="form-check-inline">
                        <input type="checkbox" class="checkbox-small" disabled {{ in_array((string) ($i + 1), $potensiSelected, true) ? 'checked' : '' }}>
                        <span>{{ $t }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <table class="bordered mb-3">
        <thead>
        <tr>
            <th style="width:6%" class="text-center">No</th>
            <th>Karakteristik Asesi</th>
            <th style="width:22%" class="text-center">Butuh Penyesuaian</th>
            <th style="width:24%">Keterangan</th>
        </tr>
        </thead>
        <tbody>
        @php
            $rows = [
                1 => 'Keterbatasan asesi terhadap persyaratan bahasa, literasi, numerasi.',
                2 => 'Penyediaan dukungan pembaca, penerjemah, pelayan, penulis.',
                3 => 'Penggunaan teknologi adaptif atau peralatan khusus.',
                4 => 'Pelaksanaan asesmen secara fleksibel karena alasan keletihan atau keperluan pengobatan.',
                5 => 'Penyediaan peralatan asesmen berupa braille, audio/video-tape.',
                6 => 'Penyesuaian tempat fisik/lingkungan asesmen',
                7 => 'Pertimbangan umur/usia lanjut/gender asesi.',
                8 => 'Pertimbangan budaya/tradisi/agama.',
            ];
        @endphp
        @foreach ($rows as $k => $q)
            <tr>
                <td class="text-center fw-semibold">{{ $k }}</td>
                <td>{{ $q }}</td>
                <td class="text-center">
                    <div class="form-check-inline">
                        <input type="radio" class="radio-small" name="p{{ $k }}_perlu" value="ya" disabled {{ ($data['p'.$k.'_perlu'] ?? '') === 'ya' ? 'checked' : '' }}>
                        <span>Ya</span>
                    </div>
                    <div class="form-check-inline">
                        <input type="radio" class="radio-small" name="p{{ $k }}_perlu" value="tidak" disabled {{ ($data['p'.$k.'_perlu'] ?? '') === 'tidak' ? 'checked' : '' }}>
                        <span>Tidak</span>
                    </div>
                </td>
                <td>{{ $data['p'.$k.'_lainnya'] ?? '—' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <!-- ===== AK.01 Persetujuan ===== -->
    <h3>Form AK.01 — Persetujuan Asesmen dan Kerahasiaan</h3>

    <table class="meta mb-0">
        <tr><td class="fw-semibold" style="width:30%">Asesi:</td><td>Bahwa saya telah mendapatkan penjelasan terkait hak dan prosedur banding asesmen dari asesor</td></tr>
        <tr><td class="fw-semibold">Asesor:</td><td>Menyatakan tidak akan membuka hasil pekerjaan yang saya peroleh karena penugasan saya sebagai Asesor dalam pekerjaan Asesmen kepada siapapun atau organisasi apapun selain kepada pihak yang berwenang sehubungan dengan kewajiban saya sebagai Asesor yang ditugaskan oleh LSP.</td></tr>
        <tr><td class="fw-semibold">Asesi:</td><td>Saya setuju mengikuti asesmen dengan pemahaman bahwa informasi yang dikumpulkan hanya digunakan untuk pengembangan profesional dan hanya dapat diakses oleh orang tertentu saja.</td></tr>
    </table>

    <!-- ===== Informasi Banding ===== -->
    <h3>Informasi Mengenai Banding Asesmen</h3>
    <div class="alert-info">
        Informasi prosedur banding asesmen telah dijelaskan dan dibaca sebelum asesmen dimulai.
    </div>

    <table class="signatures mt-4">
        <tr>
            <td>
                <div class="signature-line"></div>
                <div>Tanda tangan Asesi</div>
                <div>{{ $data['disetujui_tanggal'] ?? '—' }}</div>
            </td>
            <td>
                <div class="signature-line"></div>
                <div>Tanggal</div>
                <div>{{ $data['disetujui_tanggal'] ?? '—' }}</div>
            </td>
            <td>
                <div class="signature-line"></div>
                <div>Disetujui pada</div>
                <div>{{ $persetujuan?->disetujui_pada?->format('d/m/Y H:i') ?? '—' }}</div>
            </td>
        </tr>
    </table>

    <p class="muted mt-3">* Coret yang tidak perlu</p>
</body>
</html>
