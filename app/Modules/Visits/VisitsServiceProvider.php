<?php

namespace App\Modules\Visits;

use Illuminate\Support\ServiceProvider;

class VisitsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
    }
}
