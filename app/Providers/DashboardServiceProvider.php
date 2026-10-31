<?php

namespace App\Providers;

use App\Domain\Dashboard\Contracts\Widget;
use App\Domain\Dashboard\DashboardRegistry;
use Illuminate\Support\ServiceProvider;

class DashboardServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DashboardRegistry::class, function () {
            $registry = new DashboardRegistry();

            // Register widgets here as they are implemented.
            // Example: $registry->register('stat_ringkas', \App\Domain\Dashboard\Widgets\StatRingkas::class);

            return $registry;
        });
    }

    public function boot(): void
    {
        //
    }
}
