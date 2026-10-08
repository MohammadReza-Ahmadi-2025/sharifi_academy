<?php

namespace App\Modules\Social\Services;

use App\Modules\Social\Models\Social;
use App\Modules\Social\Support\SocialPresenter;
use Illuminate\Support\Facades\Cache;

class SiteSocialService
{
    public static function flush(): void
    {
        Cache::forget('site_socials');
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function forSite(): array
    {
        return Cache::remember('site_socials', 600, function () {
            return Social::query()
                ->where('is_active', true)
                ->orderBy('sort')
                ->orderBy('id')
                ->get()
                ->map(fn (Social $social) => SocialPresenter::forSite($social))
                ->values()
                ->all();
        });
    }
}
