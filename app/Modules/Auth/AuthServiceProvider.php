<?php

namespace App\Modules\Auth;

use App\Modules\Auth\Contracts\OtpCodeGenerator;
use App\Modules\Auth\Contracts\OtpSender;
use App\Modules\Auth\Services\LogOtpSender;
use App\Modules\Auth\Services\RandomOtpCodeGenerator;
use App\Modules\Auth\Services\SmsIrOtpSender;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(OtpCodeGenerator::class, RandomOtpCodeGenerator::class);
        $this->app->singleton(OtpSender::class, function ($app) {
            if ($app->runningUnitTests()) {
                return $app->make(LogOtpSender::class);
            }

            $apiKey = config('sms.drivers.smsir.api_key');

            if (filled($apiKey)) {
                return $app->make(SmsIrOtpSender::class);
            }

            return $app->make(LogOtpSender::class);
        });
        $this->app->singleton(Services\MobileOtpService::class);
        $this->app->singleton(Services\SmsIrService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
    }
}
