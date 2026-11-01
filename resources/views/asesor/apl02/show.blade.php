@extends('layouts.asesor')

@section('title', 'Verifikasi APL-02')
@section('pretitle', 'Penilaian Pra-Asesmen')
@section('page-title', 'Verifikasi APL-02 · '.$pendaftaran->no_pendaftaran)

@section('actions')
    <a href="{{ route('asesor.mak02.show', $pendaftaran) }}" class="btn btn-outline-primary">
        <i class="ti ti-file-analytics me-1"></i> Input FR.AK.02
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
                <div class="card mb-3 border-primary">
                    <div class="card-header bg-primary-lt">
                        <h3 class="h5 m-0">{{ $elemen->nama_elemen }}</h3>
                    </div>
                    <div class="card-body">
                        {{-- KUK list display --}}
                        <div class="text-secondary small mb-3">
                            <strong>Kriteria Unjuk Kerja:</strong>
                            <ul class="mb-0 ps-3">
                                @foreach ($elemen->kriteriaUnjukKerjas as $kuk)
                                    <li>{{ $kuk->pernyataan_kuk }}</li>
                                @endforeach
                            </ul>
                        </div>

                        {{-- Jawaban asesi per elemen --}}
                        @php 
                            $firstKuk = $elemen->kriteriaUnjukKerjas->first();
                            $elemenJawaban = $firstKuk ? $jawabans->get($firstKuk->id) : null;
                        @endphp
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="text-secondary small">Pernyataan Asesi</div>
                                <div class="fw-semibold">
                                    @if ($elemenJawaban?->jawaban_asesi)
                                        <span class="badge bg-success-lt text-success">Ya, mampu semua</span>
                                    @elseif ($elemenJawaban)
                                        <span class="badge bg-danger-lt text-danger">Belum mampu semua</span>
                                    @else
                                        <span class="badge bg-secondary-lt">Belum dijawab</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-secondary small">Bukti Asesi</div>
                                <div class="text-break">{{ $elemenJawaban?->bukti_asesi ?: '—' }}</div>
                            </div>
                        </div>

                        <hr>

                        {{-- Verifikasi per elemen --}}
                        <label class="form-label">Catatan Verifikasi Asesor untuk Elemen Ini</label>
                        <textarea name="bukti[{{ $elemen->id }}]" rows="3" class="form-control" maxlength="1000"
                                  @disabled(! $dapatDiverifikasi)
                                  placeholder="Catatan pemeriksaan bukti kompetensi untuk elemen ini">{{ old('bukti.'.$elemen->id, $elemenJawaban?->bukti_asesor) }}</textarea>

                        <div class="mt-3">
                            <div class="text-secondary small mb-2">Empat Bukti Kompetensi (4C) untuk Elemen Ini</div>
                            <div class="d-flex flex-wrap gap-3">
                                @foreach (['valid' => 'Valid', 'autentik' => 'Autentik', 'terkini' => 'Terkini', 'memadai' => 'Memadai'] as $key => $label)
                                    <label class="form-check">
                                        <input type="checkbox" class="form-check-input"
                                               name="verifikasi[{{ $elemen->id }}][{{ $key }}]" value="1"
                                               @checked(old('verifikasi.'.$elemen->id.'.'.$key, $elemenJawaban?->{$key}))
                                               @disabled(! $dapatDiverifikasi)>
                                        <span class="form-check-label">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </x-tabler.card>
    @endforeach

    @if ($dapatDiverifikasi)
        <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-outline-success"
                    onclick="cekSemuaValid()">
                <i class="ti ti-check-all me-1"></i> Cek Semua Valid
            </button>
            <button type="submit" class="btn btn-primary"
                    onclick="return confirm('Simpan hasil verifikasi APL-02?')">
                <i class="ti ti-check me-1"></i> Simpan Verifikasi APL-02
            </button>
        </div>
    @endif
</form>
@push('scripts')
<script>
/**
 * Centang semua 4C (valid/autentik/terkini/memadai) pada seluruh elemen sekaligus.
 */
function cekSemuaValid() {
    document.querySelectorAll('input[name^="verifikasi["]').forEach(function (el) {
        if (!el.disabled) { el.checked = true; }
    });
}
</script>
@endpush
@endsection
