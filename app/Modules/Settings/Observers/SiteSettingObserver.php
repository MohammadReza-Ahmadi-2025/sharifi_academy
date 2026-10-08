<?php

namespace App\Modules\Settings\Observers;

use App\Modules\Settings\Models\SiteSetting;
use App\Modules\Settings\Services\SiteSettingsService;
use App\Services\Home\HomePageService;

class SiteSettingObserver
{
    public function __construct(
        private readonly SiteSettingsService $settingsService,
    ) {}

    public function saved(SiteSetting $setting): void
    {
        $this->settingsService->forgetCache();
        $this->forgetHomeCacheIfNeeded($setting->key);
    }

    public function deleted(SiteSetting $setting): void
    {
        $this->settingsService->forgetCache();
        $this->forgetHomeCacheIfNeeded($setting->key);
    }

    private function forgetHomeCacheIfNeeded(string $key): void
    {
        if (str_starts_with($key, 'home_')) {
            HomePageService::flush();
        }
    }
}
