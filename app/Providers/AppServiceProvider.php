<?php

namespace App\Providers;
use Illuminate\Auth\Events\Login;
use App\Listeners\LogDeviceLogin;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
    protected $listen = [
        Login::class => [
            LogDeviceLogin::class,
        ],
    ];
}
