@php
    use App\Support\ActivePortal;
    use App\Support\TenantContext;

    $user = auth()->user();
    $activeInstitution = TenantContext::institution();
    $activeInstitutionName = $activeInstitution?->name ?? config('app.name');
    $availablePortals = $user ? ActivePortal::availablePortals($user) : [];
    $activePortal = $user ? ActivePortal::get(request()) : null;
    $hasMultiplePortals = count($availablePortals) > 1;

    $portalLabels = [
        ActivePortal::PORTAL_ASESI  => ['label' => 'Peserta (Asesi)',   'icon' => 'ti-user-check'],
        ActivePortal::PORTAL_ASESOR => ['label' => 'Asesor Kompetensi', 'icon' => 'ti-clipboard-check'],
        ActivePortal::PORTAL_ADMIN  => ['label' => 'Pengelola',          'icon' => 'ti-settings'],
    ];
@endphp

{{--
    x-tabler.header — Navbar header Tabler.

    Tidak memuat brand (brand hanya di sidebar, via x-brand).
    Tidak memuat hamburger yang menarget #navbar-menu (tidak ada).
    Berisi: menu pengguna (portal switcher hanya jika multi-portal).
    Branch `single`: tenant switcher dihapus (satu LSP per instalasi).
--}}
<header class="navbar navbar-expand-md d-print-none border-bottom">
    <div class="container-xl">
        {{-- Pemisah kiri — kosong, sidebar sudah menangani brand dan toggler --}}
        <div class="flex-grow-1"></div>

        <div class="navbar-nav flex-row order-md-last align-items-center gap-2">

            {{-- Menu Pengguna --}}
            @if ($user)
                <div class="nav-item dropdown">
                    <a href="#"
                       class="nav-link d-flex lh-1 text-reset p-0 align-items-center gap-2"
                       data-bs-toggle="dropdown"
                       aria-label="Menu pengguna: {{ $user->name }}"
                       aria-haspopup="true"
                       aria-expanded="false">
                        <span class="avatar avatar-sm bg-primary-lt text-primary fw-bold" aria-hidden="true">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </span>
                        <div class="d-none d-xl-block">
                            <div class="fw-semibold lh-1">{{ $user->name }}</div>
                            <div class="mt-1 small text-secondary lh-1 d-flex align-items-center gap-1">
                                @if ($activePortal && isset($portalLabels[$activePortal]))
                                    <i class="ti {{ $portalLabels[$activePortal]['icon'] }}" aria-hidden="true"></i>
                                    {{ $portalLabels[$activePortal]['label'] }}
                                @else
                                    {{ ucfirst($user->getRoleNames()->first() ?? 'Pengguna') }}
                                @endif
                            </div>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow shadow-sm">
                        {{-- Info user --}}
                        <div class="dropdown-header text-body-secondary">
                            <div class="fw-semibold text-body">{{ $user->name }}</div>
                            <div class="small">{{ $user->email }}</div>
                        </div>

                        @if (Route::has('profile.show'))
                            <a href="{{ route('profile.show') }}" class="dropdown-item">
                                <i class="ti ti-user me-2 text-secondary" aria-hidden="true"></i>
                                Profil Saya
                            </a>
                        @endif

                        {{-- Portal Switcher: tampil hanya jika user punya > 1 portal --}}
                        @if ($hasMultiplePortals)
                            <div class="dropdown-divider"></div>
                            <div class="dropdown-header">Ganti Portal</div>
                            @foreach ($availablePortals as $portalKey => $portalLabel)
                                @php
                                    $isActive = $activePortal === $portalKey;
                                    $pInfo = $portalLabels[$portalKey] ?? ['label' => $portalLabel, 'icon' => 'ti-circle'];
                                @endphp
                                @if ($isActive)
                                    <span class="dropdown-item active d-flex align-items-center justify-content-between pe-none">
                                        <span>
                                            <i class="ti {{ $pInfo['icon'] }} me-2" aria-hidden="true"></i>
                                            {{ $pInfo['label'] }}
                                        </span>
                                        <i class="ti ti-check ms-2 text-success" aria-hidden="true"></i>
                                    </span>
                                @else
                                    <form method="POST" action="{{ route('portal.switch') }}" class="d-block">
                                        @csrf
                                        <input type="hidden" name="portal" value="{{ $portalKey }}">
                                        <button type="submit" class="dropdown-item d-flex align-items-center">
                                            <i class="ti {{ $pInfo['icon'] }} me-2 text-secondary" aria-hidden="true"></i>
                                            {{ $pInfo['label'] }}
                                        </button>
                                    </form>
                                @endif
                            @endforeach
                        @endif

                        <div class="dropdown-divider"></div>

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
