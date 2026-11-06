@extends('layouts.asesi')

@section('title', 'Dokumen Pre-Asesmen')
@section('pretitle', 'Pendaftaran Sertifikasi')
@section('page-title', 'Dokumen Pre-Asesmen')

@section('actions')
    <a href="{{ route('asesi.pendaftaran.show', $pendaftaran) }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i> Kembali
    </a>
@endsection

@section('content')
@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif
@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<x-tabler.card title="Informasi Pendaftaran" class="mb-3">
    <div class="row g-3">
        <div class="col-md-4">
            <div class="text-secondary small">No. Pendaftaran</div>
            <div class="fw-semibold">{{ $pendaftaran->no_pendaftaran }}</div>
        </div>
        <div class="col-md-4">
            <div class="text-secondary small">Skema</div>
            <div class="fw-semibold">{{ $skema->nama_skema }}</div>
        </div>
        <div class="col-md-4">
            <div class="text-secondary small">Jadwal</div>
            <div class="fw-semibold">
                {{ $jadwal?->tanggal?->translatedFormat('d F Y') ?? '—' }}
            </div>
        </div>
    </div>
</x-tabler.card>

@php
    $dAk01 = $data['ak01'] ?? [];
    $dAk04 = $data['ak04'] ?? [];
    $dAk07 = $data['ak07'] ?? [];
    $pAk01 = $persetujuans['ak01'] ?? null;
    $pAk04 = $persetujuans['ak04'] ?? null;
    $pAk07 = $persetujuans['ak07'] ?? null;
    $asesiNama = $asesiUser->name ?? '—';
    $asesorNama = $jadwal?->asesors->first()?->nama_lengkap ?? '—';
    $skemaJudul = $skema->nama_skema ?? '—';
    $skemaNomor = $skema->nomor_skema ?? '—';
    $tukNama = $jadwal?->tuk?->nama_tuk ?? '—';
    $tglJadwal = $jadwal?->tanggal?->translatedFormat('d F Y') ?? '—';
    $waktuJadwal = $jadwal?->jam_mulai.'–'.$jadwal?->jam_selesai ?? '—';
    $sudah01 = $pAk01?->isApproved();
    $sudah07 = $pAk07?->isApproved();
    $sudah04 = $pAk04?->isApproved();
@endphp

<!-- ===== KARTU GABUNGAN AK.07 + AK.01 + INFO BANDING ===== -->
<div class="row row-cards">
    <div class="col-12">
        <x-tabler.card title="Form Penyesuaian (AK.07), Persetujuan (AK.01) dan Informasi Mengenai Banding Asesmen" class="mb-3">
            <x-slot:actions>
                <span class="badge {{ $sudah01 && $sudah07 && $sudah04 ? 'bg-success' : 'bg-warning text-dark' }} ms-2">
                    {{ $sudah01 && $sudah07 && $sudah04 ? 'Semua Disetujui' : (($sudah01 || $sudah07 || $sudah04) ? 'Sebagian' : 'Belum') }}
                </span>
            </x-slot:actions>
            <div class="mb-3 text-secondary small">
                Formulir ini wajib dibaca dan disetujui sebelum asesmen dimulai.
            </div>
            <div class="d-flex justify-content-end">
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalAk01Ak04Ak07">
                    <i class="ti ti-file me-1"></i> Buka Formulir
                </button>
                <a href="{{ route('asesi.pre-asesmen.pdf.gabungan', $pendaftaran) }}"
                   class="btn btn-outline-secondary">
                    <i class="ti ti-printer me-1"></i> Cetak PDF
                </a>
                @if (! ($sudah01 && $sudah07 && $sudah04))
                    <button type="button" class="btn btn-success ms-2" data-bs-toggle="modal" data-bs-target="#modalAk01Ak04Ak07">
                        <i class="ti ti-check me-1"></i> Setujui Formulir
                    </button>
                @endif
            </div>
        </x-tabler.card>
    </div>
</div>
@endsection

@push('modals')
<!-- ===== MODAL AK.07 + AK.01 + INFO BANDING (GABUNGAN) ===== -->
<div class="modal fade" id="modalAk01Ak04Ak07" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Formulir Pre-Asesmen (AK.07 + AK.01 + Informasi Banding)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('asesi.pre-asesmen.approve', $pendaftaran) }}" id="formAk01Ak04Ak07">
                    @csrf

                    <!-- Header -->
                    <x-tabler.card title="Identitas Asesmen" class="mb-3">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small text-muted">Nama Skema</label>
                                <input type="text" name="nama_skema" class="form-control form-control-sm"
                                       value="{{ $dAk07['nama_skema'] ?? $skemaJudul }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted">Judul Skema</label>
                                <input type="text" name="judul_skema" class="form-control form-control-sm"
                                       value="{{ $dAk07['judul_skema'] ?? $skemaNomor }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted">Hari / Tanggal</label>
                                <input type="text" name="hari_tanggal" class="form-control form-control-sm"
                                       value="{{ $dAk07['hari_tanggal'] ?? $tglJadwal }}" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted">Waktu</label>
                                <input type="text" name="waktu" class="form-control form-control-sm"
                                       value="{{ $dAk07['waktu'] ?? $waktuJadwal }}" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted">Asesor</label>
                                <input type="text" name="asesor" class="form-control form-control-sm"
                                       value="{{ $dAk07['asesor'] ?? $asesorNama }}" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted">TUK</label>
                                <input type="text" name="tuk" class="form-control form-control-sm"
                                       value="{{ $dAk07['tuk'] ?? $tukNama }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted">Jenis Bukti</label>
                                <select name="jenis_bukti" class="form-select form-select-sm">
                                    <option value="">—</option>
                                    @php $bukti = $dAk01['jenis_bukti'] ?? $dAk07['jenis_bukti'] ?? []; @endphp
                                    @foreach (['portofolio' => 'Hasil Verifikasi Portofolio', 'reviu_produk' => 'Hasil Reviu Produk', 'observasi' => 'Hasil Observasi Langsung', 'kegiatan_terstruktur' => 'Hasil Kegiatan Terstruktur', 'tertulis' => 'Hasil Pertanyaan Tertulis', 'lisan' => 'Hasil Pertanyaan Lisan', 'wawancara' => 'Hasil Pertanyaan Wawancara'] as $k => $label)
                                        <option value="{{ $k }}" {{ in_array($k, $bukti, true) ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </x-tabler.card>

                    <!-- AK.07 Penyesuaian -->
                    <x-tabler.card title="Form AK.07 — Penyesuaian yang Wajar dan Beralasan" class="mb-3">
                        <p class="text-secondary small mb-2">
                            Formulir ini dapat digunakan jika ada asesi yang mempunyai keterbatasan sehingga diperlukan penyesuaian yang wajar dan beralasan.
                        </p>

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
                                    $potensiSelected = array_map(fn ($v) => (string) $v, old('potensi', $dAk07['potensi'] ?? []));
                                @endphp
                                @foreach ($potensi as $i => $t)
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="potensi[]"
                                                   value="{{ $i + 1 }}" id="potensi{{ $i + 1 }}"
                                                   {{ in_array((string) ($i + 1), $potensiSelected, true) ? 'checked' : '' }}
                                                   {{ $sudah07 ? 'disabled' : '' }}>
                                            <label class="form-check-label" for="potensi{{ $i + 1 }}">
                                                {{ $t }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <table class="table table-bordered table-sm mb-0">
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
                                $perluMap = [];
                                $lainnyaMap = [];
                                foreach ($rows as $k => $_) {
                                    $perluMap[$k] = old('p'.$k.'_perlu') ?? $dAk07['p'.$k.'_perlu'] ?? '';
                                    $lainnyaMap[$k] = old('p'.$k.'_lainnya') ?? $dAk07['p'.$k.'_lainnya'] ?? '';
                                }
                            @endphp
                            @foreach ($rows as $k => $q)
                                <tr>
                                    <td class="text-center fw-semibold">{{ $k }}</td>
                                    <td>{{ $q }}</td>
                                    <td class="text-center">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="p{{ $k }}_perlu"
                                                   value="ya" id="p{{ $k }}ya{{ $sudah07 ? ' disabled' : '' }}"
                                                   {{ ($perluMap[$k] ?? '') === 'ya' ? 'checked' : '' }} {{ $sudah07 ? 'disabled' : '' }}>
                                            <label class="form-check-label" for="p{{ $k }}ya{{ $sudah07 ? ' disabled' : '' }}">Ya</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="p{{ $k }}_perlu"
                                                   value="tidak" id="p{{ $k }}tidak{{ $sudah07 ? ' disabled' : '' }}"
                                                   {{ ($perluMap[$k] ?? '') === 'tidak' ? 'checked' : '' }} {{ $sudah07 ? 'disabled' : '' }}>
                                            <label class="form-check-label" for="p{{ $k }}tidak{{ $sudah07 ? ' disabled' : '' }}">Tidak</label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" name="p{{ $k }}_lainnya"
                                               class="form-control form-control-sm"
                                               value="{{ $lainnyaMap[$k] ?? '' }}"
                                               placeholder="Keterangan bila perlu..." {{ $sudah07 ? 'disabled' : '' }}>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </x-tabler.card>

                    <!-- AK.01 Persetujuan -->
                    <x-tabler.card title="Form AK.01 — Persetujuan Asesmen dan Kerahasiaan" class="mb-3">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <td class="fw-semibold">Asesi:</td>
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
                    </x-tabler.card>

                    <!-- Informasi Mengenai Banding Asesmen -->
                    <x-tabler.card title="Informasi Mengenai Banding Asesmen" class="mb-3">
                        <div class="alert alert-info mb-0">
                            <i class="ti ti-info me-1"></i>
                            Informasi prosedur banding asesmen telah dijelaskan dan dibaca sebelum asesmen dimulai.
                        </div>
                    </x-tabler.card>

                    <!-- Konfirmasi setuju -->
                    @if (! $sudah01 || ! $sudah07 || ! $sudah04)
                        <hr class="my-3">
                        <div class="mb-3">
                            <label class="form-label small text-muted fw-semibold">Setujui Formulir</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="setuju_ak07" value="1"
                                       id="setujuAk07" {{ $sudah07 ? 'checked disabled' : '' }}>
                                <label class="form-check-label" for="setujuAk07">
                                    Setujui Form AK.07 (Penyesuaian)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="setuju_ak01" value="1"
                                       id="setujuAk01" {{ $sudah01 ? 'checked disabled' : '' }}>
                                <label class="form-check-label" for="setujuAk01">
                                    Setujui Form AK.01 (Persetujuan)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="setuju_ak04" value="1"
                                       id="setujuAk04" {{ $sudah04 ? 'checked disabled' : '' }}>
                                <label class="form-check-label" for="setujuAk04">
                                    Setujui Informasi Mengenai Banding Asesmen
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small text-muted">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control form-control-sm"
                                   value="{{ old('tanggal', $dAk07['disetujui_tanggal'] ?? now()->format('Y-m-d')) }}"
                                   {{ $sudah01 && $sudah07 && $sudah04 ? 'disabled' : '' }}>
                        </div>

                        <p class="text-secondary small mt-2">
                            @if ($sudah01 && $sudah07 && $sudah04)
                                Anda telah menandatangani berkas ini pada tanggal
                                {{ $pAk01?->disetujui_pada?->format('d/m/Y H:i') ?? $pAk07?->disetujui_pada?->format('d/m/Y H:i') }}.
                            @else
                                Centang formulir yang disetujui, lalu klik Simpan.
                            @endif
                        </p>
                    @else
                        <div class="alert alert-success mb-0">
                            <i class="ti ti-check me-1"></i>
                            Anda telah menandatangani berkas ini pada tanggal
                            {{ $pAk01?->disetujui_pada?->format('d/m/Y H:i') ?? $pAk07?->disetujui_pada?->format('d/m/Y H:i') }}.
                        </div>
                    @endif
                </form>
            </div>
            <div class="modal-footer">
                @if ($sudah01 && $sudah07 && $sudah04)
                    <span class="badge bg-success">Semua Disetujui</span>
                @else
                    <button type="button" class="btn btn-success"
                            onclick="document.getElementById('formAk01Ak04Ak07').requestSubmit()">
                        <i class="ti ti-check me-1"></i> Simpan
                    </button>
                @endif
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endpush