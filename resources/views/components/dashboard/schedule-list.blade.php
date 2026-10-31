@props([
    'items'     => [],   // array{label: string, meta: string, href: string, date?: string}
    'title'     => 'Jadwal 7 Hari ke Depan',
    'emptyHref' => null,
    'emptyLabel' => 'Buat Jadwal Uji',
])

{{--
    x-dashboard.schedule-list — Jadwal mendatang dalam 7 hari.

    Props:
    - items: daftar jadwal dari widget Jadwal7Hari
    - title: judul kartu
    - emptyHref: tautan tombol aksi saat kosong
    - emptyLabel: teks tombol aksi saat kosong
--}}

<x-tabler.card :title="$title">
    @if (empty($items))
        <div class="empty py-4">
            <div class="empty-icon">
                <i class="ti ti-calendar-off fs-1 text-secondary" aria-hidden="true"></i>
            </div>
            <p class="empty-title">Belum ada jadwal 7 hari ke depan</p>
            <p class="empty-subtitle text-secondary">Buat jadwal uji kompetensi untuk memulai proses asesmen.</p>
            @if ($emptyHref)
                <div class="empty-action">
                    <a href="{{ $emptyHref }}" class="btn btn-primary">
                        <i class="ti ti-plus me-1" aria-hidden="true"></i>
                        {{ $emptyLabel }}
                    </a>
                </div>
            @endif
        </div>
    @else
        <ul class="list-group list-group-flush -mx-3 -my-3">
            @foreach ($items as $item)
                <li class="list-group-item px-3 py-2">
                    <div class="d-flex align-items-center gap-2">
                        <i class="ti ti-calendar-event text-primary flex-shrink-0" aria-hidden="true"></i>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="fw-semibold text-truncate">{{ $item['label'] }}</div>
                            <div class="text-secondary small">{{ $item['meta'] }}</div>
                        </div>
                        <a href="{{ $item['href'] }}" class="btn btn-sm btn-outline-secondary flex-shrink-0">
                            <i class="ti ti-arrow-right" aria-hidden="true"></i>
                        </a>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</x-tabler.card>
