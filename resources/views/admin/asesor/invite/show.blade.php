@extends('layouts.admin')

@section('title', 'Detail Undangan Asesor')
@section('pretitle', 'Asesor / Undang Keanggotaan')
@section('page-title', 'Undangan: '.$invitation->nama)

@section('actions')
    <a href="{{ route('admin.asesor.invite.index') }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i> Kembali
    </a>
    @if ($invitation->asesor)
        <a href="{{ route('admin.asesor.edit', $invitation->asesor) }}" class="btn btn-outline-primary">
            <i class="ti ti-user me-1"></i> Edit Profil Asesor
        </a>
    @endif
@endsection

@section('content')
<div class="row row-cards justify-content-center">
    <div class="col-lg-7">
        <x-tabler.card title="Detail Undangan">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="text-secondary small">Nama</div>
                    <div class="fw-semibold">{{ $invitation->nama }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-secondary small">Email</div>
                    <div>{{ $invitation->email }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-secondary small">No. Register</div>
                    <div>{{ $invitation->no_register }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-secondary small">Bidang Keahlian</div>
                    <div>{{ $invitation->bidang_keahlian ?: '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-secondary small">Diundang Oleh</div>
                    <div>{{ $invitation->invitedBy?->name ?? '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-secondary small">Tanggal Undangan</div>
                    <div>{{ $invitation->created_at->translatedFormat('d M Y H:i') }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-secondary small">Berlaku Hingga</div>
                    <div>{{ $invitation->expires_at?->translatedFormat('d M Y H:i') ?? '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-secondary small">Status</div>
                    <x-tabler.status-badge :status="$invitation->status" />
                </div>
                @if ($invitation->responded_at)
                    <div class="col-12">
                        <div class="text-secondary small">Waktu Respons</div>
                        <div>{{ $invitation->responded_at->translatedFormat('d M Y H:i') }}</div>
                    </div>
                @endif
            </div>

            @if ($invitation->status === \App\Models\AsesorInvitation::STATUS_MENUNGGU)
                <div class="mt-4 d-flex justify-content-end">
                    <form method="POST"
                          action="{{ route('admin.asesor.invite.destroy', $invitation) }}"
                          onsubmit="return confirm('Batalkan undangan ini? Akun asesor akan dinonaktifkan.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="ti ti-x me-1"></i> Batalkan Undangan
                        </button>
                    </form>
                </div>
            @endif
        </x-tabler.card>
    </div>
</div>
@endsection
