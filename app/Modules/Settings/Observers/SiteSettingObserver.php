<?php

namespace App\Modules\Settings\Observers;

use App\Modules\Settings\Models\SiteSetting;
use App\Modules\Settings\Services\SiteSettingsService;

class SiteSettingObserver
{
    public function __construct(
        private readonly SiteSettingsService $settingsService,
    ) {}

    public function saved(SiteSetting $setting): void
    {
        $this->settingsService->forgetCache();
    }

    public function deleted(SiteSetting $setting): void
    {
        $this->settingsService->forgetCache();
    }
}
