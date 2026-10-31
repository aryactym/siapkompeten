@extends('layouts.asesor')

@section('title', 'Verifikasi APL-02')
@section('pretitle', 'Penilaian Pra-Asesmen')
@section('page-title', 'Verifikasi APL-02 · '.$pendaftaran->no_pendaftaran)

@section('actions')
    <a href="{{ route('asesor.mak02.show', $pendaftaran) }}" class="btn btn-outline-primary">
        <i class="ti ti-file-analytics me-1"></i> Input MAK-02
    </a>
@endsection

@section('content')
@if (! $dapatDiverifikasi)
    <div class="alert {{ $penilaian->status === \App\Models\Apl02Penilaian::STATUS_DIVERIFIKASI ? 'alert-success' : 'alert-warning' }}">
        <div class="d-flex">
            <div><i class="ti ti-alert-circle alert-icon fs-2 me-2"></i></div>
            <div>
                <div class="alert-title fw-semibold">
                    {{ $penilaian->status === \App\Models\Apl02Penilaian::STATUS_DIVERIFIKASI
                        ? 'APL-02 Sudah Diverifikasi'
                        : 'Menunggu asesi menyerahkan APL-02' }}
                </div>
                <div class="text-secondary">
                    {{ $penilaian->status === \App\Models\Apl02Penilaian::STATUS_DIVERIFIKASI
                        ? 'Diverifikasi oleh '.($penilaian->asesor->user->name ?? 'asesor').' pada '.$penilaian->diverifikasi_pada?->translatedFormat('d F Y H:i')
                        : 'Asesi belum menyerahkan formulir APL-02 untuk skema ini.' }}
                </div>
            </div>
        </div>
    </div>
@endif

<form method="POST" action="{{ route('asesor.apl02.verifikasi', $pendaftaran) }}">
    @csrf

    @foreach ($units as $unit)
        <x-tabler.card title="{{ $unit->kode_unit }} · {{ $unit->judul_unit }}" class="mb-3">
            @foreach ($unit->elemenKompetensis as $elemen)
                <h3 class="h5 mt-3">{{ $elemen->nama_elemen }}</h3>

                @foreach ($elemen->kriteriaUnjukKerjas as $kuk)
                    @php $jawaban = $jawabans->get($kuk->id); @endphp
                    <div class="card mb-2">
                        <div class="card-body">
                            <div class="fw-semibold">{{ $kuk->pernyataan_kuk }}</div>

                            <div class="mt-2 row g-3">
                                <div class="col-md-6">
                                    <div class="text-secondary small">Jawaban asesi</div>
                                    <div class="fw-semibold">
                                        @if ($jawaban?->jawaban_asesi)
                                            <span class="badge bg-success-lt text-success">Ya, mampu</span>
                                        @elseif ($jawaban)
                                            <span class="badge bg-danger-lt text-danger">Belum mampu</span>
                                        @else
                                            <span class="badge bg-secondary-lt">Belum dijawab</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-secondary small">Bukti asesi</div>
                                    <div>{{ $jawaban?->bukti_asesi ?: '—' }}</div>
                                </div>
                            </div>

                            <hr>

                            <label class="form-label">Bukti dari asesor</label>
                            <textarea name="bukti[{{ $kuk->id }}]" rows="2" class="form-control" maxlength="1000"
                                      @disabled(! $dapatDiverifikasi)
                                      placeholder="Catatan pemeriksaan bukti">{{ old('bukti.'.$kuk->id, $jawaban?->bukti_asesor) }}</textarea>

                            <div class="mt-3">
                                <div class="text-secondary small mb-1">Empat bukti kompetensi (4C)</div>
                                <div class="d-flex flex-wrap gap-3">
                                    @foreach (['valid' => 'Valid', 'autentik' => 'Autentik', 'terkini' => 'Terkini', 'memadai' => 'Memadai'] as $key => $label)
                                        <label class="form-check">
                                            <input type="checkbox" class="form-check-input"
                                                   name="verifikasi[{{ $kuk->id }}][{{ $key }}]" value="1"
                                                   @checked(old('verifikasi.'.$kuk->id.'.'.$key, $jawaban?->{$key}))
                                                   @disabled(! $dapatDiverifikasi)>
                                            <span class="form-check-label">{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endforeach
        </x-tabler.card>
    @endforeach

    @if ($dapatDiverifikasi)
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"
                    onclick="return confirm('Simpan hasil verifikasi APL-02?')">
                <i class="ti ti-check me-1"></i> Simpan Verifikasi APL-02
            </button>
        </div>
    @endif
</form>
@endsection
