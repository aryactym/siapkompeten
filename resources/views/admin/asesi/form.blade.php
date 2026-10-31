@extends('layouts.admin')

@section('title', 'Tambah Asesi Manual')
@section('pretitle', 'Asesi & Pendaftaran')
@section('page-title', 'Tambah Asesi Manual')

@section('content')
<form method="POST" action="{{ route('admin.asesi.store') }}">
    @csrf

    <div class="row row-cards">
        {{-- Kolom Kiri: Data Akun + Alamat --}}
        <div class="col-lg-8">

            <x-tabler.card title="Data Akun Pengguna">
                <div class="form-hint mb-3 text-muted">
                    <i class="ti ti-info-circle me-1"></i>
                    Setelah disimpan, tautan aktivasi akan dikirim ke email asesi agar bisa mengatur kata sandi.
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required">Nama Lengkap</label>
                        <input type="text" name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" required maxlength="150"
                               placeholder="Sesuai KTP / identitas resmi">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required">NIK (16 digit)</label>
                        <input type="text" name="nik"
                               class="form-control @error('nik') is-invalid @enderror"
                               value="{{ old('nik') }}" required maxlength="16" minlength="16"
                               pattern="\d{16}" inputmode="numeric"
                               placeholder="3201xxxxxxxxxxxxxx">
                        @error('nik')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required">Email</label>
                        <input type="email" name="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" required maxlength="150"
                               placeholder="email@contoh.com">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required">Nomor Telepon / HP</label>
                        <input type="text" name="phone"
                               class="form-control @error('phone') is-invalid @enderror"
                               value="{{ old('phone') }}" required maxlength="20"
                               placeholder="08xxxxxxxxxx">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </x-tabler.card>

            <x-tabler.card title="Identitas Pribadi" class="mt-3">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="form-select">
                            <option value="">—</option>
                            <option value="L" @selected(old('jenis_kelamin') === 'L')>Laki-laki</option>
                            <option value="P" @selected(old('jenis_kelamin') === 'P')>Perempuan</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir"
                               class="form-control"
                               value="{{ old('tempat_lahir') }}" maxlength="100">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir"
                               class="form-control @error('tanggal_lahir') is-invalid @enderror"
                               value="{{ old('tanggal_lahir') }}">
                        @error('tanggal_lahir')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required">Kebangsaan</label>
                        <input type="text" name="kebangsaan"
                               class="form-control"
                               value="{{ old('kebangsaan', 'Indonesia') }}" required maxlength="100">
                    </div>
                </div>
            </x-tabler.card>

            <x-tabler.card title="Alamat Tinggal" class="mt-3">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Provinsi</label>
                        <select name="provinsi_code" class="form-select" id="asesi-province-select">
                            <option value="">—</option>
                            @foreach ($provinces as $province)
                                <option value="{{ $province->code }}"
                                        @selected(old('provinsi_code') === $province->code)>
                                    {{ $province->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Kota / Kabupaten</label>
                        <select name="kota_code" class="form-select" id="asesi-city-select">
                            <option value="">—</option>
                            @foreach ($cities as $city)
                                <option value="{{ $city->code }}"
                                        data-province="{{ $city->province_code }}"
                                        @selected(old('kota_code') === $city->code)>
                                    {{ $city->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Alamat Rumah</label>
                        <textarea name="alamat_rumah" class="form-control" rows="2" maxlength="1000">{{ old('alamat_rumah') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Kelurahan</label>
                        <input type="text" name="kelurahan" class="form-control"
                               value="{{ old('kelurahan') }}" maxlength="100">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Kode Pos</label>
                        <input type="text" name="kode_pos" class="form-control"
                               value="{{ old('kode_pos') }}" maxlength="10" inputmode="numeric">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Telepon Rumah</label>
                        <input type="text" name="telp_rumah" class="form-control"
                               value="{{ old('telp_rumah') }}" maxlength="50">
                    </div>
                </div>
            </x-tabler.card>

            <x-tabler.card title="Pekerjaan & Kantor" class="mt-3">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Instansi / Perusahaan</label>
                        <input type="text" name="pekerjaan_instansi" class="form-control"
                               value="{{ old('pekerjaan_instansi') }}" maxlength="200">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Jabatan</label>
                        <input type="text" name="jabatan" class="form-control"
                               value="{{ old('jabatan') }}" maxlength="150">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Alamat Kantor</label>
                        <textarea name="alamat_kantor" class="form-control" rows="2" maxlength="1000">{{ old('alamat_kantor') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Telepon Kantor</label>
                        <input type="text" name="telp_kantor" class="form-control"
                               value="{{ old('telp_kantor') }}" maxlength="50">
                    </div>
                </div>
            </x-tabler.card>

        </div>

        {{-- Kolom Kanan: Pendidikan + Aksi --}}
        <div class="col-lg-4">

            <x-tabler.card title="Pendidikan">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Pendidikan Terakhir</label>
                        <select name="pendidikan_terakhir" class="form-select">
                            <option value="">—</option>
                            @foreach (['SD', 'SMP', 'SMA/SMK', 'D1', 'D2', 'D3', 'D4/S1', 'S2', 'S3'] as $jenjang)
                                <option value="{{ $jenjang }}"
                                        @selected(old('pendidikan_terakhir') === $jenjang)>
                                    {{ $jenjang }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </x-tabler.card>

            <x-tabler.card title="Simpan" class="mt-3">
                <p class="text-muted small">
                    No. registrasi akan dibuat otomatis. Status verifikasi awal: <strong>belum terverifikasi</strong>.
                </p>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-user-plus me-1"></i> Tambah Asesi
                    </button>
                    <a href="{{ route('admin.asesi.index') }}" class="btn btn-outline-secondary">
                        Batal
                    </a>
                </div>
            </x-tabler.card>

        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
(function () {
    if (window.__asesiCityFilter) return;
    window.__asesiCityFilter = true;

    function filterCities() {
        var provSelect = document.getElementById('asesi-province-select');
        var citySelect = document.getElementById('asesi-city-select');
        if (!provSelect || !citySelect) return;
        var prov = provSelect.value;
        Array.prototype.forEach.call(citySelect.options, function (opt) {
            if (!opt.value) { opt.hidden = false; return; }
            var p = opt.getAttribute('data-province');
            opt.hidden = prov !== '' && p !== prov;
        });
        if (citySelect.selectedOptions.length && citySelect.selectedOptions[0].hidden) {
            citySelect.value = '';
        }
    }

    document.getElementById('asesi-province-select')?.addEventListener('change', filterCities);
    filterCities();
})();
</script>
@endpush
