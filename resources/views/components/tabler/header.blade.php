@php
    use App\Support\TenantContext;

    $user = auth()->user();
    $activeInstitution = TenantContext::institution();
    $accessibleInstitutions = $user ? $user->accessibleInstitutions() : collect();
    $hasMultipleTenants = $accessibleInstitutions->count() > 1;
@endphp

<header class="navbar navbar-expand-md d-print-none border-bottom bg-white">
    <div class="container-xl">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu" aria-controls="navbar-menu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Brand / Current Institution -->
        <h1 class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
            <a href="{{ route('dashboard') }}" class="d-flex align-items-center gap-2 text-decoration-none text-dark">
                @if ($activeInstitution && $activeInstitution->logo_path)
                    <img src="{{ $activeInstitution->logo_path }}" alt="{{ $activeInstitution->name }}" class="avatar avatar-sm rounded">
                @else
                    <span class="avatar avatar-sm bg-primary-lt text-primary fw-bold">
                        {{ strtoupper(substr($activeInstitution ? $activeInstitution->name : config('app.name'), 0, 2)) }}
                    </span>
                @endif
                <div class="d-flex flex-column text-start">
                    <span class="fw-bold fs-4 lh-1">{{ $activeInstitution ? $activeInstitution->name : config('app.name') }}</span>
                    <span class="text-secondary fs-6">Sistem Informasi LSP</span>
                </div>
            </a>
        </h1>

        <div class="navbar-nav flex-row order-md-last align-items-center gap-2">
            <!-- Tenant Switcher Dropdown (Shown when user has access to > 1 institution) -->
            @if ($hasMultipleTenants)
                <div class="nav-item dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle d-flex align-items-center gap-1" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="ti ti-building me-1"></i>
                        <span class="d-none d-sm-inline">Pindah LSP</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end shadow-sm">
                        <div class="dropdown-header">Lembaga Terdaftar</div>
                        @foreach ($accessibleInstitutions as $inst)
                            <form method="POST" action="{{ route('tenant.switch') }}" class="d-inline">
                                @csrf
                                <input type="hidden" name="institution_id" value="{{ $inst->id }}">
                                <button type="submit" class="dropdown-item d-flex align-items-center justify-content-between {{ $activeInstitution && $activeInstitution->id === $inst->id ? 'active fw-bold' : '' }}">
                                    <span>{{ $inst->name }}</span>
                                    @if ($activeInstitution && $activeInstitution->id === $inst->id)
                                        <i class="ti ti-check ms-2"></i>
                                    @endif
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- User Menu -->
            @if ($user)
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Open user menu">
                        <span class="avatar avatar-sm bg-blue-lt text-primary fw-bold">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </span>
                        <div class="d-none d-xl-block ps-2">
                            <div>{{ $user->name }}</div>
                            <div class="mt-1 small text-secondary">
                                {{ ucfirst($user->getRoleNames()->first() ?? 'Pengguna') }}
                            </div>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow shadow-sm">
                        <a href="{{ Route::has('profile.show') ? route('profile.show') : '#profil' }}" class="dropdown-item">
                            <i class="ti ti-user me-2 text-secondary"></i> Profil Saya
                        </a>
                        <a href="{{ route('lock') }}" class="dropdown-item">
                            <i class="ti ti-lock me-2 text-secondary"></i> Kunci Layar (Lock)
                        </a>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="ti ti-logout me-2"></i> Keluar
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</header>
