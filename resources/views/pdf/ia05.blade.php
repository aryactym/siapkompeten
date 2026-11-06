@php
    /** @var \App\Models\Ia05Asesmen $ia05 */
    $check = '✓';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>FR.IA.05 · {{ $pendaftaran->no_pendaftaran }}</title>
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
        .opsi { margin-left: 10px; }
        .page-break { page-break-after: always; }
        .grid { width: 100%; border-collapse: collapse; }
        .grid th, .grid td { border: 1px solid #333; padding: 4px; text-align: center; }
    </style>
</head>
<body>
    <h1>FR.IA.05.A — Pertanyaan Tertulis Pilihan Ganda</h1>
    <h2>{{ $skema->nama_skema }} ({{ $skema->kode_skema }})</h2>

    <table class="meta">
        <tr><td>Nama Asesi</td><td>{{ $pendaftaran->asesi->user->name ?? '—' }}</td></tr>
        <tr><td>Nama Asesor</td><td>{{ $asesor?->nama_lengkap ?? '—' }}</td></tr>
        <tr><td>TUK</td><td>{{ $pendaftaran->jadwal?->tuk?->nama_tuk ?? '—' }}</td></tr>
        <tr><td>Tanggal</td><td>{{ $pendaftaran->jadwal?->tanggal?->translatedFormat('d F Y') ?? '—' }}</td></tr>
        <tr><td>Judul</td><td>{{ $ia05->judul ?: '—' }}</td></tr>
        <tr><td>Petunjuk</td><td>{{ $ia05->petunjuk ?: 'Pilih satu jawaban paling tepat.' }}</td></tr>
        <tr><td>Status</td><td>{{ $ia05->isFinal() ? 'FINAL' : 'DRAF' }}</td></tr>
    </table>

    @forelse ($ia05->soals as $soal)
        <div style="margin-bottom: 8px;">
            <div><strong>{{ $loop->iteration }}.</strong> {!! nl2br(e($soal->pertanyaan)) !!}</div>
            @foreach ($soal->opsi() as $key => $opsi)
                @if (filled($opsi))
                    <div class="opsi">{{ strtoupper($key) }}. {{ $opsi }}</div>
                @endif
            @endforeach
        </div>
    @empty
        <p class="muted">Belum ada soal.</p>
    @endforelse

    <div class="page-break"></div>

    <h1>FR.IA.05.B — Lembar Kunci Jawaban</h1>
    <h2>{{ $skema->nama_skema }} ({{ $skema->kode_skema }})</h2>

    <table class="meta">
        <tr><td>Nama Asesi</td><td>{{ $pendaftaran->asesi->user->name ?? '—' }}</td></tr>
        <tr><td>Nama Asesor</td><td>{{ $asesor?->nama_lengkap ?? '—' }}</td></tr>
        <tr><td>Tanggal</td><td>{{ $pendaftaran->jadwal?->tanggal?->translatedFormat('d F Y') ?? '—' }}</td></tr>
    </table>

    <table class="grid">
        <thead>
            <tr>
                <th style="width:10%">No</th>
                <th>Kunci</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($ia05->soals as $soal)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ strtoupper($soal->kunci) }}</td>
                </tr>
            @empty
                <tr><td colspan="2" class="muted">—</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="page-break"></div>

    <h1>FR.IA.05.C — Lembar Jawaban Pilihan Ganda</h1>
    <h2>{{ $skema->nama_skema }} ({{ $skema->kode_skema }})</h2>

    <table class="meta">
        <tr><td>Nama Asesi</td><td>{{ $pendaftaran->asesi->user->name ?? '—' }}</td></tr>
        <tr><td>Nama Asesor</td><td>{{ $asesor?->nama_lengkap ?? '—' }}</td></tr>
        <tr><td>Tanggal</td><td>{{ $pendaftaran->jadwal?->tanggal?->translatedFormat('d F Y') ?? '—' }}</td></tr>
    </table>

    <table class="grid">
        <thead>
            <tr>
                <th style="width:8%">No</th>
                <th>A</th>
                <th>B</th>
                <th>C</th>
                <th>D</th>
                <th style="width:14%">Benar</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($ia05->soals as $soal)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $soal->jawaban_asesi === 'a' ? $check : '' }}</td>
                    <td>{{ $soal->jawaban_asesi === 'b' ? $check : '' }}</td>
                    <td>{{ $soal->jawaban_asesi === 'c' ? $check : '' }}</td>
                    <td>{{ $soal->jawaban_asesi === 'd' ? $check : '' }}</td>
                    <td>
                        @if ($soal->jawaban_asesi === null)
                            —
                        @else
                            {{ $soal->benar() ? 'Ya' : 'Tidak' }}
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="muted">—</td></tr>
            @endforelse
        </tbody>
    </table>

    <table class="meta">
        <tr>
            <td>Jumlah Benar</td>
            <td>{{ $ia05->jumlahBenar() }} dari {{ $ia05->soals->count() }}</td>
        </tr>
        <tr>
            <td>Umpan balik untuk asesi</td>
            <td>{{ $ia05->umpan_balik ?: '—' }}</td>
        </tr>
    </table>
</body>
</html>
