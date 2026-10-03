<?php

namespace App\Modules\Social\Observers;

use App\Modules\Social\Models\Social;
use Illuminate\Support\Facades\Cache;

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
        Cache::forget('site_socials');
    }
}
