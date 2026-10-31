@props([
    'title' => null,
    'subtitle' => null,
    'actions' => null,
    'footer' => null,
    'status' => null, // 'top', 'bottom', 'start' with color e.g. 'status-top status-primary'
    'class' => '',
])

<div {{ $attributes->merge(['class' => 'card ' . ($status ? 'card-' . $status : '') . ' ' . $class]) }}>
    @if ($title || $actions)
        <div class="card-header">
            <div>
                <h3 class="card-title">{{ $title }}</h3>
                @if ($subtitle)
                    <p class="card-subtitle text-muted mb-0">{{ $subtitle }}</p>
                @endif
            </div>
            @if ($actions)
                <div class="card-actions ms-auto">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif

    <div class="card-body">
        {{ $slot }}
    </div>

    @if ($footer)
        <div class="card-footer">
            {{ $footer }}
        </div>
    @endif
</div>
