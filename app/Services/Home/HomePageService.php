<?php

namespace App\Services\Home;

use App\Modules\Home\Services\HomeCacheInvalidator;
use App\Modules\Home\Services\Resolvers\SectionResolver;
use Illuminate\Support\Facades\Cache;

class HomePageService
{
    /**
     * @return array<string, mixed>
     */
    public function data(): array
    {
        return Cache::remember('site:home:payload', 600, function () {
            return [
                'sections' => app(SectionResolver::class)->resolve(),
            ];
        });
    }

    public static function flush(): void
    {
        Cache::forget('site:home:payload');
        app(HomeCacheInvalidator::class)->forgetBanners();
        app(HomeCacheInvalidator::class)->forgetSectionsSkeleton();
    }
}
