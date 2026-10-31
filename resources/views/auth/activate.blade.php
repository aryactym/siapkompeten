@extends('layouts.auth')

@section('title', 'Aktivasi Akun Asesi')

@section('content')
<div class="card card-md shadow-sm">
    <div class="card-body">
        <h2 class="h2 text-center mb-2">Aktivasi Akun</h2>
        <p class="text-secondary text-center mb-4">
            Halo <strong>{{ $user->name }}</strong>, buat kata sandi untuk akun asesi Anda.
        </p>

        <form method="POST" action="{{ $action }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" value="{{ $user->email }}" readonly disabled>
            </div>

            <div class="mb-3">
                <label class="form-label required" for="password">Kata Sandi Baru</label>
                <input type="password" name="password" id="password"
                       class="form-control @error('password') is-invalid @enderror"
                       autocomplete="new-password" required minlength="8">
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <small class="form-hint">Minimal 8 karakter, mengandung huruf dan angka.</small>
            </div>

            <div class="mb-3">
                <label class="form-label required" for="password_confirmation">Ulangi Kata Sandi</label>
                <input type="password" name="password_confirmation" id="password_confirmation"
                       class="form-control" autocomplete="new-password" required minlength="8">
            </div>

            <div class="form-footer">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="ti ti-key me-1"></i> Simpan Kata Sandi
                </button>
            </div>
        </form>
    </div>
</div>

<div class="text-center text-secondary mt-3">
    Sudah punya kata sandi? <a href="{{ route('login') }}" class="fw-medium text-decoration-none">Masuk di sini</a>
</div>
@endsection
