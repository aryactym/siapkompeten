@extends('layouts.asesor')

@section('title', 'Input Hasil FR.AK.02')
@section('pretitle', 'Hasil Asesmen')
@section('page-title', 'FR.AK.02 · '.$pendaftaran->no_pendaftaran)

@section('actions')
    <a href="{{ route('asesor.apl02.show', $pendaftaran) }}" class="btn btn-outline-secondary">
        <i class="ti ti-list-check me-1"></i> APL-02
    </a>
    <a href="{{ route('asesor.mak02.pdf', $pendaftaran) }}" class="btn btn-outline-primary" target="_blank">
        <i class="ti ti-file-pdf me-1"></i> Unduh PDF
    </a>
@endsection

@section('content')
@if (session('success'))
    <div class="alert alert-success alert-dismissible mb-3" role="alert">
        <i class="ti ti-check me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@php
    use App\Models\HasilAsesmen;
    use App\Models\HasilAsesmenMetode;

    $sudahFinal  = $hasil->isFinal();

    // Progres ringkasan
    $totalKuk    = $kuks->count();
    $totalUnit   = $units->count();
    $kukYa       = $hasilKuks->filter(fn ($k) => $k->tercapai === true)->count();
    $kukTidak    = $hasilKuks->filter(fn ($k) => $k->tercapai === false)->count();
    $kukDinilai  = $kukYa + $kukTidak;
    $unitDinilai = $hasilUnits->filter(fn ($u) => filled($u->keputusan))->count();

    $keputusanLabels = [
        'kompeten'       => 'Kompeten',
        'belum_kompeten' => 'Belum Kompeten',
        'asesmen_lanjut' => 'Asesmen Lanjut',
    ];

    $rekomendasiSaran = $hasilUnits->isNotEmpty() && $hasilUnits->every(fn ($u) => $u->keputusan === 'kompeten')
        ? HasilAsesmen::REKOMENDASI_DIREKOMENDASIKAN
        : HasilAsesmen::REKOMENDASI_TIDAK;
@endphp

{{-- ===== BANNER STATUS FINAL ===== --}}
@if ($sudahFinal)
    <div class="alert alert-success mb-3">
        <div class="d-flex align-items-start gap-3">
            <i class="ti ti-certificate fs-2"></i>
            <div>
                <div class="fw-semibold fs-5">FR.AK.02 Final</div>
                <div>
                    Asesi dinyatakan
                    <strong>{{ $hasil->rekomendasi === HasilAsesmen::REKOMENDASI_DIREKOMENDASIKAN ? 'KOMPETEN' : 'BELUM KOMPETEN' }}</strong>
                    ({{ number_format($hasil->persen_kompeten, 1, ',', '.') }}% KUK tercapai)
                </div>
                <div class="text-secondary small">
                    Difinalkan oleh {{ $hasil->asesor?->nama_lengkap ?? '—' }}
                    · {{ $hasil->finalized_at?->translatedFormat('d F Y H:i') }}
                </div>
            </div>
        </div>
    </div>
@endif

@if (! $apl02Terverifikasi && ! $sudahFinal)
    <div class="alert alert-warning mb-3">
        <div class="d-flex align-items-center gap-2">
            <i class="ti ti-alert-triangle fs-3"></i>
            <div>
                <div class="fw-semibold">APL-02 Belum Diverifikasi</div>
                <div class="small">
                    Draf dapat disimpan, tetapi finalisasi FR.AK.02 diblokir hingga APL-02 diverifikasi.
                    <a href="{{ route('asesor.apl02.show', $pendaftaran) }}" class="alert-link">Buka APL-02 →</a>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- ===== RINGKASAN PROGRES ===== --}}
<div class="row row-cards mb-3">
    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2">
                    <span class="avatar bg-blue-lt text-blue"><i class="ti ti-list-numbers"></i></span>
                    <div>
                        <div class="small text-secondary">KUK Dinilai</div>
                        <div class="fw-bold fs-4">{{ $kukDinilai }}<span class="text-secondary fw-normal fs-6"> / {{ $totalKuk }}</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2">
                    <span class="avatar bg-green-lt text-green"><i class="ti ti-check"></i></span>
                    <div>
                        <div class="small text-secondary">KUK Tercapai</div>
                        <div class="fw-bold fs-4 text-success">{{ $kukYa }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2">
                    <span class="avatar bg-red-lt text-red"><i class="ti ti-x"></i></span>
                    <div>
                        <div class="small text-secondary">KUK Tidak Tercapai</div>
                        <div class="fw-bold fs-4 text-danger">{{ $kukTidak }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2">
                    <span class="avatar bg-purple-lt text-purple"><i class="ti ti-layout-grid"></i></span>
                    <div>
                        <div class="small text-secondary">Unit Diputuskan</div>
                        <div class="fw-bold fs-4">{{ $unitDinilai }}<span class="text-secondary fw-normal fs-6"> / {{ $totalUnit }}</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ===== FORM ===== --}}
<form method="POST" action="{{ route('asesor.mak02.store', $pendaftaran) }}" id="form-mak02">
    @csrf

    {{-- Validasi error (menampilkan semua error termasuk nested) --}}
    @if ($errors->any())
        <div class="alert alert-danger mb-3">
            <div class="fw-semibold mb-1"><i class="ti ti-alert-triangle me-1"></i> Perbaiki kesalahan berikut:</div>
            <ul class="mb-0 ps-3 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ===== PANEL METODE DEFAULT ===== --}}
    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">
                <i class="ti ti-microscope me-2"></i> Metode Asesmen
            </h3>
        </div>
        <div class="card-body">
            <p class="text-secondary small mb-3">
                Pilih metode yang digunakan untuk asesmen.
            </p>
            <div class="row g-2 mb-3">
                @foreach (\App\Models\HasilAsesmenMetode::METODES as $metode)
                    <div class="col-auto">
                        <label class="form-check form-check-inline">
                            <input type="checkbox" class="form-check-input"
                                   name="metode_default[]" value="{{ $metode }}"
                                   @checked(in_array($metode, old('metode_default', $hasil->metode_default ?? [])))
                                   @disabled($sudahFinal)>
                            <span class="form-check-label">
                                {{ \App\Models\HasilAsesmenMetode::METODE_LABELS[$metode] ?? ucfirst(str_replace('_', ' ', $metode)) }}
                            </span>
                        </label>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- UNIT SECTIONS --}}
    <div class="mb-3">
        @foreach ($units as $unitIdx => $unit)
            @php
                $hasilUnit  = $hasilUnits->get($unit->id);
                $saran      = $saranPerUnit[$unit->id] ?? null;
                $keputusan  = old('units.'.$unit->id.'.keputusan', $hasilUnit?->keputusan);

                // Status badge unit untuk header card
                $unitBadgeClass = match ($keputusan) {
                    'kompeten'       => 'bg-success-lt text-success',
                    'belum_kompeten' => 'bg-danger-lt text-danger',
                    'asesmen_lanjut' => 'bg-warning-lt text-warning',
                    default          => 'bg-secondary-lt text-secondary',
                };
                $unitBadgeLabel = $keputusan ? ($keputusanLabels[$keputusan] ?? $keputusan) : 'Belum diputuskan';

                // Jumlah KUK per unit
                $kukUnit = $unit->elemenKompetensis
                    ->flatMap(fn ($e) => $e->kriteriaUnjukKerjas)
                    ->pluck('id');
                $kukUnitDinilai = $hasilKuks->only($kukUnit->all())
                    ->filter(fn ($k) => $k->tercapai !== null)->count();

                // Auto-open accordion jika unit belum ada keputusan, atau ada error, atau saran berbeda
                $bukaDefault = blank($keputusan)
                    || ($saran !== null && $keputusan !== $saran)
                    || $errors->has('units.'.$unit->id.'.*');
            @endphp
            <div class="card mb-2">
                <div class="card-header"
                     data-bs-toggle="collapse"
                     data-bs-target="#unit-{{ $unit->id }}"
                     role="button"
                     aria-expanded="{{ $bukaDefault ? 'true' : 'false' }}"
                     aria-controls="unit-{{ $unit->id }}"
                     style="cursor: pointer;">
                    <div class="d-flex align-items-center justify-content-between w-100 pe-3">
                        <div>
                            <h3 class="mb-0 fw-semibold fs-5">
                                <span class="text-secondary small me-2">{{ $unit->kode_unit }}</span>
                                {{ $unit->judul_unit }}
                            </h3>
                            <div class="small text-secondary mt-1">
                                {{ $kukUnit->count() }} KUK · {{ $kukUnitDinilai }} dinilai
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 ms-3">
                            @if ($saran !== null && $keputusan && $keputusan !== $saran)
                                <span class="badge bg-orange-lt text-orange" title="Keputusan berbeda dari saran otomatis">
                                    <i class="ti ti-alert-circle me-1"></i> Perlu catatan
                                </span>
                            @endif
                            <span class="badge {{ $unitBadgeClass }}">{{ $unitBadgeLabel }}</span>
                            <i class="ti ti-chevron-down text-secondary collapse-icon" style="transition: transform 0.2s;"></i>
                        </div>
                    </div>
                </div>

                <div id="unit-{{ $unit->id }}" class="collapse {{ $bukaDefault ? 'show' : '' }}">
                    <div class="card-body pt-0">

                        {{-- (B) DAFTAR ELEMEN & PENILAIAN --}}
                        <h4 class="fw-semibold fs-6 mb-2 text-secondary text-uppercase tracking-wide">
                            <i class="ti ti-list-check me-1"></i> Elemen Kompetensi
                        </h4>

                        @foreach ($unit->elemenKompetensis as $elemen)
                            @php
                                $kukElemen = $elemen->kriteriaUnjukKerjas->pluck('id');
                                $hasilKukElemen = $hasilKuks->only($kukElemen->all());
                                $tercapaiCount = $hasilKukElemen->filter(fn ($k) => $k->tercapai === true)->count();
                                $totalKukElemen = $kukElemen->count();
                                
                                // Status elemen: jika ada hasil, set. Jika tidak, default belum dinilai
                                $elemenTercapai = old('elemenTercapai.'.$elemen->id,
                                    $hasilKukElemen->count() > 0 ? (
                                        $tercapaiCount === $totalKukElemen ? '1' : '0'
                                    ) : ''
                                );
                            @endphp
                            <fieldset class="border rounded p-3 mb-3 @if($elemenTercapai === '1') border-success-subtle bg-success-lt @elseif($elemenTercapai === '0') border-danger-subtle bg-danger-lt @endif">
                                <legend class="fw-semibold small float-none w-auto px-1 mb-2">
                                    {{ $elemen->nama_elemen }}
                                    <span class="text-secondary fw-normal">({{ $totalKukElemen }} KUK)</span>
                                </legend>

                                {{-- KUK list untuk referensi --}}
                                <div class="text-secondary small mb-3">
                                    <strong>Kriteria Unjuk Kerja:</strong>
                                    <ul class="mb-0 ps-3">
                                        @foreach ($elemen->kriteriaUnjukKerjas as $kuk)
                                            <li>{{ $kuk->pernyataan_kuk }}</li>
                                        @endforeach
                                    </ul>
                                </div>

                                {{-- Bukti asesi per elemen dari APL-02 --}}
                                @php 
                                    $firstKuk = $elemen->kriteriaUnjukKerjas->first();
                                    $apl02Jaw = $firstKuk ? $apl02Jawabans->get($firstKuk->id) : null;
                                @endphp
                                @if ($apl02Jaw && filled($apl02Jaw->bukti_asesi))
                                    <div class="small text-secondary mb-3 p-2 bg-light rounded">
                                        <i class="ti ti-quote me-1"></i>
                                        <span class="fw-medium">Bukti dari APL-02:</span>
                                        {{ $apl02Jaw->bukti_asesi }}
                                    </div>
                                @endif

                                {{-- Penilaian elemen --}}
                                <div class="row g-2 align-items-center mb-3">
                                    <div class="col-auto">
                                        <div class="d-flex gap-2">
                                            <label class="form-check mb-0 d-flex align-items-center gap-1"
                                                   for="elemen-ya-{{ $elemen->id }}">
                                                <input type="radio"
                                                       id="elemen-ya-{{ $elemen->id }}"
                                                       class="form-check-input"
                                                       name="elemenTercapai[{{ $elemen->id }}]"
                                                       value="1"
                                                       @checked($elemenTercapai === '1')
                                                       @disabled($sudahFinal)>
                                                <span class="form-check-label badge bg-success-lt text-success fw-medium">
                                                    <i class="ti ti-check me-1"></i> Semua tercapai
                                                </span>
                                            </label>
                                            <label class="form-check mb-0 d-flex align-items-center gap-1"
                                                   for="elemen-tidak-{{ $elemen->id }}">
                                                <input type="radio"
                                                       id="elemen-tidak-{{ $elemen->id }}"
                                                       class="form-check-input"
                                                       name="elemenTercapai[{{ $elemen->id }}]"
                                                       value="0"
                                                       @checked($elemenTercapai === '0')
                                                       @disabled($sudahFinal)>
                                                <span class="form-check-label badge bg-danger-lt text-danger fw-medium">
                                                    <i class="ti ti-x me-1"></i> Belum tercapai
                                                </span>
                                            </label>
                                            <label class="form-check mb-0 d-flex align-items-center gap-1"
                                                   for="elemen-belum-{{ $elemen->id }}">
                                                <input type="radio"
                                                       id="elemen-belum-{{ $elemen->id }}"
                                                       class="form-check-input"
                                                       name="elemenTercapai[{{ $elemen->id }}]"
                                                       value=""
                                                       @checked($elemenTercapai === '' || $elemenTercapai === null)
                                                       @disabled($sudahFinal)>
                                                <span class="form-check-label badge bg-secondary-lt text-secondary fw-medium">
                                                    Belum dinilai
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                {{-- Catatan & 4C per elemen --}}
                                <label class="form-label fw-semibold small">Catatan Penilaian</label>
                                <textarea class="form-control form-control-sm mb-2"
                                          name="elemenCatatan[{{ $elemen->id }}]"
                                          rows="2"
                                          maxlength="1000"
                                          placeholder="Catatan asesor untuk elemen ini"
                                          @disabled($sudahFinal)>{{ old('elemenCatatan.'.$elemen->id, $hasilKukElemen->first()?->catatan) }}</textarea>

                                <label class="form-label fw-semibold small">Empat Bukti Kompetensi (4C)</label>
                                <div class="d-flex flex-wrap gap-3">
                                    @foreach (['valid' => 'Valid', 'autentik' => 'Autentik', 'terkini' => 'Terkini', 'memadai' => 'Memadai'] as $key => $label)
                                        <label class="form-check">
                                            <input type="checkbox" class="form-check-input"
                                                   name="elemen4C[{{ $elemen->id }}][{{ $key }}]" value="1"
                                                   @checked(old('elemen4C.'.$elemen->id.'.'.$key, $hasilKukElemen->first()?->{$key}))
                                                   @disabled($sudahFinal)>
                                            <span class="form-check-label">{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>
                        @endforeach

                        {{-- (C) KEPUTUSAN UNIT --}}
                        <div class="row g-3 mt-2 pt-2 border-top">
                            <div class="col-md-5">
                                <label class="form-label fw-semibold required"
                                       for="keputusan-unit-{{ $unit->id }}">
                                    Keputusan Unit Kompetensi
                                </label>
                                @if ($saran !== null)
                                    <div class="small text-secondary mb-1">
                                        Saran otomatis:
                                        <span class="fw-semibold {{ $saran === 'kompeten' ? 'text-success' : 'text-danger' }}">
                                            {{ $keputusanLabels[$saran] }}
                                        </span>
                                    </div>
                                @else
                                    <div class="small text-secondary mb-1">
                                        Saran akan muncul setelah semua KUK dinilai.
                                    </div>
                                @endif
                                <select id="keputusan-unit-{{ $unit->id }}"
                                        name="units[{{ $unit->id }}][keputusan]"
                                        class="form-select"
                                        @disabled($sudahFinal)>
                                    <option value="">— Pilih keputusan —</option>
                                    @foreach ($keputusanLabels as $val => $lbl)
                                        <option value="{{ $val }}"
                                            @selected($keputusan === $val)>
                                            {{ $lbl }}
                                            @if ($val === $saran) ← saran @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('units.'.$unit->id.'.keputusan')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-7">
                                <label class="form-label fw-semibold"
                                       for="catatan-unit-{{ $unit->id }}">
                                    Catatan
                                    @if ($saran !== null && $keputusan && $keputusan !== $saran)
                                        <span class="text-danger">*</span>
                                        <span class="text-danger small">(wajib karena berbeda dari saran)</span>
                                    @endif
                                </label>
                                <textarea id="catatan-unit-{{ $unit->id }}"
                                          name="units[{{ $unit->id }}][catatan]"
                                          class="form-control form-control-sm"
                                          rows="2"
                                          maxlength="2000"
                                          placeholder="Catatan keputusan asesor"
                                          @disabled($sudahFinal)>{{ old('units.'.$unit->id.'.catatan', $hasilUnit?->catatan) }}</textarea>
                                @error('units.'.$unit->id.'.catatan')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ===== KARTU REKOMENDASI & FINALISASI ===== --}}
    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">
                <i class="ti ti-award me-2"></i> Rekomendasi & Finalisasi
            </h3>
        </div>
        <div class="card-body">
            @if ($sudahFinal)
                {{-- Mode read-only setelah final --}}
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="text-secondary small">Rekomendasi</div>
                        <div class="fw-semibold fs-5">
                            @if ($hasil->rekomendasi === HasilAsesmen::REKOMENDASI_DIREKOMENDASIKAN)
                                <span class="text-success"><i class="ti ti-check-circle me-1"></i>Direkomendasikan (Kompeten)</span>
                            @else
                                <span class="text-danger"><i class="ti ti-x-circle me-1"></i>Tidak Direkomendasikan</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-secondary small">KUK Tercapai</div>
                        <div class="fw-semibold fs-5">{{ number_format($hasil->persen_kompeten, 1, ',', '.') }}%</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-secondary small">Umpan Balik</div>
                        <div class="fw-semibold">{{ $hasil->umpan_balik_diberikan ? 'Sudah diberikan' : 'Belum' }}</div>
                    </div>
                </div>
                <div class="alert alert-info mt-3 mb-0 small">
                    <i class="ti ti-lock me-1"></i>
                    Laporan terkunci. Untuk mengajukan perubahan, hubungi admin LSP.
                </div>
            @else
                {{-- REKOMENDASI --}}
                <fieldset class="mb-3">
                    <legend class="form-label required fw-semibold">Rekomendasi</legend>
                    <div class="d-flex gap-2 text-secondary small mb-1">
                        Saran otomatis:
                        <strong class="{{ $rekomendasiSaran === HasilAsesmen::REKOMENDASI_DIREKOMENDASIKAN ? 'text-success' : 'text-danger' }}">
                            {{ $rekomendasiSaran === HasilAsesmen::REKOMENDASI_DIREKOMENDASIKAN ? 'Direkomendasikan' : 'Tidak Direkomendasikan' }}
                        </strong>
                    </div>
                    <div class="form-selectgroup">
                        <label class="form-selectgroup-item flex-fill">
                            <input type="radio"
                                   name="rekomendasi"
                                   value="{{ HasilAsesmen::REKOMENDASI_DIREKOMENDASIKAN }}"
                                   class="form-selectgroup-input"
                                   @checked(old('rekomendasi', $rekomendasiSaran) === HasilAsesmen::REKOMENDASI_DIREKOMENDASIKAN)>
                            <span class="form-selectgroup-label text-center">
                                <i class="ti ti-check-circle me-1 text-success"></i>
                                Direkomendasikan (Kompeten)
                            </span>
                        </label>
                        <label class="form-selectgroup-item flex-fill">
                            <input type="radio"
                                   name="rekomendasi"
                                   value="{{ HasilAsesmen::REKOMENDASI_TIDAK }}"
                                   class="form-selectgroup-input"
                                   @checked(old('rekomendasi', $rekomendasiSaran) === HasilAsesmen::REKOMENDASI_TIDAK)>
                            <span class="form-selectgroup-label text-center">
                                <i class="ti ti-x-circle me-1 text-danger"></i>
                                Tidak Direkomendasikan
                            </span>
                        </label>
                    </div>
                    @error('rekomendasi')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </fieldset>

                {{-- UMPAN BALIK --}}
                <fieldset class="mb-3">
                    <legend class="form-label fw-semibold required">Umpan Balik</legend>
                    <label class="form-check" for="umpan-balik">
                        <input type="checkbox"
                               id="umpan-balik"
                               class="form-check-input"
                               name="umpan_balik"
                               value="1"
                               @checked(old('umpan_balik'))>
                        <span class="form-check-label">
                            Umpan balik dan penjelasan hasil asesmen telah disampaikan kepada asesi
                        </span>
                    </label>
                    @error('umpan_balik')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </fieldset>

                {{-- PRASYARAT FINALISASI --}}
                @php
                    $unitTanpaKeputusan = $hasilUnits->filter(fn ($u) => blank($u->keputusan))->count();
                    $unitBelumAda = $totalUnit - $hasilUnits->count();
                    $kukBelumDinilai = $totalKuk - $kukDinilai;
                    $bisaFinal = $unitTanpaKeputusan === 0 && $unitBelumAda === 0
                        && $kukBelumDinilai === 0 && $apl02Terverifikasi;
                @endphp

                @if (! $bisaFinal)
                    <div class="alert alert-warning small mb-3">
                        <div class="fw-semibold mb-1"><i class="ti ti-list-check me-1"></i> Prasyarat finalisasi belum terpenuhi:</div>
                        <ul class="mb-0 ps-3">
                            @if (! $apl02Terverifikasi)
                                <li>APL-02 belum diverifikasi asesor</li>
                            @endif
                            @if ($kukBelumDinilai > 0)
                                <li>{{ $kukBelumDinilai }} KUK belum dinilai</li>
                            @endif
                            @if ($unitTanpaKeputusan + $unitBelumAda > 0)
                                <li>{{ $unitTanpaKeputusan + $unitBelumAda }} unit belum memiliki keputusan</li>
                            @endif
                        </ul>
                    </div>
                @endif

                {{-- TOMBOL --}}
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-outline-success"
                            onclick="semuaTercapai()">
                        <i class="ti ti-check-all me-1"></i> Semua Tercapai
                    </button>
                    <button type="submit" form="form-mak02" class="btn btn-outline-primary">
                        <i class="ti ti-device-floppy me-1"></i> Simpan Draf
                    </button>
                    <button type="button"
                            class="btn btn-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#modal-finalisasi"
                            @disabled(! $bisaFinal)
                            @if(! $bisaFinal) title="Penuhi semua prasyarat terlebih dahulu" @endif>
                        <i class="ti ti-lock me-1"></i> Finalkan FR.AK.02
                    </button>
                </div>
            @endif
        </div>
    </div>

</form>

{{-- ===== MODAL KONFIRMASI FINALISASI (Vanilla Bootstrap) ===== --}}
<div class="modal fade" id="modal-finalisasi" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1061 !important;">
    <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable" role="document" style="z-index: 1061 !important;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Finalisasi FR.AK.02</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('asesor.mak02.final', $pendaftaran) }}" id="form-finalisasi">
                @csrf
                <div class="modal-body">
                    {{-- Error dari validasi server (tampil di dalam modal) --}}
                    @error('rekomendasi')
                        <div class="alert alert-danger small mb-3">{{ $message }}</div>
                    @enderror
                    @error('umpan_balik')
                        <div class="alert alert-danger small mb-3">{{ $message }}</div>
                    @enderror
                    @error('konfirmasi')
                        <div class="alert alert-danger small mb-3">{{ $message }}</div>
                    @enderror
                    @error('hasil')
                        <div class="alert alert-danger small mb-3">{{ $message }}</div>
                    @enderror
                    @error('kuks')
                        <div class="alert alert-danger small mb-3">{{ $message }}</div>
                    @enderror

                    {{-- Ringkasan keputusan per unit --}}
                    <div class="mb-3">
                        <div class="fw-semibold mb-2">Ringkasan Keputusan Per Unit</div>
                        <div class="table-responsive">
                            <table class="table table-sm table-vcenter">
                                <thead class="table-light">
                                    <tr>
                                        <th>Unit Kompetensi</th>
                                        <th>KUK</th>
                                        <th>Keputusan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($units as $unit)
                                        @php
                                            $hu = $hasilUnits->get($unit->id);
                                            $kukIds = $unit->elemenKompetensis
                                                ->flatMap(fn ($e) => $e->kriteriaUnjukKerjas)
                                                ->pluck('id');
                                            $yaCount = $hasilKuks->only($kukIds->all())
                                                ->filter(fn ($k) => $k->tercapai === true)->count();
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="fw-medium small">{{ $unit->kode_unit }}</div>
                                                <div class="text-secondary" style="font-size:.8rem">{{ Str::limit($unit->judul_unit, 50) }}</div>
                                            </td>
                                            <td class="small">{{ $yaCount }}/{{ $kukIds->count() }} tercapai</td>
                                            <td>
                                                @if ($hu && filled($hu->keputusan))
                                                    <span class="badge {{ match($hu->keputusan) {
                                                        'kompeten' => 'bg-success-lt text-success',
                                                        'belum_kompeten' => 'bg-danger-lt text-danger',
                                                        default => 'bg-warning-lt text-warning'
                                                    } }}">
                                                        {{ $keputusanLabels[$hu->keputusan] ?? $hu->keputusan }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary-lt text-secondary">Belum diputuskan</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Rekomendasi --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold required" for="modal-rekomendasi">Rekomendasi</label>
                        <select id="modal-rekomendasi" name="rekomendasi" class="form-select" required>
                            <option value="">— Pilih —</option>
                            <option value="{{ HasilAsesmen::REKOMENDASI_DIREKOMENDASIKAN }}"
                                @selected(old('rekomendasi', $rekomendasiSaran) === HasilAsesmen::REKOMENDASI_DIREKOMENDASIKAN)>
                                Direkomendasikan (Kompeten)
                            </option>
                            <option value="{{ HasilAsesmen::REKOMENDASI_TIDAK }}"
                                @selected(old('rekomendasi', $rekomendasiSaran) === HasilAsesmen::REKOMENDASI_TIDAK)>
                                Tidak Direkomendasikan
                            </option>
                        </select>
                        @error('rekomendasi')
                            <div class="text-danger small mt-n2 mb-2"><i class="ti ti-alert-circle me-1"></i>{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Umpan Balik --}}
                    <label class="form-check mb-3" for="modal-umpan-balik">
                        <input type="checkbox" id="modal-umpan-balik" class="form-check-input"
                               name="umpan_balik" value="1"
                               @checked(old('umpan_balik'))>
                        <span class="form-check-label">
                            Umpan balik dan penjelasan hasil asesmen telah disampaikan kepada asesi
                        </span>
                    </label>
                    @error('umpan_balik')
                        <div class="text-danger small mt-n2 mb-2"><i class="ti ti-alert-circle me-1"></i>{{ $message }}</div>
                    @enderror

                    {{-- Konfirmasi penguncian --}}
                    <label class="form-check mb-4" for="modal-konfirmasi">
                        <input type="checkbox" id="modal-konfirmasi" class="form-check-input"
                               name="konfirmasi" value="1"
                               @checked(old('konfirmasi'))>
                        <span class="form-check-label">
                            Saya memahami laporan FR.AK.02 <strong>terkunci permanen</strong> setelah difinalkan.
                            Perubahan berikutnya wajib melalui admin LSP dan tercatat di audit log.
                        </span>
                    </label>
                    @error('konfirmasi')
                        <div class="text-danger small mt-n2 mb-2"><i class="ti ti-alert-circle me-1"></i>{{ $message }}</div>
                    @enderror
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-lock me-1"></i> Ya, Finalkan Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
/**
 * Auto-close modal finalisasi saat page load jika ada success message.
 * Indikasi finalisasi berhasil.
 */
document.addEventListener('DOMContentLoaded', function () {
    var successAlert = document.querySelector('.alert-success');
    var modal = document.getElementById('modal-finalisasi');
    if (successAlert && modal) {
        // Tunggu bootstrap tersedia (max 100ms)
        var attempts = 0;
        var checkBootstrap = setInterval(function () {
            attempts++;
            if (typeof bootstrap !== 'undefined') {
                clearInterval(checkBootstrap);
                var bsModal = bootstrap.Modal.getOrCreateInstance(modal);
                if (bsModal) { bsModal.hide(); }
            } else if (attempts >= 10) {
                // Fallback: tutup dengan vanilla JS (backdrop + aria-hidden)
                clearInterval(checkBootstrap);
                modal.classList.remove('show');
                modal.style.display = 'none';
                var backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) { backdrop.remove(); }
                document.body.classList.remove('modal-open');
            }
        }, 10);
    }
});

/**
 * Buka ulang modal finalisasi jika server mengembalikan error
 * dari field yang ada di modal (rekomendasi, umpan_balik, konfirmasi).
 */
@if ($errors->hasAny(['rekomendasi', 'umpan_balik', 'konfirmasi', 'hasil', 'kuks', 'units', 'elemenTercapai', 'elemenCatatan', 'elemen4C', 'metode_default']))
document.addEventListener('DOMContentLoaded', function () {
    var el = document.getElementById('modal-finalisasi');
    if (el) { new bootstrap.Modal(el).show(); }
});
@endif

/**
 * Sinkron pilihan rekomendasi dari form utama ke modal finalisasi.
 */
document.querySelectorAll('input[name="rekomendasi"]').forEach(function (radio) {
    radio.addEventListener('change', function () {
        var modalSelect = document.getElementById('modal-rekomendasi');
        if (modalSelect) { modalSelect.value = this.value; }
    });
});

/**
 * Set semua radio elemenTercapai ke "Semua tercapai" (value="1") — hanya centang, tidak submit.
 */
function semuaTercapai() {
    document.querySelectorAll('input[name^="elemenTercapai["][value="1"]').forEach(function (el) {
        if (!el.disabled) { el.checked = true; }
    });
}
</script>
@endpush
