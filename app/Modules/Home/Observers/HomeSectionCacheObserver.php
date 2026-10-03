<?php

namespace App\Modules\Home\Observers;

use App\Modules\Home\Services\HomeCacheInvalidator;
use Illuminate\Database\Eloquent\Model;

class HomeSectionCacheObserver
{
    public function saved(Model $model): void
    {
        app(HomeCacheInvalidator::class)->forgetAll();
    }

    public function deleted(Model $model): void
    {
        app(HomeCacheInvalidator::class)->forgetAll();
    }
}
