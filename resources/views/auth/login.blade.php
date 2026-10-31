@extends('layouts.auth')

@section('title', 'Masuk ke SiapKompeten')

@section('content')
<div class="card card-md shadow-sm">
    <div class="card-body">
        <h2 class="h2 text-center mb-1">Masuk ke Portal</h2>
        @if ($institution)
            <p class="text-secondary text-center small mb-4">{{ $institution->name }}</p>
        @else
            <div class="mb-4"></div>
        @endif
        
        <form method="POST" action="{{ route('login.store') }}" autocomplete="off">
            @csrf

            <div class="mb-3">
                <label class="form-label" for="login">Email, NIK, atau Username</label>
                <div class="input-icon">
                    <span class="input-icon-addon">
                        <i class="ti ti-user text-secondary"></i>
                    </span>
                    <input
                        type="text"
                        name="login"
                        id="login"
                        class="form-control @error('login') is-invalid @enderror @error('email') is-invalid @enderror"
                        placeholder="Masukkan Email, 16 digit NIK, atau Username"
                        value="{{ old('login') ?: old('email') }}"
                        required
                        autofocus
                    >
                </div>
                <small class="form-hint">
                    Admin: Username · Asesor: NIK · Asesi: NIK atau Email
                </small>
            </div>

            <div class="mb-3">
                <label class="form-label d-flex justify-content-between align-items-center" for="password">
                    <span>Kata Sandi</span>
                    <a href="#lupa-sandi" class="small text-decoration-none">Lupa kata sandi?</a>
                </label>
                <div class="input-group input-group-flat">
                    <span class="input-group-text">
                        <i class="ti ti-key text-secondary"></i>
                    </span>
                    <input
                        type="password"
                        name="password"
                        id="password"
                        class="form-control @error('password') is-invalid @enderror"
                        placeholder="Kata sandi Anda"
                        autocomplete="current-password"
                        required
                    >
                </div>
            </div>

            <div class="mb-3">
                <label class="form-check">
                    <input type="checkbox" name="remember" value="1" class="form-check-input" {{ old('remember') ? 'checked' : '' }}>
                    <span class="form-check-label">Ingat saya di perangkat ini</span>
                </label>
            </div>

            <div class="form-footer">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="ti ti-login me-1"></i> Masuk Sekarang
                </button>
            </div>
        </form>
    </div>
</div>

<div class="text-center text-secondary mt-3">
    Belum memiliki akun asesi? <a href="{{ route('register') }}" class="fw-medium text-decoration-none">Daftar sebagai Asesi</a>
</div>
@endsection
