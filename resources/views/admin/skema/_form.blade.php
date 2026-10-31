<div class="mb-3">
    <label class="form-label required">Kode Skema</label>
    <input type="text" name="kode_skema" class="form-control @error('kode_skema') is-invalid @enderror"
           value="{{ old('kode_skema', $skema->kode_skema) }}" required maxlength="100">
    @error('kode_skema') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
<div class="mb-3">
    <label class="form-label required">Nama Skema</label>
    <input type="text" name="nama_skema" class="form-control @error('nama_skema') is-invalid @enderror"
           value="{{ old('nama_skema', $skema->nama_skema) }}" required maxlength="255">
    @error('nama_skema') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
<div class="mb-3">
    <label class="form-label">Nomor Skema</label>
    <input type="text" name="nomor_skema" class="form-control @error('nomor_skema') is-invalid @enderror"
           value="{{ old('nomor_skema', $skema->nomor_skema) }}" maxlength="150">
    @error('nomor_skema') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
<div class="row g-2">
    <div class="col-md-4 mb-3">
        <label class="form-label required">Jenis</label>
        <select name="jenis" class="form-select @error('jenis') is-invalid @enderror" required>
            @foreach (['kkni' => 'KKNI', 'okupasi' => 'Okupasi', 'klaster' => 'Klaster'] as $val => $lbl)
                <option value="{{ $val }}" @selected(old('jenis', $skema->jenis) === $val)>{{ $lbl }}</option>
            @endforeach
        </select>
        @error('jenis') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Level KKNI</label>
        <input type="number" name="level_kkni" class="form-control @error('level_kkni') is-invalid @enderror"
               value="{{ old('level_kkni', $skema->level_kkni) }}" min="1" max="9">
        @error('level_kkni') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Kategori</label>
        <input type="text" name="kategori" class="form-control @error('kategori') is-invalid @enderror"
               value="{{ old('kategori', $skema->kategori) }}" maxlength="100">
        @error('kategori') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>
<div class="mb-3">
    <label class="form-label">Deskripsi</label>
    <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" rows="3">{{ old('deskripsi', $skema->deskripsi) }}</textarea>
    @error('deskripsi') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
<div class="mb-3">
    <label class="form-label">Biaya (Rp)</label>
    <input type="number" name="biaya" class="form-control @error('biaya') is-invalid @enderror"
           value="{{ old('biaya', $skema->biaya) }}" min="0" step="0.01">
    @error('biaya') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
<div class="mb-3">
    <label class="form-check">
        <input type="checkbox" name="is_active" value="1" class="form-check-input"
               @checked(old('is_active', $skema->is_active ?? true))>
        <span class="form-check-label">Aktif</span>
    </label>
</div>
