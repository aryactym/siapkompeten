@extends('layouts.admin')

@section('title', 'Jadwal Uji Kompetensi')
@section('pretitle', 'Penjadwalan')
@section('page-title', 'Jadwal Uji Kompetensi')

@section('actions')
    <a href="{{ route('admin.jadwal.create') }}" class="btn btn-primary">
        <i class="ti ti-plus me-1"></i> Buat Jadwal
    </a>
@endsection

@section('content')
<x-tabler.card title="Daftar Jadwal">
    <x-slot:actions>
        <form method="GET" action="{{ route('admin.jadwal.index') }}" class="d-flex gap-2">
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                @foreach (['draf', 'terbuka', 'berjalan', 'selesai', 'dibatalkan'] as $item)
                    <option value="{{ $item }}" @selected($status === $item)>{{ ucfirst($item) }}</option>
                @endforeach
            </select>
        </form>
    </x-slot:actions>

    @if ($jadwals->isEmpty())
        <x-tabler.empty
            title="Belum ada jadwal uji"
            description="Buat event terlebih dahulu, lalu tambahkan jadwal uji kompetensi."
            icon="ti ti-calendar-off"
        />
    @else
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Event / Skema</th>
                        <th>Waktu</th>
                        <th>TUK</th>
                        <th>Asesor</th>
                        <th>Peserta</th>
                        <th>Status</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($jadwals as $jadwal)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $jadwal->skema->nama_skema }}</div>
                                <div class="text-secondary small">
                                    {{ $jadwal->event->nama }}
                                    @if ($jadwal->gelombang)
                                        · {{ $jadwal->gelombang }}
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div>{{ $jadwal->tanggal->translatedFormat('d M Y') }}</div>
                                <div class="text-secondary small">
                                    {{ substr($jadwal->jam_mulai, 0, 5) }}–{{ substr($jadwal->jam_selesai, 0, 5) }} WIB
                                </div>
                            </td>
                            <td class="text-secondary">{{ $jadwal->tuk->nama_tuk }}</td>
                            <td>
                                <span class="badge bg-azure-lt">{{ $jadwal->asesors_count }} asesor</span>
                            </td>
                            <td>
                                <span class="badge bg-green-lt">{{ $jadwal->pendaftarans_count }}/{{ $jadwal->kuota }}</span>
                            </td>
                            <td>
                                <x-tabler.status-badge :status="$jadwal->status" />
                            </td>
                            <td>
                                <div class="btn-list flex-nowrap">
                                    <a href="{{ route('admin.jadwal.show', $jadwal) }}" class="btn btn-sm btn-outline-primary" title="Detail">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                    <a href="{{ route('pleno.show', $jadwal) }}" class="btn btn-sm btn-outline-secondary" title="Pleno">
                                        <i class="ti ti-gavel"></i>
                                    </a>
                                    <a href="{{ route('admin.jadwal.presensi', $jadwal) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Presensi">
                                        <i class="ti ti-printer"></i>
                                    </a>
                                    <a href="{{ route('admin.jadwal.edit', $jadwal) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                        <i class="ti ti-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $jadwals->links() }}
        </div>
    @endif
</x-tabler.card>
@endsection
