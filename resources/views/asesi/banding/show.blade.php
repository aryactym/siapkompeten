@extends('layouts.asesi')

@section('title', 'Banding Asesmen FR.AK.04')
@section('pretitle', 'Banding Asesmen')
@section('page-title', 'FR.AK.04 — '.$pendaftaran->skema->nama_skema)

@section('actions')
    <a href="{{ route('asesi.pendaftaran.show', $pendaftaran) }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i> Kembali
    </a>
    @if ($banding)
        <a href="{{ route('asesi.banding.pdf', $pendaftaran) }}" class="btn btn-outline-primary">
            <i class="ti ti-file-pdf me-1"></i> Unduh PDF
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

    <x-tabler.card title="Informasi Asesmen" class="mb-3">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="text-secondary small">Skema</div>
                <div class="fw-semibold">{{ $pendaftaran->skema->nama_skema }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-secondary small">No. Pendaftaran</div>
                <div class="fw-semibold">{{ $pendaftaran->no_pendaftaran }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-secondary small">Status Pendaftaran</div>
                <div><x-tabler.status-badge :status="$pendaftaran->status" /></div>
            </div>
        </div>
    </x-tabler.card>

    @if ($banding === null)
        @if ($pendaftaran->status !== \App\Models\Pendaftaran::STATUS_SELESAI)
            <div class="alert alert-warning">
                <i class="ti ti-clock me-1"></i>
                Banding dapat diajukan setelah keputusan asesmen ditetapkan.
            </div>
        @else
            <x-tabler.card title="Ajukan Banding" class="mb-3">
                <p class="text-secondary small">
                    Banding diajukan apabila Anda tidak menyetujui keputusan asesmen. Jelaskan alasan banding secara rinci.
                </p>
                <form method="POST" action="{{ route('asesi.banding.store', $pendaftaran) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="alasan-banding">Alasan banding</label>
                        <textarea name="alasan" id="alasan-banding" rows="5" maxlength="2000" class="form-control" required>{{ old('alasan') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-send me-1"></i> Ajukan Banding
                    </button>
                </form>
            </x-tabler.card>
        @endif
    @else
        <x-tabler.card title="Status Banding" class="mb-3">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                <span class="badge bg-primary-lt text-primary">
                    {{ $bandingStatuses[$banding->status] ?? $banding->status }}
                </span>
                @if ($banding->ditinjau_pada)
                    <span class="text-secondary small">
                        Ditinjau {{ $banding->ditinjau_pada->translatedFormat('d F Y H:i') }}
                        @if ($banding->peninjau)
                            oleh {{ $banding->peninjau->name }}
                        @endif
                    </span>
                @endif
            </div>

            <div class="mb-3">
                <div class="text-secondary small">Alasan banding</div>
                <div class="fw-semibold">{{ $banding->alasan }}</div>
            </div>

            @if ($banding->keputusan)
                <div class="mb-3">
                    <div class="text-secondary small">Keputusan peninjau</div>
                    <div class="fw-semibold">{{ $banding->keputusan }}</div>
                </div>
            @endif

            <h4 class="mt-3 mb-2">Riwayat Status</h4>
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
