@extends('layouts.asesi')

@section('title', 'Sertifikat Kompetensi')
@section('pretitle', 'Hasil & Sertifikat')
@section('page-title', 'Sertifikat Kompetensi')

@section('actions')
    <a href="{{ route('asesi.sertifikat.index') }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i> Kembali
    </a>
    <a href="{{ route('asesi.sertifikat.pdf', $sertifikat) }}" target="_blank" class="btn btn-primary">
        <i class="ti ti-file-download me-1"></i> Unduh PDF
    </a>
@endsection

@section('content')
<div class="row row-cards justify-content-center">
    <div class="col-lg-8">
        <x-tabler.card>
            <div class="text-center mb-4">
                <span class="avatar avatar-xl bg-green-lt text-success mb-3">
                    <i class="ti ti-award fs-1"></i>
                </span>
                <h2 class="fw-bold">Sertifikat Kompetensi BNSP</h2>
                <p class="text-secondary mb-0">{{ $sertifikat->skema?->nama_skema }}</p>
                <div class="font-monospace fw-bold text-primary fs-4 mt-2">{{ $sertifikat->no_registrasi }}</div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="text-secondary small">Nama Pemegang</div>
                    <div class="fw-semibold">{{ $sertifikat->asesi?->user?->name ?? '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-secondary small">No. Seri Blangko BNSP</div>
                    <div class="font-monospace">{{ $sertifikat->no_seri_blangko }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-secondary small">Tanggal Terbit</div>
                    <div>{{ $sertifikat->tanggal_terbit?->translatedFormat('d F Y') }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-secondary small">Berlaku Hingga</div>
                    <div>{{ $sertifikat->berlaku_hingga?->translatedFormat('d F Y') ?? 'Seumur Hidup' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-secondary small">Status</div>
                    <x-tabler.status-badge :status="$sertifikat->status" />
                </div>
            </div>

            <hr>

            <div class="text-center">
                <div class="text-secondary small mb-2">Verifikasi Keaslian</div>
                <a href="{{ $sertifikat->verifikasiUrl() }}" target="_blank"
                   class="btn btn-outline-primary btn-sm">
                    <i class="ti ti-shield-check me-1"></i> Verifikasi Online
                </a>
                <div class="text-secondary small mt-1 font-monospace">{{ $sertifikat->qr_token }}</div>
            </div>
        </x-tabler.card>
    </div>
</div>
@endsection
