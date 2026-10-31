<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-navbar-position="vertical">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Portal') · {{ config('app.name') }}</title>
    {{-- Lewati ke konten utama (aksesibilitas) --}}
    <a class="visually-hidden-focusable" href="#main-content">Lewati ke konten utama</a>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fonts
    @stack('styles')
</head>
<body>
    <div class="page">

        {{-- Sidebar vertikal Tabler --}}
        <aside class="navbar navbar-vertical navbar-expand-lg navbar-dark bg-dark border-end d-print-none"
               id="sidebar-navbar">
            <div class="container-fluid">
                <button class="navbar-toggler"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#sidebar-menu"
                        aria-controls="sidebar-menu"
                        aria-expanded="false"
                        aria-label="Buka/tutup navigasi sidebar">
                    <span class="navbar-toggler-icon"></span>
                </button>

                {{-- Brand dari komponen terpusat (satu sumber) --}}
                <div class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-lg-3">
                    <x-brand subtitle="Admin Portal" />
                </div>

                @php
                    /**
                     * Menu sidebar Admin Portal — satu sumber data untuk seluruh item.
                     *
                     * Status:
                     * - 'ready'   : route ada, halaman berfungsi — dirender normal.
                     * - 'planned' : route belum ada atau halaman masih stub (501) —
                     *               TIDAK dirender di produksi (APP_ENV != local).
                     *               Di lokal: dirender nonaktif + badge "Segera".
                     *
                     * Aturan render (via komponen `x-menu-item`):
                     * - Route::has($route) false → nonaktif.
                     * - `soon => true` → nonaktif di produksi, nonaktif+badge di lokal.
                     * - Item aktif: `request()->routeIs($match)`.
                     * - Grup aktif: otomatis terbuka (aria-expanded + show).
                     *
                     * Item di luar BRD (belum ada keputusan) disembunyikan default (planned):
                     *   Bundle TUG, Sertifikat & TUG, Template Presensi, SIUP User,
                     *   Lampiran, Kategori Prestasi.
                     * Dicatat di docs/PROGRESS.md — keputusan §Keputusan yang menunggu konfirmasi.
                     *
                     * @var array<int, array{
                     *   title: string,
                     *   items: array<int, array{
                     *     label: string,
                     *     icon?: string,
                     *     route?: string,
                     *     match?: string|array<int,string>,
                     *     soon?: bool,
                     *     planned?: bool,
                     *     children?: array<int, array{label: string, route?: string, match?: string|array<int,string>, soon?: bool, planned?: bool}>
                     *   }>
                     * }> $adminSections
                     */
                    $isLocalEnv = app()->environment('local');

                    $adminSections = [
                        ['title' => 'Ringkasan', 'items' => [
                            ['label' => 'Dashboard', 'icon' => 'ti-layout-dashboard', 'route' => 'dashboard', 'match' => 'dashboard'],
                        ]],

                        ['title' => 'Sertifikasi', 'items' => [
                            ['label' => 'Asesi & Pendaftaran', 'icon' => 'ti-users', 'children' => [
                                ['label' => 'Asesi', 'route' => 'admin.asesi.index', 'match' => ['admin.asesi.index', 'admin.asesi.show', 'admin.asesi.verify']],
                                ['label' => 'Daftar Pendaftaran', 'route' => 'admin.pendaftaran.index', 'match' => 'admin.pendaftaran.*'],
                                ['label' => 'Impor Asesi (Excel)', 'route' => 'admin.asesi.import.create', 'match' => 'admin.asesi.import.*'],
                                ['label' => 'Verifikasi Dokumen', 'route' => 'admin.pendaftaran.index', 'match' => [], 'soon' => true],
                            ]],
                            ['label' => 'Asesor', 'icon' => 'ti-certificate', 'children' => [
                                ['label' => 'Data Asesor', 'route' => 'admin.asesor.index', 'match' => ['admin.asesor.index', 'admin.asesor.create', 'admin.asesor.store', 'admin.asesor.edit', 'admin.asesor.update']],
                                ['label' => 'Lingkup Kompetensi', 'route' => 'admin.asesor.skema.index', 'match' => 'admin.asesor.skema.*'],
                                ['label' => 'Undang Keanggotaan', 'route' => 'admin.asesor.invite.index', 'match' => 'admin.asesor.invite.*'],
                            ]],
                            ['label' => 'Penjadwalan', 'icon' => 'ti-calendar', 'children' => [
                                ['label' => 'Jadwal Asesmen', 'route' => 'admin.jadwal.index', 'match' => 'admin.jadwal.*'],
                                ['label' => 'Event', 'route' => 'admin.event.index', 'match' => 'admin.event.*'],
                                // planned: Bundle Sertifikat (luar BRD — keputusan tertunda)
                                ['label' => 'Bundle Sertifikat', 'route' => 'admin.jadwal.bundle.create', 'match' => 'admin.jadwal.bundle.*', 'planned' => true],
                            ]],
                            // planned: Sertifikat & TUG (luar BRD)
                            ['label' => 'Sertifikat', 'icon' => 'ti-award', 'children' => [
                                ['label' => 'Data Sertifikat', 'route' => 'admin.sertifikat.index', 'match' => 'admin.sertifikat.*', 'planned' => true],
                                ['label' => 'Template Presensi', 'route' => 'admin.template_presensi.index', 'match' => 'admin.template_presensi.*', 'planned' => true],
                            ], 'planned' => true],
                        ]],

                        ['title' => 'Keuangan', 'items' => [
                            ['label' => 'Biaya & Pembayaran', 'icon' => 'ti-coins', 'children' => [
                                ['label' => 'Biaya Skema', 'route' => 'admin.biaya.index', 'match' => 'admin.biaya.*'],
                                ['label' => 'Rekening Bank', 'route' => 'admin.rekening.index', 'match' => 'admin.rekening.*'],
                                ['label' => 'Validasi Pembayaran', 'route' => 'admin.pembayaran.index', 'match' => 'admin.pembayaran.*'],
                            ]],
                        ]],

                        ['title' => 'Master Data', 'items' => [
                            ['label' => 'Standar Kompetensi', 'icon' => 'ti-book', 'children' => [
                                ['label' => 'SKKNI', 'route' => 'admin.stkkni.index', 'match' => 'admin.stkkni.*'],
                                ['label' => 'Skema Sertifikasi', 'route' => 'admin.skema.index', 'match' => 'admin.skema.*'],
                                ['label' => 'Unit Kompetensi', 'route' => 'admin.unit.index', 'match' => 'admin.unit.*'],
                                ['label' => 'Elemen & KUK', 'route' => 'admin.elemen.index', 'match' => 'admin.elemen.*'],
                                ['label' => 'TUK', 'route' => 'admin.tuk.index', 'match' => 'admin.tuk.*'],
                            ]],
                            ['label' => 'Pelaporan', 'icon' => 'ti-receipt', 'children' => [
                                ['label' => 'Data Laporan', 'route' => 'admin.pelaporan.index', 'match' => 'admin.pelaporan.*'],
                            ]],
                        ]],

                        ['title' => 'Komunikasi', 'items' => [
                            ['label' => 'Outbox Notifikasi', 'icon' => 'ti-bell-ringing', 'route' => 'admin.notifikasi.index', 'match' => 'admin.notifikasi.*'],
                            // planned: Audit Log, Pengguna & Peran (ada di BRD, route belum ada)
                            ['label' => 'Pengguna & Peran', 'icon' => 'ti-users-group', 'route' => 'admin.pengguna.index', 'match' => 'admin.pengguna.*', 'planned' => true],
                            ['label' => 'Audit Log', 'icon' => 'ti-history', 'route' => 'admin.audit.index', 'match' => 'admin.audit.*', 'planned' => true],
                        ]],

                        ['title' => 'Konten & Publik', 'items' => [
                            ['label' => 'CMS Konten', 'icon' => 'ti-file-text', 'route' => 'admin.cms.index', 'match' => 'admin.cms.*'],
                        ]],

                        ['title' => 'Sistem', 'items' => [
                            ['label' => 'Pengaturan', 'icon' => 'ti-settings', 'children' => [
                                ['label' => 'Profil Lembaga', 'route' => 'admin.institution.index', 'match' => 'admin.institution.*'],
                                ['label' => 'Tim & Persetujuan', 'route' => 'admin.pengaturan.group.show', 'match' => 'admin.pengaturan.group.*'],
                            ]],
                        ]],
                    ];

                    /**
                     * Apakah item (atau grup) harus disembunyikan di produksi.
                     * Item 'planned' hanya dirender di lingkungan lokal.
                     */
                    $isItemHidden = static function (array $item) use ($isLocalEnv): bool {
                        if (! empty($item['planned']) && ! $isLocalEnv) {
                            return true;
                        }

                        return false;
                    };

                    /** Menandai item menu yang siap dipakai dan cocok dengan route halaman saat ini. */
                    $isMenuItemActive = static function (array $item): bool {
                        if (! empty($item['soon']) || ! empty($item['planned']) || empty($item['route']) || ! Route::has($item['route'])) {
                            return false;
                        }

                        $patterns = (is_array($item['match']) && $item['match'] !== []) ? $item['match'] : $item['route'];

                        return request()->routeIs($patterns);
                    };
                @endphp

                <nav class="collapse navbar-collapse" id="sidebar-menu" aria-label="Menu Navigasi Admin">
                    <ul class="navbar-nav pt-lg-3">
                        @foreach ($adminSections as $section)
                            @php
                                // Cek apakah seluruh grup planned — kalau iya, sembunyikan judulnya juga
                                $sectionHasVisibleItems = false;
                                foreach ($section['items'] as $entry) {
                                    if (! $isItemHidden($entry)) {
                                        $sectionHasVisibleItems = true;
                                        break;
                                    }
                                }
                            @endphp
                            @if ($sectionHasVisibleItems)
                                <li class="nav-section-title">{{ $section['title'] }}</li>

                                @foreach ($section['items'] as $entry)
                                    @if ($isItemHidden($entry))
                                        @continue
                                    @endif

                                    @if (isset($entry['children']))
                                        @php
                                            $visibleChildren = collect($entry['children'])
                                                ->filter(fn (array $child) => ! $isItemHidden($child));
                                            $groupActive = $visibleChildren->contains(fn (array $child): bool => $isMenuItemActive($child));
                                        @endphp

                                        @if ($visibleChildren->isNotEmpty())
                                            <li class="nav-item dropdown{{ $groupActive ? ' active' : '' }}">
                                                <a class="nav-link dropdown-toggle"
                                                   href="#"
                                                   data-bs-toggle="dropdown"
                                                   data-bs-auto-close="false"
                                                   role="button"
                                                   aria-haspopup="true"
                                                   aria-expanded="{{ $groupActive ? 'true' : 'false' }}">
                                                    <span class="nav-link-icon d-sm-none d-lg-inline-block" aria-hidden="true">
                                                        <i class="ti {{ $entry['icon'] ?? 'ti-point' }}"></i>
                                                    </span>
                                                    <span class="nav-link-title">{{ $entry['label'] }}</span>
                                                </a>
                                                <div class="dropdown-menu{{ $groupActive ? ' show' : '' }}">
                                                    @foreach ($visibleChildren as $child)
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
                                        @endif
                                    @else
                                        <li class="nav-item">
                                            <x-menu-item
                                                :label="$entry['label']"
                                                :icon="$entry['icon'] ?? null"
                                                :route="$entry['route'] ?? null"
                                                :match="$entry['match'] ?? []"
                                                :soon="$entry['soon'] ?? false"
                                            />
                                        </li>
                                    @endif
                                @endforeach
                            @endif
                        @endforeach
                    </ul>
                </nav>
            </div>
        </aside>

        <div class="page-wrapper">
            {{-- Header: satu navbar, tanpa brand duplikat, hamburger sidebar saja --}}
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
                            {{-- Satu <h1> per halaman: judul halaman, bukan brand --}}
                            <h1 class="page-title">
                                @yield('page-title', 'Dashboard')
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

            {{-- Footer: hanya © tahun dan nama app dari config --}}
            <footer class="footer footer-transparent d-print-none border-top">
                <div class="container-xl">
                    <div class="row text-center align-items-center">
                        <div class="col">
                            <span class="text-secondary">
                                &copy; {{ date('Y') }} {{ config('app.name') }}.
                                Sistem Informasi Lembaga Sertifikasi Profesi.
                            </span>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    @if (config('app.debug') || app()->environment('local'))
        @stack('scripts')
    @else
        @stack('scripts')
    @endif
</body>
</html>
