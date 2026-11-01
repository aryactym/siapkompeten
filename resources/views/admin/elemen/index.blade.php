@extends('layouts.admin')

@section('title', 'Elemen Kompetensi')
@section('pretitle', 'Master Standar')
@section('page-title', 'Elemen & Kriteria Unjuk Kerja (KUK)')

@section('actions')
    <a href="{{ route('admin.elemen.index') }}" class="btn btn-sm btn-ghost-secondary">
        <i class="ti ti-refresh me-1"></i> Reset
    </a>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-elemen-baru">
        <i class="ti ti-plus me-1"></i> Tambah Elemen
    </button>
@endsection

@section('content')
{{-- ===== FILTER SKEMA & UNIT ===== --}}
<form method="GET" action="{{ route('admin.elemen.index') }}" id="filter-form" class="mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-sm-5">
            <label class="form-label small mb-1">Filter Skema</label>
            <select name="skema" id="filter-skema" class="form-select">
                <option value="">— Semua Skema —</option>
                @foreach ($skemas as $skema)
                    <option value="{{ $skema->id }}" @selected($skemaId === $skema->id)>
                        {{ $skema->kode_skema }} — {{ $skema->nama_skema }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-5">
            <label class="form-label small mb-1">Filter Unit</label>
            <select name="unit" id="filter-unit" class="form-select">
                <option value="">— Semua Unit —</option>
                @foreach ($units as $unit)
                    <option value="{{ $unit->id }}" @selected($unitId === $unit->id)>
                        {{ $unit->kode_unit }} — {{ $unit->judul_unit }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-2">
            <button type="submit" class="btn btn-primary w-100">
                <i class="ti ti-filter me-1"></i> Terapkan
            </button>
        </div>
    </div>
</form>

{{-- Info filter aktif --}}
@if ($skemaId || $unitId)
    <div class="alert alert-info d-flex align-items-center gap-2 mb-3">
        <i class="ti ti-filter"></i>
        <div>
            Menampilkan elemen
            @if ($unitTerpilih)
                untuk unit: <strong>{{ $unitTerpilih->kode_unit }} — {{ $unitTerpilih->judul_unit }}</strong>
            @elseif ($skemaId)
                untuk skema: <strong>{{ $skemas->firstWhere('id', $skemaId)?->nama_skema ?? $skemaId }}</strong>
            @endif
            &mdash; <a href="{{ route('admin.elemen.index') }}" class="alert-link">Lihat semua</a>
        </div>
    </div>
@endif

<x-tabler.card title="Daftar Elemen Kompetensi" subtitle="Elemen dan KUK per unit kompetensi.">
    @if ($elemens->isEmpty())
        <x-tabler.empty title="Belum ada elemen" description="Tambahkan elemen kompetensi." icon="ti ti-list" />
    @else
        @foreach ($elemens as $elemen)
            <div class="card mb-3">
                <div class="card-header">
                    <div>
                        <div class="fw-semibold">{{ $elemen->urutan }}. {{ $elemen->nama_elemen }}</div>
                        <div class="text-secondary small">{{ $elemen->unitKompetensi?->kode_unit }} — {{ $elemen->unitKompetensi?->judul_unit }}</div>
                    </div>
                    <div class="card-actions ms-auto btn-list">
                        <button type="button" class="btn btn-sm btn-outline-primary"
                                data-bs-toggle="modal" data-bs-target="#modal-elemen-{{ $elemen->id }}">
                            <i class="ti ti-edit"></i>
                        </button>
                        <form method="POST" action="{{ route('admin.elemen.destroy', $elemen) }}" onsubmit="return confirm('Hapus elemen ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="ti ti-trash"></i></button>
                        </form>
                        <button type="button" class="btn btn-sm btn-outline-success"
                                data-bs-toggle="modal" data-bs-target="#modal-kuk-{{ $elemen->id }}">
                            <i class="ti ti-plus"></i> KUK
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if ($elemen->kriteriaUnjukKerjas->isEmpty())
                        <div class="text-secondary small">Belum ada KUK.</div>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($elemen->kriteriaUnjukKerjas as $kuk)
                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="fw-semibold small">{{ $kuk->urutan }}. {{ $kuk->pernyataan_kuk }}</div>
                                        @if ($kuk->pertanyaan_observasi)
                                            <div class="text-secondary small">Observasi: {{ $kuk->pertanyaan_observasi }}</div>
                                        @endif
                                    </div>
                                    <form method="POST" action="{{ route('admin.kuk.destroy', $kuk) }}" onsubmit="return confirm('Hapus KUK ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="ti ti-trash"></i></button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <!-- Modal Ubah Elemen -->
            <div class="modal modal-blur fade" id="modal-elemen-{{ $elemen->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <form method="POST" action="{{ route('admin.elemen.update', $elemen) }}" class="modal-content">
                        @csrf @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Ubah Elemen</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            @include('admin.elemen._form', ['elemen' => $elemen, 'units' => $units])
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary ms-auto"><i class="ti ti-device-floppy me-1"></i> Simpan</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Modal Tambah KUK -->
            <div class="modal modal-blur fade" id="modal-kuk-{{ $elemen->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                    <form method="POST" action="{{ route('admin.elemen.kuk.store', $elemen) }}" class="modal-content">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Tambah KUK — {{ $elemen->nama_elemen }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            @include('admin.elemen._kuk_form', ['elemen' => $elemen])
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary ms-auto"><i class="ti ti-device-floppy me-1"></i> Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    @endif
</x-tabler.card>

<div class="modal modal-blur fade" id="modal-elemen-baru" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form method="POST" action="{{ route('admin.elemen.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Tambah Elemen Kompetensi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @include('admin.elemen._form', ['elemen' => $newElemen, 'units' => $units])
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary ms-auto">
                    <i class="ti ti-device-floppy me-1"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
/**
 * Dependent select: pilih skema → reload daftar unit yang terpetakan ke skema tersebut,
 * lalu submit form filter otomatis.
 */
(function () {
    const skemaSelect = document.getElementById('filter-skema');
    const unitSelect  = document.getElementById('filter-unit');
    const form        = document.getElementById('filter-form');

    if (!skemaSelect || !unitSelect) return;

    skemaSelect.addEventListener('change', function () {
        const skemaId  = this.value;
        const savedUnit = unitSelect.value;

        // Reset unit pilihan dulu
        unitSelect.innerHTML = '<option value="">— Semua Unit —</option>';
        unitSelect.disabled = true;

        if (!skemaId) {
            // Kembalikan semua unit (reload halaman tanpa filter skema)
            form.submit();
            return;
        }

        // Minta unit milik skema ini via endpoint wilayah-style yang sudah ada
        // Endpoint belum ada untuk ini, jadi kita pakai pendekatan sederhana:
        // ambil semua option dari allUnits yang punya data-skema matching.
        // Karena data sudah ada di server-side, kita encode via hidden input.
        restoreUnitsForSkema(skemaId, savedUnit);
        unitSelect.disabled = false;

        // Auto-submit setelah unit diisi
        setTimeout(() => form.submit(), 50);
    });

    function restoreUnitsForSkema(skemaId, savedUnit) {
        // Unit data di-embed dari server (lihat hidden JSON di bawah)
        const map = window.__skemaUnitsMap || {};
        const units = map[skemaId] || [];

        units.forEach(function (u) {
            const opt = document.createElement('option');
            opt.value = u.id;
            opt.textContent = u.kode_unit + ' — ' + u.judul_unit;
            if (u.id === savedUnit) opt.selected = true;
            unitSelect.appendChild(opt);
        });

        unitSelect.disabled = units.length === 0;
    }
})();
</script>

{{-- Map skema → units untuk dependent select (aman, tidak ada PII) --}}
<script>
window.__skemaUnitsMap = @json($skemaUnitsMap);
</script>
@endpush
