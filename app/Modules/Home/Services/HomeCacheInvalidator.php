<?php

namespace App\Modules\Home\Services;

use App\Modules\Home\Models\HomeSectionTab;
use App\Services\Home\HomePageService;
use Illuminate\Support\Facades\Cache;

class HomeCacheInvalidator
{
    public function bannersKey(): string
    {
        return 'home:banners';
    }

    public function sectionsSkeletonKey(): string
    {
        return 'home:sections:skeleton';
    }

    public function tabKey(int $tabId): string
    {
        return "home:tab:{$tabId}";
    }

    public function forgetAll(): void
    {
        $this->forgetBanners();
        $this->forgetSectionsSkeleton();

        HomeSectionTab::query()->pluck('id')->each(fn ($id) => $this->forgetTab((int) $id));

        HomePageService::flush();
    }

    public function forgetBanners(): void
    {
        Cache::forget($this->bannersKey());
    }

    public function forgetSectionsSkeleton(): void
    {
        Cache::forget($this->sectionsSkeletonKey());
    }

    public function forgetTab(int $tabId): void
    {
        Cache::forget($this->tabKey($tabId));
    }

    public function forgetTabAndSkeleton(int $tabId): void
    {
        $this->forgetTab($tabId);
        $this->forgetSectionsSkeleton();
        HomePageService::flush();
    }

    public function forgetTabsByContentType(string $contentType): void
    {
        HomeSectionTab::query()
            ->where('content_type', $contentType)
            ->pluck('id')
            ->each(fn ($id) => $this->forgetTab((int) $id));

        $this->forgetSectionsSkeleton();
        HomePageService::flush();
    }
}
