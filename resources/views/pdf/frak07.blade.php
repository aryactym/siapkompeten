<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>FR.AK.07 · {{ $pendaftaran->no_pendaftaran }}</title>
    <style>
        @page { margin: 14mm 12mm; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color: #111; font-size: 9.5px; }
        h1 { font-size: 13px; margin: 0 0 2px; text-align: center; text-transform: uppercase; }
        h2 { font-size: 10.5px; margin: 0 0 12px; text-align: center; font-weight: normal; }
        table { width: 100%; border-collapse: collapse; }
        .meta td { vertical-align: top; padding: 2px 0; }
        .meta td:first-child { width: 30%; font-weight: bold; }
        .bordered th, .bordered td { border: 1px solid #333; padding: 4px 5px; vertical-align: top; }
        .bordered th { background: #eeeeee; font-size: 8.5px; text-transform: uppercase; }
        .muted { color: #555; }
        .signatures { margin-top: 30px; }
        .signatures td { width: 50%; text-align: center; vertical-align: top; }
        .signature-line { border-top: 1px solid #333; margin: 40px 12px 0; padding-top: 4px; }
        .sm { font-size: 8px; color: #555; }
        .panduan { background: #f8f9fa; border: 1px solid #dee2e6; padding: 6px 8px; font-size: 8.5px; line-height: 1.4; }
    </style>
</head>
<body>
    <h1>FR.AK.07 · Ceklis Penyesuaian yang Wajar dan Beralasan</h1>
    <h2>{{ $skema->nama_skema }} ({{ $skema->nomor_skema ?? '—' }})</h2>

    <table class="meta">
        <tr>
            <td class="fw-semibold" style="width:30%">Skema Sertifikasi (KKNI/Okupasi/Klaster)</td>
            <td>Judul : {{ $data['skema_judul'] ?? $skema->nama_skema ?? '—' }}</td>
        </tr>
        <tr><td class="fw-semibold">Nomor</td><td>{{ $data['skema_nomor'] ?? $skema->nomor_skema ?? '—' }}</td></tr>
        <tr><td class="fw-semibold">TUK</td><td>{{ $data['tuk_jenis'] ?? 'Sewaktu' }}</td></tr>
        <tr><td class="fw-semibold">Nama Asesor</td><td>{{ $data['asesor_nama'] ?? '—' }}</td></tr>
        <tr><td class="fw-semibold">Nama Asesi</td><td>{{ $data['asesi_nama'] ?? $asesiUser->name ?? '—' }}</td></tr>
        <tr><td class="fw-semibold">Tanggal</td><td>{{ $data['tanggal'] ?? $jadwal?->tanggal?->translatedFormat('d F Y') ?? '—' }}</td></tr>
    </table>

    <p class="sm">*Coret yang tidak perlu</p>

    <div class="panduan mb-3">
        <strong>PANDUAN BAGI ASESOR</strong><br>
        Formulir ini dapat digunakan (sebelum pra asesmen / saat pelaksanaan pra asesmen / setelah pra asesmen)* jika ada asesi yang mempunyai keterbatasan sesuai karakteristik yang dimilikinya sehingga diperlukan penyesuaian yang wajar dan beralasan, jika rencana asesmen dan perangkat asesmen tidak sesuai dengan acuan pembanding, potensi asesi dan konteks asesi, jika asesi merasa keletihan, sakit, serta jika kondisi alam, listrik padam, ……..<br>
        Coretlah pada tanda * yang tidak sesuai.<br>
        Berilah tanda √ pada kotak ‘☐’ pada kolom potensi asesi.<br>
        Berilah tanda √ Ya atau Tidak pada tanda ** sesuai pilihan, jika jawaban Ya selanjutnya pada kolom keterangan berilah tanda √ di kotak ‘☐’ yang tersedia, pilihan boleh lebih dari satu.
    </div>

    @php
        $potensi = [
            'Hasil pelatihan dan / atau pendidikan, dimana Kurikulum dan fasilitas praktek mampu telusur terhadap standar kompetensi',
            'Hasil pelatihan dan / atau pendidikan, dimana kurikulum belum berbasis kompetensi.',
            'Pekerja berpengalaman, dimana berasal dari industri/tempat kerja yang dalam operasionalnya mampu telusur dengan standar kompetensi',
            'Pekerja berpengalaman, dimana berasal dari industri/tempat kerja yang dalam operasionalnya belum berbasis kompetensi.',
            'Pelatihan / belajar mandiri atau otodidak.',
        ];
        $rows = [
            1 => ['Keterbatasan asesi terhadap persyaratan bahasa, literasi, numerasi.', [
                'Memerlukan dukungan pembaca, penerjemah, pelayan, penulis. untuk merekam jawaban asesi.',
                'Melakukan asesmen verbal (gunakan pertanyaan lisan/pertanyaan wawancara) dengan dilengkapi gambar diagram dan bentuk-bentuk visual.',
                'Menggunakan Hasil produksi',
                'Menggunakan Ceklis observasi/demonstrasi.',
                'Menggunakan daftar instruksi terstruktur.',
            ]],
            2 => ['Penyediaan dukungan pembaca, penerjemah, pelayan, penulis.', [
                'Menggunakan pertanyaan lisan dengan dilengkapi gambar diagram dan bentuk-bentuk visual.',
                'Menggunakan pertanyaan wawancara dengan dilengkapi gambar diagram dan bentuk-bentuk visual.',
            ]],
            3 => ['Penggunaan teknologi adaptif atau peralatan khusus. (Tidak dapat menggunakan teknologi adaptif (misal: mengoperasikan komputer dan printer, peralatan digital dsb).', [
                'Ceklis observasi/demonstrasi Demonstrasi.',
                'Pertanyaan lisan',
                'Pertanyaan tertulis.',
                'Pertanyaan wawancara.',
                'Daftar instruksi terstruktur.',
                'Ceklis verifikasi portofolio.',
                'Menggunakan dukungan operator komputer.',
            ]],
            4 => ['Pelaksanaan asesmen secara fleksibel karena alasan keletihan atau keperluan pengobatan.', [
                'Menggunakan juru tulis.',
                'Menggunakan kamera perekam video/atau audio.',
                'Memperbolehkan periode waktu yang lebih panjang untuk menyelesaikan tugas pekerjaan dalam asesmen.',
                'Melakukan tugas pekerjaan dalam asesmen dengan waktu lebih pendek.',
                'Menggunakan instruksi-instruksi spesifik pada proyek yang dapat dilakukan pada berbagai tingkatan.',
            ]],
            5 => ['Penyediaan peralatan asesmen berupa braille, audio/video-tape.', [
                'Menggunakan pertanyaan lisan.',
                'Menggunakan pertanyaan wawancara.',
            ]],
            6 => ['Penyesuaian tempat fisik/lingkungan asesmen', [
                'Pertanyaan lisan.',
                'Pertanyaan tulis.',
                'Pertanyaan wawancara.',
                'Ceklis Verifikasi portofolio.',
                'Ceklis reviu produk.',
                'Daftar instruksi terstruktur.',
            ]],
            7 => ['Pertimbangan umur/usia lanjut/gender asesi. (Adanya perbedaan usia dengan asesor yang lebih muda).', [
                'Menggunakan studi kasus/daftar instruksi terstruktur',
                'Menggunakan instrumen asesmen dengan huruf normal jangan terlalu kecil.',
                'Menggunakan asesor dengan jenis kelamin yang sama dengan asesi.',
                'Menggunakan instrumen asesmen yang sama walaupun berbeda jenis kelamin (tidak boleh memberi tanda tambahan pada instrumen asesmen yang digunakan dengan tujuan untuk membedakan jenis kelamin).',
            ]],
            8 => ['Pertimbangan budaya/tradisi/agama.', [
                'Menggunakan studi kasus daftar instruksi terstruktur',
                'Menggunakan asesor tanpa pertimbangan budaya/tradisi/agama.',
                'Menggunakan instrumen asesmen yang sama walaupun berbeda budaya/tradisi/agama).',
            ]],
        ];
        $potensiSelected = $data['potensi'] ?? [];
        $opsiMap = [];
        foreach ($rows as $k => $_) {
            $opsiMap[$k] = $data['p'.$k.'_opsi'] ?? [];
        }
    @endphp

    <table class="bordered mb-3">
        <thead>
            <tr>
                <th style="width:5%" class="text-center">No</th>
                <th>Mengidentifikasi Persyaratan Modifikasi dan Kontekstualisasi** (karakteristik asesi)</th>
                <th class="text-center" style="width:10%">Ya</th>
                <th class="text-center" style="width:12%">Tidak</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($rows as $k => [$q, $opts])
            <tr>
                <td class="text-center fw-semibold">{{ $k }}</td>
                <td>{{ $q }}</td>
                <td class="text-center">{{ ($data['p'.$k.'_perlu'] ?? '') === 'ya' ? '✓' : '☐' }}</td>
                <td class="text-center">{{ ($data['p'.$k.'_perlu'] ?? '') === 'tidak' ? '✓' : '☐' }}</td>
                <td>
                    @php $keterangan = $data['p'.$k.'_lainnya'] ?? ''; @endphp
                    @if ($keterangan !== '')
                        {{ $keterangan }}
                    @elseif (! empty($opts))
                        <span class="sm">{{ implode('; ', $opts) }}</span>
                    @else
                        —
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <table class="meta mb-3">
        <tr>
            <td colspan="2"><b>Hasil Penyesuaian yang wajar dan beralasan disepakati menggunakan :</b></td>
        </tr>
        <tr><td class="fw-semibold">Acuan Pembanding Asesmen</td><td>{{ $data['acuan'] ?? '—' }}</td></tr>
        <tr><td class="fw-semibold">Metode Asesmen</td><td>{{ $data['metode'] ?? '—' }}</td></tr>
        <tr><td class="fw-semibold">Instrumen Asesmen</td><td>{{ $data['instrumen'] ?? '—' }}</td></tr>
    </table>

    <table class="signatures mt-4">
        <tr>
            <td>
                <div class="signature-line"></div>
                <div>Nama Asesor</div>
                <div>{{ $data['asesor_nama2'] ?? '—' }}</div>
            </td>
            <td>
                <div class="signature-line"></div>
                <div>Nama Asesi</div>
                <div>{{ $data['asesi_nama2'] ?? $asesiUser->name ?? '—' }}</div>
            </td>
        </tr>
    </table>

    <p class="sm mt-3">* Coret yang tidak perlu</p>

    @if ($persetujuan?->disetujui_pada)
        <p class="muted mt-3">Disetujui pada {{ $persetujuan->disetujui_pada->translatedFormat('d F Y H:i') }}.</p>
    @endif
</body>
</html>
