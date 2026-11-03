<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11pt;
            color: #1a1a2e;
            background: #fff;
        }
        .page {
            width: 270mm;
            min-height: 190mm;
            padding: 18mm 20mm;
            position: relative;
        }
        .border-outer {
            border: 4px double #1a6b3a;
            padding: 12mm 16mm;
            position: relative;
        }
        .header { text-align: center; margin-bottom: 8mm; }
        .header .lsp-logo { max-height: 40px; margin-bottom: 4px; }
        .header .lsp-name { font-size: 14pt; font-weight: bold; color: #1a6b3a; }
        .header .lsp-sub { font-size: 9pt; color: #555; }
        .divider { border-top: 2px solid #1a6b3a; margin: 4mm 0; }
        .title { text-align: center; margin-bottom: 6mm; }
        .title h1 { font-size: 20pt; font-weight: bold; letter-spacing: 1px; color: #1a6b3a; }
        .title .sub { font-size: 10pt; color: #555; margin-top: 2px; }
        .awarded { text-align: center; font-size: 10pt; color: #555; margin-bottom: 2mm; }
        .name { text-align: center; font-size: 18pt; font-weight: bold; margin-bottom: 2mm; border-bottom: 1px solid #ccc; padding-bottom: 2mm; }
        .scheme { text-align: center; font-size: 12pt; font-weight: bold; color: #1a6b3a; margin-bottom: 6mm; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 8mm; font-size: 9pt; }
        .info-table td { padding: 2px 6px; }
        .info-table td:first-child { font-weight: bold; width: 35%; color: #555; }
        .footer-row { display: table; width: 100%; margin-top: 10mm; }
        .sign-block { display: table-cell; width: 50%; text-align: center; vertical-align: bottom; }
        .sign-block img { max-height: 50px; max-width: 150px; margin: 4px 0; }
        .sign-line { border-top: 1px solid #333; width: 70%; margin: 0 auto; padding-top: 2px; }
        .reg-no { text-align: center; font-size: 8pt; color: #888; margin-top: 4mm; }
        .watermark {
            position: absolute; top: 50%; left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 60pt; color: rgba(26,107,58,0.06);
            font-weight: bold; pointer-events: none; white-space: nowrap;
        }
    </style>
</head>
<body>
<div class="page">
    <div class="border-outer">
        <div class="watermark">KOMPETEN</div>

        <div class="header">
            @if (!empty($institution?->logo_path) && file_exists(storage_path('app/public/'.$institution->logo_path)))
                <img src="{{ storage_path('app/public/'.$institution->logo_path) }}"
                     class="lsp-logo" alt="{{ $institution->name }}">
            @endif
            <div class="lsp-name">{{ $institution?->name ?? config('app.name') }}</div>
            <div class="lsp-sub">Lembaga Sertifikasi Profesi</div>
        </div>

        <div class="divider"></div>

        <div class="title">
            <h1>SERTIFIKAT KOMPETENSI</h1>
            <div class="sub">Berdasarkan Penilaian Portofolio dan Asesmen Langsung</div>
        </div>

        <div class="awarded">Dengan bangga diberikan kepada:</div>
        <div class="name">{{ $sertifikat->asesi?->user?->name ?? '—' }}</div>
        <div class="awarded">Yang telah dinyatakan KOMPETEN pada skema:</div>
        <div class="scheme">{{ $sertifikat->skema?->nama_skema ?? '—' }}</div>

        <table class="info-table">
            <tr>
                <td>No. Registrasi Lisensi</td>
                <td>: {{ $sertifikat->no_registrasi }}</td>
                <td>Tanggal Terbit</td>
                <td>: {{ $sertifikat->tanggal_terbit?->translatedFormat('d F Y') }}</td>
            </tr>
            <tr>
                <td>No. Seri Blangko</td>
                <td>: {{ $sertifikat->no_seri_blangko }}</td>
                <td>Berlaku Hingga</td>
                <td>: {{ $sertifikat->berlaku_hingga?->translatedFormat('d F Y') ?? 'Seumur Hidup' }}</td>
            </tr>
        </table>

        <div class="footer-row">
            <div class="sign-block">
                <div class="lsp-sub">Tempat, {{ $sertifikat->tanggal_terbit?->translatedFormat('d F Y') }}</div>
                <br>
                @if ($sertifikat->asesi?->user?->signature_path && file_exists(storage_path('app/public/'.$sertifikat->asesi->user->signature_path)))
                    <img src="{{ storage_path('app/public/'.$sertifikat->asesi->user->signature_path) }}"
                         alt="Tanda Tangan Asesi"
                         style="max-height: 60px; max-width: 200px; display: block; margin: 8px auto;">
                @else
                    <br><br>
                @endif
                <div class="sign-line">
                    <div>{{ $sertifikat->asesi?->user?->name ?? '—' }}</div>
                    <div class="lsp-sub">Peserta</div>
                </div>
            </div>
            <div class="sign-block">
                <div class="lsp-sub">Diterbitkan Oleh</div>
                <br>
                @php
                    $asesor = $sertifikat->pendaftaran?->hasilAsesmens?->first()?->asesor;
                @endphp
                @if ($asesor?->user?->signature_path && file_exists(storage_path('app/public/'.$asesor->user->signature_path)))
                    <img src="{{ storage_path('app/public/'.$asesor->user->signature_path) }}"
                         alt="Tanda Tangan Asesor"
                         style="max-height: 60px; max-width: 200px; display: block; margin: 8px auto;">
                @else
                    <br><br>
                @endif
                <div class="sign-line">
                    <div>{{ $asesor?->nama_lengkap ?? $sertifikat->penerbit?->name ?? '—' }}</div>
                    <div class="lsp-sub">{{ $asesor ? 'Asesor' : 'Administrator' }}</div>
                </div>
            </div>
        </div>

        <div class="reg-no">
            Verifikasi: {{ route('sertifikat.verifikasi', $sertifikat->qr_token) }}
        </div>
    </div>
</div>
</body>
</html>
