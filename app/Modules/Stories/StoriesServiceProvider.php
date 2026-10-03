<?php

namespace App\Modules\Stories;

use Illuminate\Support\ServiceProvider;

class StoriesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
    }
}
