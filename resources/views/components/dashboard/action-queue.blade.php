@props([
    'items' => [],   // array{label: string, count: int, href: string, color?: string}
    'title' => 'Antrean Tindakan',
])

{{--
    x-dashboard.action-queue — Daftar item yang memerlukan tindakan admin.

    Tiap baris menampilkan label, jumlah, dan tautan ke halaman terkait.
    Bila semua kosong, tampilkan empty state positif.
--}}

@php
    $hasItems = collect($items)->filter(fn ($i) => ($i['count'] ?? 0) > 0)->isNotEmpty();
@endphp

<x-tabler.card :title="$title">
    @if ($hasItems)
        <ul class="list-group list-group-flush -mx-3 -my-3">
            @foreach ($items as $item)
                @if (($item['count'] ?? 0) > 0)
                    <li class="list-group-item d-flex align-items-center px-3 py-2">
                        <span class="badge bg-{{ $item['color'] ?? 'orange' }}-lt text-{{ $item['color'] ?? 'orange' }} fw-bold me-3 fs-6">
                            {{ number_format($item['count'], 0, ',', '.') }}
                        </span>
                        <span class="flex-grow-1">{{ $item['label'] }}</span>
                        <a href="{{ $item['href'] }}" class="btn btn-sm btn-outline-secondary ms-2" aria-label="{{ $item['label'] }}">
                            <i class="ti ti-arrow-right" aria-hidden="true"></i>
                        </a>
                    </li>
                @endif
            @endforeach
        </ul>
    @else
        <div class="text-center py-4">
            <i class="ti ti-checks fs-1 text-success mb-2 d-block" aria-hidden="true"></i>
            <div class="fw-semibold">Tidak ada yang perlu ditindak</div>
            <div class="text-secondary small">Semua antrean bersih.</div>
        </div>
    @endif
</x-tabler.card>
