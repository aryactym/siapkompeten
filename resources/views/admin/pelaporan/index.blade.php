@extends('layouts.admin')

@section('title', 'Pelaporan')
@section('pretitle', 'Pelaporan')
@section('page-title', 'Pelaporan & Rekap Data')

@section('content')
<form method="GET" action="{{ route('admin.pelaporan.index') }}" class="row g-2 align-items-center mb-3">
    <div class="col-auto">
        <label class="form-label small mb-0">Dari</label>
        <input type="date" name="dari" value="{{ $dari }}" class="form-control form-control-sm">
    </div>
    <div class="col-auto">
        <label class="form-label small mb-0">Sampai</label>
        <input type="date" name="sampai" value="{{ $sampai }}" class="form-control form-control-sm">
    </div>
    <div class="col-auto align-self-end">
        <button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-filter me-1"></i> Terapkan</button>
    </div>
</form>

<div class="row row-cards mb-3">
    @foreach (['asesi' => ['Asesi', 'ti-users', 'primary'], 'pendaftaran' => ['Pendaftaran', 'ti-list-check', 'info'], 'jadwal' => ['Jadwal Asesmen', 'ti-calendar', 'warning'], 'skema' => ['Skema', 'ti-certificate', 'success']] as $key => [$label, $icon, $color])
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="bg-{{ $color }}-lt text-{{ $color }} avatar"><i class="ti {{ $icon }}"></i></span>
                        </div>
                        <div class="col">
                            <div class="text-secondary small">{{ $label }}</div>
                            <div class="fw-bold fs-3">{{ $rekap[$key] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<x-tabler.card title="Rekap Status Pendaftaran" class="mb-3">
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead><tr><th>Status</th><th class="text-end">Jumlah</th></tr></thead>
            <tbody>
                @forelse ($rekap['rekapStatus'] as $status => $total)
                    <tr>
                        <td>{{ str_replace('_', ' ', $status) }}</td>
                        <td class="text-end">{{ $total }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="text-center text-secondary">Tidak ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-tabler.card>

<x-tabler.card title="Daftar Pendaftaran">
    @if ($pendaftarans->isEmpty())
        <x-tabler.empty title="Belum ada pendaftaran" icon="ti ti-list-check" />
    @else
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>No. Pendaftaran</th>
                        <th>Asesi</th>
                        <th>Skema</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pendaftarans as $p)
                        <tr>
                            <td class="fw-semibold">{{ $p->no_pendaftaran }}</td>
                            <td>{{ $p->asesi->user->name ?? '—' }}</td>
                            <td>{{ $p->skema->nama_skema ?? '—' }}</td>
                            <td><span class="badge bg-blue-lt text-blue">{{ str_replace('_', ' ', $p->status) }}</span></td>
                            <td class="text-secondary small">{{ $p->created_at->translatedFormat('d M Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $pendaftarans->links() }}</div>
    @endif
</x-tabler.card>
@endsection
