<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Masuk') · {{ config('app.name', 'SiapKompeten') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fonts
    @stack('styles')
</head>
<body class="d-flex flex-column">
    <div class="page @yield('page_class', 'page-center')">
        <div class="container @yield('container_class', 'container-tight') py-4">
            <div class="text-center mb-4">
                <a href="{{ url('/') }}" class="navbar-brand navbar-brand-autodark text-decoration-none">
                    <span class="avatar avatar-md bg-primary-lt text-primary fw-bold rounded-3 me-2">SK</span>
                    <span class="fs-2 fw-bold text-dark">{{ config('app.name', 'SiapKompeten') }}</span>
                </a>
                <div class="text-secondary small mt-1">Sistem Informasi Lembaga Sertifikasi Profesi</div>
            </div>

            @if (session('status'))
                <div class="alert alert-success alert-dismissible mb-3" role="alert">
                    <div class="d-flex">
                        <div><i class="ti ti-check alert-icon me-2"></i></div>
                        <div>{{ session('status') }}</div>
                    </div>
                    <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                </div>
            @endif

            @if ($errors->has('login') || $errors->has('email'))
                <div class="alert alert-danger alert-dismissible mb-3" role="alert">
                    <div class="d-flex">
                        <div><i class="ti ti-alert-circle alert-icon me-2"></i></div>
                        <div>{{ $errors->first('login') ?: $errors->first('email') }}</div>
                    </div>
                    <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                </div>
            @endif

            @yield('content')

            <div class="text-center text-secondary mt-4 fs-6">
                &copy; {{ date('Y') }} {{ config('app.name', 'SiapKompeten') }}. Hak Cipta Dilindungi.
            </div>
        </div>
    </div>
    @stack('scripts')
</body>
</html>