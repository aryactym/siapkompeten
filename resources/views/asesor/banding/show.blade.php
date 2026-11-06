@extends('layouts.asesor')

@section('title', 'Banding Asesmen FR.AK.04')
@section('pretitle', 'Banding Asesmen')
@section('page-title', 'FR.AK.04 — '.$pendaftaran->skema->nama_skema)

@section('actions')
    <a href="{{ route('asesor.jadwal.peserta', $pendaftaran->jadwal) }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i> Kembali
    </a>
    @if ($banding)
        <a href="{{ route('asesor.banding.pdf', $pendaftaran) }}" class="btn btn-outline-primary">
            <i class="ti ti-file-pdf me-1"></i> Cetak PDF
        </a>
    @endif
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success">
            <i class="ti ti-check me-1"></i> {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <x-tabler.card title="Informasi Pendaftaran" class="mb-3">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="text-secondary small">Asesi</div>
                <div class="fw-semibold">{{ $pendaftaran->asesi->user->name ?? '—' }}</div>
            </div>
            <div class="col-md-3">
                <div class="text-secondary small">No. Pendaftaran</div>
                <div class="fw-semibold">{{ $pendaftaran->no_pendaftaran }}</div>
            </div>
            <div class="col-md-3">
                <div class="text-secondary small">Skema</div>
                <div class="fw-semibold">{{ $pendaftaran->skema->nama_skema }}</div>
            </div>
            <div class="col-md-3">
                <div class="text-secondary small">Status Pendaftaran</div>
                <div><x-tabler.status-badge :status="$pendaftaran->status" /></div>
            </div>
        </div>
    </x-tabler.card>

    @if ($banding === null)
        <div class="alert alert-info">
            <i class="ti ti-info-circle me-1"></i>
            Asesi belum mengajukan banding untuk pendaftaran ini.
        </div>
    @else
        <x-tabler.card title="Detail Banding" class="mb-3">
            <div class="mb-3">
                <div class="text-secondary small">Status</div>
                <span class="badge bg-primary-lt text-primary">
                    {{ $bandingStatuses[$banding->status] ?? $banding->status }}
                </span>
            </div>

            <div class="mb-3">
                <div class="text-secondary small">Alasan banding</div>
                <div>{{ $banding->alasan }}</div>
            </div>

            @if ($banding->keputusan)
                <div class="mb-3">
                    <div class="text-secondary small">Keputusan peninjau</div>
                    <div>{{ $banding->keputusan }}</div>
                </div>
            @endif

            @if ($banding->ditinjau_pada)
                <div class="mb-3">
                    <div class="text-secondary small">Ditinjau</div>
                    <div>
                        {{ $banding->ditinjau_pada->translatedFormat('d F Y H:i') }}
                        @if ($banding->peninjau)
                            oleh {{ $banding->peninjau->name }}
                        @endif
                    </div>
                </div>
            @endif
        </x-tabler.card>

        @if (! $banding->isClosed())
            <x-tabler.card title="Tinjau Banding" class="mb-3">
                <form method="POST" action="{{ route('asesor.banding.review', $pendaftaran) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="banding-status">Keputusan</label>
                        <select name="status" id="banding-status" class="form-select" required>
                            @foreach (['ditinjau', 'diterima', 'ditolak', 'selesai'] as $status)
                                <option value="{{ $status }}" @selected(old('status', $banding->status) === $status)>
                                    {{ $bandingStatuses[$status] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="banding-keputusan">Uraian keputusan (wajib untuk diterima/ditolak)</label>
                        <textarea name="keputusan" id="banding-keputusan" rows="4" maxlength="2000" class="form-control">{{ old('keputusan', $banding->keputusan) }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary"
                            onclick="return confirm('Simpan keputusan banding?')">
                        <i class="ti ti-gavel me-1"></i> Simpan Keputusan
                    </button>
                </form>
            </x-tabler.card>
        @endif

        <x-tabler.card title="Riwayat Status" class="mb-3">
            <div class="list-group">
                @foreach ($banding->histories as $history)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <span class="fw-semibold">{{ $bandingStatuses[$history->status] ?? $history->status }}</span>
                            <span class="text-secondary small">{{ $history->created_at->translatedFormat('d F Y H:i') }}</span>
                        </div>
                        @if ($history->catatan)
                            <div class="small text-secondary mt-1">{{ $history->catatan }}</div>
                        @endif
                        @if ($history->pembuat)
                            <div class="small text-secondary">Oleh: {{ $history->pembuat->name }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </x-tabler.card>
    @endif
@endsection
