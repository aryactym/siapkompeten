@extends('layouts.asesi')

@section('title', 'Asesmen Mandiri APL-02')
@section('pretitle', 'Asesmen Mandiri')
@section('page-title', 'Formulir APL-02')

@section('actions')
    <a href="{{ route('asesi.pembayaran.show', $pendaftaran) }}" class="btn btn-outline-secondary d-none d-sm-inline-block">
        <i class="ti ti-cash me-1"></i> Pembayaran
    </a>
    <a href="{{ route('asesi.apl02.pdf', $pendaftaran) }}" class="btn btn-outline-primary">
        <i class="ti ti-file-pdf me-1"></i> Unduh PDF
    </a>
@endsection

@section('content')
@if (! $dapatDiisi)
    <div class="alert alert-success">
        <div class="d-flex">
            <div><i class="ti ti-circle-check alert-icon fs-2 me-2"></i></div>
            <div>
                <div class="alert-title fw-semibold">
                    {{ $penilaian->status === \App\Models\Apl02Penilaian::STATUS_DIVERIFIKASI
                        ? 'APL-02 Terverifikasi Asesor'
                        : 'APL-02 Sudah Diserahkan' }}
                </div>
                <div class="text-secondary">
                    Formulir dikunci. {{ $penilaian->diserahkan_pada?->translatedFormat('d F Y H:i') ?? '' }}
                    @if ($penilaian->status === \App\Models\Apl02Penilaian::STATUS_DIVERIFIKASI && $penilaian->diverifikasi_pada)
                        · Diverifikasi {{ $penilaian->diverifikasi_pada->translatedFormat('d F Y H:i') }}
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif

<form method="POST"
      action="{{ route('asesi.apl02.store', $pendaftaran) }}"
      @if (! $dapatDiisi) data-readonly @endif>
    @csrf

    @foreach ($units as $unit)
        <x-tabler.card title="{{ $unit->kode_unit }} · {{ $unit->judul_unit }}" class="mb-3">
            <p class="text-secondary mb-3">{{ $unit->deskripsi }}</p>

            @foreach ($unit->elemenKompetensis as $elemen)
                <h3 class="h5 mt-3">{{ $elemen->nama_elemen }}</h3>

                @foreach ($elemen->kriteriaUnjukKerjas as $kuk)
                    @php $jawaban = $jawabans->get($kuk->id); @endphp
                    <div class="card mb-2">
                        <div class="card-body">
                            <div class="fw-semibold">{{ $kuk->pernyataan_kuk }}</div>

                            <div class="mt-2">
                                <div class="text-secondary small mb-1">Saya menyatakan mampu memenuhi KUK ini:</div>
                                <div class="form-selectgroup form-selectgroup-pills">
                                    <label class="form-selectgroup-item">
                                        <input type="radio" name="jawaban[{{ $kuk->id }}]" value="1"
                                               class="form-selectgroup-input"
                                               @checked(old('jawaban.'.$kuk->id, $jawaban?->jawaban_asesi))
                                               @disabled(! $dapatDiisi)>
                                        <span class="form-selectgroup-label">Ya, mampu</span>
                                    </label>
                                    <label class="form-selectgroup-item">
                                        <input type="radio" name="jawaban[{{ $kuk->id }}]" value="0"
                                               class="form-selectgroup-input"
                                               @checked(old('jawaban.'.$kuk->id, $jawaban !== null && ! $jawaban->jawaban_asesi))
                                               @disabled(! $dapatDiisi)>
                                        <span class="form-selectgroup-label">Belum mampu</span>
                                    </label>
                                </div>
                            </div>

                            <div class="mt-2">
                                <label class="form-label">Bukti / penjelasan singkat</label>
                                <textarea name="bukti[{{ $kuk->id }}]" rows="2" class="form-control"
                                          maxlength="1000"
                                          @disabled(! $dapatDiisi)
                                          placeholder="Contoh: sertifikat pelatihan, portofolio, pengalaman kerja">{{ old('bukti.'.$kuk->id, $jawaban?->bukti_asesi) }}</textarea>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endforeach
        </x-tabler.card>
    @endforeach

    @if ($dapatDiisi)
        <div class="d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-outline-primary">
                <i class="ti ti-device-floppy me-1"></i> Simpan Draf
            </button>
            <button type="submit" formaction="{{ route('asesi.apl02.serah', $pendaftaran) }}"
                    class="btn btn-primary"
                    onclick="return confirm('Serahkan APL-02? Formulir akan terkunci setelah diserahkan.')">
                <i class="ti ti-send me-1"></i> Serahkan APL-02
            </button>
        </div>
    @endif
</form>
@endsection
