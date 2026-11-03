@php
    use App\Support\ActivePortal;
    use App\Support\PortalNavigation;
    use App\Support\TenantContext;

    $activePortal = auth()->check()
        ? ActivePortal::resolve(request(), auth()->user())
        : ActivePortal::PORTAL_ASESI;

    $portalKey = $portal ?? $activePortal;
    $portalSections = $sections ?? PortalNavigation::sections($portalKey);
    $portalLabel = PortalNavigation::label($portalKey);

    $sectionTitle = trim($__env->yieldContent('title'));
    $documentTitle = $sectionTitle !== '' ? $sectionTitle : $portalLabel;
    $institutionName = TenantContext::name() ?? config('app.name', 'SiapKompeten');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-navbar-position="vertical">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $documentTitle }} · {{ $institutionName }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fonts
    <style>
        .step-item.active {
            font-weight: 600;
        }
    </style>
    @stack('styles')
</head>
<body @class(['bg-light' => $portalKey !== ActivePortal::PORTAL_ADMIN])>
    <div class="page">

        <a class="visually-hidden-focusable" href="#main-content">Lewati ke konten utama</a>

        {{-- Sidebar vertikal Tabler — satu komponen untuk semua portal --}}
        <x-portal-sidebar :sections="$portalSections" :portal="$portalLabel" />

        <div class="page-wrapper">
            <x-tabler.header />

            {{-- Page Header --}}
            <div class="page-header d-print-none">
                <div class="container-xl">
                    <div class="row g-2 align-items-center">
                        <div class="col">
                            @hasSection('pretitle')
                                <div class="page-pretitle text-secondary">
                                    @yield('pretitle')
                                </div>
                            @endif
                            {{-- Satu <h1> per halaman: judul konten, bukan brand --}}
                            <h1 class="page-title">
                                @yield('page-title', $portalLabel)
                            </h1>
                        </div>
                        @hasSection('actions')
                            <div class="col-auto ms-auto d-print-none">
                                <div class="btn-list">
                                    @yield('actions')
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Page Body --}}
            <div class="page-body" id="main-content">
                <div class="container-xl">
                    @if (session('success'))
                        <x-tabler.alert type="success" title="Berhasil">
                            {{ session('success') }}
                        </x-tabler.alert>
                    @endif

                    @if (session('error'))
                        <x-tabler.alert type="danger" title="Peringatan">
                            {{ session('error') }}
                        </x-tabler.alert>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <div class="fw-semibold mb-1">Periksa kembali isian berikut:</div>
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </div>

            <footer class="footer footer-transparent d-print-none border-top">
                <div class="container-xl">
                    <div class="row text-center align-items-center">
                        <div class="col">
                            <span class="text-secondary">
                                &copy; {{ date('Y') }} {{ $institutionName }} &middot; {{ $portalLabel }}
                            </span>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    @stack('modals')
    @stack('scripts')
</body>
</html>
