@php
    $rekeningId = 'rekening-'.$rekening->getKey();
@endphp

<div class="mb-3">
    <label class="form-label required" for="nama_bank-{{ $rekeningId }}">Nama Bank</label>
    <input type="text" name="nama_bank" id="nama_bank-{{ $rekeningId }}" class="form-control"
           value="{{ old('nama_bank', $rekening->nama_bank) }}" maxlength="60" placeholder="Contoh: Bank Mandiri" required>
    @error('nama_bank')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label required" for="no_rekening-{{ $rekeningId }}">Nomor Rekening</label>
    <input type="text" name="no_rekening" id="no_rekening-{{ $rekeningId }}" class="form-control"
           value="{{ old('no_rekening', $rekening->no_rekening) }}" maxlength="40" placeholder="12 digit nomor rekening" required>
    @error('no_rekening')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label required" for="atas_nama-{{ $rekeningId }}">Atas Nama</label>
    <input type="text" name="atas_nama" id="atas_nama-{{ $rekeningId }}" class="form-control"
           value="{{ old('atas_nama', $rekening->atas_nama) }}" maxlength="120" placeholder="Nama pemilik rekening" required>
    @error('atas_nama')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>

<div class="mb-0">
    <label class="form-check">
        <input type="hidden" name="is_aktif" value="0">
        <input type="checkbox" name="is_aktif" value="1" class="form-check-input"
               @checked(old('is_aktif', $rekening->is_aktif ?? true))>
        <span class="form-check-label">Tampilkan ke peserta</span>
    </label>
</div>
