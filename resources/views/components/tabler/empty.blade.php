@props([
    'title' => 'Tidak Ada Data',
    'subtitle' => 'Belum ada rekaman data yang tersedia saat ini.',
    'icon' => 'ti-database-off',
    'action' => null,
])

<div class="empty">
    <div class="empty-icon">
        <i class="ti {{ $icon }} fs-1 text-secondary"></i>
    </div>
    <p class="empty-title">{{ $title }}</p>
    <p class="empty-subtitle text-secondary">
        {{ $subtitle }}
    </p>
    @if ($action)
        <div class="empty-action">
            {{ $action }}
        </div>
    @endif
</div>
