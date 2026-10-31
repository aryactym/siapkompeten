<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Portal') · {{ config('app.name', 'SiapKompeten') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @import url('https://rsms.me/inter/inter.css');
        :root {
            --tblr-font-sans-serif: 'Inter Var', -apple-system, BlinkMacSystemFont, "San Francisco", "Segoe UI", Roboto, Helvetica Neue, sans-serif;
        }
        body {
            font-feature-settings: "cv03", "cv04", "cv11";
        }
    </style>
    @stack('styles')
</head>
<body class="bg-light">
    <!-- Page -->
    <div class="page">

        <!-- Sidebar -->
        <div class="sidebar sidebar-darksidebar expand-sidebar" id="sidebar-menu">
            <div class="sidebar-inner px-2 pt-3">
                <!-- Logo -->
                <div class="sidebar-header d-flex flex-column flex-sm-row flex-column-reverse align-items-center justify-content-between p-3 mb-4">
                    <a href="{{ route('dashboard') }}" class="d-flex align-items-center text-decoration-none text-body">
                        <div class="logo-src">
                            <img class="segoe-font" src="{{ asset('vendor/tabler-assets/logo.png') }}" alt="SiapKompeten Logo" width="32" height="32">
                        </div>
                        <div class="sidebar-logo-text ms-3">
                            <span class="logo-text">SiapKompeten</span>
                            <span class="logo-subtext text-body-emphasis d-none d-sm-inline-block">Admin Portal</span>
                        </div>
                    </a>
                    <button class="btn-open close-sidebar-btn d-lg-none btn btn-icon btn-ghost-primary ms-auto" type="button" aria-label="Close sidebar">
                        <i class="ti ti-x"></i>
                    </button>
                </div>

                <!-- Navigation -->
                <nav class="sidebar-nav">

                    <!-- Dashboard -->
                    <div class="nav-item nav-dropdown">
                        <a class="nav-link nav-dropdown-toggle d-flex justify-content-between align-items-center w-100" href="#"
                           data-bs-toggle="collapse" data-bs-target="#sidebar-dashboard" aria-expanded="false" aria-controls="sidebar-dashboard">
                            <span class="nav-link-title">Dashboard</span>
                            <i class="nav-arrow ti ti-chevron-down"></i>
                        </a>
                        <ul class="nav-dropdown-items collapse list-unstyled border-start ms-3 ps-2" id="sidebar-dashboard">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                                   href="{{ route('dashboard') }}">
                                    <span class="nav-link-icon d-sm-none d-lg-inline-block">
                                        <i class="ti ti-layout-dashboard fs-4"></i>
                                    </span>
                                    <span class="nav-link-title">Dashboard Utama</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Master Standar -->
                    <div class="nav-item nav-dropdown">
                        <a class="nav-link nav-dropdown-toggle d-flex justify-content-between align-items-center w-100"
                           href="#"
                           data-bs-toggle="collapse" data-bs-target="#sidebar-master" aria-expanded="false" aria-controls="sidebar-master">
                            <span class="nav-link-title">Master Standar</span>
                            <i class="nav-arrow ti ti-chevron-down"></i>
                        </a>
                        <ul class="nav-dropdown-items collapse list-unstyled border-start ms-3 ps-2" id="sidebar-master">
                             <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.skema.index', 'admin.skema.create', 'admin.skema.edit') ? 'active' : '' }}"
                                   href="{{ route('admin.skema.index') }}">
                                    <span class="nav-link-icon d-sm-none d-lg-inline-block">
                                        <i class="ti ti-file-plus fs-4"></i>
                                    </span>
                                    <span class="nav-link-title">Data Skema</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Asesi & Pendaftaran -->
                    <div class="nav-item nav-dropdown">
                        <a class="nav-link nav-dropdown-toggle d-flex justify-content-between align-items-center w-100"
                           href="#"
                           data-bs-toggle="collapse" data-bs-target="#sidebar-asesi" aria-expanded="false" aria-controls="sidebar-asesi">
                            <span class="nav-link-title">Asesi &amp; Pendaftaran</span>
                            <i class="nav-arrow ti ti-chevron-down"></i>
                        </a>
                        <ul class="nav-dropdown-items collapse list-unstyled border-start ms-3 ps-2" id="sidebar-asesi">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.asesi.index', 'admin.asesi.create', 'admin.asesi.edit', 'admin.asesi.impor') ? 'active' : '' }}"
                                   href="{{ route('admin.asesi.index') }}">
                                    <span class="nav-link-icon d-sm-none d-lg-inline-block">
                                        <i class="ti ti-users fs-4"></i>
                                    </span>
                                    <span class="nav-link-title">Asesi</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.pendaftaran.index', 'admin.pendaftaran.show') ? 'active' : '' }}"
                                   href="{{ route('admin.pendaftaran.index') }}">
                                    <span class="nav-link-icon d-sm-none d-lg-inline-block">
                                        <i class="ti ti-list-check fs-4"></i>
                                    </span>
                                    <span class="nav-link-title">Daftar Pendaftaran</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.asesi.import.create', 'admin.asesi.import.show', 'admin.asesi.import.report') ? 'active' : '' }}"
                                   href="{{ route('admin.asesi.import.create') }}">
                                    <span class="nav-link-icon d-sm-none d-lg-inline-block">
                                        <i class="ti ti-file-import fs-4"></i>
                                    </span>
                                    <span class="nav-link-title">Impor Asesi (Excel)</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Asesor -->
                    <div class="nav-item nav-dropdown">
                        <a class="nav-link nav-dropdown-toggle d-flex justify-content-between align-items-center w-100"
                           href="#"
                           data-bs-toggle="collapse" data-bs-target="#sidebar-asesor" aria-expanded="false" aria-controls="sidebar-asesor">
                            <span class="nav-link-title">Asesor</span>
                            <i class="nav-arrow ti ti-chevron-down"></i>
                        </a>
                        <ul class="nav-dropdown-items collapse list-unstyled border-start ms-3 ps-2" id="sidebar-asesor">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.asesor.index', 'admin.asesor.show', 'admin.asesor.impor') ? 'active' : '' }}"
                                   href="{{ route('admin.asesor.index') }}">
                                    <span class="nav-link-icon d-sm-none d-lg-inline-block">
                                        <i class="ti ti-stamp fs-4"></i>
                                    </span>
                                    <span class="nav-link-title">Data Asesor</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.asesor.skema.index', 'admin.asesor.skema.create') ? 'active' : '' }}"
                                   href="{{ Route::has('admin.asesor.skema.index') ? route('admin.asesor.skema.index') : '#' }}">
                                    <span class="nav-link-icon d-sm-none d-lg-inline-block">
                                        <i class="ti ti-settings fs-4"></i>
                                    </span>
                                    <span class="nav-link-title">Lingkup Kompetensi</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.asesor.invite.index', 'admin.asesor.invite.store') ? 'active' : '' }}"
                                   href="{{ Route::has('admin.asesor.invite.index') ? route('admin.asesor.invite.index') : '#' }}">
                                    <span class="nav-link-icon d-sm-none d-lg-inline-block">
                                        <i class="ti ti-email fs-4"></i>
                                    </span>
                                    <span class="nav-link-title">Undang Keanggotaan</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Penjadwalan -->
                    <div class="nav-item nav-dropdown">
                        <a class="nav-link nav-dropdown-toggle d-flex justify-content-between align-items-center w-100"
                           href="#"
                           data-bs-toggle="collapse" data-bs-target="#sidebar-jadwal" aria-expanded="false" aria-controls="sidebar-jadwal">
                            <span class="nav-link-title">Penjadwalan</span>
                            <i class="nav-arrow ti ti-chevron-down"></i>
                        </a>
                        <ul class="nav-dropdown-items collapse list-unstyled border-start ms-3 ps-2" id="sidebar-jadwal">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.jadwal.index', 'admin.jadwal.show', 'admin.jadwal.create', 'admin.jadwal.bundle.preview') ? 'active' : '' }}"
                                   href="{{ route('admin.jadwal.index') }}">
                                    <span class="nav-link-icon d-sm-none d-lg-inline-block">
                                        <i class="ti ti-calendar fs-4"></i>
                                    </span>
                                    <span class="nav-link-title">Jadwal Asesmen</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.jadwal.bundle.create', 'admin.jadwal.bundle.store') ? 'active' : '' }}"
                                   href="{{ Route::has('admin.jadwal.bundle.create') ? route('admin.jadwal.bundle.create') : '#' }}">
                                    <span class="nav-link-icon d-sm-none d-lg-inline-block">
                                        <i class="ti ti-download fs-4"></i>
                                    </span>
                                    <span class="nav-link-title">Implikasi Sertifikat (Bundle)</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.event.index', 'admin.event.show', 'admin.event.create', 'admin.event.asem.list') ? 'active' : '' }}"
                                   href="{{ Route::has('admin.event.index') ? route('admin.event.index') : '#' }}">
                                    <span class="nav-link-icon d-sm-none d-lg-inline-block">
                                        <i class="ti ti-calendar-clock fs-4"></i>
                                    </span>
                                    <span class="nav-link-title">Event</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Sertifikat & TUG -->
                    <div class="nav-item nav-dropdown">
                        <a class="nav-link nav-dropdown-toggle d-flex justify-content-between align-items-center w-100"
                           href="#"
                           data-bs-toggle="collapse" data-bs-target="#sidebar-sertifikat" aria-expanded="false" aria-controls="sidebar-sertifikat">
                            <span class="nav-link-title">Sertifikat &amp; TUG</span>
                            <i class="nav-arrow ti ti-chevron-down"></i>
                        </a>
                        <ul class="nav-dropdown-items collapse list-unstyled border-start ms-3 ps-2" id="sidebar-sertifikat">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.tuk.index', 'admin.tuk.show', 'admin.tuk.bundle.list') ? 'active' : '' }}"
                                   href="{{ Route::has('admin.tuk.index') ? route('admin.tuk.index') : '#' }}">
                                    <span class="nav-link-icon d-sm-none d-lg-inline-block">
                                        <i class="ti ti-file-text fs-4"></i>
                                    </span>
                                    <span class="nav-link-title">TUG (Tugas UTS)</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.tuk.bundle.create,frame','admin.tuk.bundle.confirm','admin.tuk.bundle.finished') ? 'active' : '' }}"
                                   href="{{ Route::has('admin.tuk.bundle.create') ? route('admin.tuk.bundle.create') : '#' }}">
                                    <span class="nav-link-icon d-sm-none d-lg-inline-block">
                                        <i class="ti ti-file-compressed fs-4"></i>
                                    </span>
                                    <span class="nav-link-title">Bundle TUG</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.sertifikat.index', 'admin.sertifikat.show', 'admin.sertifikat.pdf') ? 'active' : '' }}"
                                   href="{{ Route::has('admin.sertifikat.index') ? route('admin.sertifikat.index') : '#' }}">
                                    <span class="nav-link-icon d-sm-none d-lg-inline-block">
                                        <i class="ti ti-award fs-4"></i>
                                    </span>
                                    <span class="nav-link-title">Sertifikat</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.template_presensi.index','admin.template_presensi.show','admin.template_presensi.create','admin.template_presensi.edit','admin.template_presensi.replicate') ? 'active' : '' }}"
                                   href="{{ Route::has('admin.template_presensi.index') ? route('admin.template_presensi.index') : '#' }}">
                                    <span class="nav-link-icon d-sm-none d-lg-inline-block">
                                        <i class="ti ti-presence fs-4"></i>
                                    </span>
                                    <span class="nav-link-title">Template Presensi</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Pelaporan -->
                    <div class="nav-item nav-dropdown">
                        <a class="nav-link nav-dropdown-toggle d-flex justify-content-between align-items-center w-100"
                           href="#"
                           data-bs-toggle="collapse" data-bs-target="#sidebar-pelaporan" aria-expanded="false" aria-controls="sidebar-pelaporan">
                            <span class="nav-link-title">Pelaporan</span>
                            <i class="nav-arrow ti ti-chevron-down"></i>
                        </a>
                        <ul class="nav-dropdown-items collapse list-unstyled border-start ms-3 ps-2" id="sidebar-pelaporan">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.pelaporan.index') ? 'active' : '' }}"
                                   href="{{ Route::has('admin.pelaporan.index') ? route('admin.pelaporan.index') : '#' }}">
                                    <span class="nav-link-icon d-sm-none d-lg-inline-block">
                                        <i class="ti ti-receipt fs-4"></i>
                                    </span>
                                    <span class="nav-link-title">Data Laporan</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Integrasi -->
                    <div class="nav-item nav-dropdown">
                        <a class="nav-link nav-dropdown-toggle d-flex justify-content-between align-items-center w-100"
                           href="#"
                           data-bs-toggle="collapse" data-bs-target="#sidebar-integrasi" aria-expanded="false" aria-controls="sidebar-integrasi">
                            <span class="nav-link-title">Integrasi</span>
                            <i class="nav-arrow ti ti-chevron-down"></i>
                        </a>
                        <ul class="nav-dropdown-items collapse list-unstyled border-start ms-3 ps-2" id="sidebar-integrasi">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.siup_user.index','admin.siup_user.import','admin.siup_user.stat') ? 'active' : '' }}"
                                   href="{{ Route::has('admin.siup_user.index') ? route('admin.siup_user.index') : '#' }}">
                                    <span class="nav-link-icon d-sm-none d-lg-inline-block">
                                        <i class="ti ti-cloud-arrow-up fs-4"></i>
                                    </span>
                                    <span class="nav-link-title">SIUP User</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.lampiran.index','admin.lampiran.upload','admin.lampiran.download') ? 'active' : '' }}"
                                   href="{{ Route::has('admin.lampiran.index') ? route('admin.lampiran.index') : '#' }}">
                                    <span class="nav-link-icon d-sm-none d-lg-inline-block">
                                        <i class="ti ti-file fs-4"></i>
                                    </span>
                                    <span class="nav-link-title">Lampiran</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Kategori Prestasi -->
                    <div class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.prestasi.kategori.index','admin.prestasi.kategori.create','admin.prestasi.kategori.store','admin.prestasi.kategori.destroy') ? 'active' : '' }}"
                           href="{{ Route::has('admin.prestasi.kategori.index') ? route('admin.prestasi.kategori.index') : '#' }}">
                            <span class="nav-link-icon d-sm-none d-lg-inline-block">
                                <i class="ti ti-trophy fs-4"></i>
                            </span>
                            <span class="nav-link-title">Kategori Prestasi</span>
                        </a>
                    </div>

                    <!-- Pengaturan -->
                    <div class="nav-item nav-dropdown">
                        <a class="nav-link nav-dropdown-toggle d-flex justify-content-between align-items-center w-100"
                           href="#"
                           data-bs-toggle="collapse" data-bs-target="#sidebar-pengaturan" aria-expanded="false" aria-controls="sidebar-pengaturan">
                            <span class="nav-link-title">Pengaturan</span>
                            <i class="nav-arrow ti ti-chevron-down"></i>
                        </a>
                        <ul class="nav-dropdown-items collapse list-unstyled border-start ms-3 ps-2" id="sidebar-pengaturan">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.pengaturan.group.show','admin.event.asem.list','admin.template_presensi.index','admin.tuk.bundle.create','admin.tuk.bundle.list','admin.asesor.skema.index','admin.asesor.invite.index','admin.pendaftaran.index','admin.presensi.index','admin.detail_presensi.index','admin.rpl.generate-batch','admin.rpl.index','admin.lampiran.index','admin.persyaratan.index','admin.prestasi.kategori') ? 'active' : '' }}"
                                   href="{{ Route::has('admin.pengaturan.group.show') ? route('admin.pengaturan.group.show') : '#' }}">
                                    <span class="nav-link-icon d-sm-none d-lg-inline-block">
                                        <i class="ti ti-settings fs-4"></i>
                                    </span>
                                    <span class="nav-link-title">Tim &amp; Persetujuan</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.pengaturan.institution.show') ? 'active' : '' }}"
                                   href="{{ Route::has('admin.pengaturan.institution.show') ? route('admin.pengaturan.institution.show') : '#' }}">
                                    <span class="nav-link-icon d-sm-none d-lg-inline-block">
                                        <i class="ti ti-building fs-4"></i>
                                    </span>
                                    <span class="nav-link-title">Profil Lembaga</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                </nav>
            </div>
        </div>

        <!-- Page wrapper -->
        <div class="page-wrapper">
            <!-- Navbar Header Component with Tenant Switcher -->
            <x-tabler.header />

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
                                @yield('page-title', 'Dashboard')
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

            <!-- Footer -->
            <footer class="footer footer-transparent d-print-none border-top">
                <div class="container-xl">
                    <div class="row text-center align-items-center flex-row-reverse">
                        <div class="col-lg-auto ms-lg-auto">
                            <ul class="list-inline list-inline-dots mb-0">
                                <li class="list-inline-item">
                                    <span class="text-secondary">Standar Keamanan: OWASP Top 10:2025</span>
                                </li>
                                <li class="list-inline-item">
                                    <span class="text-secondary">Multi-LSP Ready</span>
                                </li>
                            </ul>
                        </div>
                        <div class="col-12 col-lg-auto mt-3 mt-lg-0">
                            <ul class="list-inline list-inline-dots mb-0">
                                <li class="list-inline-item">
                                    &copy; {{ date('Y') }} <strong>SiapKompeten</strong>. Sistem Informasi Lembaga Sertifikasi Profesi.
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
