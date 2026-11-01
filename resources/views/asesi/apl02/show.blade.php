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
                <div class="card mb-3 border-primary">
                    <div class="card-header bg-primary-lt">
                        <h3 class="h5 m-0">{{ $elemen->nama_elemen }}</h3>
                    </div>
                    <div class="card-body">
                        {{-- Persetujuan per elemen --}}
                        <div class="mb-3">
                            <div class="text-secondary small mb-2">Saya menyatakan mampu memenuhi semua KUK elemen ini:</div>
                            <div class="form-selectgroup form-selectgroup-pills">
                                <label class="form-selectgroup-item">
                                    <input type="radio" name="elemen_approval[{{ $elemen->id }}]" value="1"
                                           class="form-selectgroup-input elemen-approval"
                                           data-elemen="{{ $elemen->id }}"
                                           @disabled(! $dapatDiisi)>
                                    <span class="form-selectgroup-label">Ya, mampu semua</span>
                                </label>
                                <label class="form-selectgroup-item">
                                    <input type="radio" name="elemen_approval[{{ $elemen->id }}]" value="0"
                                           class="form-selectgroup-input elemen-approval"
                                           data-elemen="{{ $elemen->id }}"
                                           @disabled(! $dapatDiisi)>
                                    <span class="form-selectgroup-label">Belum mampu semua</span>
                                </label>
                            </div>
                        </div>

                        {{-- Detail KUK (read-only display) --}}
                        <div class="text-secondary small">
                            <strong>Kriteria Unjuk Kerja:</strong>
                            <ul class="mb-0 ps-3">
                                @foreach ($elemen->kriteriaUnjukKerjas as $kuk)
                                    <li>{{ $kuk->pernyataan_kuk }}</li>
                                @endforeach
                            </ul>
                        </div>

                        {{-- Bukti per elemen --}}
                        <div class="mt-3">
                            <label class="form-label">Bukti / penjelasan singkat untuk elemen ini</label>
                            <textarea name="bukti[{{ $elemen->id }}]" rows="3" class="form-control"
                                      maxlength="1000"
                                      @disabled(! $dapatDiisi)
                                      placeholder="Contoh: sertifikat pelatihan, portofolio, pengalaman kerja, deskripsi kompetensi">{{ old('bukti.'.$elemen->id, $jawabans->where('elemen_id', $elemen->id)->first()?->bukti_asesi) }}</textarea>
                        </div>
                    </div>
                </div>
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
