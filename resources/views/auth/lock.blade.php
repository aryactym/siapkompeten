@extends('layouts.lock')

@section('content')
<div class="card card-md shadow-sm border-0">
    <div class="card-body text-center py-4">
        <div class="mb-3">
            <span class="avatar avatar-xl rounded-circle bg-primary-lt text-primary fw-bold fs-1">
                {{ strtoupper(substr($user->name ?? 'U', 0, 2)) }}
            </span>
        </div>
        <h2 class="card-title mb-1 fs-2">{{ $user->name }}</h2>
        <div class="text-secondary small mb-3">
            <span class="badge bg-yellow-lt text-yellow mb-1">
                <i class="ti ti-lock me-1"></i> Sesi Terkunci
            </span>
            <div class="text-muted">{{ $user->email }}</div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible text-start" role="alert">
                <div class="d-flex">
                    <div>
                        <i class="ti ti-alert-triangle fs-2 me-2"></i>
                    </div>
                    <div>
                        <div class="fw-semibold">Gagal Membuka Kunci</div>
                        <div class="text-secondary small">{{ $errors->first() }}</div>
                    </div>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('lock.unlock') }}" autocomplete="off">
            @csrf
            <div class="mb-3 text-start">
                <label class="form-label required">Kata Sandi</label>
                <div class="input-group input-group-flat">
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="form-control @error('password') is-invalid @enderror" 
                           placeholder="Masukkan kata sandi Anda" 
                           required 
                           autofocus>
                    <span class="input-group-text">
                        <a href="javascript:void(0)" class="link-secondary" title="Tampilkan sandi" onclick="togglePasswordVisibility()">
                            <i id="toggle-eye" class="ti ti-eye fs-2"></i>
                        </a>
                    </span>
                </div>
            </div>

            <div class="form-footer mt-4">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="ti ti-lock-open me-2"></i> Buka Kunci Layar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function togglePasswordVisibility() {
        const input = document.getElementById('password');
        const icon = document.getElementById('toggle-eye');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'ti ti-eye-off fs-2';
        } else {
            input.type = 'password';
            icon.className = 'ti ti-eye fs-2';
        }
    }
</script>
@endsection
