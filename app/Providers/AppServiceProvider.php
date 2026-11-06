<?php

namespace App\Providers;

use App\Models\RencanaAsesmen;
use App\Models\Skema;
use App\Policies\RencanaAsesmenPolicy;
use App\Policies\SkemaPolicy;
use App\Services\Notifications\LogSmsGateway;
use App\Services\Notifications\SmsGateway;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SmsGateway::class, function ($app): SmsGateway {
            $driver = (string) config('notifikasi.default_sms', 'log');
            $gatewayClass = config('notifikasi.drivers.'.$driver.'.class', LogSmsGateway::class);

            if (! is_string($gatewayClass) || ! class_exists($gatewayClass)) {
                $gatewayClass = LogSmsGateway::class;
            }

            return $app->make($gatewayClass);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }

    private function registerPolicies(): void
    {
        Gate::policy(Skema::class, SkemaPolicy::class);
        Gate::policy(RencanaAsesmen::class, RencanaAsesmenPolicy::class);
    }
}
