@props([
    'checks' => [],  // array{label: string, status: 'ok'|'warn'|'error', value: string, icon?: string}
    'title'  => 'Kesehatan Sistem',
])

{{--
    x-dashboard.system-health — Panel kesehatan sistem berdasarkan pengecekan nyata.

    TIDAK menampilkan klaim statis (OWASP, AES-256, "Terisolasi", "Resmi BNSP").
    Setiap item harus berasal dari pengecekan runtime.

    Props:
    - checks: array pengecekan
    - title: judul kartu
--}}

@php
    $colorMap = ['ok' => 'success', 'warn' => 'yellow', 'error' => 'danger'];
    $iconMap  = ['ok' => 'circle-check', 'warn' => 'alert-triangle', 'error' => 'circle-x'];
@endphp

<x-tabler.card :title="$title">
    @if (empty($checks))
        <div class="text-secondary text-center py-3">
            <i class="ti ti-heartbeat fs-2 mb-2 d-block" aria-hidden="true"></i>
            Pengecekan belum tersedia.
        </div>
    @else
        <div class="divide-y">
            @foreach ($checks as $check)
                @php
                    $statusColor = $colorMap[$check['status'] ?? 'ok'] ?? 'secondary';
                    $statusIcon  = $iconMap[$check['status'] ?? 'ok'] ?? 'circle-check';
                    $itemIcon    = $check['icon'] ?? 'server';
                @endphp
                <div class="py-2 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <i class="ti ti-{{ $itemIcon }} text-{{ $statusColor }}" aria-hidden="true"></i>
                        <div>
                            <div class="fw-semibold">{{ $check['label'] }}</div>
                            @if (! empty($check['value']))
                                <div class="text-secondary small">{{ $check['value'] }}</div>
                            @endif
                        </div>
                    </div>
                    <span class="badge bg-{{ $statusColor }}-lt text-{{ $statusColor }}">
                        <i class="ti ti-{{ $statusIcon }} me-1" aria-hidden="true"></i>
                        {{ $check['status'] === 'ok' ? 'Baik' : ($check['status'] === 'warn' ? 'Perhatian' : 'Masalah') }}
                    </span>
                </div>
            @endforeach
        </div>
    @endif
</x-tabler.card>
