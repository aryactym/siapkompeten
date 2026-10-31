@php
    $biayaId = 'biaya-'.$biaya->getKey();
@endphp

<div class="mb-3">
    <label class="form-label required" for="skema_id-{{ $biayaId }}">Skema Sertifikasi</label>
    <select name="skema_id" id="skema_id-{{ $biayaId }}" class="form-select" required>
        <option value="">— Pilih skema —</option>
        @foreach ($skemas as $skema)
            <option value="{{ $skema->id }}" @selected((string) old('skema_id', $biaya->skema_id) === (string) $skema->id)>
                {{ $skema->nama_skema }}
            </option>
        @endforeach
    </select>
    @error('skema_id')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label required" for="jenis_biaya-{{ $biayaId }}">Jenis Biaya</label>
    <input type="text" name="jenis_biaya" id="jenis_biaya-{{ $biayaId }}" class="form-control"
           value="{{ old('jenis_biaya', $biaya->jenis_biaya) }}" maxlength="80"
           placeholder="Contoh: Biaya Asesmen, Biaya Sertifikasi" required>
    @error('jenis_biaya')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label required" for="nominal-{{ $biayaId }}">Nominal (Rp)</label>
    <input type="text" name="nominal" id="nominal-{{ $biayaId }}" class="form-control text-end"
           data-uang inputmode="numeric"
           value="{{ old('nominal') !== null ? number_format((int) old('nominal'), 0, ',', '.') : number_format((int) ($biaya->nominal ?? 0), 0, ',', '.') }}"
           placeholder="0" required>
    <small class="form-hint">Angka bulat tanpa pemisah; format otomatis saat diketik (FR-PAY-06).</small>
    @error('nominal')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>

<div class="mb-0">
    <label class="form-label required">Ditanggung Oleh</label>
    <div class="form-selectgroup form-selectgroup-pills">
        <label class="form-selectgroup-item">
            <input type="radio" name="tanggung_jawab" value="peserta" class="form-selectgroup-input"
                   @checked(old('tanggung_jawab', $biaya->tanggung_jawab ?? 'peserta') === 'peserta')>
            <span class="form-selectgroup-label">Peserta</span>
        </label>
        <label class="form-selectgroup-item">
            <input type="radio" name="tanggung_jawab" value="fasilitator" class="form-selectgroup-input"
                   @checked(old('tanggung_jawab', $biaya->tanggung_jawab) === 'fasilitator')>
            <span class="form-selectgroup-label">Fasilitator / Sponsor</span>
        </label>
    </div>
    @error('tanggung_jawab')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
