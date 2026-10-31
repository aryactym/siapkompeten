@props([
    'label',
    'value',
    'icon' => 'ti-activity',
    'color' => 'primary',
    'href' => null,
    'hint' => null,
    'trend' => null,
    'trendColor' => 'success',
    'trendIcon' => null,
])

{{--
    x-tabler.stat — Kartu statistik satu angka (pola Tabler).

    Props:
    - label: teks label (subheader, huruf kapital kecil)
    - value: angka/teks nilai utama
    - icon: nama ikon Tabler tanpa prefix "ti ti-" (mis. "certificate")
    - color: warna Tabler (primary, green, yellow, azure, red, orange, cyan, purple, pink)
    - href: bila diisi, seluruh kartu dapat diklik via stretched-link
    - hint: teks kecil di bawah angka (konteks: mis. "3 belum terverifikasi")
    - trend: teks tren opsional (mis. "+12%")
    - trendColor: warna badge tren
    - trendIcon: ikon untuk tren (mis. "arrow-up", "arrow-down")
--}}

<div class="card card-sm">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-auto">
                <span class="bg-{{ $color }}-lt text-{{ $color }} avatar avatar-md" aria-hidden="true">
                    <i class="ti ti-{{ $icon }} fs-2"></i>
                </span>
            </div>
            <div class="col">
                <div class="text-secondary fw-medium text-uppercase fs-6">{{ $label }}</div>
                <div class="h1 fs-1 fw-bold mt-1 mb-0 lh-1">
                    @if ($href)
                        <a href="{{ $href }}" class="stretched-link text-reset">{{ $value }}</a>
                    @else
                        {{ $value }}
                    @endif
                </div>
                @if ($hint || $trend)
                    <div class="text-secondary fs-6 mt-1 d-flex align-items-center gap-1">
                        @if ($trend)
                            <span class="text-{{ $trendColor }} d-inline-flex align-items-center gap-1">
                                @if ($trendIcon)
                                    <i class="ti ti-{{ $trendIcon }}"></i>
                                @endif
                                {{ $trend }}
                            </span>
                        @endif
                        @if ($hint)
                            <span>{{ $hint }}</span>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
