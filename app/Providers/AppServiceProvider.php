<?php

namespace App\Providers;

use App\Services\Sms\SmsManager;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SmsManager::class, function ($app) {
            return new SmsManager($app['config']->get('sms', []));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
