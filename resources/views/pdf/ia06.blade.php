@php
    /** @var \App\Models\Ia06Asesmen $ia06 */
    $check = '✓';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>FR.IA.06 · {{ $pendaftaran->no_pendaftaran }}</title>
    <style>
        @page { margin: 14mm 12mm; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color: #111; font-size: 9.5px; line-height: 1.4; }
        h1 { font-size: 13px; margin: 0 0 2px; text-align: center; text-transform: uppercase; }
        h2 { font-size: 10.5px; margin: 0 0 12px; text-align: center; font-weight: normal; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .meta td { vertical-align: top; padding: 2px 0; }
        .meta td:first-child { width: 32%; font-weight: bold; }
        .bordered th, .bordered td { border: 1px solid #333; padding: 4px 5px; vertical-align: top; }
        .bordered th { background: #eeeeee; font-size: 8.5px; text-transform: uppercase; }
        .center { text-align: center; }
        .muted { color: #555; }
        .page-break { page-break-after: always; }
        .answer-box { border: 1px solid #333; min-height: 60px; padding: 5px 6px; margin-bottom: 10px; }
    </style>
</head>
<body>
    <h1>FR.IA.06.A — Pertanyaan Tertulis Esai</h1>
    <h2>{{ $skema->nama_skema }} ({{ $skema->kode_skema }})</h2>

    <table class="meta">
        <tr><td>Nama Asesi</td><td>{{ $pendaftaran->asesi->user->name ?? '—' }}</td></tr>
        <tr><td>Nama Asesor</td><td>{{ $asesor?->nama_lengkap ?? '—' }}</td></tr>
        <tr><td>TUK</td><td>{{ $pendaftaran->jadwal?->tuk?->nama_tuk ?? '—' }}</td></tr>
        <tr><td>Tanggal</td><td>{{ $pendaftaran->jadwal?->tanggal?->translatedFormat('d F Y') ?? '—' }}</td></tr>
        <tr><td>Judul</td><td>{{ $ia06->judul ?: '—' }}</td></tr>
        <tr><td>Petunjuk</td><td>{{ $ia06->petunjuk ?: 'Jawablah pertanyaan berikut secara singkat dan jelas.' }}</td></tr>
        <tr><td>Status</td><td>{{ $ia06->isFinal() ? 'FINAL' : 'DRAF' }}</td></tr>
    </table>

    @forelse ($ia06->soals as $soal)
        <div style="margin-bottom: 10px;">
            <div><strong>{{ $loop->iteration }}.</strong> {!! nl2br(e($soal->pertanyaan)) !!}</div>
            <div class="answer-box"></div>
        </div>
    @empty
        <p class="muted">Belum ada soal.</p>
    @endforelse

    <div class="page-break"></div>

    <h1>FR.IA.06.B — Lembar Kunci Jawaban Esai</h1>
    <h2>{{ $skema->nama_skema }} ({{ $skema->kode_skema }})</h2>

    <table class="meta">
        <tr><td>Nama Asesi</td><td>{{ $pendaftaran->asesi->user->name ?? '—' }}</td></tr>
        <tr><td>Nama Asesor</td><td>{{ $asesor?->nama_lengkap ?? '—' }}</td></tr>
        <tr><td>Tanggal</td><td>{{ $pendaftaran->jadwal?->tanggal?->translatedFormat('d F Y') ?? '—' }}</td></tr>
    </table>

    <table class="bordered">
        <thead>
            <tr>
                <th style="width:6%" class="center">No</th>
                <th style="width:34%">Pertanyaan</th>
                <th>Kunci / rambu-rambu jawaban</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($ia06->soals as $soal)
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td>{{ $soal->pertanyaan }}</td>
                    <td>{!! nl2br(e($soal->kunci ?: '—')) !!}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="center muted">—</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="page-break"></div>

    <h1>FR.IA.06.C — Lembar Jawaban Esai</h1>
    <h2>{{ $skema->nama_skema }} ({{ $skema->kode_skema }})</h2>

    <table class="meta">
        <tr><td>Nama Asesi</td><td>{{ $pendaftaran->asesi->user->name ?? '—' }}</td></tr>
        <tr><td>Nama Asesor</td><td>{{ $asesor?->nama_lengkap ?? '—' }}</td></tr>
        <tr><td>Tanggal</td><td>{{ $pendaftaran->jadwal?->tanggal?->translatedFormat('d F Y') ?? '—' }}</td></tr>
    </table>

    <table class="bordered">
        <thead>
            <tr>
                <th style="width:6%" class="center">No</th>
                <th>Jawaban Asesi</th>
                <th style="width:10%" class="center">Ya</th>
                <th style="width:10%" class="center">Tidak</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($ia06->soals as $soal)
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td>{!! nl2br(e($soal->jawaban_asesi ?: '—')) !!}</td>
                    <td class="center">{{ $soal->pencapaian === true ? $check : '' }}</td>
                    <td class="center">{{ $soal->pencapaian === false ? $check : '' }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="center muted">—</td></tr>
            @endforelse
        </tbody>
    </table>

    <table class="meta">
        <tr>
            <td>Umpan balik untuk asesi</td>
            <td>{{ $ia06->umpan_balik ?: '—' }}</td>
        </tr>
    </table>
</body>
</html>
