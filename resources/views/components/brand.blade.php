@props([
    'subtitle' => 'Sistem Informasi LSP',
    'href'     => null,
])

@php
    use App\Support\TenantContext;
    $brandInstitution = TenantContext::institution();
    $brandName        = $brandInstitution?->name ?? config('app.name', 'SiapKompeten');
    $brandInitials    = strtoupper(substr($brandName, 0, 2));
    $brandHref        = $href ?? route('dashboard');
@endphp

{{--
    x-brand — Brand satu sumber (sidebar, header, dll).

    Mengambil nama & logo dari LSP aktif via TenantContext.
    Bukan <h1> — semantik heading hanya untuk judul konten halaman.

    Props:
    - subtitle : teks sub-judul, default "Sistem Informasi LSP"
    - href     : URL tujuan klik, default route('dashboard')
--}}
<a href="{{ $brandHref }}"
   class="d-flex align-items-center gap-2 text-decoration-none"
   aria-label="{{ $brandName }}">
    @if ($brandInstitution && $brandInstitution->logo_path)
        <img src="{{ $brandInstitution->logo_path }}"
             alt="{{ $brandName }}"
             class="avatar avatar-sm rounded"
             width="32"
             height="32">
    @else
        <span class="avatar avatar-sm bg-primary-lt text-primary fw-bold" aria-hidden="true">
            {{ $brandInitials }}
        </span>
    @endif
    <div class="d-flex flex-column text-start">
        <span class="fw-bold lh-1" style="color: inherit;">{{ $brandName }}</span>
        <span class="text-white-50 small lh-1 mt-1">{{ $subtitle }}</span>
    </div>
</a>
