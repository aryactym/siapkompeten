@extends('layouts.admin')

@section('title', 'Undang Keanggotaan Asesor')
@section('pretitle', 'Asesor')
@section('page-title', 'Undang Keanggotaan Asesor')

@section('actions')
    <a href="{{ route('admin.asesor.invite.create') }}" class="btn btn-primary">
        <i class="ti ti-send me-1"></i> Kirim Undangan
    </a>
@endsection

@section('content')
<x-tabler.card title="Riwayat Undangan">
    <p class="text-secondary small">
        Undangan membuat akun asesor baru dan mengirim tautan aktivasi (set kata sandi) ke email yang didaftarkan.
    </p>

    @if ($invitations->isEmpty())
        <x-tabler.empty
            title="Belum ada undangan"
            description="Kirim undangan ke calon asesor untuk bergabung sebagai anggota lembaga."
            icon="ti ti-mail-forward"
        />
    @else
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Nama / Email</th>
                        <th>No. Register</th>
                        <th>Dikirim Oleh</th>
                        <th>Berlaku Hingga</th>
                        <th>Status</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invitations as $invitation)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $invitation->nama }}</div>
                                <div class="text-secondary small">{{ $invitation->email }}</div>
                            </td>
                            <td class="text-secondary">{{ $invitation->no_register }}</td>
                            <td class="text-secondary small">{{ $invitation->invitedBy?->name ?? '—' }}</td>
                            <td class="text-secondary small">
                                {{ $invitation->expires_at?->translatedFormat('d M Y') ?? '—' }}
                            </td>
                            <td>
                                <x-tabler.status-badge :status="$invitation->status" />
                            </td>
                            <td>
                                <div class="btn-list flex-nowrap">
                                    <a href="{{ route('admin.asesor.invite.show', $invitation) }}"
                                       class="btn btn-sm btn-outline-secondary">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                    @if ($invitation->status === \App\Models\AsesorInvitation::STATUS_MENUNGGU)
                                        <form method="POST"
                                              action="{{ route('admin.asesor.invite.destroy', $invitation) }}"
                                              onsubmit="return confirm('Batalkan undangan ini? Akun asesor akan dinonaktifkan.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="ti ti-x"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $invitations->links() }}
        </div>
    @endif
</x-tabler.card>
@endsection
