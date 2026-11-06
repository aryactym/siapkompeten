<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>FR.AK.04 · {{ $pendaftaran->no_pendaftaran }}</title>
    @include('pdf.partials.official-styles')
</head>
<body>
    @php
        $pendaftaran ??= null;
        $skema = $skema ?? $pendaftaran?->skema;
        $jadwal = $jadwal ?? $pendaftaran?->jadwal;
        $persetujuan ??= null;
        $data ??= [];

        $asesiUser = $asesiUser ?? $pendaftaran?->asesi?->user;
        $asesorUser = $jadwal?->asesors->first()?->user;
        $asesiSignature = \App\Support\SignatureImage::for($asesiUser);
        $asesorSignature = \App\Support\SignatureImage::for($asesorUser);
        $data = $data ?? [];
        $signedAt = $persetujuan?->disetujui_pada?->translatedFormat('d F Y') ?? ($data['tanggal'] ?? null);
        $assessmentDate = $data['tanggal'] ?? $jadwal?->tanggal?->translatedFormat('d F Y') ?? '—';
        $questions = [
            1 => 'Apakah Proses Banding telah dijelaskan kepada Anda?',
            2 => 'Apakah Anda telah mendiskusikan Banding dengan Asesor?',
            3 => 'Apakah Anda mau melibatkan “orang lain” membantu Anda dalam Proses Banding?',
        ];
        $answers = [
            1 => $data['proses_dijelaskan'] ?? null,
            2 => $data['didiskusikan'] ?? null,
            3 => $data['libatkan_orang_lain'] ?? null,
        ];
    @endphp

    <table class="hd">
        <tr><td>FR.AK.04.</td><td>BANDING ASESMEN</td></tr>
    </table>
    <div class="gap"></div>

    <table>
        <colgroup><col style="width:38%"><col style="width:4mm"><col></colgroup>
        <tr><td>Nama Asesi</td><td class="sep">:</td><td>{{ $data['asesi_nama'] ?? $asesiUser?->name ?? '—' }}</td></tr>
        <tr><td>Nama Asesor</td><td class="sep">:</td><td>{{ $data['asesor_nama'] ?? $asesorUser?->name ?? '—' }}</td></tr>
        <tr><td>Tanggal Asesmen</td><td class="sep">:</td><td>{{ $assessmentDate }}</td></tr>
    </table>

    <table>
        <thead>
            <tr><th rowspan="2" class="c" style="width:8mm">No</th><th rowspan="2">Jawablah dengan Ya atau Tidak pertanyaan-pertanyaan berikut ini :</th><th colspan="2" class="c">Jawaban</th></tr>
            <tr><th class="c" style="width:16mm">YA</th><th class="c" style="width:16mm">TIDAK</th></tr>
        </thead>
        <tbody>
            @foreach ($questions as $number => $question)
                @php $answer = strtolower((string) ($answers[$number] ?? '')); @endphp
                <tr>
                    <td class="c">{{ $number }}</td>
                    <td>{{ $question }}</td>
                    <td class="c">{{ $answer === 'ya' ? 'X' : '' }}</td>
                    <td class="c">{{ $answer === 'tidak' ? 'X' : '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table>
        <tr><td>Banding ini diajukan atas Keputusan Asesmen yang dibuat terhadap Skema Sertifikasi (Kualifikasi/Klaster/Okupasi) berikut :</td></tr>
        <tr>
            <td>
                <table class="nb">
                    <colgroup><col style="width:45mm"><col style="width:28mm"><col style="width:4mm"><col></colgroup>
                    <tr><td rowspan="2">Skema Sertifikasi</td><td>Judul</td><td class="sep">:</td><td>{{ $data['skema_judul'] ?? $skema?->nama_skema ?? '—' }}</td></tr>
                    <tr><td>Nomor</td><td class="sep">:</td><td>{{ $data['skema_nomor'] ?? $skema?->nomor_skema ?? '—' }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <table>
        <tr><td><b>Banding ini diajukan atas alasan sebagai berikut :</b></td></tr>
        <tr><td style="min-height: 45mm; white-space: pre-wrap;">{{ $data['alasan'] ?? '—' }}</td></tr>
    </table>

    <table>
        <tr><td class="sm">Anda mempunyai hak mengajukan banding jika Anda menilai Proses Asesmen tidak sesuai SOP dan tidak memenuhi Prinsip Asesmen.</td></tr>
    </table>

    <table class="avoid-break" style="margin-bottom: 0">
        <colgroup><col style="width:50%"><col style="width:50%"></colgroup>
        <tr>
            <td class="ttd">
                Tanda tangan Asesi :<br>
                @if ($asesiSignature)
                    <img src="{{ $asesiSignature }}" alt="Tanda Tangan Asesi" style="max-height: 18mm; max-width: 45mm; margin-top: 3mm;">
                @else
                    <div class="sig-missing">Tanda tangan belum tersimpan.</div>
                @endif
                <br><span class="sm">Tanggal : {{ $signedAt ?? '—' }}</span>
            </td>
            <td class="ttd">
                Tanda tangan Asesor :<br>
                @if ($asesorSignature)
                    <img src="{{ $asesorSignature }}" alt="Tanda Tangan Asesor" style="max-height: 18mm; max-width: 45mm; margin-top: 3mm;">
                @else
                    <div class="sig-missing">Tanda tangan belum tersimpan.</div>
                @endif
                <br><span class="sm">Tanggal : {{ $signedAt ?? '—' }}</span>
            </td>
        </tr>
    </table>

    @if ($persetujuan?->disetujui_pada)
        <p><span class="b">Disetujui pada</span> {{ $persetujuan->disetujui_pada->translatedFormat('d F Y H:i') }}.</p>
    @endif
</body>
</html>