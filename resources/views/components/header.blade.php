@php
    $headerUser = auth()->user();
    $headerInstitutions = $headerUser?->accessibleInstitutions() ?? collect();
    $headerLogo = session(\App\Support\TenantContext::SESSION_LOGO);
    $headerName = session(\App\Support\TenantContext::SESSION_NAME, 'Lembaga belum dipilih');
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
            @if ($headerInstitutions->count() > 1)
                <form method="POST" action="{{ route('tenant.switch') }}">
                    @csrf
                    <label class="muted" for="institution-switcher" style="font-size: 12px; margin-right: 4px;">Lembaga aktif</label>
                    <select
                        id="institution-switcher"
                        name="institution_id"
                        class="select"
                        onchange="this.form.submit()"
                        aria-label="Pilih lembaga aktif"
                    >
                        @foreach ($headerInstitutions as $institution)
                            <option value="{{ $institution->id }}" @selected(session(\App\Support\TenantContext::SESSION_ID) === $institution->id)>
                                {{ $institution->name }}
                            </option>
                        @endforeach
                    </select>
                    <noscript><button type="submit" class="btn btn-sm">Ganti</button></noscript>
                </form>
            @endif

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-secondary btn-sm">Keluar</button>
            </form>
        @endauth
    </div>
</header>
