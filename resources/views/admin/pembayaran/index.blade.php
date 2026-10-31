@extends('layouts.admin')

@section('title', 'Validasi Pembayaran')
@section('pretitle', 'Asesi & Pendaftaran')
@section('page-title', 'Validasi Pembayaran')

@section('content')
@php
    $tabs = [
        'semua' => 'Semua',
        'menunggu' => 'Menunggu Validasi',
        'valid' => 'Valid',
        'tidak_valid' => 'Tidak Valid',
    ];
@endphp

<div class="nav-tabs-responsive mb-3">
    <ul class="nav nav-tabs">
        @foreach ($tabs as $key => $label)
            <li class="nav-item">
                <a class="nav-link @if ($statusAktif === $key) active @endif"
                   href="{{ route('admin.pembayaran.index', array_filter(['status' => $key !== 'semua' ? $key : null])) }}">
                    {{ $label }}
                </a>
            </li>
        @endforeach
    </ul>
</div>

<x-tabler.card title="Daftar Pembayaran">
    @if ($pembayarans->isEmpty())
        <x-tabler.empty
            title="Belum ada pembayaran"
            description="Bukti bayar yang diunggah peserta akan muncul di sini untuk divalidasi."
            icon="ti ti-cash-banknote"
        />
    @else
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>No. Pendaftaran</th>
                        <th>Asesi</th>
                        <th>Skema</th>
                        <th class="text-end">Total</th>
                        <th>Tanggal Bayar</th>
                        <th>Status</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pembayarans as $pembayaran)
                        @php $pendaftaran = $pembayaran->pendaftaran; @endphp
                        <tr>
                            <td class="fw-semibold">{{ $pendaftaran->no_pendaftaran }}</td>
                            <td>{{ $pendaftaran->asesi->user->name ?? '—' }}</td>
                            <td class="text-secondary">{{ $pendaftaran->skema->nama_skema ?? '—' }}</td>
                            <td class="text-end">Rp {{ number_format($pembayaran->total, 0, ',', '.') }}</td>
                            <td class="text-secondary">{{ $pembayaran->tanggal_bayar?->translatedFormat('d M Y') ?? '—' }}</td>
                            <td>
                                @if ($pembayaran->status === \App\Models\Pembayaran::STATUS_VALID)
                                    <span class="badge bg-success-lt text-success">Valid</span>
                                @elseif ($pembayaran->status === \App\Models\Pembayaran::STATUS_TIDAK_VALID)
                                    <span class="badge bg-danger-lt text-danger">Tidak Valid</span>
                                @else
                                    <span class="badge bg-warning-lt text-warning">Menunggu</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.pembayaran.show', $pembayaran) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="ti ti-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $pembayarans->links() }}
        </div>
    @endif
</x-tabler.card>
@endsection
