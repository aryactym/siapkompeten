@extends('layouts.admin')

@section('title', 'Event Uji Kompetensi')
@section('pretitle', 'Penjadwalan')
@section('page-title', 'Event Uji Kompetensi')

@section('actions')
    <a href="{{ route('admin.event.create') }}" class="btn btn-primary">
        <i class="ti ti-plus me-1"></i> Buat Event
    </a>
@endsection

@section('content')
<x-tabler.card title="Daftar Event">
    @if ($events->isEmpty())
        <x-tabler.empty
            title="Belum ada event uji kompetensi"
            description="Buat event tahunan atau periode untuk menampung jadwal uji kompetensi."
            icon="ti ti-calendar-plus"
        />
    @else
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Nama Event</th>
                        <th>Tahun / Periode</th>
                        <th>Rentang Tanggal</th>
                        <th>Jumlah Jadwal</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($events as $event)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $event->nama }}</div>
                                <div class="text-secondary small">{{ $event->tempat_ringkas ?: '—' }}</div>
                            </td>
                            <td>
                                {{ $event->tahun }}
                                @if ($event->periode_label)
                                    <span class="text-secondary">· {{ $event->periode_label }}</span>
                                @endif
                            </td>
                            <td class="text-secondary">
                                @if ($event->tanggal_mulai)
                                    {{ $event->tanggal_mulai->translatedFormat('d M Y') }}
                                    @if ($event->tanggal_selesai)
                                        – {{ $event->tanggal_selesai->translatedFormat('d M Y') }}
                                    @endif
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-blue-lt">{{ $event->jadwals_count }} jadwal</span>
                            </td>
                            <td>
                                <div class="btn-list flex-nowrap">
                                    <a href="{{ route('admin.event.edit', $event) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="ti ti-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.event.destroy', $event) }}"
                                          onsubmit="return confirm('Hapus event ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" @disabled($event->jadwals_count > 0)>
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $events->links() }}
        </div>
    @endif
</x-tabler.card>
@endsection
