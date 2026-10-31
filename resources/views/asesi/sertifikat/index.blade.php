@extends('layouts.asesi')

@section('title', 'Hasil & Sertifikat')
@section('pretitle', 'Sertifikasi Kompetensi')
@section('page-title', 'Hasil & Sertifikat Saya')

@section('content')
@if ($sertifikats->isEmpty())
    <x-tabler.card>
        <x-tabler.empty
            title="Belum ada sertifikat"
            description="Sertifikat akan diterbitkan oleh admin LSP setelah proses asesmen selesai dan Anda dinyatakan kompeten."
            icon="ti ti-award"
        />
    </x-tabler.card>
@else
    <div class="row row-cards">
        @foreach ($sertifikats as $sertifikat)
            <div class="col-md-6 col-lg-4">
                <div class="card card-link card-link-pop">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="avatar bg-green-lt text-success">
                                <i class="ti ti-award fs-2"></i>
                            </span>
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-semibold text-truncate">{{ $sertifikat->skema?->nama_skema ?? '—' }}</div>
                                <div class="text-secondary small">{{ $sertifikat->skema?->kode_skema ?? '' }}</div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="text-secondary small">No. Registrasi</div>
                            <div class="font-monospace fw-semibold text-primary">{{ $sertifikat->no_registrasi }}</div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="text-secondary small">Tanggal Terbit</div>
                                <div class="small">{{ $sertifikat->tanggal_terbit?->translatedFormat('d M Y') }}</div>
                            </div>
                            <div class="col-6">
                                <div class="text-secondary small">Berlaku Hingga</div>
                                <div class="small">{{ $sertifikat->berlaku_hingga?->translatedFormat('d M Y') ?? '—' }}</div>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('asesi.sertifikat.show', $sertifikat) }}"
                               class="btn btn-sm btn-outline-secondary flex-fill">
                                <i class="ti ti-eye me-1"></i> Detail
                            </a>
                            <a href="{{ route('asesi.sertifikat.pdf', $sertifikat) }}"
                               target="_blank" class="btn btn-sm btn-primary flex-fill">
                                <i class="ti ti-download me-1"></i> Unduh PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
