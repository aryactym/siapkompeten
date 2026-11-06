<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>FR.AK.01 · {{ $pendaftaran->no_pendaftaran }}</title>
    @include('pdf.partials.official-styles')
</head>
<body>
    @php
        $skema = $skema ?? $pendaftaran?->skema;
        $jadwal = $jadwal ?? $pendaftaran?->jadwal;
        $persetujuan ??= null;
        $data ??= [];
        $asesi = $pendaftaran->asesi;
        $asesiUser = $asesiUser ?? $asesi?->user;
        $asesorUser = $jadwal?->asesors->first()?->user;
        $asesiSignature = \App\Support\SignatureImage::for($asesiUser);
        $asesorSignature = \App\Support\SignatureImage::for($asesorUser);
        $jenisTuk = $data['tuk_jenis'] ?? ($jadwal?->tuk?->jenis);
        $bukti = $data['bukti'] ?? [];
        $buktiKeys = array_map('strval', (array) $bukti);
        $isChecked = fn (string $value): string => in_array($value, $buktiKeys, true) ? 'checked' : '';
        $signedAt = $persetujuan?->disetujui_pada?->translatedFormat('d F Y');
        $dayDate = $data['tanggal'] ?? $jadwal?->tanggal?->translatedFormat('d F Y') ?? '—';
        $time = $data['waktu'] ?? ($jadwal ? substr((string) $jadwal->jam_mulai, 0, 5).' – '.substr((string) $jadwal->jam_selesai, 0, 5) : '—');
        $tukName = $data['tuk_nama'] ?? $jadwal?->tuk?->nama_tuk ?? '—';
    @endphp

    <table class="hd">
        <tr><td>FR.AK.01.</td><td>PERSETUJUAN ASESMEN DAN KERAHASIAAN</td></tr>
    </table>
    <div class="gap"></div>

    <table>
        <tr><td>Persetujuan Asesmen ini untuk menjamin bahwa Asesi telah diberi arahan secara rinci tentang perencanaan dan proses asesmen.</td></tr>
    </table>

    <table>
        <colgroup><col style="width:38%"><col style="width:14mm"><col style="width:4mm"><col></colgroup>
        <tr><td rowspan="2">Skema Sertifikasi (KKNI/Okupasi/Klaster)</td><td>Judul</td><td class="sep">:</td><td>{{ $data['skema_judul'] ?? $skema?->nama_skema ?? '—' }}</td></tr>
        <tr><td>Nomor</td><td class="sep">:</td><td>{{ $data['skema_nomor'] ?? $skema?->nomor_skema ?? '—' }}</td></tr>
        <tr><td>TUK</td><td colspan="2" class="sep">:</td><td class="strike">
            <span class="{{ ($jenisTuk === 'sewaktu' || $jenisTuk === 'Sewaktu') ? 'yes' : 'no' }}">Sewaktu</span> /
            <span class="{{ in_array($jenisTuk, ['tempat_kerja', 'Tempat Kerja'], true) ? 'yes' : 'no' }}">Tempat Kerja</span> /
            <span class="{{ in_array($jenisTuk, ['mandiri', 'Mandiri'], true) ? 'yes' : 'no' }}">Mandiri</span>*
        </td></tr>
        <tr><td>Nama Asesor</td><td colspan="2" class="sep">:</td><td>{{ $data['asesor_nama'] ?? $asesorUser?->name ?? '—' }}</td></tr>
        <tr><td>Nama Asesi</td><td colspan="2" class="sep">:</td><td>{{ $data['asesi_nama'] ?? $asesiUser?->name ?? '—' }}</td></tr>
    </table>

    <table>
        <colgroup><col style="width:38%"><col><col></colgroup>
        <tr>
            <td rowspan="4">Bukti yang akan dikumpulkan :</td>
            <td><span class="box">{{ in_array('portofolio', $buktiKeys, true) ? 'X' : '' }}</span> Hasil Verifikasi Portofolio</td>
            <td><span class="box">{{ in_array('reviu_produk', $buktiKeys, true) ? 'X' : '' }}</span> Hasil Reviu Produk</td>
        </tr>
        <tr>
            <td><span class="box">{{ in_array('observasi', $buktiKeys, true) ? 'X' : '' }}</span> Hasil Observasi Langsung</td>
            <td><span class="box">{{ in_array('kegiatan_terstruktur', $buktiKeys, true) ? 'X' : '' }}</span> Hasil Kegiatan Terstruktur</td>
        </tr>
        <tr>
            <td><span class="box">{{ in_array('tertulis', $buktiKeys, true) ? 'X' : '' }}</span> Hasil Pertanyaan Tertulis</td>
            <td><span class="box">{{ in_array('lisan', $buktiKeys, true) ? 'X' : '' }}</span> Hasil Pertanyaan Lisan</td>
        </tr>
        <tr>
            <td><span class="box">{{ in_array('wawancara', $buktiKeys, true) ? 'X' : '' }}</span> Hasil Pertanyaan Wawancara</td>
            <td><span class="box">{{ in_array('lainnya', $buktiKeys, true) ? 'X' : '' }}</span> Lainnya{{ $data['bukti_lainnya_text'] ?? '' ? ' — '.$data['bukti_lainnya_text'] : '' }}</td>
        </tr>
    </table>

    <table>
        <colgroup><col style="width:38%"><col style="width:28mm"><col style="width:4mm"><col></colgroup>
        <tr><td rowspan="3">Pelaksanaan asesmen disepakati pada:</td><td>Hari/ Tanggal</td><td class="sep">:</td><td>{{ $dayDate }}</td></tr>
        <tr><td>Waktu</td><td class="sep">:</td><td>{{ $time }}</td></tr>
        <tr><td>TUK</td><td class="sep">:</td><td>{{ $tukName }}</td></tr>
    </table>

    <table>
        <tr><td><b>Asesi :</b> Bahwa saya telah mendapatkan penjelasan terkait hak dan prosedur banding asesmen dari asesor.</td></tr>
        <tr><td><b>Asesor :</b> Menyatakan tidak akan membuka hasil pekerjaan yang saya peroleh karena penugasan saya sebagai Asesor dalam pekerjaan <i>Asesmen</i> kepada siapapun atau organisasi apapun selain kepada pihak yang berwenang sehubungan dengan kewajiban saya sebagai Asesor yang ditugaskan oleh LSP.</td></tr>
        <tr><td><b>Asesi :</b> Saya setuju mengikuti asesmen dengan pemahaman bahwa informasi yang dikumpulkan hanya digunakan untuk pengembangan profesional dan hanya dapat diakses oleh orang tertentu saja.</td></tr>
    </table>

    <table class="avoid-break" style="margin-bottom: 0">
        <colgroup><col style="width:50%"><col style="width:50%"></colgroup>
        <tr>
            <td class="ttd">
                Tanda tangan Asesor :<br>
                @if ($asesorSignature)
                    <img src="{{ $asesorSignature }}" alt="Tanda Tangan Asesor" style="max-height: 18mm; max-width: 45mm; margin-top: 3mm;">
                @else
                    <div class="sig-missing">Tanda tangan belum tersimpan.</div>
                @endif
                <br><span class="sm">Tanggal : {{ $signedAt ?? '—' }}</span>
            </td>
            <td class="ttd">
                Tanda tangan Asesi :<br>
                @if ($asesiSignature)
                    <img src="{{ $asesiSignature }}" alt="Tanda Tangan Asesi" style="max-height: 18mm; max-width: 45mm; margin-top: 3mm;">
                @else
                    <div class="sig-missing">Tanda tangan belum tersimpan.</div>
                @endif
                <br><span class="sm">Tanggal : {{ $signedAt ?? '—' }}</span>
            </td>
        </tr>
    </table>
    <p class="sm">*) Coret yang tidak perlu</p>

    @if ($persetujuan?->disetujui_pada)
        <p><span class="b">Disetujui pada</span> {{ $persetujuan->disetujui_pada->translatedFormat('d F Y H:i') }}.</p>
    @endif
</body>
</html>