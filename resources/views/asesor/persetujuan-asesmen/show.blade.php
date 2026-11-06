@extends('layouts.asesor')

@section('title', 'Dokumen Pre-Asesmen')
@section('pretitle', 'Asesmen')
@section('page-title', 'Dokumen Pre-Asesmen')

@section('actions')
    <a href="{{ route('asesor.jadwal.peserta', $pendaftaran->jadwal) }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i> Kembali
    </a>
    <a href="{{ route('asesor.pre-asesmen.pdf.gabungan', $pendaftaran) }}"
       class="btn btn-outline-secondary">
        <i class="ti ti-printer me-1"></i> Cetak PDF Gabungan
    </a>
@endsection

@section('content')
@if ($allApproved)
    <div class="alert alert-success">
        <i class="ti ti-check me-1"></i> Seluruh dokumen pre-asesmen telah disetujui asesi. Asesmen dapat dimulai.
    </div>
@else
    <div class="alert alert-warning">
        <i class="ti ti-alert-triangle me-1"></i> Dokumen pre-asesmen belum lengkap. Asesmen belum boleh dimulai.
    </div>
@endif

<x-tabler.card title="Informasi Pendaftaran" class="mb-3">
    <div class="row g-3">
        <div class="col-md-4">
            <div class="text-secondary small">No. Pendaftaran</div>
            <div class="fw-semibold">{{ $pendaftaran->no_pendaftaran }}</div>
        </div>
        <div class="col-md-4">
            <div class="text-secondary small">Asesi</div>
            <div class="fw-semibold">{{ $pendaftaran->asesi->user->name ?? '—' }}</div>
        </div>
        <div class="col-md-4">
            <div class="text-secondary small">Skema</div>
            <div class="fw-semibold">{{ $pendaftaran->skema->nama_skema }}</div>
        </div>
    </div>
</x-tabler.card>

<div class="row row-cards">
    @foreach (\App\Models\PersetujuanAsesmen::requiredJenis() as $jenis)
        @php
            $p = $persetujuans[$jenis] ?? null;
            $d = $p?->data_json ?? [];
            $approved = $p?->isApproved();
            $label = \App\Models\PersetujuanAsesmen::JENIS_LABELS[$jenis] ?? $jenis;
        @endphp
        <div class="col-12">
            <x-tabler.card title="{{ $label }}" class="mb-3">
                <x-slot:actions>
                    <span class="badge {{ $approved ? 'bg-success' : 'bg-warning text-dark' }} ms-2">
                        {{ $approved ? 'Disetujui' : 'Belum' }}
                    </span>
                </x-slot:actions>

                <div class="mb-3 text-secondary small">
                    @if ($approved)
                        Disetujui pada {{ $p->disetujui_pada?->translatedFormat('d F Y H:i') }}
                        @if ($p->disetujui_oleh)
                            oleh {{ $p->penyetuju->name ?? '—' }}
                        @endif
                    @else
                        Belum disetujui asesi.
                    @endif
                </div>

                <!-- ===== FR.AK.01: Persetujuan & Kerahasiaan ===== -->
                @if ($jenis === 'ak01')
                    <div class="mb-3">
                        <label class="form-label small text-muted fw-semibold">Jenis Bukti</label>
                        <div class="text-secondary small">
                            @php
                                $bukti = $d['jenis_bukti'] ?? [];
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
                                @foreach ($bukti as $b)
                                    <span class="badge bg-light text-dark border me-1 mb-1">{{ $buktiLabels[$b] ?? $b }}</span>
                                @endforeach
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </div>
                    </div>
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td class="fw-semibold" style="width:30%">Asesi:</td>
                            <td class="text-secondary small">
                                Bahwa saya telah mendapatkan penjelasan terkait hak dan prosedur banding asesmen dari asesor
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Asesor:</td>
                            <td class="text-secondary small">
                                Menyatakan tidak akan membuka hasil pekerjaan yang saya peroleh karena penugasan saya sebagai Asesor dalam pekerjaan Asesmen kepada siapapun atau organisasi apapun selain kepada pihak yang berwenang sehubungan dengan kewajiban saya sebagai Asesor yang ditugaskan oleh LSP.
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Asesi:</td>
                            <td class="text-secondary small">
                                Saya setuju mengikuti asesmen dengan pemahaman bahwa informasi yang dikumpulkan hanya digunakan untuk pengembangan profesional dan hanya dapat diakses oleh orang tertentu saja.
                            </td>
                        </tr>
                    </table>
                @endif

                <!-- ===== FR.AK.04: Banding Asesmen ===== -->
                @if ($jenis === 'ak04')
                    <table class="table table-bordered table-sm mb-3">
                        <thead>
                            <tr>
                                <th style="width:60%">Pertanyaan</th>
                                <th class="text-center" style="width:20%">YA</th>
                                <th class="text-center" style="width:20%">TIDAK</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Apakah Proses Banding telah dijelaskan kepada Anda?</td>
                                <td class="text-center">{{ ($d['proses_dijelaskan'] ?? null) === 'ya' ? '✓' : '✗' }}</td>
                                <td class="text-center">{{ ($d['proses_dijelaskan'] ?? null) === 'tidak' ? '✓' : '✗' }}</td>
                            </tr>
                            <tr>
                                <td>Apakah Anda telah mendiskusikan Banding dengan Asesor?</td>
                                <td class="text-center">{{ ($d['didiskusikan'] ?? null) === 'ya' ? '✓' : '✗' }}</td>
                                <td class="text-center">{{ ($d['didiskusikan'] ?? null) === 'tidak' ? '✓' : '✗' }}</td>
                            </tr>
                            <tr>
                                <td>Apakah Anda mau melibatkan &ldquo;orang lain&rdquo; membantu Anda dalam Proses Banding?</td>
                                <td class="text-center">{{ ($d['libatkan_orang_lain'] ?? null) === 'ya' ? '✓' : '✗' }}</td>
                                <td class="text-center">{{ ($d['libatkan_orang_lain'] ?? null) === 'tidak' ? '✓' : '✗' }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="mb-3">
                        <label class="form-label small text-muted fw-semibold">Skema Sertifikasi</label>
                        <div class="text-secondary small">{{ $d['skema_judul'] ?? '—' }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted fw-semibold">No. Skema Sertifikasi</label>
                        <div class="text-secondary small">{{ $d['skema_nomor'] ?? '—' }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted fw-semibold">Alasan Banding</label>
                        <div class="p-2 border rounded bg-light">{{ $d['alasan'] ?? '—' }}</div>
                    </div>
                @endif

                <!-- ===== FR.AK.07: Penyesuaian Wajar dan Beralasan ===== -->
                @if ($jenis === 'ak07')
                    <div class="mb-3">
                        <label class="form-label small text-muted fw-semibold">Potensi Asesi</label>
                        <div class="row g-2">
                            @php
                                $potensi = [
                                    'Hasil pelatihan dan / atau pendidikan, dimana Kurikulum dan fasilitas praktek mampu telusur terhadap standar kompetensi',
                                    'Hasil pelatihan dan / atau pendidikan, dimana kurikulum belum berbasis kompetensi.',
                                    'Pekerja berpengalaman, dimana berasal dari industri/tempat kerja yang dalam operasionalnya mampu telusur dengan standar kompetensi',
                                    'Pekerja berpengalaman, dimana berasal dari industri/tempat kerja yang dalam operasionalnya belum berbasis kompetensi.',
                                    'Pelatihan / belajar mandiri atau otodidak.',
                                ];
                                $potensiSelected = array_map(fn ($v) => (string) $v, $d['potensi'] ?? []);
                            @endphp
                            @foreach ($potensi as $i => $t)
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                               value="{{ $i + 1 }}" id="potensi{{ $i + 1 }}"
                                               {{ in_array((string) ($i + 1), $potensiSelected, true) ? 'checked' : '' }}
                                               disabled>
                                        <label class="form-check-label" for="potensi{{ $i + 1 }}">
                                            {{ $t }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <table class="table table-bordered table-sm mb-3">
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
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio"
                                                   name="p{{ $k }}_perlu" value="ya"
                                                   {{ ($d['p'.$k.'_perlu'] ?? null) === 'ya' ? 'checked' : '' }}
                                                   disabled>
                                            <label class="form-check-label">Ya</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio"
                                                   name="p{{ $k }}_perlu" value="tidak"
                                                   {{ ($d['p'.$k.'_perlu'] ?? null) === 'tidak' ? 'checked' : '' }}
                                                   disabled>
                                            <label class="form-check-label">Tidak</label>
                                        </div>
                                    </td>
                                    <td>
                                        {{ $d['p'.$k.'_lainnya'] ?? '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mb-3">
                        <label class="form-label small text-muted fw-semibold">Acuan Pembanding Asesmen</label>
                        <div class="text-secondary small">{{ $d['acuan'] ?? '—' }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted fw-semibold">Metode Asesmen</label>
                        <div class="text-secondary small">{{ $d['metode'] ?? '—' }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted fw-semibold">Instrumen Asesmen</label>
                        <div class="text-secondary small">{{ $d['instrumen'] ?? '—' }}</div>
                    </div>
                @endif
            </x-tabler.card>
        </div>
    @endforeach
</div>
@endsection