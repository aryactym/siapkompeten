<div class="mb-3">
    <label class="form-label required">Unit Kompetensi</label>
    <select name="unit_kompetensi_id" class="form-select @error('unit_kompetensi_id') is-invalid @enderror" required>
        <option value="">Pilih unit...</option>
        @foreach ($units as $unit)
            <option value="{{ $unit->id }}" @selected(old('unit_kompetensi_id', $elemen->unit_kompetensi_id) === $unit->id)>
                {{ $unit->kode_unit }} — {{ $unit->judul_unit }}
            </option>
        @endforeach
    </select>
    @error('unit_kompetensi_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
<div class="mb-3">
    <label class="form-label required">Urutan</label>
    <input type="number" name="urutan" class="form-control @error('urutan') is-invalid @enderror"
           value="{{ old('urutan', $elemen->urutan ?? 1) }}" required min="1">
    @error('urutan') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
<div class="mb-3">
    <label class="form-label required">Nama Elemen</label>
    <input type="text" name="nama_elemen" class="form-control @error('nama_elemen') is-invalid @enderror"
           value="{{ old('nama_elemen', $elemen->nama_elemen) }}" required maxlength="255">
    @error('nama_elemen') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
