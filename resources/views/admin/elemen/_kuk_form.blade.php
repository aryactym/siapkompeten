<div class="mb-3">
    <label class="form-label required">Urutan</label>
    <input type="number" name="urutan" class="form-control @error('urutan') is-invalid @enderror"
           value="{{ old('urutan', 1) }}" required min="1">
    @error('urutan') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
<div class="mb-3">
    <label class="form-label required">Pernyataan KUK</label>
    <textarea name="pernyataan_kuk" class="form-control @error('pernyataan_kuk') is-invalid @enderror" rows="2" required>{{ old('pernyataan_kuk') }}</textarea>
    @error('pernyataan_kuk') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
<div class="mb-3">
    <label class="form-label">Pertanyaan Observasi</label>
    <textarea name="pertanyaan_observasi" class="form-control" rows="2">{{ old('pertanyaan_observasi') }}</textarea>
</div>
<div class="mb-3">
    <label class="form-label">Pertanyaan Lisan</label>
    <textarea name="pertanyaan_lisan" class="form-control" rows="2">{{ old('pertanyaan_lisan') }}</textarea>
</div>
<div class="mb-3">
    <label class="form-label">Pertanyaan Tertulis</label>
    <textarea name="pertanyaan_tertulis" class="form-control" rows="2">{{ old('pertanyaan_tertulis') }}</textarea>
</div>
