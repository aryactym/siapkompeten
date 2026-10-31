@extends('layouts.auth')

@section('title', 'Registrasi Asesi')

@section('content')
<div class="card card-md shadow-sm">
    <div class="card-body">
        <h2 class="h2 text-center mb-1">Registrasi Asesi</h2>
        <p class="text-secondary text-center small mb-4">
            {{ $institution->name }} · Lengkapi data pokok untuk memulai sertifikasi.
        </p>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register.store') }}" autocomplete="off">
            @csrf

            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label required">Nama Lengkap</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required maxlength="150">
                </div>
                <div class="col-md-6">
                    <label class="form-label required">NIK (16 digit)</label>
                    <input type="text" name="nik" class="form-control" value="{{ old('nik') }}"
                           required inputmode="numeric" pattern="\d{16}" maxlength="16"
                           placeholder="3201xxxxxxxxxxxx">
                    <div class="form-hint">NIK disimpan terenkripsi dan digunakan untuk login.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label required">Nomor HP</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}"
                           required maxlength="20" placeholder="08xxxxxxxxxx">
                </div>
                <div class="col-12">
                    <label class="form-label required">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required maxlength="150">
                </div>
                <div class="col-md-6">
                    <label class="form-label required">Kata Sandi</label>
                    <input type="password" name="password" class="form-control" required minlength="8" autocomplete="new-password">
                    <div class="form-hint">Minimal 8 karakter, kombinasi huruf dan angka.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label required">Konfirmasi Kata Sandi</label>
                    <input type="password" name="password_confirmation" class="form-control" required minlength="8" autocomplete="new-password">
                </div>

                <div class="col-md-4">
                    <label class="form-label required">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="form-select" required>
                        <option value="">Pilih</option>
                        <option value="L" @selected(old('jenis_kelamin') === 'L')>Laki-laki</option>
                        <option value="P" @selected(old('jenis_kelamin') === 'P')>Perempuan</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label required">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" class="form-control" value="{{ old('tempat_lahir') }}" required maxlength="100">
                </div>
                <div class="col-md-4">
                    <label class="form-label required">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" class="form-control" value="{{ old('tanggal_lahir') }}" required>
                </div>

                <div class="col-12">
                    <label class="form-label required">Alamat Rumah</label>
                    <textarea name="alamat_rumah" class="form-control" rows="2" required maxlength="1000">{{ old('alamat_rumah') }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label required" for="provinsi_code">Provinsi</label>
                    <select name="provinsi_code" id="provinsi_code" class="form-select" required
                            autocomplete="address-level1">
                        <option value="">Pilih provinsi</option>
                        @foreach ($provinces as $province)
                            <option value="{{ $province->code }}" @selected(old('provinsi_code') === $province->code)>
                                {{ $province->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label required" for="kota_code">Kota / Kabupaten</label>
                    <select name="kota_code" id="kota_code" class="form-select" required
                            autocomplete="address-level2" disabled>
                        <option value="">Pilih provinsi terlebih dahulu</option>
                        @if (old('kota_code'))
                            {{-- Opsi lama dipertahankan agar re-submit tetap terisi --}}
                            <option value="{{ old('kota_code') }}" selected>{{ old('kota_code') }}</option>
                        @endif
                    </select>
                    <div class="form-hint d-none" id="kota_loading">
                        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                        Memuat daftar kota…
                    </div>
                    <div class="invalid-feedback" id="kota_error"></div>
                </div>
                <div class="col-12">
                    <label class="form-label required">Pendidikan Terakhir</label>
                    <input type="text" name="pendidikan_terakhir" class="form-control" value="{{ old('pendidikan_terakhir') }}"
                           required maxlength="100" placeholder="Contoh: SMK RPL / D3 Informatika">
                </div>

                <div class="col-12">
                    <label class="form-check">
                        <input type="checkbox" name="consent" value="1" class="form-check-input" required
                               @checked(old('consent'))>
                        <span class="form-check-label small">
                            Saya menyetujui pemrosesan data pribadi untuk keperluan sertifikasi sesuai kebijakan lembaga.
                        </span>
                    </label>
                </div>
            </div>

            <div class="form-footer mt-4">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="ti ti-user-plus me-1"></i> Daftar Sebagai Asesi
                </button>
            </div>
        </form>
    </div>
</div>

<div class="text-center text-secondary mt-3">
    Sudah memiliki akun? <a href="{{ route('login') }}" class="fw-medium text-decoration-none">Masuk di sini</a>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const provinceSelect = document.getElementById('provinsi_code');
    const citySelect     = document.getElementById('kota_code');
    const loadingHint    = document.getElementById('kota_loading');
    const errorHint      = document.getElementById('kota_error');

    // Nilai yang perlu dipra-pilih setelah kota dimuat (saat ada old() dari server)
    const preselectedCity = @json(old('kota_code', ''));

    function setLoading(isLoading) {
        loadingHint.classList.toggle('d-none', !isLoading);
        citySelect.disabled = isLoading;
    }

    function setError(message) {
        errorHint.textContent = message;
        citySelect.classList.toggle('is-invalid', message !== '');
    }

    async function loadCities(provinceCode) {
        if (!provinceCode) {
            citySelect.innerHTML = '<option value="">Pilih provinsi terlebih dahulu</option>';
            citySelect.disabled = true;
            return;
        }

        setLoading(true);
        setError('');

        try {
            const url = '/wilayah/provinsi/' + encodeURIComponent(provinceCode) + '/kota';
            const response = await fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (!response.ok) {
                throw new Error('Gagal memuat daftar kota (HTTP ' + response.status + ').');
            }

            const cities = await response.json();

            citySelect.innerHTML = '<option value="">Pilih kota/kabupaten</option>';
            cities.forEach(function (city) {
                const option = document.createElement('option');
                option.value = city.code;
                option.textContent = city.nama;
                if (city.code === preselectedCity) {
                    option.selected = true;
                }
                citySelect.appendChild(option);
            });

            citySelect.disabled = false;
        } catch (err) {
            citySelect.innerHTML = '<option value="">Pilih kota/kabupaten</option>';
            citySelect.disabled = true;
            setError(err.message || 'Gagal memuat kota. Coba lagi.');
        } finally {
            setLoading(false);
        }
    }

    provinceSelect.addEventListener('change', function () {
        loadCities(this.value);
    });

    // Jika ada pilihan provinsi sebelumnya (old() setelah validasi gagal), langsung muat
    if (provinceSelect.value) {
        loadCities(provinceSelect.value);
    }
})();
</script>
@endpush
