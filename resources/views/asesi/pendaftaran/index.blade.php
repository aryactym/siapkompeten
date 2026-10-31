@extends('layouts.asesi')

@section('title', 'Pendaftaran Saya')
@section('pretitle', 'Sertifikasi')
@section('page-title', 'Pendaftaran Sertifikasi Saya')

@section('actions')
    <a href="{{ route('asesi.pendaftaran.create') }}" class="btn btn-primary">
        <i class="ti ti-plus me-1"></i> Daftar Skema
    </a>
@endsection

@section('content')
@unless ($asesi->isProfileComplete())
    <div class="alert alert-warning">
        Profil pokok belum lengkap.
        <a href="{{ route('asesi.profil.edit') }}" class="alert-link">Lengkapi profil</a>
        sebelum mendaftar skema sertifikasi.
    </div>
@endunless

<x-tabler.card title="Riwayat Pendaftaran">
    @if ($pendaftarans->isEmpty())
        <x-tabler.empty
            title="Belum ada pendaftaran"
            description="Pilih skema sertifikasi yang sesuai dengan kualifikasi Anda untuk memulai proses asesmen."
            icon="ti ti-file-plus"
        />
    @else
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>No. Pendaftaran</th>
                        <th>Skema</th>
                        <th>Tahapan Aktif</th>
                        <th>Jadwal</th>
                        <th>Status</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pendaftarans as $pendaftaran)
                        <tr>
                            <td class="fw-semibold">{{ $pendaftaran->no_pendaftaran }}</td>
                            <td>
                                <div>{{ $pendaftaran->skema->nama_skema }}</div>
                                <div class="text-secondary small">{{ $pendaftaran->skema->kode_skema }}</div>
                            </td>
                            <td class="text-secondary small">{{ $pendaftaran->currentStage()['label'] }}</td>
                            <td class="text-secondary small">
                                @if ($pendaftaran->jadwal)
                                    {{ $pendaftaran->jadwal->tanggal->translatedFormat('d M Y') }}
                                    <div>{{ $pendaftaran->jadwal->tuk->nama_tuk }}</div>
                                @else
                                    —
                                @endif
                            </td>
                            <td><x-tabler.status-badge :status="$pendaftaran->status" /></td>
                            <td>
                                <a href="{{ route('asesi.pendaftaran.show', $pendaftaran) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="ti ti-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $pendaftarans->links() }}
        </div>
    @endif
</x-tabler.card>
@endsection
