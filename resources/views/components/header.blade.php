@php
    $headerLogo = \App\Support\TenantContext::institution()?->logo_path;
    $headerName = \App\Support\TenantContext::institution()?->name ?? config('app.name');
@endphp

<header class="app-header">
    <div class="brand">
        @if ($headerLogo)
            <img class="brand-logo" src="{{ Storage::disk('public')->url($headerLogo) }}" alt="Logo {{ $headerName }}">
        @else
            <div class="brand-logo" aria-hidden="true"></div>
        @endif
        <div>
            <div class="brand-name">{{ $headerName }}</div>
            <div class="brand-sub">SiapKompeten · Sistem Informasi LSP</div>
        </div>
    </div>

    <div class="header-actions">
        @auth
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-secondary btn-sm">Keluar</button>
            </form>
        @endauth
    </div>
</header>
