@extends('layouts.asesor')

@section('title', 'Input Hasil MAK-02')
@section('pretitle', 'Hasil Asesmen')
@section('page-title', 'MAK-02 · '.$pendaftaran->no_pendaftaran)

@section('actions')
    <a href="{{ route('asesor.apl02.show', $pendaftaran) }}" class="btn btn-outline-secondary">
        <i class="ti ti-list-check me-1"></i> APL-02
    </a>
    <a href="{{ route('asesor.mak02.pdf', $pendaftaran) }}" class="btn btn-outline-primary">
        <i class="ti ti-file-pdf me-1"></i> Unduh PDF
    </a>
@endsection

@section('content')
@php
    $sudahFinal = $hasil->isFinal();
@endphp

@if ($sudahFinal)
    <div class="alert alert-success">
        <div class="d-flex">
            <div><i class="ti ti-certificate alert-icon fs-2 me-2"></i></div>
            <div>
                <div class="alert-title fw-semibold">MAK-02 Final</div>
                <div class="text-secondary">
                    Asesi dinyatakan {{ number_format($hasil->persen_kompeten, 0, ',', '.') }}%
                    {{ $hasil->rekomendasi === \App\Models\HasilAsesmen::REKOMENDASI_DIREKOMENDASIKAN ? 'KOMPETEN' : 'BELUM KOMPETEN' }}
                    · {{ $hasil->finalized_at?->translatedFormat('d F Y H:i') }}
                </div>
            </div>
        </div>
    </div>
@endif

@if (! $apl02Terverifikasi && ! $sudahFinal)
    <div class="alert alert-warning">
        <i class="ti ti-alert-triangle me-1"></i>
        APL-02 belum diverifikasi. Draf dapat disimpan, tetapi finalisasi MAK-02 diblokir sampai APL-02 diverifikasi.
    </div>
@endif

<form method="POST" action="{{ route('asesor.mak02.store', $pendaftaran) }}">
    @csrf

    @foreach ($units as $unit)
        <x-tabler.card title="{{ $unit->kode_unit }} · {{ $unit->judul_unit }}" class="mb-3">
            @php $hasilUnit = $hasilUnits->get($unit->id); @endphp

            <div class="row g-3 align-items-end mb-3">
                <div class="col-md-6">
                    <label class="form-label">Keputusan unit kompetensi</label>
                    <select name="units[{{ $unit->id }}][keputusan]" class="form-select" @disabled($sudahFinal)>
                        <option value="">— Pilih keputusan —</option>
                        @foreach (['kompeten' => 'Kompeten', 'belum_kompeten' => 'Belum Kompeten', 'asesmen_lanjut' => 'Asesmen Lanjut'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('units.'.$unit->id.'.keputusan', $hasilUnit?->keputusan) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Capaian unit</label>
                    <label class="form-check">
                        <input type="checkbox" class="form-check-input"
                               name="units[{{ $unit->id }}][pencapaian]" value="1"
                               @checked(old('units.'.$unit->id.'.pencapaian', $hasilUnit?->pencapaian))
                               @disabled($sudahFinal)>
                        <span class="form-check-label">Seluruh elemen tercapai</span>
                    </label>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-vcenter">
                    <thead>
                        <tr>
                            <th>Metode Asesmen</th>
                            <th class="text-center">Digunakan</th>
                            <th class="text-center">Bukti Langsung</th>
                            <th class="text-center">Bukti Tidak Langsung</th>
                            <th class="text-center">Bukti Tambahan</th>
                            <th class="text-center">Pencapaian</th>
                            <th>Keputusan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (\App\Models\HasilAsesmenMetode::METODES as $metode)
                            @php $hasilMetode = $hasilUnit?->metodes->firstWhere('metode', $metode); @endphp
                            <tr>
                                <td class="fw-semibold">
                                    {{ ['observasi' => 'Observasi', 'tes_lisan' => 'Tes Lisan', 'tes_tertulis' => 'Tes Tertulis'][$metode] }}
                                </td>
                                @foreach (['digunakan', 'bukti_langsung', 'bukti_tidak_langsung', 'bukti_tambahan', 'pencapaian'] as $flag)
                                    <td class="text-center">
                                        <input type="hidden" name="units[{{ $unit->id }}][metode][{{ $metode }}][{{ $flag }}]" value="0">
                                        <input type="checkbox" class="form-check-input mt-0"
                                               name="units[{{ $unit->id }}][metode][{{ $metode }}][{{ $flag }}]" value="1"
                                               @checked(old('units.'.$unit->id.'.metode.'.$metode.'.'.$flag, $hasilMetode?->{$flag}))
                                               @disabled($sudahFinal)>
                                    </td>
                                @endforeach
                                <td>
                                    <select name="units[{{ $unit->id }}][metode][{{ $metode }}][keputusan]" class="form-select form-select-sm" @disabled($sudahFinal)>
                                        <option value="">—</option>
                                        @foreach (['kompeten' => 'Kompeten', 'belum_kompeten' => 'Belum Kompeten', 'asesmen_lanjut' => 'Lanjut'] as $value => $label)
                                            <option value="{{ $value }}" @selected(old('units.'.$unit->id.'.metode.'.$metode.'.keputusan', $hasilMetode?->keputusan) === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @foreach ($unit->elemenKompetensis as $elemen)
                <h3 class="h6 mt-3 text-secondary">{{ $elemen->nama_elemen }}</h3>

                @foreach ($elemen->kriteriaUnjukKerjas as $kuk)
                    @php $hasilKuk = $hasilKuks->get($kuk->id); @endphp
                    <div class="card mb-2">
                        <div class="card-body">
                            <div class="fw-semibold">{{ $kuk->pernyataan_kuk }}</div>

                            <div class="row g-3 mt-1 align-items-end">
                                <div class="col-md-5">
                                    <label class="form-label">Nomor bukti</label>
                                    <input type="text" class="form-control" maxlength="60"
                                           name="kuks[{{ $kuk->id }}][bukti_no]"
                                           value="{{ old('kuks.'.$kuk->id.'.bukti_no', $hasilKuk?->bukti_no) }}"
                                           @disabled($sudahFinal)
                                           placeholder="cth. BUKTI-001">
                                </div>
                                <div class="col-md-7">
                                    <label class="form-label d-block">4C asesor</label>
                                    <div class="d-flex flex-wrap gap-3">
                                        @foreach (['valid' => 'Valid', 'autentik' => 'Autentik', 'terkini' => 'Terkini', 'memadai' => 'Memadai'] as $key => $label)
                                            <label class="form-check">
                                                <input type="hidden" name="kuks[{{ $kuk->id }}][{{ $key }}]" value="0">
                                                <input type="checkbox" class="form-check-input"
                                                       name="kuks[{{ $kuk->id }}][{{ $key }}]" value="1"
                                                       @checked(old('kuks.'.$kuk->id.'.'.$key, $hasilKuk?->{$key}))
                                                       @disabled($sudahFinal)>
                                                <span class="form-check-label">{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-check">
                                        <input type="hidden" name="kuks[{{ $kuk->id }}][pencapaian]" value="0">
                                        <input type="checkbox" class="form-check-input"
                                               name="kuks[{{ $kuk->id }}][pencapaian]" value="1"
                                               @checked(old('kuks.'.$kuk->id.'.pencapaian', $hasilKuk?->pencapaian))
                                               @disabled($sudahFinal)>
                                        <span class="form-check-label">KUK tercapai (Ya)</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endforeach
        </x-tabler.card>
    @endforeach

    <x-tabler.card title="Rekomendasi & Finalisasi" class="mb-3">
        @if ($sudahFinal)
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="text-secondary small">Rekomendasi</div>
                    <div class="fw-semibold">{{ $hasil->rekomendasi === \App\Models\HasilAsesmen::REKOMENDASI_DIREKOMENDASIKAN ? 'Direkomendasikan (Kompeten)' : 'Tidak Direkomendasikan' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-secondary small">Persentase KUK tercapai</div>
                    <div class="fw-semibold">{{ number_format($hasil->persen_kompeten, 2, ',', '.') }}%</div>
                </div>
            </div>
        @else
            <div class="mb-3">
                <label class="form-label required">Rekomendasi</label>
                <div class="form-selectgroup form-selectgroup-pills">
                    <label class="form-selectgroup-item">
                        <input type="radio" name="rekomendasi" value="direkomendasikan" class="form-selectgroup-input"
                               @checked(old('rekomendasi') === 'direkomendasikan')>
                        <span class="form-selectgroup-label">Direkomendasikan (Kompeten)</span>
                    </label>
                    <label class="form-selectgroup-item">
                        <input type="radio" name="rekomendasi" value="tidak_direkomendasikan" class="form-selectgroup-input"
                               @checked(old('rekomendasi') === 'tidak_direkomendasikan')>
                        <span class="form-selectgroup-label">Tidak Direkomendasikan</span>
                    </label>
                </div>
                @error('rekomendasi')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <label class="form-check mb-3">
                <input type="hidden" name="umpan_balik" value="0">
                <input type="checkbox" class="form-check-input" name="umpan_balik" value="1"
                       @checked(old('umpan_balik'))>
                <span class="form-check-label">Umpan balik telah diberikan kepada asesi</span>
            </label>

            <label class="form-check mb-3">
                <input type="checkbox" class="form-check-input" name="konfirmasi" value="1"
                       @checked(old('konfirmasi'))>
                <span class="form-check-label">
                    Saya memahami laporan MAK-02 terkunci setelah difinalkan dan perubahan berikutnya wajib diaudit admin.
                </span>
            </label>
            @error('konfirmasi')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        @endif

        <div class="d-flex flex-wrap gap-2">
            @unless ($sudahFinal)
                <button type="submit" class="btn btn-outline-primary">
                    <i class="ti ti-device-floppy me-1"></i> Simpan Draf
                </button>
                <button type="submit" formaction="{{ route('asesor.mak02.final', $pendaftaran) }}"
                        class="btn btn-primary"
                        onclick="return confirm('Finalkan MAK-02? Data terkunci.')">
                    <i class="ti ti-lock me-1"></i> Finalkan MAK-02
                </button>
            @endunless

            @if (session('success'))
                <span class="d-none">{{ session('success') }}</span>
            @endif
        </div>
    </x-tabler.card>
</form>
@endsection
