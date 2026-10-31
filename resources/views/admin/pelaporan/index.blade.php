@extends('layouts.admin')

@section('title', 'Pelaporan')
@section('pretitle', 'Pelaporan')
@section('page-title', 'Pelaporan & Rekap Data')

@section('content')
{{-- ===== FILTER FORM ===== --}}
<form method="GET" action="{{ route('admin.pelaporan.index') }}" id="filter-form">
    <x-tabler.card title="Filter Laporan" class="mb-3">
        <x-slot:actions>
            <button type="button" class="btn btn-sm btn-ghost-secondary"
                    onclick="document.getElementById('filter-form').reset(); document.getElementById('filter-form').submit();">
                <i class="ti ti-refresh me-1"></i> Reset
            </button>
        </x-slot:actions>

        <div class="row g-3">
            {{-- Rentang Tanggal --}}
            <div class="col-sm-6 col-lg-3">
                <label class="form-label small">Dari Tanggal</label>
                <input type="date" name="dari" value="{{ $filters['dari'] }}" class="form-control">
            </div>
            <div class="col-sm-6 col-lg-3">
                <label class="form-label small">Sampai Tanggal</label>
                <input type="date" name="sampai" value="{{ $filters['sampai'] }}" class="form-control">
            </div>

            {{-- No. Pendaftaran --}}
            <div class="col-sm-6 col-lg-3">
                <label class="form-label small">No. Pendaftaran</label>
                <input type="text" name="no_pendaftaran"
                       value="{{ $filters['no_pendaftaran'] }}"
                       class="form-control"
                       placeholder="Contoh: PDT-2026-0001">
            </div>

            {{-- Nama Asesi --}}
            <div class="col-sm-6 col-lg-3">
                <label class="form-label small">Nama Asesi</label>
                <input type="text" name="nama_asesi"
                       value="{{ $filters['nama_asesi'] }}"
                       class="form-control"
                       placeholder="Ketik sebagian nama…">
            </div>

            {{-- Nama Asesor --}}
            <div class="col-sm-6 col-lg-3">
                <label class="form-label small">Nama Asesor</label>
                <input type="text" name="nama_asesor"
                       value="{{ $filters['nama_asesor'] }}"
                       class="form-control"
                       placeholder="Ketik sebagian nama…">
            </div>

            {{-- Skema --}}
            <div class="col-sm-6 col-lg-3">
                <label class="form-label small">Skema Sertifikasi</label>
                <select name="skema_id" class="form-select">
                    <option value="">— Semua Skema —</option>
                    @foreach ($skemas as $skema)
                        <option value="{{ $skema->id }}"
                                @selected($filters['skema_id'] === $skema->id)>
                            {{ $skema->kode_skema }} · {{ $skema->nama_skema }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Status --}}
            <div class="col-sm-6 col-lg-3">
                <label class="form-label small">Status Pendaftaran</label>
                <select name="status" class="form-select">
                    <option value="">— Semua Status —</option>
                    @foreach ($statuses as $s)
                        <option value="{{ $s }}" @selected($filters['status'] === $s)>
                            {{ ucfirst(str_replace('_', ' ', $s)) }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Tombol --}}
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-filter me-1"></i> Terapkan Filter
                </button>
                {{-- Export Excel — sertakan semua filter aktif --}}
                <a href="{{ route('admin.pelaporan.export', array_filter($filters)) }}"
                   class="btn btn-success">
                    <i class="ti ti-file-spreadsheet me-1"></i> Export Excel
                </a>
            </div>
        </div>
    </x-tabler.card>
</form>

{{-- ===== STAT CARDS ===== --}}
<div class="row row-cards mb-3">
    @foreach ([
        'asesi'       => ['Asesi',           'ti-users',        'primary'],
        'pendaftaran' => ['Pendaftaran',      'ti-list-check',   'info'],
        'jadwal'      => ['Jadwal Asesmen',   'ti-calendar',     'warning'],
        'skema'       => ['Skema Aktif',      'ti-certificate',  'success'],
    ] as $key => [$label, $icon, $color])
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="bg-{{ $color }}-lt text-{{ $color }} avatar">
                                <i class="ti {{ $icon }}"></i>
                            </span>
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

{{-- ===== REKAP STATUS ===== --}}
<x-tabler.card title="Rekap Status Pendaftaran" class="mb-3">
    <x-slot:actions>
        <span class="text-secondary small">Berdasarkan rentang tanggal: {{ \Carbon\Carbon::parse($dari)->translatedFormat('d M Y') }} – {{ \Carbon\Carbon::parse($sampai)->translatedFormat('d M Y') }}</span>
    </x-slot:actions>
    <div class="table-responsive">
        <table class="table table-vcenter card-table table-sm">
            <thead>
                <tr>
                    <th>Status</th>
                    <th class="text-end">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rekap['rekapStatus'] as $status => $total)
                    <tr>
                        <td>
                            <x-tabler.status-badge :status="$status" />
                        </td>
                        <td class="text-end fw-semibold">{{ $total }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="text-center text-secondary py-3">
                            Tidak ada data pada periode ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if ($rekap['rekapStatus']->isNotEmpty())
                <tfoot>
                    <tr class="fw-bold">
                        <td>Total</td>
                        <td class="text-end">{{ $rekap['rekapStatus']->sum() }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</x-tabler.card>

{{-- ===== DAFTAR PENDAFTARAN ===== --}}
<x-tabler.card title="Daftar Pendaftaran">
    <x-slot:actions>
        @php
            $activeFilters = collect($filters)->filter(fn($v) => $v !== '' && $v !== null);
            $hasFilter = $activeFilters->except(['dari','sampai'])->isNotEmpty();
        @endphp
        @if ($hasFilter)
            <span class="badge bg-blue-lt text-blue">
                <i class="ti ti-filter me-1"></i>{{ $hasFilter ? 'Filter aktif' : '' }}
            </span>
        @endif
        <span class="text-secondary small">{{ $pendaftarans->total() }} data</span>
    </x-slot:actions>

    @if ($pendaftarans->isEmpty())
        <x-tabler.empty
            title="Tidak ada data"
            description="Tidak ada pendaftaran yang sesuai dengan filter yang diterapkan."
            icon="ti ti-list-search"
        />
    @else
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>No. Pendaftaran</th>
                        <th>Asesi</th>
                        <th>Skema</th>
                        <th>Jadwal / TUK</th>
                        <th>Asesor</th>
                        <th>Status</th>
                        <th>Tgl Daftar</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pendaftarans as $p)
                        <tr>
                            <td class="fw-semibold font-monospace small">{{ $p->no_pendaftaran }}</td>
                            <td>
                                <div class="fw-semibold">{{ $p->asesi?->user?->name ?? '—' }}</div>
                                <div class="text-secondary small">{{ $p->asesi?->user?->email ?? '' }}</div>
                            </td>
                            <td>
                                <div>{{ $p->skema?->nama_skema ?? '—' }}</div>
                                <div class="text-secondary small">{{ $p->skema?->kode_skema ?? '' }}</div>
                            </td>
                            <td class="text-secondary small">
                                @if ($p->jadwal)
                                    <div>{{ $p->jadwal->tanggal->translatedFormat('d M Y') }}</div>
                                    <div>{{ $p->jadwal->tuk?->nama_tuk ?? '—' }}</div>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-secondary small">
                                @forelse ($p->jadwal?->asesors ?? [] as $asesor)
                                    <div>{{ $asesor->nama_lengkap }}</div>
                                @empty
                                    —
                                @endforelse
                            </td>
                            <td><x-tabler.status-badge :status="$p->status" /></td>
                            <td class="text-secondary small">
                                {{ $p->created_at->translatedFormat('d M Y') }}
                            </td>
                            <td>
                                <a href="{{ route('admin.pendaftaran.show', $p) }}"
                                   class="btn btn-sm btn-outline-secondary" title="Detail">
                                    <i class="ti ti-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex align-items-center justify-content-between">
            <div class="text-secondary small">
                Menampilkan {{ $pendaftarans->firstItem() }}–{{ $pendaftarans->lastItem() }}
                dari {{ $pendaftarans->total() }} data
            </div>
            {{ $pendaftarans->links() }}
        </div>
    @endif
</x-tabler.card>
@endsection
