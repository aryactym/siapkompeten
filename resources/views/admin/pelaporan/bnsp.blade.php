@extends('layouts.admin')

@section('title', 'Laporan BNSP')
@section('pretitle', 'Pelaporan')
@section('page-title', 'Rekap Laporan ke BNSP')

@section('actions')
    <a href="{{ route('admin.pelaporan.bnsp.pdf', ['dari' => $dari, 'sampai' => $sampai]) }}" class="btn btn-primary">
        <i class="ti ti-file-pdf me-1"></i> Cetak Surat Pengantar Blanko
    </a>
@endsection

@section('content')
    <x-tabler.card title="Filter Periode" class="mb-3">
        <form method="GET" action="{{ route('admin.pelaporan.bnsp') }}" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Dari</label>
                <input type="date" name="dari" class="form-control" value="{{ $dari }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Sampai</label>
                <input type="date" name="sampai" class="form-control" value="{{ $sampai }}">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-filter me-1"></i> Terapkan
                </button>
            </div>
        </form>
    </x-tabler.card>

    <x-tabler.card title="Rekap per Skema" class="mb-3">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Skema</th>
                        <th class="text-center">Asesi</th>
                        <th class="text-center">Kompeten</th>
                        <th class="text-center">Sertifikat Terbit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rekap as $baris)
                        <tr>
                            <td>
                                {{ $baris['skema']->nama_skema }}
                                <div class="text-secondary small">{{ $baris['skema']->kode_skema }}</div>
                            </td>
                            <td class="text-center">{{ $baris['asesi'] }}</td>
                            <td class="text-center">{{ $baris['kompeten'] }}</td>
                            <td class="text-center">{{ $baris['sertifikat'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="text-secondary small mb-0">
            Periode {{ \Carbon\Carbon::parse($dari)->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($sampai)->translatedFormat('d F Y') }}.
        </p>
    </x-tabler.card>
@endsection
