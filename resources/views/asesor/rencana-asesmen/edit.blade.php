@extends('layouts.asesor')

@section('title', 'Ubah Rencana Asesmen')
@section('pretitle', 'Rencana & MAPA')
@section('page-title', 'Rencana Asesmen — {{ $rencana->pendaftaran->no_pendaftaran }}')

@section('actions')
    <a href="{{ route('asesor.rencana-asesmen.index') }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i> Kembali
    </a>
    @if ($rencana->isFinal())
        <a href="{{ route('asesor.rencana-asesmen.pdf', $rencana) }}" class="btn btn-outline-success" target="_blank">
            <i class="ti ti-file-pdf me-1"></i> Cetak PDF MAPA.01
        </a>
        <a href="{{ route('asesor.rencana-asesmen.pdf.mapa02', $rencana) }}" class="btn btn-outline-success" target="_blank">
            <i class="ti ti-file-pdf me-1"></i> Cetak PDF MAPA.02
        </a>
    @endif
@endsection

@section('content')
<x-tabler.card title="Pendaftaran & Skema" class="mb-3">
    <div class="row g-3">
        <div class="col-md-4">
            <div class="text-secondary small">No. Pendaftaran</div>
            <div class="fw-semibold">{{ $rencana->pendaftaran->no_pendaftaran }}</div>
        </div>
        <div class="col-md-4">
            <div class="text-secondary small">Asesi</div>
            <div class="fw-semibold">{{ $rencana->pendaftaran->asesi->user->name }}</div>
        </div>
        <div class="col-md-4">
            <div class="text-secondary small">Skema</div>
            <div class="fw-semibold">{{ $skema->nama_skema }}</div>
        </div>
    </div>
</x-tabler.card>

<form method="POST" action="{{ route('asesor.rencana-asesmen.update', $rencana) }}">
    @csrf
    @method('PUT')

    <x-tabler.card title="Identitas Rencana" class="mb-3">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label small text-muted">Pendekatan Asesmen</label>
                <select name="pendekatan" class="form-select form-select-sm" required
                        {{ $rencana->isFinal() ? 'disabled' : '' }}>
                    <option value="observasi" {{ old('pendekatan', $rencana->pendekatan) === 'observasi' ? 'selected' : '' }}>Observasi</option>
                    <option value="demonstrasi_simulasi" {{ old('pendekatan', $rencana->pendekatan) === 'demonstrasi_simulasi' ? 'selected' : '' }}>Demonstrasi / Simulasi</option>
                    <option value="tes_lisan" {{ old('pendekatan', $rencana->pendekatan) === 'tes_lisan' ? 'selected' : '' }}>Tes Lisan</option>
                    <option value="tes_tertulis" {{ old('pendekatan', $rencana->pendekatan) === 'tes_tertulis' ? 'selected' : '' }}>Tes Tertulis</option>
                    <option value="wawancara" {{ old('pendekatan', $rencana->pendekatan) === 'wawancara' ? 'selected' : '' }}>Wawancara</option>
                    <option value="portofolio" {{ old('pendekatan', $rencana->pendekatan) === 'portofolio' ? 'selected' : '' }}>Portofolio</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted">Tanggal Asesmen</label>
                <input type="date" name="tanggal_asesmen" class="form-control form-control-sm" required
                       value="{{ old('tanggal_asesmen', $rencana->tanggal_asesmen) }}"
                       {{ $rencana->isFinal() ? 'disabled' : '' }}>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label small text-muted">Tujuan Asesmen</label>
            <textarea name="tujuan" class="form-control form-control-sm" rows="3" required
                      {{ $rencana->isFinal() ? 'disabled' : '' }}>{{ old('tujuan', $rencana->tujuan) }}</textarea>
        </div>
        <div class="mb-3">
            <label class="form-label small text-muted">Ruang Lingkup</label>
            <textarea name="ruang_lingkup" class="form-control form-control-sm" rows="3" required
                      {{ $rencana->isFinal() ? 'disabled' : '' }}>{{ old('ruang_lingkup', $rencana->ruang_lingkup) }}</textarea>
        </div>
        <div class="mb-3">
            <label class="form-label small text-muted">Catatan (opsional)</label>
            <textarea name="catatan" class="form-control form-control-sm" rows="2"
                      {{ $rencana->isFinal() ? 'disabled' : '' }}>{{ old('catatan', $rencana->catatan) }}</textarea>
        </div>
    </x-tabler.card>

    <x-tabler.card title="Unit Kompetensi & Peta Instrumen (MAPA.02)" class="mb-3">
        <p class="text-secondary small mb-2">
            Pilih unit kompetensi yang akan diuji. Untuk setiap unit, tentukan instrumen dan metode asesmen per KUK.
        </p>

        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0">
                <thead>
                    <tr>
                        <th style="width:5%" class="text-center">Pilih</th>
                        <th>Unit Kompetensi</th>
                        <th style="width:40%">KUK (Kriteria Unjuk Kerja)</th>
                        <th style="width:20%" class="text-center">Jenis Instrumen</th>
                        <th style="width:20%" class="text-center">Metode</th>
                        <th style="width:20%">Bukti</th>
                    </tr>
                </thead>
                <tbody>
                @php
                    $selectedUnits = $rencana->units->pluck('unit_kompetensi_id')->all();
                    $petaMap = [];
                    foreach ($rencana->units as $unit) {
                        foreach ($unit->petaInstrumens as $peta) {
                            $petaMap[$unit->unit_kompetensi_id][$peta->kuk_id] = $peta;
                        }
                    }
                @endphp
                @foreach ($allUnits as $unit)
                    <tr>
                        <td class="text-center">
                            <input type="checkbox" name="unit_ids[]" value="{{ $unit->id }}"
                                   {{ in_array($unit->id, $selectedUnitIds, true) ? 'checked' : '' }}
                                   {{ $rencana->isFinal() ? 'disabled' : '' }}>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $unit->kode_unit }}</div>
                            <div class="text-secondary small">{{ $unit->nama_unit }}</div>
                        </td>
                        <td>
                            @php $kuks = $unit->elemenKompetensi->flatMap(fn ($e) => $e->kriteriaUnjukKerjas); @endphp
                            @foreach ($kuks as $kuk)
                                <div class="form-check form-check-inline mb-1">
                                    <input type="checkbox" name="units[{{ $unit->id }}][kuks][]" value="{{ $kuk->id }}"
                                           class="form-check-input kuk-check"
                                           data-unit="{{ $unit->id }}"
                                           {{ in_array($kuk->id, $petaMap[$unit->id] ?? [], true) ? 'checked' : '' }}
                                           {{ $rencana->isFinal() ? 'disabled' : '' }}>
                                    <label class="form-check-label">{{ $kuk->kode_kuk ?? $kuk->id }}</label>
                                </div>
                            @endforeach
                        </td>
                        <td class="text-center">
                            <select name="units[{{ $unit->id }}][jenis_instrumen]" class="form-select form-select-sm jenis-instrumen-select"
                                    data-unit="{{ $unit->id }}"
                                    {{ $rencana->isFinal() ? 'disabled' : '' }}>
                                <option value="">—</option>
                                @foreach ($metodeOptions as $m)
                                    <option value="{{ $m }}" {{ old('jenis_instrumen.' . $unit->id, $petaMap[$unit->id][$kuks->first()->id ?? null]->jenis_instrumen ?? '') === $m ? 'selected' : '' }}>{{ $m }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="text-center">
                            <select name="units[{{ $unit->id }}][metode]" class="form-select form-select-sm metode-select"
                                    data-unit="{{ $unit->id }}"
                                    {{ $rencana->isFinal() ? 'disabled' : '' }}>
                                <option value="">—</option>
                                @foreach ($metodeOptions as $m)
                                    <option value="{{ $m }}">{{ $m }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="text" name="units[{{ $unit->id }}][bukti]" class="form-control form-control-sm"
                                   placeholder="Bukti"
                                   {{ $rencana->isFinal() ? 'disabled' : '' }}>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        @if ($rencana->isFinal())
            <div class="alert alert-info mt-2">
                <i class="ti ti-info me-1"></i> Rencana sudah difinal dan tidak dapat diubah.
            </div>
        @else
            <div class="d-flex justify-content-end gap-2 mt-3">
                <a href="{{ route('asesor.rencana-asesmen.index') }}" class="btn btn-outline-secondary">Batal</a>
                <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('formRencana').requestSubmit()">
                    <i class="ti ti-save me-1"></i> Simpan Perubahan
                </button>
                <button type="button" class="btn btn-success" id="btnFinal"
                        onclick="if(confirm('Finalisasi rencana? Rencana yang sudah difinal tidak dapat diubah.')){ document.getElementById('formFinal').submit(); }">
                    <i class="ti ti-lock me-1"></i> Finalisasi Rencana
                </button>
            </div>
        @endif
    </x-tabler.card>

    <form id="formFinal" method="POST" action="{{ route('asesor.rencana-asesmen.final', $rencana) }}" style="display:none;">
        @csrf
    </form>
</form>
</div>
@endsection

@push('scripts')
<script>
    // Sinkronkan checkbox KUK dengan jenis instrumen yang dipilih
    document.querySelectorAll('.kuk-check').forEach(function(cb) {
        cb.addEventListener('change', function() {
            const unitId = this.dataset.unit;
            const select = document.querySelector('.jenis-instrumen-select[data-unit="' + unitId + '"]');
            if (this.checked && select) select.disabled = false;
            if (!this.checked) {
                // Uncheck all other KUK checks for same unit? No, allow multiple.
            }
        });
    });
</script>
@endpush