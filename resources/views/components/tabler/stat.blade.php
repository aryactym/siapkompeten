@props([
    'title',
    'value',
    'subtext' => null,
    'icon' => 'ti-activity',
    'color' => 'primary', // primary, success, warning, danger, info
    'trend' => null, // e.g. '+12%'
    'trendColor' => 'success',
])

<div class="card card-sm">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-auto">
                <span class="bg-{{ $color }}-lt text-{{ $color }} avatar avatar-md">
                    <i class="ti {{ $icon }} fs-2"></i>
                </span>
            </div>
            <div class="col">
                <div class="text-secondary font-weight-medium text-uppercase fs-6">
                    {{ $title }}
                </div>
                <div class="font-weight-bold fs-2 mt-1">
                    {{ $value }}
                </div>
                @if ($subtext || $trend)
                    <div class="text-secondary fs-6 mt-1 d-flex align-items-center gap-1">
                        @if ($trend)
                            <span class="text-{{ $trendColor }} d-inline-flex align-items-center">
                                {{ $trend }}
                            </span>
                        @endif
                        @if ($subtext)
                            <span>{{ $subtext }}</span>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
