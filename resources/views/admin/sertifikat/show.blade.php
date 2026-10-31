@extends('layouts.admin')

@section('title', 'Detail Sertifikat')
@section('pretitle', 'Sertifikasi')
@section('page-title', 'Sertifikat '.$sertifikat->no_registrasi)

@section('actions')
    <a href="{{ route('admin.sertifikat.index') }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i> Kembali
    </a>
    <a href="{{ route('admin.sertifikat.pdf', $sertifikat) }}" target="_blank" class="btn btn-primary">
        <i class="ti ti-file-download me-1"></i> Unduh PDF
    </a>
@endsection

@section('content')
<div class="row row-cards">
    <div class="col-lg-8">
        <x-tabler.card title="Informasi Sertifikat">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="text-secondary small">Asesi</div>
                    <div class="fw-semibold">{{ $sertifikat->asesi?->user?->name ?? '—' }}</div>
                    <div class="text-secondary small">{{ $sertifikat->asesi?->no_registrasi ?? '' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-secondary small">Skema Sertifikasi</div>
                    <div class="fw-semibold">{{ $sertifikat->skema?->nama_skema ?? '—' }}</div>
                    <div class="text-secondary small">{{ $sertifikat->skema?->kode_skema ?? '' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-secondary small">No. Registrasi Lisensi</div>
                    <div class="font-monospace fw-semibold text-primary">{{ $sertifikat->no_registrasi }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-secondary small">No. Seri Blangko BNSP</div>
                    <div class="font-monospace fw-semibold">{{ $sertifikat->no_seri_blangko }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-secondary small">Tanggal Terbit</div>
                    <div>{{ $sertifikat->tanggal_terbit?->translatedFormat('d F Y') ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-secondary small">Berlaku Hingga</div>
                    <div>{{ $sertifikat->berlaku_hingga?->translatedFormat('d F Y') ?? 'Seumur Hidup' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-secondary small">Status</div>
                    <x-tabler.status-badge :status="$sertifikat->status" />
                </div>
                @if ($sertifikat->pendaftaran?->jadwal?->tuk)
                    <div class="col-md-6">
                        <div class="text-secondary small">Tempat Uji Kompetensi</div>
                        <div>{{ $sertifikat->pendaftaran->jadwal->tuk->nama_tuk }}</div>
                    </div>
                @endif
                <div class="col-12">
                    <div class="text-secondary small">Diterbitkan Oleh</div>
                    <div>{{ $sertifikat->penerbit?->name ?? '—' }} · {{ $sertifikat->created_at->translatedFormat('d M Y H:i') }}</div>
                </div>
            </div>
        </x-tabler.card>
    </div>

    <div class="col-lg-4">
        <x-tabler.card title="Verifikasi QR">
            <div class="text-center py-3">
                {{-- QR code sederhana dengan URL verifikasi --}}
                <div class="mb-3">
                    <a href="{{ $sertifikat->verifikasiUrl() }}" target="_blank"
                       class="btn btn-outline-secondary btn-sm">
                        <i class="ti ti-external-link me-1"></i> Buka Halaman Verifikasi
                    </a>
                </div>
                <div class="text-secondary small font-monospace text-break">
                    {{ $sertifikat->qr_token }}
                </div>
            </div>
        </x-tabler.card>
    </div>
</div>
@endsection
