@php
    use App\Support\TenantContext;

    $user = auth()->user();
    $activeInstitution = TenantContext::institution();
    $accessibleInstitutions = $user ? $user->accessibleInstitutions() : collect();
    $hasMultipleTenants = $accessibleInstitutions->count() > 1;
    $activeInstitutionName = $activeInstitution?->name ?? config('app.name');
@endphp

{{--
    x-tabler.header — Navbar header Tabler.

    Tidak memuat brand (brand hanya di sidebar, via x-brand).
    Tidak memuat hamburger yang menarget #navbar-menu (tidak ada).
    Berisi: tenant switcher (multi-tenant), menu pengguna.
    Tenant switcher hanya muncul bila pengguna punya akses > 1 lembaga.
--}}
<header class="navbar navbar-expand-md d-print-none border-bottom">
    <div class="container-xl">
        {{-- Pemisah kiri — kosong, sidebar sudah menangani brand dan toggler --}}
        <div class="flex-grow-1"></div>

        <div class="navbar-nav flex-row order-md-last align-items-center gap-2">

            {{-- Tenant Switcher: hanya jika user punya > 1 lembaga --}}
            @if ($hasMultipleTenants)
                <div class="nav-item dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle d-flex align-items-center gap-1"
                            data-bs-toggle="dropdown"
                            aria-haspopup="true"
                            aria-expanded="false"
                            aria-label="Pilih lembaga aktif: {{ $activeInstitutionName }}">
                        <i class="ti ti-building" aria-hidden="true"></i>
                        <span class="d-none d-sm-inline text-truncate" style="max-width: 160px;">{{ $activeInstitutionName }}</span>
                        <i class="ti ti-chevron-down" aria-hidden="true"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end shadow-sm">
                        <div class="dropdown-header">Pilih Lembaga Aktif</div>
                        @foreach ($accessibleInstitutions as $inst)
                            <form method="POST" action="{{ route('tenant.switch') }}" class="d-block">
                                @csrf
                                <input type="hidden" name="institution_id" value="{{ $inst->id }}">
                                <button type="submit"
                                        class="dropdown-item d-flex align-items-center justify-content-between {{ $activeInstitution && $activeInstitution->id === $inst->id ? 'active fw-bold' : '' }}">
                                    <span>{{ $inst->name }}</span>
                                    @if ($activeInstitution && $activeInstitution->id === $inst->id)
                                        <i class="ti ti-check ms-2 text-success" aria-hidden="true"></i>
                                    @endif
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Menu Pengguna --}}
            @if ($user)
                <div class="nav-item dropdown">
                    <a href="#"
                       class="nav-link d-flex lh-1 text-reset p-0"
                       data-bs-toggle="dropdown"
                       aria-label="Menu pengguna: {{ $user->name }}"
                       aria-haspopup="true"
                       aria-expanded="false">
                        <span class="avatar avatar-sm bg-primary-lt text-primary fw-bold" aria-hidden="true">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </span>
                        <div class="d-none d-xl-block ps-2">
                            <div class="fw-semibold lh-1">{{ $user->name }}</div>
                            <div class="mt-1 small text-secondary lh-1">
                                {{ ucfirst($user->getRoleNames()->first() ?? 'Pengguna') }}
                            </div>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow shadow-sm">
                        @if (Route::has('profile.show'))
                            <a href="{{ route('profile.show') }}" class="dropdown-item">
                                <i class="ti ti-user me-2 text-secondary" aria-hidden="true"></i>
                                Profil Saya
                            </a>
                        @endif

                        {{-- Kunci Layar: POST + CSRF agar tidak mengubah state via GET --}}
                        <form method="POST" action="{{ route('lock.lock') }}" id="form-lock-screen">
                            @csrf
                        </form>
                        <button type="button"
                                class="dropdown-item"
                                onclick="document.getElementById('form-lock-screen').submit()">
                            <i class="ti ti-lock me-2 text-secondary" aria-hidden="true"></i>
                            Kunci Layar
                        </button>

                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="ti ti-logout me-2" aria-hidden="true"></i>
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</header>
