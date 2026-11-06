@extends('layouts.auth')

@section('title', 'Registrasi Asesi')
@section('container_class', 'container-md')
@section('page_class', 'page-top')

@section('content')
<div class="card card-md shadow-sm register-card">
    <div class="card-body">
        <header class="register-header mb-4">
            <h2 class="h2 mb-1">Registrasi Asesi</h2>
            <p class="text-secondary mb-0">
                {{ $institution->name }} · Lengkapi data pokok untuk memulai sertifikasi.
            </p>
        </header>

        @if ($errors->any())
            <div class="alert alert-danger mb-4" role="alert" id="error-summary">
                <div class="d-flex">
                    <div><i class="ti ti-alert-circle alert-icon me-2" aria-hidden="true"></i></div>
                    <div>
                        <div class="fw-semibold mb-1">Periksa kembali data Anda:</div>
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <script type="application/json" id="preselected-city">@json(old('kota_code', ''))</script>

        <form method="POST" action="{{ route('register.store') }}" id="register-form" class="register-form">
            @csrf

            {{-- 1. Akun --}}
            <section class="register-section" id="section-akun" aria-labelledby="heading-akun">
                <h3 class="register-section-title" id="heading-akun">Akun</h3>

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label" for="email">Email <span class="required-mark" aria-hidden="true">*</span></label>
                        <input type="email" name="email" id="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" required maxlength="150"
                               autocomplete="email" inputmode="email" aria-describedby="email-error">
                        @error('email')
                            <div class="invalid-feedback d-block" id="email-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="password">Kata Sandi <span class="required-mark" aria-hidden="true">*</span></label>
                        <div class="input-group input-group-flat">
                            <span class="input-group-text" aria-hidden="true">
                                <i class="ti ti-lock text-secondary"></i>
                            </span>
                            <input type="password" name="password" id="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   required minlength="8" autocomplete="new-password"
                                   aria-describedby="password-hint password-strength">
                            <button type="button" class="input-group-text cursor-pointer register-icon-button" id="toggle-password"
                                    aria-label="Tampilkan atau sembunyikan kata sandi" aria-controls="password" aria-pressed="false">
                                <i class="ti ti-eye text-secondary" id="password-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                        <div class="form-hint" id="password-hint">Minimal 8 karakter, kombinasi huruf dan angka.</div>
                        <div class="password-strength" id="password-strength" data-strength="0" role="status" aria-live="polite" aria-label="Kekuatan kata sandi">
                            <div class="progress progress-sm password-strength-track" aria-hidden="true">
                                <div class="progress-bar" id="password-strength-bar" style="width: 0%"></div>
                            </div>
                            <span class="password-strength-label" id="password-strength-label">Lemah</span>
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block" id="password-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="password_confirmation">Konfirmasi Kata Sandi <span class="required-mark" aria-hidden="true">*</span></label>
                        <div class="input-group input-group-flat">
                            <span class="input-group-text" aria-hidden="true">
                                <i class="ti ti-lock text-secondary"></i>
                            </span>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                   class="form-control @error('password_confirmation') is-invalid @enderror"
                                   required minlength="8" autocomplete="new-password"
                                   aria-describedby="password-confirmation-error">
                            <button type="button" class="input-group-text cursor-pointer register-icon-button" id="toggle-password-confirmation"
                                    aria-label="Tampilkan atau sembunyikan konfirmasi kata sandi" aria-controls="password_confirmation" aria-pressed="false">
                                <i class="ti ti-eye text-secondary" id="password-confirmation-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback d-none" id="password-confirmation-error">Kata sandi tidak sama.</div>
                        @error('password_confirmation')
                            <div class="invalid-feedback d-block" id="password-confirmation-server-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </section>

            {{-- 2. Data Pribadi --}}
            <section class="register-section" id="section-pribadi" aria-labelledby="heading-pribadi">
                <h3 class="register-section-title" id="heading-pribadi">Data Pribadi</h3>

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label" for="name">Nama Lengkap <span class="required-mark" aria-hidden="true">*</span></label>
                        <input type="text" name="name" id="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" required maxlength="150" autocomplete="name">
                        @error('name')
                            <div class="invalid-feedback d-block" id="name-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="nik">NIK (16 digit) <span class="required-mark" aria-hidden="true">*</span></label>
                        <div class="register-input-suffix">
                            <input type="text" name="nik" id="nik"
                                   class="form-control @error('nik') is-invalid @enderror"
                                   value="{{ old('nik') }}" required inputmode="numeric"
                                   pattern="\d{16}" maxlength="16" autocomplete="off"
                                   placeholder="3201xxxxxxxxxxxx" aria-describedby="nik-hint nik-counter">
                            <span class="register-suffix-badge" id="nik-counter" aria-hidden="true">0/16</span>
                        </div>
                        <div class="form-hint" id="nik-hint">16 digit sesuai KTP/KIA.</div>
                        @error('nik')
                            <div class="invalid-feedback d-block" id="nik-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="phone">Nomor HP <span class="required-mark" aria-hidden="true">*</span></label>
                        <input type="tel" name="phone" id="phone"
                               class="form-control @error('phone') is-invalid @enderror"
                               value="{{ old('phone') }}" required maxlength="20"
                               inputmode="tel" autocomplete="tel" placeholder="08xxxxxxxxxx"
                               aria-describedby="phone-hint phone-error">
                        <div class="form-hint" id="phone-hint">Format Indonesia: 08…, +62…, atau 62…</div>
                        <div class="invalid-feedback" id="phone-error">Nomor HP harus format Indonesia (08 / +62 / 62).</div>
                        @error('phone')
                            <div class="invalid-feedback d-block" id="phone-server-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="jenis_kelamin">Jenis Kelamin <span class="required-mark" aria-hidden="true">*</span></label>
                        <select name="jenis_kelamin" id="jenis_kelamin" class="form-select @error('jenis_kelamin') is-invalid @enderror"
                                required autocomplete="sex">
                            <option value="">Pilih</option>
                            <option value="L" @selected(old('jenis_kelamin') === 'L')>Laki-laki</option>
                            <option value="P" @selected(old('jenis_kelamin') === 'P')>Perempuan</option>
                        </select>
                        @error('jenis_kelamin')
                            <div class="invalid-feedback d-block" id="jenis-kelamin-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="tempat_lahir">Tempat Lahir <span class="required-mark" aria-hidden="true">*</span></label>
                        <input type="text" name="tempat_lahir" id="tempat_lahir"
                               class="form-control @error('tempat_lahir') is-invalid @enderror"
                               value="{{ old('tempat_lahir') }}" required maxlength="100" autocomplete="address-level2">
                        @error('tempat_lahir')
                            <div class="invalid-feedback d-block" id="tempat-lahir-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="tanggal_lahir">Tanggal Lahir <span class="required-mark" aria-hidden="true">*</span></label>
                        <input type="date" name="tanggal_lahir" id="tanggal_lahir"
                               class="form-control @error('tanggal_lahir') is-invalid @enderror"
                               value="{{ old('tanggal_lahir') }}" required
                               min="1900-01-01" max="{{ now()->format('Y-m-d') }}"
                               autocomplete="bday" aria-describedby="tanggal-lahir-hint">
                        <div class="form-hint" id="tanggal-lahir-hint">Wajib diisi, sebelum hari ini.</div>
                        @error('tanggal_lahir')
                            <div class="invalid-feedback d-block" id="tanggal-lahir-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </section>

            {{-- 3. Alamat --}}
            <section class="register-section" id="section-alamat" aria-labelledby="heading-alamat">
                <h3 class="register-section-title" id="heading-alamat">Alamat</h3>

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label" for="alamat_rumah">Alamat Rumah <span class="required-mark" aria-hidden="true">*</span></label>
                        <textarea name="alamat_rumah" id="alamat_rumah" rows="3"
                                  class="form-control @error('alamat_rumah') is-invalid @enderror"
                                  required maxlength="1000" autocomplete="street-address"
                                  aria-describedby="alamat-counter">{{ old('alamat_rumah') }}</textarea>
                        <div class="register-char-counter" id="alamat-counter" aria-live="polite">0/1000</div>
                        @error('alamat_rumah')
                            <div class="invalid-feedback d-block" id="alamat-rumah-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="provinsi_code_search">Provinsi <span class="required-mark" aria-hidden="true">*</span></label>
                        <select name="provinsi_code" id="provinsi_code"
                                class="form-select @error('provinsi_code') is-invalid @enderror"
                                required autocomplete="address-level1" data-placeholder="Ketik nama provinsi…"
                                aria-hidden="true" tabindex="-1">
                            <option value="">Pilih provinsi</option>
                            @foreach ($provinces as $province)
                                <option value="{{ $province->code }}" @selected(old('provinsi_code') === $province->code)>
                                    {{ $province->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('provinsi_code')
                            <div class="invalid-feedback d-block" id="provinsi-code-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="kota_code_search">Kota / Kabupaten <span class="required-mark" aria-hidden="true">*</span></label>
                        <select name="kota_code" id="kota_code"
                                class="form-select @error('kota_code') is-invalid @enderror"
                                required autocomplete="address-level2" disabled
                                data-placeholder="Ketik nama kota/kabupaten…" aria-hidden="true" tabindex="-1">
                            <option value="">Pilih provinsi terlebih dahulu</option>
                            @if (old('kota_code'))
                                <option value="{{ old('kota_code') }}" selected>Kota tersimpan ({{ old('kota_code') }})</option>
                            @endif
                        </select>
                        <div class="form-hint d-none" id="kota_loading" role="status" aria-live="polite">
                            <span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>
                            Memuat daftar kota…
                        </div>
                        <div class="invalid-feedback d-none" id="kota_error" role="alert"></div>
                        @error('kota_code')
                            <div class="invalid-feedback d-block" id="kota-code-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </section>

            {{-- 4. Pendidikan --}}
            <section class="register-section" id="section-pendidikan" aria-labelledby="heading-pendidikan">
                <h3 class="register-section-title" id="heading-pendidikan">Pendidikan</h3>

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label" for="pendidikan_terakhir">Pendidikan Terakhir <span class="required-mark" aria-hidden="true">*</span></label>
                        <input type="text" name="pendidikan_terakhir" id="pendidikan_terakhir"
                               class="form-control @error('pendidikan_terakhir') is-invalid @enderror"
                               value="{{ old('pendidikan_terakhir') }}" required maxlength="100"
                               autocomplete="off" placeholder="Contoh: SMK RPL / D3 Informatika">
                        @error('pendidikan_terakhir')
                            <div class="invalid-feedback d-block" id="pendidikan-terakhir-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </section>

            <div class="register-consent">
                <label class="form-check mb-2" for="consent">
                    <input type="checkbox" name="consent" value="1" class="form-check-input @error('consent') is-invalid @enderror"
                           id="consent" required @checked(old('consent'))>
                    <span class="form-check-label">
                        Saya menyetujui pemrosesan data pribadi untuk keperluan sertifikasi sesuai
                        <a href="{{ route('kebijakan-privasi') }}" target="_blank" rel="noopener noreferrer">Kebijakan Privasi</a>.
                    </span>
                </label>
                @error('consent')
                    <div class="invalid-feedback d-block" id="consent-error">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary w-100 register-submit" id="register-submit">
                <span class="register-submit-label"><i class="ti ti-user-plus me-2" aria-hidden="true"></i>Daftar Sebagai Asesi</span>
            </button>
        </form>
    </div>
</div>

<div class="register-help text-secondary mt-3">
    Sudah punya akun? <a href="{{ route('login') }}" class="fw-medium text-decoration-none">Masuk di sini</a>
</div>
@endsection
