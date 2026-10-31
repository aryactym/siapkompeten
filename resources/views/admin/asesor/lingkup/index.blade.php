@extends('layouts.admin')

@section('title', 'Lingkup Kompetensi Asesor')
@section('pretitle', 'Asesor')
@section('page-title', 'Lingkup Kompetensi Asesor')

@section('actions')
    <a href="{{ route('admin.asesor.index') }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i> Kembali ke Daftar Asesor
    </a>
@endsection

@section('content')
<x-tabler.card title="Asesor & Skema Kompetensi">
    <p class="text-secondary">
        Kelola skema sertifikasi yang dapat diasesi oleh setiap asesor.
        Asesor tanpa lingkup terdaftar dianggap kompeten di seluruh skema aktif.
    </p>
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>Asesor</th>
                    <th>No. Register</th>
                    <th>Lingkup Skema</th>
                    <th class="w-1">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($asesors as $asesor)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $asesor->nama_lengkap }}</div>
                            <div class="text-secondary small">{{ $asesor->bidang_keahlian ?: '—' }}</div>
                        </td>
                        <td class="text-secondary">{{ $asesor->no_register }}</td>
                        <td>
                            @if ($asesor->kompetensis_count === 0)
                                <span class="badge bg-secondary-lt">Semua Skema</span>
                            @else
                                <span class="badge bg-blue-lt">{{ $asesor->kompetensis_count }} skema</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.asesor.skema.show', $asesor) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="ti ti-settings me-1"></i> Kelola
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-secondary py-4">
                            Belum ada asesor terdaftar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $asesors->links() }}
    </div>
</x-tabler.card>
@endsection
