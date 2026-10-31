@extends('layouts.asesi')

@section('title', 'Profil & Dokumen Pokok')
@section('pretitle', 'Kelengkapan Data')
@section('page-title', 'Profil & Dokumen Pokok')

@section('content')
@unless ($asesi->isProfileComplete())
    <div class="alert alert-warning">
        <i class="ti ti-alert-triangle me-1"></i>
        Lengkapi profil pokok (NIK, data pribadi, alamat, wilayah, pendidikan, dan telepon) agar dapat mendaftar skema sertifikasi.
    </div>
@endunless

<form method="POST" action="{{ route('asesi.profil.update') }}">
    @csrf
    @method('PUT')

    <div class="row row-cards">
        <div class="col-lg-8">
            <x-tabler.card title="Data Pribadi">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $asesi->user->name) }}" required maxlength="150">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">NIK (tidak dapat diubah)</label>
                        <input type="text" class="form-control" value="{{ $asesi->user->masked_nik }}" disabled>
                        <div class="form-hint">Hubungi admin untuk koreksi NIK.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required">Email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $asesi->user->email) }}" required maxlength="150">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required">Nomor HP</label>
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                               value="{{ old('phone', $asesi->user->phone) }}" required maxlength="20" placeholder="08xxxxxxxxxx">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="form-select" required>
                            <option value="">—</option>
                            <option value="L" @selected(old('jenis_kelamin', $asesi->jenis_kelamin) === 'L')>Laki-laki</option>
                            <option value="P" @selected(old('jenis_kelamin', $asesi->jenis_kelamin) === 'P')>Perempuan</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" class="form-control" value="{{ old('tempat_lahir', $asesi->tempat_lahir) }}" required maxlength="100">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" class="form-control" value="{{ old('tanggal_lahir', $asesi->tanggal_lahir?->toDateString()) }}" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label required">Alamat Rumah</label>
                        <textarea name="alamat_rumah" class="form-control" rows="2" required maxlength="1000">{{ old('alamat_rumah', $asesi->alamat_rumah) }}</textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Kelurahan / Desa</label>
                        <input type="text" name="kelurahan" class="form-control" value="{{ old('kelurahan', $asesi->kelurahan) }}" maxlength="100">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required">Provinsi</label>
                        <select name="provinsi_code" id="provinsi_code" class="form-select" required>
                            <option value="">—</option>
                            @foreach ($provinces as $province)
                                <option value="{{ $province->code }}" @selected(old('provinsi_code', $asesi->provinsi_code) === $province->code)>
                                    {{ $province->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required">Kota / Kabupaten</label>
                        <select name="kota_code" id="kota_code" class="form-select" required>
                            <option value="">—</option>
                            @foreach ($cities as $city)
                                <option value="{{ $city->code }}" data-province="{{ $city->province_code }}"
                                    @selected(old('kota_code', $asesi->kota_code) === $city->code)>
                                    {{ $city->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Kode Pos</label>
                        <input type="text" name="kode_pos" class="form-control" value="{{ old('kode_pos', $asesi->kode_pos) }}" maxlength="10">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Telepon Rumah</label>
                        <input type="text" name="telp_rumah" class="form-control" value="{{ old('telp_rumah', $asesi->telp_rumah) }}" maxlength="50">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Telepon Kantor</label>
                        <input type="text" name="telp_kantor" class="form-control" value="{{ old('telp_kantor', $asesi->telp_kantor) }}" maxlength="50">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label required">Kebangsaan</label>
                        <input type="text" name="kebangsaan" class="form-control" value="{{ old('kebangsaan', $asesi->kebangsaan ?? 'Indonesia') }}" required maxlength="100">
                    </div>
                </div>
            </x-tabler.card>
        </div>

        <div class="col-lg-4">
            <x-tabler.card title="Pendidikan & Pekerjaan">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label required">Pendidikan Terakhir</label>
                        <input type="text" name="pendidikan_terakhir" class="form-control @error('pendidikan_terakhir') is-invalid @enderror"
                               value="{{ old('pendidikan_terakhir', $asesi->pendidikan_terakhir) }}" required maxlength="100"
                               placeholder="Contoh: D3 Teknik Informatika">
                        @error('pendidikan_terakhir')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">Instansi / Perusahaan</label>
                        <input type="text" name="pekerjaan_instansi" class="form-control" value="{{ old('pekerjaan_instansi', $asesi->pekerjaan_instansi) }}" maxlength="200">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Jabatan</label>
                        <input type="text" name="jabatan" class="form-control" value="{{ old('jabatan', $asesi->jabatan) }}" maxlength="150">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Alamat Kantor</label>
                        <textarea name="alamat_kantor" class="form-control" rows="2" maxlength="1000">{{ old('alamat_kantor', $asesi->alamat_kantor) }}</textarea>
                    </div>
                </div>

                <div class="mt-4 d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i> Simpan Profil
                    </button>
                    <a href="{{ route('asesi.pendaftaran.index') }}" class="btn btn-outline-secondary">Pendaftaran Saya</a>
                </div>
            </x-tabler.card>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    const provinceSelect = document.getElementById('provinsi_code');
    const citySelect = document.getElementById('kota_code');
    const cityOptions = Array.from(citySelect.querySelectorAll('option[data-province]'));

    function filterCities() {
        const province = provinceSelect.value;
        cityOptions.forEach((option) => {
            option.hidden = province !== '' && option.dataset.province !== province;
        });
    }

    provinceSelect.addEventListener('change', filterCities);
    filterCities();
</script>
@endpush
