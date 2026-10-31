@extends('layouts.admin')

@section('title', 'Notifikasi Keluar')
@section('pretitle', 'Komunikasi')
@section('page-title', 'Outbox Notifikasi')

@section('content')
<x-tabler.card title="Arsip Notifikasi">
    <x-slot:actions>
        <form method="GET" action="{{ route('admin.notifikasi.index') }}" class="d-flex gap-2">
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                @foreach (['menunggu', 'terkirim', 'gagal'] as $item)
                    <option value="{{ $item }}" @selected($status === $item)>{{ ucfirst($item) }}</option>
                @endforeach
            </select>
            <select name="channel" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Semua Kanal</option>
                @foreach (['email', 'sms', 'whatsapp', 'inapp'] as $item)
                    <option value="{{ $item }}" @selected($channel === $item)>{{ strtoupper($item) }}</option>
                @endforeach
            </select>
        </form>
    </x-slot:actions>

    @if ($notifikasis->isEmpty())
        <x-tabler.empty
            title="Belum ada notifikasi"
            description="Notifikasi penugasan asesor dan perubahan status pendaftaran akan tercatat di sini."
            icon="ti ti-mail-off"
        />
    @else
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Tujuan</th>
                        <th>Pesan</th>
                        <th>Status</th>
                        <th>Percobaan</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($notifikasis as $notifikasi)
                        <tr>
                            <td class="text-secondary small">{{ $notifikasi->created_at->translatedFormat('d M Y H:i') }}</td>
                            <td>
                                <div class="fw-semibold">{{ $notifikasi->tujuan }}</div>
                                <div class="text-secondary small">
                                    {{ strtoupper($notifikasi->channel) }} · {{ $notifikasi->user?->name ?? '—' }}
                                </div>
                            </td>
                            <td style="max-width: 360px;">
                                <div class="fw-semibold small">{{ $notifikasi->judul }}</div>
                                <div class="text-secondary small text-truncate">{{ $notifikasi->pesan }}</div>
                                @if ($notifikasi->error)
                                    <div class="text-danger small">{{ $notifikasi->error }}</div>
                                @endif
                            </td>
                            <td><x-tabler.status-badge :status="$notifikasi->status" /></td>
                            <td class="text-secondary">{{ $notifikasi->attempts }}×</td>
                            <td>
                                @if ($notifikasi->status !== 'terkirim')
                                    <form method="POST" action="{{ route('admin.notifikasi.retry', $notifikasi) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-primary">
                                            <i class="ti ti-refresh me-1"></i> Kirim Ulang
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $notifikasis->links() }}
        </div>
    @endif
</x-tabler.card>
@endsection
