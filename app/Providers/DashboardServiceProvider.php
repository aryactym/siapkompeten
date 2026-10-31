<?php

namespace App\Providers;

use App\Domain\Dashboard\DashboardRegistry;
use App\Domain\Dashboard\Widgets\AntreanTindakan;
use App\Domain\Dashboard\Widgets\AsesiGender;
use App\Domain\Dashboard\Widgets\AsesiUsia;
use App\Domain\Dashboard\Widgets\AsesiWilayah;
use App\Domain\Dashboard\Widgets\HasilPerSkema;
use App\Domain\Dashboard\Widgets\Jadwal7Hari;
use App\Domain\Dashboard\Widgets\JadwalKeterisian;
use App\Domain\Dashboard\Widgets\PendaftaranFunnel;
use App\Domain\Dashboard\Widgets\PendaftaranTren;
use App\Domain\Dashboard\Widgets\StatRingkas;
use Illuminate\Support\ServiceProvider;

class DashboardServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DashboardRegistry::class, function () {
            $registry = new DashboardRegistry;

            $registry->register('stat_ringkas', StatRingkas::class);
            $registry->register('pendaftaran_tren', PendaftaranTren::class);
            $registry->register('asesi_usia', AsesiUsia::class);
            $registry->register('asesi_gender', AsesiGender::class);
            $registry->register('pendaftaran_funnel', PendaftaranFunnel::class);
            $registry->register('hasil_per_skema', HasilPerSkema::class);
            $registry->register('jadwal_keterisian', JadwalKeterisian::class);
            $registry->register('asesi_wilayah', AsesiWilayah::class);
            $registry->register('antrean_tindakan', AntreanTindakan::class);
            $registry->register('jadwal_7_hari', Jadwal7Hari::class);

            return $registry;
        });
    }

    public function boot(): void
    {
        //
    }
}
