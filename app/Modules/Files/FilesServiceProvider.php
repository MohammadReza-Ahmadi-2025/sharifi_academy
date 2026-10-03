<?php

namespace App\Modules\Files;

use App\Modules\Files\Services\MorphFileSyncService;
use Illuminate\Support\ServiceProvider;

class FilesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MorphFileSyncService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
    }
}
