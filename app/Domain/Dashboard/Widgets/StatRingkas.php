<?php

namespace App\Domain\Dashboard\Widgets;

use App\Domain\Dashboard\Contracts\Widget;
use App\Domain\Dashboard\DashboardFilter;
use App\Domain\Dashboard\WidgetResult;
use App\Models\Asesi;
use App\Models\Asesor;
use App\Models\JadwalAsesmen;
use App\Models\Pendaftaran;
use App\Models\Skema;
use App\Models\User;

class StatRingkas implements Widget
{
    public function key(): string
    {
        return 'stat_ringkas';
    }

    public function type(): string
    {
        return 'stat';
    }

    public function title(): string
    {
        return 'Ringkasan';
    }

    public function roles(): array
    {
        return ['admin', 'pimpinan'];
    }

    public function authorize(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('pimpinan');
    }

    public function ttl(): int
    {
        return 300;
    }

    public function data(DashboardFilter $filter): WidgetResult
    {
        $skemaAktif = Skema::query()->where('is_active', true)->count();
        $asesorAktif = Asesor::query()->where('status', 'aktif')->count();
        $asesiTotal = Asesi::query()->count();
        $asesiBelumVerif = Asesi::query()->where('status_verifikasi', 'belum_terverifikasi')->count();
        $jadwalMendatang = JadwalAsesmen::query()->where('tanggal', '>=', now()->toDateString())->count();
        $pendaftaranTerjadwal = Pendaftaran::query()->where('status', Pendaftaran::STATUS_TERJADWAL)->count();

        return new WidgetResult(
            key: $this->key(),
            type: $this->type(),
            title: $this->title(),
            unit: '',
            meta: [
                'total_skema' => $skemaAktif,
                'total_asesor' => $asesorAktif,
                'total_asesi' => $asesiTotal,
                'asesi_belum_verifikasi' => $asesiBelumVerif,
                'jadwal_mendatang' => $jadwalMendatang,
                'pendaftaran_terjadwal' => $pendaftaranTerjadwal,
            ],
            empty: false,
        );
    }
}
