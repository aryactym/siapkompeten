<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal Asesor') · {{ config('app.name', 'SiapKompeten') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @import url('https://rsms.me/inter/inter.css');
        :root {
            --tblr-font-sans-serif: 'Inter Var', -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
        }
        body {
            font-feature-settings: "cv03", "cv04", "cv11";
        }
    </style>
    @stack('styles')
</head>
<body class="layout-boxed">
    <div class="page">
        <div class="page-wrapper">

        <!-- Header with Tenant Switcher and User Menu -->
        <x-tabler.header />

        <!-- Navigation Bar for Asesor -->
        <header class="navbar-expand-md">
            <div class="collapse navbar-collapse" id="navbar-menu">
                <div class="navbar navbar-light bg-light border-bottom">
                    <div class="container-xl">
                        <ul class="navbar-nav">
                            <li class="nav-item {{ request()->routeIs('asesor.dashboard') || request()->routeIs('dashboard') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('dashboard') }}">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                                        <i class="ti ti-home fs-2"></i>
                                    </span>
                                    <span class="nav-link-title">Dashboard</span>
                                </a>
                            </li>
                            <li class="nav-item {{ request()->routeIs('asesor.jadwal.index', 'asesor.jadwal.peserta') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('asesor.jadwal.index') }}">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                                        <i class="ti ti-calendar-event fs-2"></i>
                                    </span>
                                    <span class="nav-link-title">Jadwal Uji Saya</span>
                                </a>
                            </li>
                            <li class="nav-item {{ request()->routeIs('asesor.apl02.show', 'asesor.apl02.verifikasi') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('asesor.jadwal.index') }}">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                                        <i class="ti ti-list-check fs-2"></i>
                                    </span>
                                    <span class="nav-link-title">Penilaian Pra-Asesmen (APL-02)</span>
                                </a>
                            </li>
                            <li class="nav-item {{ request()->routeIs('asesor.mak02.show', 'asesor.mak02.store', 'asesor.mak02.final', 'asesor.mak02.pdf') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('asesor.jadwal.index') }}">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                                        <i class="ti ti-file-analytics fs-2"></i>
                                    </span>
                                    <span class="nav-link-title">Hasil Asesmen (MAK-02)</span>
                                </a>
                            </li>
                            <li class="nav-item ms-lg-auto">
                                <a class="nav-link text-primary fw-medium" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                                        <i class="ti ti-logout fs-2"></i>
                                    </span>
                                    <span class="nav-link-title">Keluar</span>
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->

            <!-- Page Header -->
            <div class="page-header d-print-none">
                <div class="container-xl">
                    <div class="row g-2 align-items-center">
                        <div class="col">
                            @hasSection('pretitle')
                                <div class="page-pretitle text-secondary">
                                    @yield('pretitle')
                                </div>
                            @endif
                            <h2 class="page-title text-dark">
                                @yield('page-title', 'Portal Asesor')
                            </h2>
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

            <!-- Page Body -->
            <div class="page-body">
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

            <!-- Footer -->
            <footer class="footer footer-transparent d-print-none border-top">
                <div class="container-xl">
                    <div class="row text-center align-items-center flex-row-reverse">
                        <div class="col-12 col-lg-auto mt-3 mt-lg-0">
                            <span class="text-secondary">&copy; {{ date('Y') }} {{ config('app.name', 'SiapKompeten') }} · Portal Asesor Kompetensi</span>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
