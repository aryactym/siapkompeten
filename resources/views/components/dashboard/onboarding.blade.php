@props([
    'steps' => [],   // array{label: string, done: bool, href: string, description?: string}
    'title' => 'Selesaikan Pengaturan Awal Lembaga',
])

{{--
    x-dashboard.onboarding — Checklist setup awal LSP.

    Ditampilkan selama belum semua langkah selesai.
    Hilang otomatis bila semua langkah `done = true`.

    Props:
    - steps: array langkah onboarding
    - title: judul kartu
--}}

@php
    $allDone = collect($steps)->every(fn ($s) => $s['done']);
    $doneCount = collect($steps)->filter(fn ($s) => $s['done'])->count();
    $totalCount = count($steps);
    $percent = $totalCount > 0 ? (int) round(($doneCount / $totalCount) * 100) : 0;
@endphp

@if (! $allDone)
    <div class="card mb-3 border-primary">
        <div class="card-header">
            <div class="d-flex align-items-center gap-2 w-100">
                <i class="ti ti-checklist text-primary fs-3" aria-hidden="true"></i>
                <div class="flex-grow-1">
                    <h3 class="card-title mb-0">{{ $title }}</h3>
                    <div class="text-secondary small">{{ $doneCount }} dari {{ $totalCount }} langkah selesai</div>
                </div>
                <span class="badge bg-primary-lt text-primary fw-bold">{{ $percent }}%</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="progress progress-sm m-0 rounded-0" role="progressbar" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar bg-primary" style="width: {{ $percent }}%"></div>
            </div>
            <ul class="list-group list-group-flush">
                @foreach ($steps as $i => $step)
                    <li class="list-group-item d-flex align-items-center gap-3 py-2 px-3 {{ $step['done'] ? 'opacity-75' : '' }}">
                        <span class="avatar avatar-xs {{ $step['done'] ? 'bg-success-lt text-success' : 'bg-secondary-lt text-secondary' }}" aria-hidden="true">
                            @if ($step['done'])
                                <i class="ti ti-check"></i>
                            @else
                                <span class="fw-bold small">{{ $i + 1 }}</span>
                            @endif
                        </span>
                        <div class="flex-grow-1">
                            <div class="fw-semibold {{ $step['done'] ? 'text-decoration-line-through text-secondary' : '' }}">
                                {{ $step['label'] }}
                            </div>
                            @if (! empty($step['description']))
                                <div class="text-secondary small">{{ $step['description'] }}</div>
                            @endif
                        </div>
                        @if (! $step['done'])
                            <a href="{{ $step['href'] }}" class="btn btn-sm btn-outline-primary flex-shrink-0">
                                Mulai <i class="ti ti-arrow-right ms-1" aria-hidden="true"></i>
                            </a>
                        @else
                            <span class="text-success small flex-shrink-0">
                                <i class="ti ti-check" aria-hidden="true"></i> Selesai
                            </span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
