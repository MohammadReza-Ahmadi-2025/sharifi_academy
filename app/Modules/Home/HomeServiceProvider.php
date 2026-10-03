<?php

namespace App\Modules\Home;

use App\Modules\Home\Models\Banner;
use App\Modules\Home\Models\HomeSection;
use App\Modules\Home\Models\HomeSectionItem;
use App\Modules\Home\Models\HomeSectionTab;
use App\Modules\Home\Observers\HomeSectionCacheObserver;
use Illuminate\Support\ServiceProvider;

class HomeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');

        HomeSection::observe(HomeSectionCacheObserver::class);
        HomeSectionTab::observe(HomeSectionCacheObserver::class);
        HomeSectionItem::observe(HomeSectionCacheObserver::class);
        Banner::observe(HomeSectionCacheObserver::class);
    }
}
