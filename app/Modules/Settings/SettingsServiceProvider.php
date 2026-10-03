<?php

namespace App\Modules\Settings;

use App\Modules\Settings\Models\SiteSetting;
use App\Modules\Settings\Observers\SiteSettingObserver;
use App\Modules\Settings\Services\SiteSettingsService;
use Illuminate\Support\ServiceProvider;

class SettingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SiteSettingsService::class);
    }

    public function boot(): void
    {
        require_once __DIR__.'/Support/site_setting.php';

        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
        SiteSetting::observe(SiteSettingObserver::class);
    }
}
