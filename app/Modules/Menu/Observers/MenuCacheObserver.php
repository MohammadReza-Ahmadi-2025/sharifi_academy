<?php

namespace App\Modules\Menu\Observers;

use App\Modules\Menu\Services\SiteMenuService;
use Illuminate\Database\Eloquent\Model;

class MenuCacheObserver
{
    public function saved(Model $model): void
    {
        SiteMenuService::flush();
    }

    public function deleted(Model $model): void
    {
        SiteMenuService::flush();
    }
}
