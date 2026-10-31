@extends('layouts.asesor')

@section('title', 'Peserta Jadwal Uji')
@section('pretitle', 'Asesor')
@section('page-title', 'Peserta '.$jadwal->skema->nama_skema)

@section('actions')
    <a href="{{ route('asesor.jadwal.index') }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i> Kembali
    </a>
@endsection

@section('content')
<x-tabler.card title="Informasi Jadwal" class="mb-3">
    <div class="row g-3">
        <div class="col-md-3">
            <div class="text-secondary small">Tanggal</div>
            <div class="fw-semibold">{{ $jadwal->tanggal->translatedFormat('d F Y') }}</div>
        </div>
        <div class="col-md-3">
            <div class="text-secondary small">Waktu</div>
            <div class="fw-semibold">
                {{ substr($jadwal->jam_mulai, 0, 5) }}–{{ substr($jadwal->jam_selesai, 0, 5) }} WIB
            </div>
        </div>
        <div class="col-md-3">
            <div class="text-secondary small">TUK</div>
            <div class="fw-semibold">{{ $jadwal->tuk->nama_tuk }}</div>
        </div>
        <div class="col-md-3">
            <div class="text-secondary small">Status</div>
            <x-tabler.status-badge :status="$jadwal->status" />
        </div>
    </div>
</x-tabler.card>

<x-tabler.card title="Daftar Peserta">
    @if ($jadwal->pendaftarans->isEmpty())
        <x-tabler.empty
            title="Belum ada peserta"
            description="Peserta yang telah diplotting ke jadwal ini akan muncul di sini."
            icon="ti ti-users-off"
        />
    @else
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Peserta</th>
                        <th>Wilayah</th>
                        <th>Pendidikan</th>
                        <th>Dokumen</th>
                        <th>Status</th>
                    <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($jadwal->pendaftarans as $pendaftaran)
                        @php($asesi = $pendaftaran->asesi)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $asesi->user->name }}</div>
                                <div class="text-secondary small">{{ $asesi->no_registrasi }}</div>
                            </td>
                            <td class="text-secondary small">
                                {{ $asesi->city?->name ?? '—' }}
                                @if ($asesi->province)
                                    <div>{{ $asesi->province->name }}</div>
                                @endif
                            </td>
                            <td class="text-secondary small">{{ $asesi->pendidikan_terakhir ?: '—' }}</td>
                            <td>
                                <span class="badge bg-azure-lt">
                                    {{ $pendaftaran->dokumens->where('status', 'disetujui')->count() }} disetujui
                                </span>
                            </td>
                            <td><x-tabler.status-badge :status="$pendaftaran->status" /></td>
                            <td>
                                <div class="btn-list flex-nowrap">
                                    <a href="{{ route('asesor.apl02.show', $pendaftaran) }}"
                                       class="btn btn-sm btn-outline-primary" title="Verifikasi APL-02">
                                        <i class="ti ti-list-check"></i>
                                    </a>
                                    <a href="{{ route('asesor.mak02.show', $pendaftaran) }}"
                                       class="btn btn-sm btn-outline-primary" title="Input MAK-02">
                                        <i class="ti ti-file-analytics"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-tabler.card>
@endsection
