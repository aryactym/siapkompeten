<!DOCTYPE html>
<html lang="id" data-bs-navbar-position="vertical">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Portal') · {{ config('app.name', 'SiapKompeten') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fonts
    <style>
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
        <aside class="navbar navbar-vertical navbar-expand-lg navbar-dark bg-dark border-end d-print-none" id="sidebar-navbar">
            <div class="container-fluid">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu"
                        aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <h1 class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-lg-3">
                    <a href="{{ route('dashboard') }}" class="d-flex align-items-center gap-2 text-decoration-none text-dark">
                        <span class="avatar avatar-sm bg-primary-lt text-primary fw-bold">SK</span>
                        <div>
                            <div class="fw-bold text-white">SiapKompeten</div>
                            <div class="text-white-50" style="font-size: .7rem;">Admin Portal</div>
                        </div>
                    </a>
                </h1>

            @php
                /**
                 * Menu sidebar Admin Portal — satu sumber data untuk seluruh item.
                 *
                 * Aturan render (lihat komponen `x-menu-item`):
                 * - route belum terdaftar (Route::has false) -> item nonaktif + badge "Segera".
                 * - `soon => true` -> route sudah ada tetapi halaman masih stub (501) -> nonaktif + badge "Segera".
                 * - item aktif ditandai lewat pola `match` (`request()->routeIs()`),
                 *   grup yang berisi item aktif otomatis terbuka (aria-expanded + show).
                 *
                 * @var array<int, array{title: string, items: array<int, array{label: string, icon?: string, route?: string, match?: string|array<int, string>, soon?: bool, children?: array<int, array{label: string, route?: string, match?: string|array<int, string>, soon?: bool}>}>}> $adminSections
                 */
                $adminSections = [
                    ['title' => 'Ringkasan', 'items' => [
                        ['label' => 'Dashboard', 'icon' => 'ti-layout-dashboard', 'route' => 'dashboard', 'match' => 'dashboard'],
                    ]],

                    ['title' => 'Sertifikasi', 'items' => [
                        ['label' => 'Asesi & Pendaftaran', 'icon' => 'ti-users', 'children' => [
                            ['label' => 'Asesi', 'route' => 'admin.asesi.index', 'match' => ['admin.asesi.index', 'admin.asesi.show', 'admin.asesi.verify']],
                            ['label' => 'Daftar Pendaftaran', 'route' => 'admin.pendaftaran.index', 'match' => 'admin.pendaftaran.*'],
                            ['label' => 'Impor Asesi (Excel)', 'route' => 'admin.asesi.import.create', 'match' => 'admin.asesi.import.*'],
                        ]],
                        ['label' => 'Asesor', 'icon' => 'ti-certificate', 'children' => [
                            ['label' => 'Data Asesor', 'route' => 'admin.asesor.index', 'match' => ['admin.asesor.index', 'admin.asesor.create', 'admin.asesor.store', 'admin.asesor.edit', 'admin.asesor.update']],
                            ['label' => 'Lingkup Kompetensi', 'route' => 'admin.asesor.skema.index', 'match' => 'admin.asesor.skema.*'],
                            ['label' => 'Undang Keanggotaan', 'route' => 'admin.asesor.invite.index', 'match' => 'admin.asesor.invite.*'],
                        ]],
                        ['label' => 'Penjadwalan', 'icon' => 'ti-calendar', 'children' => [
                            ['label' => 'Jadwal Asesmen', 'route' => 'admin.jadwal.index', 'match' => 'admin.jadwal.*'],
                            ['label' => 'Event', 'route' => 'admin.event.index', 'match' => 'admin.event.*'],
                            ['label' => 'Bundle Sertifikat', 'route' => 'admin.jadwal.bundle.create', 'match' => 'admin.jadwal.bundle.*'],
                        ]],
                        ['label' => 'Sertifikat & TUG', 'icon' => 'ti-award', 'children' => [
                            ['label' => 'Bundle TUG', 'route' => 'admin.tuk.bundle.create', 'match' => 'admin.tuk.bundle.*'],
                            ['label' => 'Sertifikat', 'route' => 'admin.sertifikat.index', 'match' => 'admin.sertifikat.*'],
                            ['label' => 'Template Presensi', 'route' => 'admin.template_presensi.index', 'match' => 'admin.template_presensi.*'],
                        ]],
                    ]],

                    ['title' => 'Keuangan', 'items' => [
                        ['label' => 'Biaya & Pembayaran', 'icon' => 'ti-coins', 'children' => [
                            ['label' => 'Biaya Skema', 'route' => 'admin.biaya.index', 'match' => 'admin.biaya.*'],
                            ['label' => 'Rekening Bank', 'route' => 'admin.rekening.index', 'match' => 'admin.rekening.*'],
                            ['label' => 'Validasi Pembayaran', 'route' => 'admin.pembayaran.index', 'match' => 'admin.pembayaran.*'],
                        ]],
                    ]],

                    ['title' => 'Data & Integrasi', 'items' => [
                        ['label' => 'Master Standar', 'icon' => 'ti-book', 'children' => [
                            ['label' => 'SKKNI', 'route' => 'admin.stkkni.index', 'match' => 'admin.stkkni.*'],
                            ['label' => 'Skema Sertifikasi', 'route' => 'admin.skema.index', 'match' => 'admin.skema.*'],
                            ['label' => 'Unit Kompetensi', 'route' => 'admin.unit.index', 'match' => 'admin.unit.*'],
                            ['label' => 'Elemen Kompetensi', 'route' => 'admin.elemen.index', 'match' => 'admin.elemen.*'],
                            ['label' => 'TUK', 'route' => 'admin.tuk.index', 'match' => 'admin.tuk.index'],
                            ['label' => 'Wilayah', 'route' => 'admin.wilayah.index', 'match' => 'admin.wilayah.*', 'soon' => true],
                        ]],
                        ['label' => 'Integrasi', 'icon' => 'ti-cloud-upload', 'children' => [
                            ['label' => 'SIUP User', 'route' => 'admin.siup_user.index', 'match' => 'admin.siup_user.*'],
                            ['label' => 'Lampiran', 'route' => 'admin.lampiran.index', 'match' => 'admin.lampiran.*'],
                        ]],
                        ['label' => 'Pelaporan', 'icon' => 'ti-receipt', 'children' => [
                            ['label' => 'Data Laporan', 'route' => 'admin.pelaporan.index', 'match' => 'admin.pelaporan.*'],
                        ]],
                        ['label' => 'Outbox Notifikasi', 'icon' => 'ti-bell-ringing', 'route' => 'admin.notifikasi.index', 'match' => 'admin.notifikasi.*'],
                        ['label' => 'Kategori Prestasi', 'icon' => 'ti-trophy', 'route' => 'admin.prestasi.kategori.index', 'match' => 'admin.prestasi.kategori.*'],
                    ]],

                    ['title' => 'Konten & Publik', 'items' => [
                        ['label' => 'CMS Konten', 'icon' => 'ti-file-text', 'route' => 'admin.cms.index', 'match' => 'admin.cms.*'],
                    ]],

                    ['title' => 'Sistem', 'items' => [
                        ['label' => 'Pengaturan', 'icon' => 'ti-settings', 'children' => [
                            ['label' => 'Tim & Persetujuan', 'route' => 'admin.pengaturan.group.show', 'match' => 'admin.pengaturan.group.*'],
                            ['label' => 'Profil Lembaga', 'route' => 'admin.institution.index', 'match' => 'admin.institution.*'],
                        ]],
                    ]],
                ];

                /** Menandai item menu yang siap dipakai dan cocok dengan route halaman saat ini. */
                $isMenuItemActive = static function (array $item): bool {
                    if (! empty($item['soon']) || empty($item['route']) || ! Route::has($item['route'])) {
                        return false;
                    }

                    return request()->routeIs($item['match'] ?? $item['route']);
                };
            @endphp

            <nav class="collapse navbar-collapse" id="sidebar-menu" aria-label="Sidebar">
                <ul class="navbar-nav pt-lg-3">
                    @foreach ($adminSections as $section)
                        <li class="nav-section-title">{{ $section['title'] }}</li>

                        @foreach ($section['items'] as $entry)
                            @if (isset($entry['children']))
                                @php($groupActive = collect($entry['children'])->contains(fn (array $item): bool => $isMenuItemActive($item)))
                                <li class="nav-item dropdown{{ $groupActive ? ' active' : '' }}">
                                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown"
                                       data-bs-auto-close="false" role="button" aria-haspopup="true"
                                       aria-expanded="{{ $groupActive ? 'true' : 'false' }}">
                                        <span class="nav-link-icon d-sm-none d-lg-inline-block">
                                            <i class="ti {{ $entry['icon'] }}"></i>
                                        </span>
                                        <span class="nav-link-title">{{ $entry['label'] }}</span>
                                    </a>

                                    <div class="dropdown-menu{{ $groupActive ? ' show' : '' }}">
                                        @foreach ($entry['children'] as $child)
                                            <x-menu-item
                                                variant="dropdown"
                                                :label="$child['label']"
                                                :route="$child['route'] ?? null"
                                                :match="$child['match'] ?? []"
                                                :soon="$child['soon'] ?? false"
                                            />
                                        @endforeach
                                    </div>
                                </li>
                            @else
                                <li class="nav-item">
                                    <x-menu-item
                                        :label="$entry['label']"
                                        :icon="$entry['icon']"
                                        :route="$entry['route'] ?? null"
                                        :match="$entry['match'] ?? []"
                                        :soon="$entry['soon'] ?? false"
                                    />
                                </li>
                            @endif
                        @endforeach
                    @endforeach
                </ul>
            </nav>
            </div>
        </aside>

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
