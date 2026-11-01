@extends('layouts.admin')

@section('title', 'Terbitkan Bundle Sertifikat')
@section('pretitle', 'Sertifikasi')
@section('page-title', 'Bundle Sertifikat — '.$jadwal->skema?->nama_skema)

@section('actions')
    <a href="{{ route('admin.sertifikat.bundle.index') }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i> Daftar Jadwal
    </a>
@endsection

@section('content')

{{-- Info jadwal --}}
<div class="card mb-3">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="text-secondary small">Jadwal</div>
                <div class="fw-semibold">{{ $jadwal->tanggal?->translatedFormat('d M Y') }}</div>
                <div class="text-secondary small">{{ substr($jadwal->jam_mulai, 0, 5) }}–{{ substr($jadwal->jam_selesai, 0, 5) }} WIB</div>
            </div>
            <div class="col-md-3">
                <div class="text-secondary small">Event</div>
                <div class="fw-semibold">{{ $jadwal->event?->nama ?? '—' }}</div>
                @if ($jadwal->gelombang)
                    <div class="text-secondary small">{{ $jadwal->gelombang }}</div>
                @endif
            </div>
            <div class="col-md-3">
                <div class="text-secondary small">Skema</div>
                <div class="fw-semibold">{{ $jadwal->skema?->nama_skema ?? '—' }}</div>
                <div class="text-secondary small">{{ $jadwal->skema?->kode_skema ?? '' }}</div>
            </div>
            <div class="col-md-3">
                <div class="text-secondary small">TUK</div>
                <div class="fw-semibold">{{ $jadwal->tuk?->nama_tuk ?? '—' }}</div>
            </div>
        </div>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible mb-3">
        <i class="ti ti-circle-check alert-icon me-2"></i>
        {{ session('success') }}
        <a class="btn-close" data-bs-dismiss="alert"></a>
    </div>
@endif

@if (session('warning'))
    <div class="alert alert-warning alert-dismissible mb-3">
        <i class="ti ti-alert-triangle alert-icon me-2"></i>
        {{ session('warning') }}
        <a class="btn-close" data-bs-dismiss="alert"></a>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger mb-3">
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Peserta siap terbit --}}
@if ($pesertaLayak->isNotEmpty())
<x-tabler.card title="Peserta Kompeten — Belum Terbit ({{ $pesertaLayak->count() }})">
    <form method="POST" action="{{ route('admin.sertifikat.bundle.store', $jadwal) }}"
          onsubmit="return confirm('Terbitkan {{ $pesertaLayak->count() }} sertifikat sekaligus?')">
        @csrf

        <div class="table-responsive">
            <table class="table table-vcenter">
                <thead>
                    <tr>
                        <th>Asesi</th>
                        <th>No. Registrasi Asesi</th>
                        <th>No. Seri Blangko BNSP <span class="text-danger">*</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pesertaLayak as $i => $pendaftaran)
                        <input type="hidden" name="sertifikat[{{ $i }}][pendaftaran_id]"
                               value="{{ $pendaftaran->id }}">
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $pendaftaran->asesi?->user?->name ?? '—' }}</div>
                                <div class="text-secondary small">{{ $pendaftaran->no_pendaftaran }}</div>
                            </td>
                            <td class="text-secondary small">{{ $pendaftaran->asesi?->no_registrasi ?? '—' }}</td>
                            <td>
                                <input type="text"
                                       name="sertifikat[{{ $i }}][no_seri_blangko]"
                                       class="form-control form-control-sm font-monospace @error("sertifikat.{$i}.no_seri_blangko") is-invalid @enderror"
                                       value="{{ old("sertifikat.{$i}.no_seri_blangko") }}"
                                       placeholder="cth: LSP.SKM.00001.2026"
                                       required
                                       maxlength="100">
                                @error("sertifikat.{$i}.no_seri_blangko")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            <div class="alert alert-info py-2 mb-3">
                <i class="ti ti-info-circle me-1"></i>
                Format nomor seri blangko sesuai standar BNSP: <code>KODE-LSP.KODE-SKEMA.NNNNN.TAHUN</code>
                — contoh: <code>BNSP-LSP-1882-ID.SKM-MPW-01.00001.2026</code>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="ti ti-certificate me-1"></i>
                Terbitkan {{ $pesertaLayak->count() }} Sertifikat Sekaligus
            </button>
        </div>
    </form>
</x-tabler.card>
@else
    <div class="alert alert-success">
        <i class="ti ti-circle-check me-2"></i>
        Semua peserta kompeten pada jadwal ini sudah diterbitkan sertifikatnya.
    </div>
@endif

{{-- Peserta yang sudah terbit --}}
@if ($pesertaSudahTerbit->isNotEmpty())
<x-tabler.card title="Sudah Diterbitkan ({{ $pesertaSudahTerbit->count() }})" class="mt-3">
    <div class="table-responsive">
        <table class="table table-vcenter table-sm">
            <thead>
                <tr>
                    <th>Asesi</th>
                    <th>No. Registrasi</th>
                    <th>No. Seri Blangko</th>
                    <th>Tanggal Terbit</th>
                    <th class="w-1">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pesertaSudahTerbit as $pendaftaran)
                    <tr>
                        <td>{{ $pendaftaran->asesi?->user?->name ?? '—' }}</td>
                        <td class="font-monospace small text-primary">
                            {{ $pendaftaran->sertifikat?->no_registrasi ?? '—' }}
                        </td>
                        <td class="font-monospace small">
                            {{ $pendaftaran->sertifikat?->no_seri_blangko ?? '—' }}
                        </td>
                        <td class="text-secondary small">
                            {{ $pendaftaran->sertifikat?->tanggal_terbit?->translatedFormat('d M Y') ?? '—' }}
                        </td>
                        <td>
                            @if ($pendaftaran->sertifikat)
                                <a href="{{ route('admin.sertifikat.show', $pendaftaran->sertifikat) }}"
                                   class="btn btn-sm btn-outline-secondary">
                                    <i class="ti ti-eye"></i>
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-tabler.card>
@endif

@endsection
