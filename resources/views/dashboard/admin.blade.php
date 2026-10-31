@extends('layouts.admin')

@section('title', 'Dashboard Lembaga')
@section('pretitle', 'Ringkasan & Metrik Operasional')
@section('page-title', 'Dashboard Lembaga')

@section('actions')
    <a href="{{ route('admin.jadwal.create') }}"
       class="btn btn-primary"
       aria-label="Buat Jadwal Uji Kompetensi">
        <i class="ti ti-plus me-1" aria-hidden="true"></i>
        <span class="d-none d-sm-inline">Buat Jadwal Uji</span>
    </a>
    <a href="{{ route('admin.skema.create') }}"
       class="btn btn-outline-primary"
       aria-label="Tambah Skema Sertifikasi">
        <i class="ti ti-certificate me-1" aria-hidden="true"></i>
        <span class="d-none d-sm-inline">Tambah Skema</span>
    </a>
@endsection

@section('content')

{{-- 1. Onboarding checklist (hilang otomatis bila semua selesai) --}}
<x-dashboard.onboarding :steps="$onboardingSteps" />

{{-- 2. Baris kartu statistik — 1 kolom mobile, 2 sm, 4 lg --}}
<x-dashboard.stat-row>
    <div class="col-sm-6 col-lg-3">
        <x-tabler.stat
            label="Skema Sertifikasi"
            :value="number_format($stats['total_skema'], 0, ',', '.')"
            icon="certificate"
            color="primary"
            :href="route('admin.skema.index')"
            :hint="$stats['skema_nonaktif'] > 0 ? $stats['skema_nonaktif'].' nonaktif' : null"
        />
    </div>
    <div class="col-sm-6 col-lg-3">
        <x-tabler.stat
            label="Total Asesi"
            :value="number_format($stats['total_asesi'], 0, ',', '.')"
            icon="users"
            color="green"
            :href="route('admin.asesi.index')"
            :hint="$stats['asesi_belum_verif'] > 0 ? $stats['asesi_belum_verif'].' belum terverifikasi' : null"
        />
    </div>
    <div class="col-sm-6 col-lg-3">
        <x-tabler.stat
            label="Jadwal Mendatang"
            :value="number_format($stats['jadwal_mendatang'], 0, ',', '.')"
            icon="calendar-event"
            color="yellow"
            :href="route('admin.jadwal.index')"
            :hint="$stats['jadwal_mendatang_7hari'] > 0 ? $stats['jadwal_mendatang_7hari'].' dalam 7 hari' : null"
        />
    </div>
    <div class="col-sm-6 col-lg-3">
        <x-tabler.stat
            label="Asesor Aktif"
            :value="number_format($stats['total_asesor'], 0, ',', '.')"
            icon="user-check"
            color="azure"
            :href="route('admin.asesor.index')"
            :hint="$stats['asesor_aktif'] !== $stats['total_asesor'] ? ($stats['total_asesor'] - $stats['asesor_aktif']).' tidak aktif' : null"
        />
    </div>
</x-dashboard.stat-row>

{{-- 3. Konten utama: antrean + jadwal | kesehatan + dokumen --}}
<div class="row row-cards mt-3">
    <div class="col-lg-8">

        {{-- 3a. Antrean tindakan --}}
        <x-dashboard.action-queue
            title="Antrean Tindakan"
            :items="$actionQueue"
        />

        {{-- 3b. Jadwal 7 hari ke depan --}}
        <div class="mt-3">
            <x-dashboard.schedule-list
                title="Jadwal 7 Hari ke Depan"
                :items="$jadwal7Hari"
                :emptyHref="route('admin.jadwal.create')"
                emptyLabel="Buat Jadwal Uji"
            />
        </div>

    </div>

    <div class="col-lg-4">

        {{-- 3c. Kesehatan sistem (pengecekan nyata, tanpa klaim statis) --}}
        <x-dashboard.system-health
            title="Kesehatan Sistem"
            :checks="$systemHealth"
        />

        {{-- 3d. Ringkasan dokumen lembaga --}}
        <div class="mt-3">
            <x-tabler.card title="Dokumen Lembaga">
                <x-slot:actions>
                    <a href="{{ route('admin.institution.index') }}" class="btn btn-sm btn-outline-secondary">
                        Kelola
                    </a>
                </x-slot:actions>

                @if ($documents->isEmpty())
                    <div class="text-center py-3 text-secondary small">
                        <i class="ti ti-files fs-2 d-block mb-1" aria-hidden="true"></i>
                        Belum ada dokumen.
                        <a href="{{ route('admin.institution.index') }}">Unggah di Profil Lembaga.</a>
                    </div>
                @else
                    <ul class="list-group list-group-flush -mx-3 -my-3">
                        @foreach ($documents->take(5) as $doc)
                            <li class="list-group-item px-3 py-2 d-flex align-items-center gap-2">
                                <i class="ti ti-file-text text-secondary flex-shrink-0" aria-hidden="true"></i>
                                <span class="flex-grow-1 text-truncate small">{{ $doc->title }}</span>
                                <a href="{{ route('documents.download', $doc) }}"
                                   class="btn btn-sm btn-ghost-secondary flex-shrink-0"
                                   title="Unduh {{ $doc->title }}">
                                    <i class="ti ti-download" aria-hidden="true"></i>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    @if ($documents->count() > 5)
                        <div class="card-footer text-center py-2">
                            <a href="{{ route('admin.institution.index') }}" class="small text-secondary">
                                Lihat semua {{ $documents->count() }} dokumen
                            </a>
                        </div>
                    @endif
                @endif
            </x-tabler.card>
        </div>

    </div>
</div>

@endsection
