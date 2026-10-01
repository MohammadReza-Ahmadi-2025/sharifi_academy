<?php

namespace App\Modules\Auth;

use App\Modules\Auth\Contracts\OtpCodeGenerator;
use App\Modules\Auth\Contracts\OtpSender;
use App\Modules\Auth\Services\LogOtpSender;
use App\Modules\Auth\Services\RandomOtpCodeGenerator;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(OtpCodeGenerator::class, RandomOtpCodeGenerator::class);
        $this->app->singleton(OtpSender::class, LogOtpSender::class);
        $this->app->singleton(Services\MobileOtpService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
    }
}
