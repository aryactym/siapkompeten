@extends('layouts.asesi')

@section('title', 'Daftar Skema Sertifikasi')
@section('pretitle', 'Pendaftaran')
@section('page-title', 'Pilih Skema Sertifikasi')

@section('content')
<form method="POST" action="{{ route('asesi.pendaftaran.store') }}">
    @csrf

    @if ($skemas->isEmpty())
        <x-tabler.card>
            <x-tabler.empty
                title="Belum ada skema aktif"
                description="Belum ada skema sertifikasi yang dibuka oleh lembaga."
                icon="ti ti-certificate-off"
            />
        </x-tabler.card>
    @else
        <div class="row row-cards">
            @foreach ($skemas as $skema)
                @php
                    $isActive = in_array($skema->id, $activeSkemaIds, true);
                @endphp
                <div class="col-lg-6">
                    <div class="card {{ $isActive ? 'border-secondary' : '' }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h3 class="card-title mb-1">{{ $skema->nama_skema }}</h3>
                                    <div class="text-secondary small">{{ $skema->kode_skema }}</div>
                                </div>
                                @if ($isActive)
                                    <span class="badge bg-yellow-lt">Sudah terdaftar</span>
                                @endif
                            </div>

                            <p class="text-secondary small mb-3">
                                {{ \Illuminate\Support\Str::limit($skema->deskripsi ?: 'Skema sertifikasi kompetensi.', 140) }}
                            </p>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <div class="text-secondary small">Biaya</div>
                                    <div class="fw-semibold">Rp {{ number_format((float) $skema->biaya, 0, ',', '.') }}</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-secondary small">Persyaratan</div>
                                    <div class="fw-semibold">{{ $skema->persyaratans->count() }} dokumen</div>
                                </div>
                            </div>

                            <label class="form-check">
                                <input type="radio" name="skema_id" value="{{ $skema->id }}" class="form-check-input"
                                       @checked(old('skema_id') === $skema->id)
                                       @disabled($isActive) required>
                                <span class="form-check-label">
                                    {{ $isActive ? 'Pendaftaran aktif sudah ada' : 'Pilih skema ini' }}
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @error('skema_id')
            <div class="alert alert-danger mt-3">{{ $message }}</div>
        @enderror

        <div class="mt-3 d-flex gap-2">
            <a href="{{ route('asesi.pendaftaran.index') }}" class="btn btn-outline-secondary">Batal</a>
            <button type="submit" class="btn btn-primary"
                    onclick="return confirm('Buat pendaftaran skema ini?')">
                <i class="ti ti-send me-1"></i> Ajukan Pendaftaran
            </button>
        </div>
    @endif
</form>
@endsection
