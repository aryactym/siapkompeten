@props([
    'type' => 'info', // success, danger, warning, info
    'title' => null,
    'dismissible' => true,
    'icon' => null,
])

@php
    $defaultIcons = [
        'success' => 'ti-circle-check',
        'danger' => 'ti-alert-circle',
        'warning' => 'ti-alert-triangle',
        'info' => 'ti-info-circle',
    ];
    $iconName = $icon ?? ($defaultIcons[$type] ?? 'ti-info-circle');
@endphp

<div {{ $attributes->merge(['class' => "alert alert-{$type} " . ($dismissible ? 'alert-dismissible' : '')]) }} role="alert">
    <div class="d-flex">
        <div>
            <i class="ti {{ $iconName }} alert-icon fs-2 me-2"></i>
        </div>
        <div>
            @if ($title)
                <h4 class="alert-title mb-1">{{ $title }}</h4>
            @endif
            <div class="text-secondary">{{ $slot }}</div>
        </div>
    </div>
    @if ($dismissible)
        <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
    @endif
</div>
