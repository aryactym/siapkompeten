<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sesi Terkunci · {{ config('app.name', 'SiapKompeten') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fonts
</head>
<body class="d-flex flex-column bg-light">
    <div class="page page-center">
        <div class="container container-tight py-4">
            <div class="text-center mb-4">
                <a href="{{ url('/') }}" class="navbar-brand navbar-brand-autodark text-decoration-none">
                    <span class="avatar avatar-md bg-primary-lt text-primary fw-bold rounded-3 me-2">SK</span>
                    <span class="fs-2 fw-bold text-dark">{{ config('app.name', 'SiapKompeten') }}</span>
                </a>
            </div>

            @yield('content')

            <div class="text-center text-secondary mt-3">
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    Bukan akun Anda? 
                    <button type="submit" class="btn btn-link p-0 text-decoration-none align-baseline">
                        Masuk sebagai pengguna lain
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
