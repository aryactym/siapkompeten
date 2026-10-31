<div class="mb-3">
    <label class="form-label required">Kode SKKNI</label>
    <input type="text" name="kode" class="form-control @error('kode') is-invalid @enderror"
           value="{{ old('kode', $skkni->kode) }}" required maxlength="100">
    @error('kode') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
<div class="mb-3">
    <label class="form-label required">Judul</label>
    <input type="text" name="judul" class="form-control @error('judul') is-invalid @enderror"
           value="{{ old('judul', $skkni->judul) }}" required maxlength="255">
    @error('judul') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
<div class="mb-3">
    <label class="form-label">Nomor Penetapan</label>
    <input type="text" name="nomor_penetapan" class="form-control @error('nomor_penetapan') is-invalid @enderror"
           value="{{ old('nomor_penetapan', $skkni->nomor_penetapan) }}" maxlength="150">
    @error('nomor_penetapan') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
<div class="row g-2">
    <div class="col-md-4 mb-3">
        <label class="form-label required">Tahun</label>
        <input type="number" name="tahun" class="form-control @error('tahun') is-invalid @enderror"
               value="{{ old('tahun', $skkni->tahun) }}" required min="1900" max="2100">
        @error('tahun') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-8 mb-3">
        <label class="form-label">Path Berkas (opsional)</label>
        <input type="text" name="file_path" class="form-control @error('file_path') is-invalid @enderror"
               value="{{ old('file_path', $skkni->file_path) }}" maxlength="500" placeholder="/storage/skkni/...">
        @error('file_path') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>
<div class="mb-3">
    <label class="form-check">
        <input type="checkbox" name="is_active" value="1" class="form-check-input"
               @checked(old('is_active', $skkni->is_active ?? true))>
        <span class="form-check-label">Aktif</span>
    </label>
</div>
