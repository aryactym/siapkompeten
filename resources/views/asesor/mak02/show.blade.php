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

    {{-- Validasi error (tangkap nested fields units.* / kuks.*) --}}
    @if ($errors->hasAny(['units.*', 'kuks.*', 'hasil', 'kuks', 'units']))
        <div class="alert alert-danger mb-3">
            <div class="fw-semibold mb-1"><i class="ti ti-alert-triangle me-1"></i> Perbaiki kesalahan berikut:</div>
            <ul class="mb-0 ps-3 small">
                @foreach ($errors->get('units.*') ?? [] as $message)
                    <li>{{ $message }}</li>
                @endforeach
                @foreach ($errors->get('kuks.*') ?? [] as $message)
                    <li>{{ $message }}</li>
                @endforeach
                @error('hasil')
                    <li>{{ $message }}</li>
                @enderror
                @error('kuks')
                    <li>{{ $message }}</li>
                @enderror
                @error('units')
                    <li>{{ $message }}</li>
                @enderror
            </ul>
        </div>
    @endif

    {{-- ACCORDION UNIT --}}
    <div class="accordion mb-3" id="accordion-units">
        @foreach ($units as $unitIdx => $unit)
            @php
                $hasilUnit  = $hasilUnits->get($unit->id);
                $saran      = $saranPerUnit[$unit->id] ?? null;
                $keputusan  = old('units.'.$unit->id.'.keputusan', $hasilUnit?->keputusan);

                // Status badge unit untuk accordion header
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
            @endphp
            <div class="card mb-2">
                <div class="card-header" id="heading-unit-{{ $unitIdx }}" style="cursor:pointer;"
                     data-bs-toggle="collapse"
                     data-bs-target="#collapse-unit-{{ $unitIdx }}"
                     aria-expanded="{{ $unitIdx === 0 ? 'true' : 'false' }}"
                     aria-controls="collapse-unit-{{ $unitIdx }}">
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
                            <i class="ti ti-chevron-down text-secondary"></i>
                        </div>
                    </div>
                </div>

                <div id="collapse-unit-{{ $unitIdx }}"
                     class="collapse {{ $unitIdx === 0 ? 'show' : '' }}"
                     aria-labelledby="heading-unit-{{ $unitIdx }}"
                     data-bs-parent="#accordion-units">
                    <div class="card-body pt-0">

                        {{-- (A) TABEL METODE ASESMEN --}}
                        <h4 class="fw-semibold fs-6 mt-3 mb-2 text-secondary text-uppercase tracking-wide">
                            <i class="ti ti-clipboard-list me-1"></i> Metode Asesmen
                        </h4>
                        <div class="table-responsive mb-3">
                            <table class="table table-sm table-vcenter align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">Metode</th>
                                        <th scope="col" class="text-center" style="width:80px;">Digunakan</th>
                                        <th scope="col" class="text-center" style="width:80px;">Bukti Langsung</th>
                                        <th scope="col" class="text-center" style="width:90px;">Bukti Tdk Langsung</th>
                                        <th scope="col" class="text-center" style="width:80px;">Bukti Tambahan</th>
                                        <th scope="col" style="width:130px;">Keputusan</th>
                                        <th scope="col">Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($metodeDaftar as $metode)
                                        @php
                                            $hasilMetode = $hasilUnit?->metodes->firstWhere('metode', $metode);
                                            $digunakan   = (bool) old(
                                                'units.'.$unit->id.'.metode.'.$metode.'.digunakan',
                                                $hasilMetode?->digunakan ?? false
                                            );
                                            $mId = 'm-'.$unit->id.'-'.$metode;
                                        @endphp
                                        <tr data-metode-row="{{ $mId }}">
                                            <td class="fw-medium">
                                                <label for="digunakan-{{ $mId }}" class="mb-0">
                                                    {{ $metodeLabels[$metode] ?? ucfirst(str_replace('_', ' ', $metode)) }}
                                                </label>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox"
                                                       id="digunakan-{{ $mId }}"
                                                       class="form-check-input mt-0 metode-digunakan"
                                                       name="units[{{ $unit->id }}][metode][{{ $metode }}][digunakan]"
                                                       value="1"
                                                       @checked($digunakan)
                                                       @disabled($sudahFinal)
                                                       data-row="{{ $mId }}">
                                            </td>
                                            @foreach (['bukti_langsung' => 'BL', 'bukti_tidak_langsung' => 'BTL', 'bukti_tambahan' => 'BT'] as $bField => $bLabel)
                                                <td class="text-center">
                                                    <input type="checkbox"
                                                           class="form-check-input mt-0 metode-bukti"
                                                           name="units[{{ $unit->id }}][metode][{{ $metode }}][{{ $bField }}]"
                                                           value="1"
                                                           @checked(old('units.'.$unit->id.'.metode.'.$metode.'.'.$bField, $hasilMetode?->{$bField}))
                                                           @disabled($sudahFinal || ! $digunakan)
                                                           data-row="{{ $mId }}">
                                                </td>
                                            @endforeach
                                            <td>
                                                <select name="units[{{ $unit->id }}][metode][{{ $metode }}][keputusan]"
                                                        class="form-select form-select-sm metode-bukti"
                                                        @disabled($sudahFinal || ! $digunakan)
                                                        data-row="{{ $mId }}">
                                                    <option value="">—</option>
                                                    @foreach ($keputusanLabels as $val => $lbl)
                                                        <option value="{{ $val }}"
                                                            @selected(old('units.'.$unit->id.'.metode.'.$metode.'.keputusan', $hasilMetode?->keputusan) === $val)>
                                                            {{ $lbl }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text"
                                                       class="form-control form-control-sm metode-bukti"
                                                       name="units[{{ $unit->id }}][metode][{{ $metode }}][catatan]"
                                                       value="{{ old('units.'.$unit->id.'.metode.'.$metode.'.catatan', $hasilMetode?->catatan) }}"
                                                       maxlength="1000"
                                                       placeholder="Catatan opsional"
                                                       @disabled($sudahFinal || ! $digunakan)
                                                       data-row="{{ $mId }}">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- (B) DAFTAR KUK PER ELEMEN --}}
                        <h4 class="fw-semibold fs-6 mb-2 text-secondary text-uppercase tracking-wide">
                            <i class="ti ti-list-check me-1"></i> Kriteria Unjuk Kerja (KUK)
                        </h4>

                        @foreach ($unit->elemenKompetensis as $elemen)
                            <h4 class="h6 fw-semibold mt-3 mb-2 ps-2 border-start border-2 border-primary">
                                {{ $elemen->nama_elemen }}
                            </h4>

                            @foreach ($elemen->kriteriaUnjukKerjas as $kuk)
                                @php
                                    $hasilKuk  = $hasilKuks->get($kuk->id);
                                    $apl02Jaw  = $apl02Jawabans->get($kuk->id);
                                    $tercapai  = old('kuks.'.$kuk->id.'.tercapai',
                                        $hasilKuk?->tercapai === null ? '' :
                                        ($hasilKuk->tercapai ? '1' : '0')
                                    );
                                @endphp
                                <fieldset class="border rounded p-2 mb-2 @if($tercapai === '1') border-success-subtle bg-success-lt @elseif($tercapai === '0') border-danger-subtle bg-danger-lt @endif"
                                          style="border-color: #dee2e6 !important;"
                                          @if($tercapai === '1') style="background: var(--tblr-success-lt)!important;border-color: var(--tblr-success)!important;" @endif>
                                    <legend class="fw-semibold small float-none w-auto px-1 mb-2">
                                        {{ $kuk->pernyataan_kuk }}
                                    </legend>

                                    {{-- Bukti asesi dari APL-02 --}}
                                    @if ($apl02Jaw && filled($apl02Jaw->bukti_asesi))
                                        <div class="small text-secondary mb-2">
                                            <i class="ti ti-quote me-1"></i>
                                            <span class="fw-medium">Bukti dari APL-02:</span>
                                            {{ $apl02Jaw->bukti_asesi }}
                                        </div>
                                    @endif

                                    <div class="row g-2 align-items-center">
                                        {{-- Radio Ya/Tidak --}}
                                        <div class="col-auto">
                                            <div class="d-flex gap-2">
                                                <label class="form-check mb-0 d-flex align-items-center gap-1"
                                                       for="kuk-ya-{{ $kuk->id }}">
                                                    <input type="radio"
                                                           id="kuk-ya-{{ $kuk->id }}"
                                                           class="form-check-input"
                                                           name="kuks[{{ $kuk->id }}][tercapai]"
                                                           value="1"
                                                           @checked($tercapai === '1')
                                                           @disabled($sudahFinal)>
                                                    <span class="form-check-label badge bg-success-lt text-success fw-medium">
                                                        <i class="ti ti-check me-1"></i> Ya, tercapai
                                                    </span>
                                                </label>
                                                <label class="form-check mb-0 d-flex align-items-center gap-1"
                                                       for="kuk-tidak-{{ $kuk->id }}">
                                                    <input type="radio"
                                                           id="kuk-tidak-{{ $kuk->id }}"
                                                           class="form-check-input"
                                                           name="kuks[{{ $kuk->id }}][tercapai]"
                                                           value="0"
                                                           @checked($tercapai === '0')
                                                           @disabled($sudahFinal)>
                                                    <span class="form-check-label badge bg-danger-lt text-danger fw-medium">
                                                        <i class="ti ti-x me-1"></i> Tidak tercapai
                                                    </span>
                                                </label>
                                                <label class="form-check mb-0 d-flex align-items-center gap-1"
                                                       for="kuk-belum-{{ $kuk->id }}">
                                                    <input type="radio"
                                                           id="kuk-belum-{{ $kuk->id }}"
                                                           class="form-check-input"
                                                           name="kuks[{{ $kuk->id }}][tercapai]"
                                                           value=""
                                                           @checked($tercapai === '' || $tercapai === null)
                                                           @disabled($sudahFinal)>
                                                    <span class="form-check-label badge bg-secondary-lt text-secondary fw-medium">
                                                        Belum dinilai
                                                    </span>
                                                </label>
                                            </div>
                                        </div>

                                        {{-- Catatan asesor (collapsible) --}}
                                        <div class="col">
                                            <input type="text"
                                                   class="form-control form-control-sm"
                                                   name="kuks[{{ $kuk->id }}][catatan]"
                                                   value="{{ old('kuks.'.$kuk->id.'.catatan', $hasilKuk?->catatan) }}"
                                                   maxlength="1000"
                                                   placeholder="Catatan asesor (opsional)"
                                                   @disabled($sudahFinal)>
                                        </div>
                                    </div>
                                </fieldset>
                            @endforeach
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

                    </div>{{-- /card-body --}}
                </div>{{-- /collapse --}}
            </div>{{-- /card --}}
        @endforeach
    </div>{{-- /accordion --}}

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

{{-- ===== MODAL KONFIRMASI FINALISASI (Tabler modal, bukan confirm()) ===== --}}
@unless ($sudahFinal)
    <x-tabler.modal id="modal-finalisasi" title="Konfirmasi Finalisasi FR.AK.02" size="lg">
        <form method="POST" action="{{ route('asesor.mak02.final', $pendaftaran) }}" id="form-finalisasi">
            @csrf

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

            {{-- Tombol di dalam form (bukan di footer slot) --}}
            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-lock me-1"></i> Ya, Finalkan Sekarang
                </button>
            </div>
        </form>
    </x-tabler.modal>
@endunless

@endsection

@push('scripts')
<script>
/**
 * Buka ulang modal finalisasi jika server mengembalikan error
 * dari field yang ada di modal (rekomendasi, umpan_balik, konfirmasi).
 */
@if ($errors->hasAny(['rekomendasi', 'umpan_balik', 'konfirmasi', 'hasil', 'kuks']))
document.addEventListener('DOMContentLoaded', function () {
    var el = document.getElementById('modal-finalisasi');
    if (el) { new bootstrap.Modal(el).show(); }
});
@endif

/**
 * Metode asesmen: toggle disabled state untuk input bukti + keputusan
 * saat checkbox "Digunakan" berubah.
 */
document.querySelectorAll('.metode-digunakan').forEach(function (cb) {
    cb.addEventListener('change', function () {
        toggleMetodeRow(this.dataset.row, this.checked);
    });
});

function toggleMetodeRow(rowId, enabled) {
    document.querySelectorAll('[data-row="' + rowId + '"].metode-bukti').forEach(function (el) {
        el.disabled = !enabled;
        if (!enabled) {
            if (el.type === 'checkbox') { el.checked = false; }
            else if (el.tagName === 'SELECT') { el.value = ''; }
            else { el.value = ''; }
        }
    });
}

/**
 * Sinkron pilihan rekomendasi dari form utama ke modal finalisasi.
 */
document.querySelectorAll('input[name="rekomendasi"]').forEach(function (radio) {
    radio.addEventListener('change', function () {
        var modalSelect = document.getElementById('modal-rekomendasi');
        if (modalSelect) { modalSelect.value = this.value; }
    });
});
</script>
@endpush
