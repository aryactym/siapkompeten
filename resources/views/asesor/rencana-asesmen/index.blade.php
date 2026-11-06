@extends('layouts.asesor')

@section('title', 'Rencana Asesmen')
@section('pretitle', 'Rencana & MAPA')
@section('page-title', 'Rencana Asesmen')

@section('content')
@if ($asesor === null)
    <div class="alert alert-warning">
        Profil asesor Anda belum dibuat oleh admin. Hubungi administrator lembaga untuk melengkapi profil asesor.
    </div>
@else
    <x-tabler.card title="Rencana Asesmen (MAPA.01)">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="text-secondary small">
                Rencana asesmen merupakan perencanaan pelaksanaan asesmen sebelum hari uji dilaksanakan.
            </span>
            <a href="{{ route('asesor.jadwal.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="ti ti-calendar me-1"></i> Jadwal Saya
            </a>
            <a href="{{ route('asesor.jadwal.index') }}" class="btn btn-sm btn-primary">
                <i class="ti ti-file-plus me-1"></i> Buat dari Jadwal
            </a>
        </div>

        <form method="GET" action="{{ route('asesor.rencana-asesmen.index') }}" class="d-flex flex-wrap gap-2 align-items-end mb-3">
            <input type="search" name="q" value="{{ old('q') }}" class="form-control form-control-sm"
                   placeholder="Cari no. pendaftaran / nama asesi / skema" style="min-width: 200px;">
            <select name="status" class="form-select form-select-sm" style="width: auto;">
                <option value="">Semua Status</option>
                <option value="draf" {{ old('status') === 'draf' ? 'selected' : '' }}>Draf</option>
                <option value="final" {{ old('status') === 'final' ? 'selected' : '' }}>Final</option>
            </select>
            <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="ti ti-search"></i></button>
        </form>

        @if ($rencanas->isEmpty())
            <x-tabler.empty
                title="Belum ada rencana asesmen"
                description="Buat rencana asesmen untuk pendaftaran di jadwal yang ditugaskan kepada Anda."
                icon="ti ti-file-off"
            />
        @else
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>No. Pendaftaran</th>
                            <th>Asesi</th>
                            <th>Skema</th>
                            <th>Pendekatan</th>
                            <th>Tanggal Asesmen</th>
                            <th>Status</th>
                            <th class="w-1">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rencanas as $rencana)
                            <tr>
                                <td class="fw-semibold">{{ $rencana->pendaftaran->no_pendaftaran }}</td>
                                <td>
                                    <div>{{ $rencana->pendaftaran->asesi->user->name }}</div>
                                    <div class="text-secondary small">{{ $rencana->pendaftaran->asesi->no_registrasi }}</div>
                                </td>
                                <td>{{ $rencana->pendaftaran->skema->nama_skema }}</td>
                                <td class="text-secondary small">{{ $rencana->pendekatan }}</td>
                                <td class="text-secondary small">{{ $rencana->tanggal_asesmen->format('d M Y') }}</td>
                                <td>
                                    @if ($rencana->isFinal())
                                        <span class="badge bg-success-lt text-success">Final</span>
                                    @else
                                        <span class="badge bg-warning-lt text-warning">Draf</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-list flex-nowrap">
                                        <a href="{{ route('asesor.rencana-asesmen.edit', $rencana) }}"
                                           class="btn btn-sm btn-outline-primary" title="Ubah">
                                            <i class="ti ti-pencil"></i>
                                        </a>
                                        @if ($rencana->isFinal())
                                            <a href="{{ route('asesor.rencana-asesmen.pdf', $rencana) }}"
                                               class="btn btn-sm btn-outline-success" target="_blank" title="Cetak PDF MAPA.01">
                                                <i class="ti ti-file-pdf"></i>
                                            </a>
                                            <a href="{{ route('asesor.rencana-asesmen.pdf.mapa02', $rencana) }}"
                                               class="btn btn-sm btn-outline-success" target="_blank" title="Cetak PDF MAPA.02">
                                                <i class="ti ti-file-pdf"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="card-footer">
                {{ $rencanas->links() }}
            </div>
        @endif
    </x-tabler.card>
@endif
@endsection