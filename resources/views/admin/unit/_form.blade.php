<div class="mb-3">
    <label class="form-label required">Kode Unit</label>
    <input type="text" name="kode_unit" class="form-control @error('kode_unit') is-invalid @enderror"
           value="{{ old('kode_unit', $unit->kode_unit) }}" required maxlength="100">
    @error('kode_unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
<div class="mb-3">
    <label class="form-label required">Judul Unit</label>
    <input type="text" name="judul_unit" class="form-control @error('judul_unit') is-invalid @enderror"
           value="{{ old('judul_unit', $unit->judul_unit) }}" required maxlength="255">
    @error('judul_unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
<div class="mb-3">
    <label class="form-label">SKKNI (induk)</label>
    <select name="skkni_id" class="form-select @error('skkni_id') is-invalid @enderror">
        <option value="">-</option>
        @foreach ($skknis as $skkni)
            <option value="{{ $skkni->id }}" @selected(old('skkni_id', $unit->skkni_id) === $skkni->id)>
                {{ $skkni->kode }} — {{ $skkni->judul }}
            </option>
        @endforeach
    </select>
    @error('skkni_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
<div class="mb-3">
    <label class="form-label">Standar Kompetensi</label>
    <textarea name="standar_kompetensi" class="form-control @error('standar_kompetensi') is-invalid @enderror" rows="2">{{ old('standar_kompetensi', $unit->standar_kompetensi) }}</textarea>
    @error('standar_kompetensi') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
<div class="mb-3">
    <label class="form-label">Deskripsi</label>
    <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" rows="2">{{ old('deskripsi', $unit->deskripsi) }}</textarea>
    @error('deskripsi') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
<div class="mb-3">
    <label class="form-check">
        <input type="checkbox" name="is_active" value="1" class="form-check-input"
               @checked(old('is_active', $unit->is_active ?? true))>
        <span class="form-check-label">Aktif</span>
    </label>
</div>
