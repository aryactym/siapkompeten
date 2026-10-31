<div class="mb-3">
    <label class="form-label required">Kode TUK</label>
    <input type="text" name="kode_tuk" class="form-control @error('kode_tuk') is-invalid @enderror"
           value="{{ old('kode_tuk', $tuk->kode_tuk) }}" required maxlength="50">
    @error('kode_tuk') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
<div class="mb-3">
    <label class="form-label required">Nama TUK</label>
    <input type="text" name="nama_tuk" class="form-control @error('nama_tuk') is-invalid @enderror"
           value="{{ old('nama_tuk', $tuk->nama_tuk) }}" required maxlength="200">
    @error('nama_tuk') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
<div class="mb-3">
    <label class="form-label required">Jenis</label>
    <select name="jenis" class="form-select @error('jenis') is-invalid @enderror" required>
        @foreach (['sewaktu' => 'Sewaktu', 'mandiri' => 'Mandiri', 'tempat_kerja' => 'Tempat Kerja'] as $val => $lbl)
            <option value="{{ $val }}" @selected(old('jenis', $tuk->jenis) === $val)>{{ $lbl }}</option>
        @endforeach
    </select>
    @error('jenis') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
<div class="mb-3">
    <label class="form-label required">Penanggung Jawab</label>
    <input type="text" name="penanggung_jawab" class="form-control @error('penanggung_jawab') is-invalid @enderror"
           value="{{ old('penanggung_jawab', $tuk->penanggung_jawab) }}" required maxlength="150">
    @error('penanggung_jawab') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
<div class="row g-2">
    <div class="col-md-6 mb-3">
        <label class="form-label">Telepon</label>
        <input type="text" name="telepon" class="form-control @error('telepon') is-invalid @enderror"
               value="{{ old('telepon', $tuk->telepon) }}" maxlength="50">
        @error('telepon') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email', $tuk->email) }}" maxlength="150">
        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>
<div class="mb-3">
    <label class="form-label">Alamat</label>
    <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror" rows="2">{{ old('alamat', $tuk->alamat) }}</textarea>
    @error('alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
<div class="row g-2">
    <div class="col-md-6 mb-3">
        <label class="form-label">Provinsi</label>
        <select name="province_code" class="form-select @error('province_code') is-invalid @enderror">
            <option value="">-</option>
            @foreach ($provinces as $province)
                <option value="{{ $province->code }}" @selected(old('province_code', $tuk->province_code) === $province->code)>
                    {{ $province->name }}
                </option>
            @endforeach
        </select>
        @error('province_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Kota/Kabupaten</label>
        <select name="city_code" class="form-select @error('city_code') is-invalid @enderror">
            <option value="">-</option>
            @foreach ($cities as $city)
                <option value="{{ $city->code }}" @selected(old('city_code', $tuk->city_code) === $city->code)>
                    {{ $city->name }}
                </option>
            @endforeach
        </select>
        @error('city_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>
<div class="mb-3">
    <label class="form-check">
        <input type="checkbox" name="is_verified" value="1" class="form-check-input"
               @checked(old('is_verified', $tuk->is_verified ?? true))>
        <span class="form-check-label">Terverifikasi</span>
    </label>
    <label class="form-check">
        <input type="checkbox" name="is_active" value="1" class="form-check-input"
               @checked(old('is_active', $tuk->is_active ?? true))>
        <span class="form-check-label">Aktif</span>
    </label>
</div>
