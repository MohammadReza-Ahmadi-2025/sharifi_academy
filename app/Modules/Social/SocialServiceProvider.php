<?php

namespace App\Modules\Social;

use App\Modules\Social\Models\Social;
use App\Modules\Social\Observers\SocialObserver;
use Illuminate\Support\ServiceProvider;

class SocialServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
        Social::observe(SocialObserver::class);
    }
}
