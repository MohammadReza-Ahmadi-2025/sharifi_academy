<?php

namespace App\Modules\Activity;

use App\Observers\ActivityLogObserver;
use App\Support\Activity\ActivityLogger;
use Illuminate\Support\ServiceProvider;

class ActivityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ActivityLogger::class);
    }

    public function boot(): void
    {
        $observer = ActivityLogObserver::class;

        foreach (config('activity_log.models', []) as $modelClass) {
            if (class_exists($modelClass)) {
                $modelClass::observe($observer);
            }
        }
    }
}
