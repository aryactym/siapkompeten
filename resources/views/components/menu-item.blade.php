@props(['label', 'icon' => null, 'route' => null, 'match' => [], 'soon' => false, 'variant' => 'nav', 'query' => null])

@php
    /**
     * Item menu sidebar Admin.
     *
     * - `variant`: 'nav' = link nav-link di sidebar (dengan ikon), 'dropdown' = item dropdown-menu (tanpa ikon).
     * - `route`: nama route tujuan. Jika route belum ada di aplikasi, item ditampilkan
     *   dinonaktifkan dengan badge "Segera" (tautan mati `#` dihindari).
     * - `match`: pola `request()->routeIs()` (string|array) untuk menandai item aktif.
     *   Default: nama route itu sendiri.
     * - `soon`: penanda eksplisit halaman yang route-nya sudah ada tetapi implementasinya
     *   masih stub (501) — tetap dinonaktifkan sampai halaman dibangun.
     * - `query`: query string tambahan (contoh: 'tab=logo') yang ditambahkan ke URL.
     *
     * @var string $label
     * @var string|null $icon
     * @var string|null $route
     * @var string|array<int, string> $match
     * @var bool $soon
     * @var string $variant
     * @var string|null $query
     */
    $patterns = (is_array($match) && $match !== []) ? $match : $route;
    $isReady = ! $soon && filled($route) && Route::has($route);
    $isActive = $isReady && request()->routeIs($patterns);
    $url = $isReady ? (filled($query) ? route($route) . '?' . $query : route($route)) : null;
@endphp

@if ($variant === 'dropdown')
    @if ($isReady)
        <a {{ $attributes->class(['dropdown-item', 'active' => $isActive]) }} href="{{ $url }}">{{ $label }}</a>
    @else
        <span {{ $attributes->class(['dropdown-item', 'disabled', 'd-flex', 'align-items-center', 'justify-content-between']) }} aria-disabled="true">
            {{ $label }}
            <span class="badge bg-secondary-lt ms-2">Segera</span>
        </span>
    @endif
@elseif ($isReady)
    <a {{ $attributes->class(['nav-link', 'd-flex', 'align-items-center', 'active' => $isActive]) }}
       href="{{ $url }}">
        <span class="nav-link-icon d-sm-none d-lg-inline-block">
            <i class="ti {{ $icon }}"></i>
        </span>
        <span class="nav-link-title">{{ $label }}</span>
    </a>
@else
    <span {{ $attributes->class(['nav-link', 'disabled', 'd-flex', 'align-items-center']) }} aria-disabled="true">
        <span class="nav-link-icon d-sm-none d-lg-inline-block">
            <i class="ti {{ $icon }}"></i>
        </span>
        <span class="nav-link-title">{{ $label }}</span>
        <span class="badge bg-secondary-lt ms-auto">Segera</span>
    </span>
@endif
