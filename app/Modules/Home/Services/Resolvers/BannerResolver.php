<?php

namespace App\Modules\Home\Services\Resolvers;

use App\Modules\Home\Models\Banner;
use App\Modules\Home\Services\HomeCacheInvalidator;
use App\Modules\Home\Support\HomeItemPresenter;
use Illuminate\Support\Facades\Cache;

class BannerResolver
{
    public function __construct(private HomeCacheInvalidator $cache) {}

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    public function resolve(): array
    {
        $grouped = Cache::remember($this->cache->bannersKey(), 600, function () {
            return Banner::query()
                ->available()
                ->with('file')
                ->orderByDesc('id')
                ->get()
                ->groupBy(fn (Banner $banner) => $banner->location?->value ?? 'section');
        });

        return $grouped
            ->map(fn ($banners) => HomeItemPresenter::present($banners, 'banner'))
            ->all();
    }
}
