<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>FR.APL.01 · {{ $pendaftaran->no_pendaftaran }}</title>
    @include('pdf.partials.official-styles')
</head>
<body>
    @php
        $asesi = $pendaftaran->asesi;
        $user = $asesi?->user;
        $skema = $pendaftaran->skema;
        $units = $skema?->unitKompetensis ?? collect();
        $docsByRequirement = $pendaftaran->dokumens->keyBy('skema_persyaratan_id');
        $requiredRequirements = $skema?->persyaratans?->where('is_required', true) ?? collect();
        $optionalRequirements = $skema?->persyaratans?->where('is_required', false) ?? collect();
        $status = $pendaftaran->status;
        $accepted = in_array($status, [
            \App\Models\Pendaftaran::STATUS_MENUNGGU_JADWAL,
            \App\Models\Pendaftaran::STATUS_TERJADWAL,
            \App\Models\Pendaftaran::STATUS_ASESMEN_BERJALAN,
            \App\Models\Pendaftaran::STATUS_SELESAI,
        ], true);
        $rejected = in_array($status, [
            \App\Models\Pendaftaran::STATUS_DITOLAK,
            \App\Models\Pendaftaran::STATUS_DOKUMEN_DITOLAK,
        ], true);
        $signature = \App\Support\SignatureImage::for($user);
        $adminSignature = \App\Support\SignatureImage::for($adminLsp);
        $tujuan = $pendaftaran->tujuan_asesmen ?: 'sertifikasi';
    @endphp

    <table class="hd">
        <tr><td>FR.APL.01</td><td>PERMOHONAN SERTIFIKASI KOMPETENSI</td></tr>
    </table>
    <div class="gap"></div>

    <p><span class="b" style="font-size:12pt">Bagian 1 : Rincian Data Pemohon Sertifikasi</span></p>
    <p>Pada bagian ini, cantumkan data pribadi, data pendidikan formal serta data pekerjaan Anda pada saat ini.</p>
    <p class="li">Data Pribadi</p>
    <table>
        <colgroup><col style="width:27.4%"><col style="width:6.3%"><col style="width:66.3%"></colgroup>
        <tr><td>Nama lengkap</td><td class="sep">:</td><td>{{ $user?->name ?? '—' }}</td></tr>
        <tr><td>No. KTP/NIK/Paspor</td><td class="sep">:</td><td>{{ $user?->nik ?? '—' }}</td></tr>
        <tr><td>Tempat / tgl. Lahir</td><td class="sep">:</td><td>{{ $asesi?->tempat_lahir ?? '—' }}{{ $asesi?->tanggal_lahir ? ', '.$asesi->tanggal_lahir->translatedFormat('d F Y') : '' }}</td></tr>
        <tr><td>Jenis kelamin</td><td class="sep">:</td><td class="strike">
            @if ($asesi?->jenis_kelamin === 'L')
                <span class="yes">Laki-laki</span> / <span class="no">Wanita</span>
            @elseif ($asesi?->jenis_kelamin === 'P')
                <span class="no">Laki-laki</span> / <span class="yes">Wanita</span>
            @else
                Laki-laki / Wanita <span class="sm">(* coret yang tidak perlu)</span>
            @endif
        </td></tr>
        <tr><td>Kebangsaan</td><td class="sep">:</td><td>{{ $asesi?->kebangsaan ?? '—' }}</td></tr>
        <tr><td>Alamat rumah</td><td class="sep">:</td><td>{{ $asesi?->alamat_rumah ?? '—' }}</td></tr>
        <tr><td>Kelurahan / Kecamatan</td><td class="sep">:</td><td>{{ $asesi?->kelurahan ?? '—' }}</td></tr>
        <tr><td>Kota / Provinsi / Kode pos</td><td class="sep">:</td><td>{{ $asesi?->city?->name ?? '—' }}{{ $asesi?->province ? ', '.$asesi->province->name : '' }}{{ $asesi?->kode_pos ? ' · '.$asesi->kode_pos : '' }}</td></tr>
        <tr><td>No. Telepon / E-mail</td><td class="sep">:</td><td>{{ $asesi?->telp_rumah ?: $asesi?->telp_kantor ?: '—' }} / {{ $user?->email ?? '—' }}</td></tr>
        <tr><td>HP / E-mail</td><td class="sep">:</td><td>{{ $user?->phone ?? '—' }} / {{ $user?->email ?? '—' }}</td></tr>
        <tr><td>Kualifikasi Pendidikan</td><td class="sep">:</td><td>{{ $asesi?->pendidikan_terakhir ?? '—' }}</td></tr>
    </table>

    <p class="li">Data Pekerjaan Sekarang</p>
    <table>
        <colgroup><col style="width:25.5%"><col style="width:6.5%"><col style="width:68.0%"></colgroup>
        <tr><td>Nama Institusi / Perusahaan</td><td class="sep">:</td><td>{{ $asesi?->pekerjaan_instansi ?? '—' }}</td></tr>
        <tr><td>Jabatan</td><td class="sep">:</td><td>{{ $asesi?->jabatan ?? '—' }}</td></tr>
        <tr><td>Alamat Kantor</td><td class="sep">:</td><td>{{ $asesi?->alamat_kantor ?? '—' }}</td></tr>
        <tr><td>No. Telp / E-mail</td><td class="sep">:</td><td>{{ $asesi?->telp_kantor ?: $user?->phone ?: '—' }} / {{ $user?->email ?? '—' }}</td></tr>
    </table>

    <p><span class="b" style="font-size:12pt">Bagian 2 : Data Sertifikasi</span></p>
    <p>Tuliskan Judul dan Nomor Skema Sertifikasi yang Anda ajukan berikut Daftar Unit Kompetensi sesuai kemasan pada skema sertifikasi.</p>
    <table>
        <colgroup><col style="width:29.4%"><col style="width:10.3%"><col style="width:4.1%"><col style="width:56.2%"></colgroup>
        <tr><td>Skema Sertifikasi (KKNI/Okupasi/Klaster)</td><td>Judul</td><td class="sep">:</td><td>{{ $skema?->nama_skema ?? '—' }}</td></tr>
        <tr><td>Nomor Skema</td><td>Nomor</td><td class="sep">:</td><td>{{ $skema?->nomor_skema ?? '—' }}</td></tr>
        <tr><td colspan="2">Tujuan Asesmen</td><td class="sep">:</td><td>
            <span class="{{ $tujuan === 'sertifikasi' ? 'b' : '' }}"><span class="box">{{ $tujuan === 'sertifikasi' ? 'X' : '' }}</span> Sertifikasi</span><br>
            <span class="{{ $tujuan === 'pkt' ? 'b' : '' }}"><span class="box">{{ $tujuan === 'pkt' ? 'X' : '' }}</span> Pengakuan Kompetensi Terkini (PKT)</span><br>
            <span class="{{ $tujuan === 'rpl' ? 'b' : '' }}"><span class="box">{{ $tujuan === 'rpl' ? 'X' : '' }}</span> Rekognisi Pembelajaran Lampau (RPL)</span><br>
            <span class="{{ $tujuan === 'lainnya' ? 'b' : '' }}"><span class="box">{{ $tujuan === 'lainnya' ? 'X' : '' }}</span> Lainnya{{ $pendaftaran->catatan_tujuan ? ' — '.$pendaftaran->catatan_tujuan : '' }}</span>
        </td></tr>
    </table>

    <p><b>Daftar Unit Kompetensi sesuai kemasan:</b></p>
    <table>
        <colgroup><col style="width:10.8%"><col style="width:22.6%"><col style="width:41.8%"><col style="width:24.8%"></colgroup>
        <thead>
            <tr><th>No.</th><th>Kode Unit</th><th>Judul Unit</th><th>Standar Kompetensi Kerja</th></tr>
        </thead>
        <tbody>
            @forelse ($units as $index => $unit)
                @php
                    $skkni = $unit->skkni;
                    $standard = $unit->standar_kompetensi ?: ($skkni
                        ? 'SKKNI '.($skkni->nomor_penetapan ?: $skkni->kode).' Tahun '.($skkni->tahun ?: '—')
                        : '—');
                @endphp
                <tr><td>{{ $index + 1 }}.</td><td>{{ $unit->kode_unit }}</td><td>{{ $unit->judul_unit }}</td><td>{{ $standard }}</td></tr>
            @empty
                <tr><td colspan="4" class="c">Belum ada unit kompetensi yang dipetakan.</td></tr>
            @endforelse
        </tbody>
    </table>

    <p><span class="b" style="font-size:12pt">Bagian 3 : Bukti Kelengkapan Pemohon</span></p>
    <p class="li">Bukti Persyaratan Dasar Pemohon</p>
    <table>
        <colgroup><col style="width:6.3%"><col style="width:51.6%"><col style="width:14.0%"><col style="width:14.0%"><col style="width:14.0%"></colgroup>
        <thead>
            <tr><th>No.</th><th>Bukti Persyaratan Dasar</th><th colspan="2">Ada</th><th>Tidak Ada</th></tr>
            <tr><th></th><th></th><th>Memenuhi Syarat</th><th>Tidak Memenuhi Syarat</th><th>Keterangan</th></tr>
        </thead>
        <tbody>
            @forelse ($requiredRequirements as $index => $requirement)
                @php
                    $document = $docsByRequirement->get($requirement->id);
                    $approved = $document?->status === \App\Models\PendaftaranDokumen::STATUS_DISETUJUI;
                    $rejectedDocument = $document?->status === \App\Models\PendaftaranDokumen::STATUS_DITOLAK;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}.</td>
                    <td>{{ $requirement->nama_dokumen }}@if ($requirement->keterangan)<br><span class="sm">{{ $requirement->keterangan }}</span>@endif</td>
                    <td class="check"><span class="box">{{ $approved ? 'X' : '' }}</span></td>
                    <td class="check"><span class="box">{{ $rejectedDocument ? 'X' : '' }}</span></td>
                    <td>{{ $document?->catatan ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="c">Belum ada persyaratan dasar.</td></tr>
            @endforelse
        </tbody>
    </table>

    <p class="li">Bukti Administratif</p>
    <table>
        <colgroup><col style="width:6.3%"><col style="width:51.6%"><col style="width:14.0%"><col style="width:14.0%"><col style="width:14.0%"></colgroup>
        <thead>
            <tr><th>No.</th><th>Bukti Administratif</th><th colspan="2">Ada</th><th>Tidak Ada</th></tr>
            <tr><th></th><th></th><th>Memenuhi Syarat</th><th>Tidak Memenuhi Syarat</th><th>Keterangan</th></tr>
        </thead>
        <tbody>
            @forelse ($optionalRequirements as $index => $requirement)
                @php
                    $document = $docsByRequirement->get($requirement->id);
                    $approved = $document?->status === \App\Models\PendaftaranDokumen::STATUS_DISETUJUI;
                    $rejectedDocument = $document?->status === \App\Models\PendaftaranDokumen::STATUS_DITOLAK;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}.</td>
                    <td>{{ $requirement->nama_dokumen }}@if ($requirement->keterangan)<br><span class="sm">{{ $requirement->keterangan }}</span>@endif</td>
                    <td class="check"><span class="box">{{ $approved ? 'X' : '' }}</span></td>
                    <td class="check"><span class="box">{{ $rejectedDocument ? 'X' : '' }}</span></td>
                    <td>{{ $document?->catatan ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="c">Belum ada bukti administratif yang tercatat.</td></tr>
            @endforelse
        </tbody>
    </table>

    <table class="avoid-break" style="margin-bottom: 0">
        <colgroup><col style="width:56.1%"><col style="width:18.2%"><col style="width:25.8%"></colgroup>
        <tr>
            <td><b>Rekomendasi (diisi oleh LSP):</b><br>Berdasarkan ketentuan persyaratan dasar, maka pemohon:<br>
                <span class="{{ $accepted ? 'b' : '' }}"><span class="box">{{ $accepted ? 'X' : '' }}</span> Diterima</span> /
                <span class="{{ $rejected ? 'b' : '' }}"><span class="box">{{ $rejected ? 'X' : '' }}</span> Tidak diterima</span>
                *) sebagai peserta sertifikasi<br><span class="sm">*) coret yang tidak sesuai</span>
            </td>
            <td colspan="2"><b>Pemohon / Kandidat :</b></td>
        </tr>
        <tr>
            <td>Catatan :<br>{{ $pendaftaran->catatan_penolakan ?: '—' }}</td>
            <td>Nama</td>
            <td>{{ $user?->name ?? '—' }}</td>
        </tr>
        <tr>
            <td></td>
            <td>Tanda tangan / Tanggal</td>
            <td>
                @if ($signature)<img src="{{ $signature }}" alt="Tanda Tangan Pemohon" style="max-height: 25mm; max-width: 45mm;">@else<div class="sig-missing">Tanda tangan belum tersimpan.</div>@endif
                <div class="sm">{{ $pendaftaran->disetujui_pada?->translatedFormat('d F Y') ?? '—' }}</div>
            </td>
        </tr>
        <tr>
            <td></td>
            <td colspan="2"><b>Admin LSP :</b></td>
        </tr>
        <tr>
            <td></td>
            <td>Nama :</td>
            <td>{{ $adminLsp?->name ?? '—' }}</td>
        </tr>
        <tr>
            <td></td>
            <td>Tanda tangan / Tanggal</td>
            <td>
                @if ($adminSignature)<img src="{{ $adminSignature }}" alt="Tanda Tangan Admin LSP" style="max-height: 25mm; max-width: 45mm;">@else<div class="sig-missing">Tanda tangan belum tersimpan.</div>@endif
                <div class="sm">{{ $pendaftaran->disetujui_pada?->translatedFormat('d F Y') ?? '—' }}</div>
            </td>
        </tr>
    </table>
</body>
</html>