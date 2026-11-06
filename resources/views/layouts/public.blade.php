<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('meta_description', 'Sistem Informasi Lembaga Sertifikasi Profesi - Uji Kompetensi Berstandar BNSP')">
    <title>@yield('title', 'Beranda') · {{ $institution->name ?? config('app.name', 'SiapKompeten') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fonts
    <style>
        body {
            font-feature-settings: "cv03", "cv04", "cv11";
        }
    </style>
    @stack('styles')
</head>
<body class="d-flex flex-column bg-white">
    <div class="page">
        <!-- Public Navigation Header -->
        <header class="navbar navbar-expand-md navbar-light d-print-none sticky-top bg-white border-bottom shadow-xs">
            <div class="container-xl">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-public" aria-controls="navbar-public" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <a href="{{ route('beranda') }}" class="navbar-brand navbar-brand-autodark d-flex align-items-center gap-2 text-decoration-none">
                    <span class="avatar avatar-sm bg-primary text-white fw-bold rounded-2">SK</span>
                    <span class="fs-2 fw-bold text-dark">{{ $institution->name ?? config('app.name', 'SiapKompeten') }}</span>
                </a>

                <div class="collapse navbar-collapse" id="navbar-public">
                    <ul class="navbar-nav ms-auto align-items-md-center gap-2">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('beranda') }}">Beranda</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('beranda') }}#skema">Skema Sertifikasi</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('beranda') }}#asesor">Daftar Asesor</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('beranda') }}#berita">Berita & Agenda</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('sertifikat.form') }}">Verifikasi Sertifikat</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('beranda') }}#kontak">Kontak</a>
                        </li>

                        @auth
                            <li class="nav-item ms-md-2">
                                <a href="{{ route('dashboard') }}" class="btn btn-primary">
                                    <i class="ti ti-dashboard me-1"></i> Dashboard
                                </a>
                            </li>
                        @else
                            <li class="nav-item ms-md-2">
                                <a href="{{ route('login') }}" class="btn btn-outline-primary">
                                    <i class="ti ti-login me-1"></i> Masuk
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('register') }}" class="btn btn-primary">
                                    <i class="ti ti-user-plus me-1"></i> Daftar Asesi
                                </a>
                            </li>
                        @endauth
                    </ul>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="flex-grow-1">
            @yield('content')
        </main>

        <!-- Public Footer -->
        <footer class="footer bg-dark text-white border-top mt-5 py-5">
            @php
                $footerMenu = ($cms ?? collect())->get('footer_menu', collect());
                $safeUrl = static function (?string $url): string {
                    $url = trim((string) $url);

                    if ($url === '') {
                        return '#';
                    }

                    if (str_starts_with($url, '#') || str_starts_with($url, '/')) {
                        return $url;
                    }

                    $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

                    return in_array($scheme, ['http', 'https', 'mailto', 'tel'], true) ? $url : '#';
                };
            @endphp
            <div class="container-xl">
                <div class="row g-4">
                    <div class="col-lg-4">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="avatar avatar-sm bg-primary text-white fw-bold rounded-2">SK</span>
                            <span class="fs-2 fw-bold text-white">{{ $institution->name ?? config('app.name', 'SiapKompeten') }}</span>
                        </div>
                        <p class="text-muted small">
                            Sistem Informasi Lembaga Sertifikasi Profesi yang andal, aman, dan siap melayani asesmen kompetensi kerja berstandar Badan Nasional Sertifikasi Profesi (BNSP).
                        </p>
                    </div>
                    <div class="col-6 col-lg-2 ms-lg-auto">
                        <h4 class="text-white mb-3">Layanan</h4>
                        <ul class="list-unstyled text-muted small lh-lg mb-0">
                            <li><a href="{{ route('beranda') }}#skema" class="text-reset text-decoration-none">Skema Sertifikasi</a></li>
                            <li><a href="{{ route('beranda') }}#jadwal" class="text-reset text-decoration-none">Jadwal Asesmen</a></li>
                            <li><a href="{{ route('beranda') }}#tuk" class="text-reset text-decoration-none">Tempat Uji Kompetensi</a></li>
                            <li><a href="{{ route('sertifikat.form') }}" class="text-reset text-decoration-none">Cek Sertifikat</a></li>
                        </ul>
                    </div>
                    <div class="col-6 col-lg-3">
                        <h4 class="text-white mb-3">Tautan Terkait</h4>
                        <ul class="list-unstyled text-muted small lh-lg mb-0">
                            @if ($footerMenu->isNotEmpty())
                                @foreach ($footerMenu as $menu)
                                    @php($menuUrl = $safeUrl($menu->isi ?: $menu->sub_judul))
                                    <li>
                                        <a href="{{ $menuUrl }}"
                                           class="text-reset text-decoration-none"
                                           @if (str_starts_with($menuUrl, 'http')) target="_blank" rel="noopener noreferrer" @endif>
                                            {{ $menu->judul }}
                                        </a>
                                    </li>
                                @endforeach
                            @else
                                <li><a href="https://bnsp.go.id" target="_blank" rel="noopener noreferrer" class="text-reset text-decoration-none">Badan Nasional Sertifikasi Profesi</a></li>
                                <li><a href="https://kemnaker.go.id" target="_blank" rel="noopener noreferrer" class="text-reset text-decoration-none">Kementerian Ketenagakerjaan RI</a></li>
                                <li><a href="{{ route('beranda') }}#daftar" class="text-reset text-decoration-none">Panduan Pendaftaran Asesi</a></li>
                            @endif
                        </ul>
                    </div>
                </div>
                <div class="row align-items-center mt-4 pt-3 border-top border-secondary">
                    <div class="col-12 col-md text-muted small text-center text-md-start">
                        &copy; {{ date('Y') }} <strong>{{ $institution->name ?? config('app.name', 'SiapKompeten') }}</strong>. Hak Cipta Dilindungi.
                    </div>
                    <div class="col-12 col-md-auto text-muted small text-center text-md-end mt-2 mt-md-0">
                        Standar Keamanan OWASP Top 10:2025
                    </div>
                </div>
            </div>
        </footer>
    </div>
    @stack('scripts')
</body>
</html>
