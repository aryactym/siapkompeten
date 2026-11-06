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

            {{-- Identitas login --}}
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
                @error('login')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            {{-- Kata sandi --}}
            <div class="mb-3">
                <label class="form-label d-flex justify-content-between align-items-center" for="password">
                    <span>Kata Sandi</span>
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
                    <span class="input-group-text cursor-pointer" id="toggle-password"
                          title="Tampilkan/sembunyikan kata sandi" role="button" tabindex="0"
                          aria-label="Tampilkan atau sembunyikan kata sandi">
                        <i class="ti ti-eye text-secondary" id="password-eye"></i>
                    </span>
                </div>
                @error('password')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            {{-- ====== PILIHAN PORTAL ====== --}}
            <div class="mb-3">
                <label class="form-label d-flex align-items-center gap-1">
                    Masuk sebagai
                </label>
                <div class="d-flex flex-wrap gap-2" role="radiogroup" aria-label="Pilih portal">
                    @foreach ([
                        'asesi'  => ['Peserta (Asesi)',  'ti-user-check',   'primary'],
                        'asesor' => ['Asesor',            'ti-clipboard-check', 'teal'],
                        'admin'  => ['Pengelola', 'ti-settings', 'orange'],
                    ] as $value => [$label, $icon, $color])
                        <label class="portal-option @error('login_as') is-invalid @enderror"
                               style="cursor:pointer;">
                            <input type="radio" name="login_as" value="{{ $value }}"
                                   class="visually-hidden portal-radio"
                                   {{ old('login_as', 'asesi') === $value ? 'checked' : '' }}>
                            <span class="portal-card d-flex align-items-center gap-2 px-3 py-2 rounded border bg-white">
                                <i class="ti {{ $icon }} text-{{ $color }}"></i>
                                <span class="small fw-medium">{{ $label }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('login_as')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
                <div class="form-hint mt-1">
                    Satu akun dapat memiliki lebih dari satu peran — pilih portal yang ingin digunakan.
                </div>
            </div>

            {{-- Ingat saya --}}
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
    Belum memiliki akun asesi?
    <a href="{{ route('register') }}" class="fw-medium text-decoration-none">Daftar sebagai Asesi</a>
</div>

@push('styles')
<style>
    /* Portal selector — khusus halaman login */
    .portal-radio:checked + .portal-card {
        border-color: var(--tblr-primary) !important;
        background-color: var(--tblr-primary-lt) !important;
        box-shadow: 0 0 0 2px rgba(var(--tblr-primary-rgb), .2);
    }
    .portal-card {
        transition: border-color .15s, background-color .15s;
        user-select: none;
        min-width: 120px;
    }
    .portal-card:hover {
        border-color: var(--tblr-border-color-translucent);
        background-color: var(--tblr-bg-surface-secondary) !important;
    }
</style>
@endpush

@push('scripts')
<script>
// Toggle show/hide password
document.getElementById('toggle-password')?.addEventListener('click', function () {
    const input = document.getElementById('password');
    const eye   = document.getElementById('password-eye');
    if (input.type === 'password') {
        input.type = 'text';
        eye.classList.replace('ti-eye', 'ti-eye-off');
    } else {
        input.type = 'password';
        eye.classList.replace('ti-eye-off', 'ti-eye');
    }
});
</script>
@endpush
@endsection
