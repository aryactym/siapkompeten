@props([
    'sections' => [],
    'portal' => 'Portal',
])

@php
    use Illuminate\Support\Facades\Route;

    /**
     * Sidebar vertikal Tabler untuk seluruh portal.
     *
     * Menerima struktur menu dari App\Support\PortalNavigation:
     * daftar grup berisi item (dengan opsional `children`).
     *
     * Item `planned` hanya dirender di lingkungan lokal sebagai nonaktif;
     * item `soon` (route ada, halaman stub) selalu nonaktif dengan badge.
     *
     * @var array<int, array{title: string, items: array<int, array<string, mixed>>}> $sections
     * @var string $portal
     */
    $isLocalEnv = app()->environment('local');

    /** Item yang tidak boleh tampil di lingkungan saat ini. */
    $isItemHidden = static function (array $item) use ($isLocalEnv): bool {
        return ! empty($item['planned']) && ! $isLocalEnv;
    };

    /** Item siap dipakai dan cocok dengan route halaman aktif. */
    $isMenuItemActive = static function (array $item): bool {
        if (! empty($item['soon']) || ! empty($item['planned']) || empty($item['route']) || ! Route::has($item['route'])) {
            return false;
        }

        $patterns = (is_array($item['match'] ?? null) && $item['match'] !== []) ? $item['match'] : $item['route'];

        return request()->routeIs($patterns);
    };
@endphp

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

        <div class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-lg-3">
            <x-brand :subtitle="$portal" />
        </div>

        <nav class="collapse navbar-collapse" id="sidebar-menu" aria-label="Menu navigasi {{ $portal }}">
            <ul class="navbar-nav pt-lg-3">
                @foreach ($sections as $section)
                    @php
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
                                        ->filter(fn (array $child): bool => ! $isItemHidden($child));
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
                                                    :query="$child['query'] ?? null"
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
                                        :query="$entry['query'] ?? null"
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
