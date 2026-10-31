@extends('layouts.admin')

@section('title', 'Monitoring APL-02')
@section('pretitle', 'Pelaporan')
@section('page-title', 'Monitoring APL-02')

@section('content')
<form method="GET" action="{{ route('admin.pelaporan.apl02') }}" class="row g-2 align-items-center mb-3">
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
    <div class="col-auto align-self-end">
        <a href="{{ route('admin.pelaporan.index') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
    </div>
</form>

<div class="row row-cards mb-3">
    @foreach ($rekap as $status => $total)
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="text-secondary small">{{ str_replace('_', ' ', $status) }}</div>
                    <div class="fw-bold fs-3">{{ $total }}</div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<x-tabler.card title="Daftar APL-02">
    @if ($penilaians->isEmpty())
        <x-tabler.empty title="Belum ada APL-02" icon="ti ti-list-check" />
    @else
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Asesi</th>
                        <th>Skema</th>
                        <th>Status</th>
                        <th>Diserahkan</th>
                        <th>Diverifikasi</th>
                        <th>Asesor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($penilaians as $p)
                        <tr>
                            <td>{{ $p->pendaftaran->asesi->user->name ?? '—' }}</td>
                            <td>{{ $p->pendaftaran->skema->nama_skema ?? '—' }}</td>
                            <td><span class="badge bg-blue-lt text-blue">{{ str_replace('_', ' ', $p->status) }}</span></td>
                            <td class="text-secondary small">{{ $p->diserahkan_pada?->translatedFormat('d M Y H:i') ?: '—' }}</td>
                            <td class="text-secondary small">{{ $p->diverifikasi_pada?->translatedFormat('d M Y H:i') ?: '—' }}</td>
                            <td>{{ $p->asesor->user->name ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $penilaians->links() }}</div>
    @endif
</x-tabler.card>
@endsection
