@extends('layouts.asesor')

@section('title', 'Jadwal Uji Saya - Kalender')
@section('pretitle', 'Penugasan')
@section('page-title', 'Jadwal Uji Kompetensi Saya (Kalender)')

@section('content')
@if ($asesor === null)
    <div class="alert alert-warning">
        Profil asesor Anda belum dibuat oleh admin. Hubungi administrator lembaga untuk melengkapi profil asesor.
    </div>
@else
    @php
        $grouped = $jadwals->groupBy(fn ($jadwal) => $jadwal->tanggal->toDateString());
        $firstDay = $month->copy()->startOfMonth();
        $lastDay = $month->copy()->endOfMonth();
        $gridStart = $firstDay->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
        $gridEnd = $lastDay->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);
        $dayNames = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
        $prevMonth = $month->copy()->subMonth()->format('Y-m');
        $nextMonth = $month->copy()->addMonth()->format('Y-m');
    @endphp

    <x-tabler.card>
        <div class="card-header">
            <div class="row g-2 align-items-center w-100">
                <div class="col-auto">
                    <a href="{{ route('asesor.jadwal.calendar', ['month' => $prevMonth]) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="ti ti-chevron-left"></i>
                    </a>
                </div>
                <div class="col text-center">
                    <h3 class="card-title mb-0">{{ $month->translatedFormat('F Y') }}</h3>
                </div>
                <div class="col-auto">
                    <a href="{{ route('asesor.jadwal.calendar', ['month' => $nextMonth]) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="ti ti-chevron-right"></i>
                    </a>
                </div>
                <div class="col-auto ms-auto">
                    <a href="{{ route('asesor.jadwal.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="ti ti-list me-1"></i> Daftar
                    </a>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-vcenter card-table mb-0">
                <thead>
                    <tr>
                        @foreach ($dayNames as $dayName)
                            <th class="text-center">{{ $dayName }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @for ($date = $gridStart->copy(); $date->lte($gridEnd); $date->addDay())
                        @if ($date->dayOfWeekIso === 1)
                            <tr>
                        @endif
                        @php
                            $isCurrentMonth = $date->month === $month->month;
                            $isToday = $date->isToday();
                            $dayJadwals = $grouped->get($date->toDateString(), collect());
                        @endphp
                        <td class="{{ $isCurrentMonth ? '' : 'bg-light text-muted' }} {{ $isToday ? 'bg-primary-lt' : '' }}" style="min-width: 120px; vertical-align: top;">
                            <div class="fw-semibold mb-1 {{ $isToday ? 'text-primary' : '' }}">{{ $date->day }}</div>
                            @foreach ($dayJadwals as $jadwal)
                                <div class="mb-1 p-1 rounded bg-blue-lt">
                                    <div class="small fw-semibold">
                                        <a href="{{ route('asesor.jadwal.peserta', $jadwal) }}" class="text-reset">
                                            {{ substr($jadwal->jam_mulai, 0, 5) }}–{{ substr($jadwal->jam_selesai, 0, 5) }}
                                        </a>
                                    </div>
                                    <div class="small text-secondary">{{ $jadwal->skema->nama_skema }}</div>
                                    <div class="small">
                                        <x-tabler.status-badge :status="$jadwal->status" />
                                    </div>
                                </div>
                            @endforeach
                        </td>
                        @if ($date->dayOfWeekIso === 7)
                            </tr>
                        @endif
                    @endfor
                </tbody>
            </table>
        </div>

        @if ($jadwals->isEmpty())
            <div class="card-body">
                <x-tabler.empty
                    title="Belum ada penugasan"
                    description="Jadwal yang ditugaskan kepada Anda akan muncul di sini."
                    icon="ti ti-calendar-off"
                />
            </div>
        @endif
    </x-tabler.card>
@endif
@endsection