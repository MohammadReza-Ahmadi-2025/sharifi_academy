<?php

namespace App\Modules\Social\Observers;

use App\Modules\Social\Models\Social;
use App\Modules\Social\Services\SiteSocialService;

class SocialObserver
{
    public function created(Social $social): void
    {
        $this->clearCache();
    }

    public function updated(Social $social): void
    {
        $this->clearCache();
    }

    public function deleted(Social $social): void
    {
        $this->clearCache();
    }

    protected function clearCache(): void
    {
        SiteSocialService::flush();
    }
}
