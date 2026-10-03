<?php

namespace App\Modules\Menu;

use App\Modules\Menu\Models\Menu;
use App\Modules\Menu\Models\MenuItem;
use App\Modules\Menu\Observers\MenuCacheObserver;
use Illuminate\Support\ServiceProvider;

class MenuServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');

        Menu::observe(MenuCacheObserver::class);
        MenuItem::observe(MenuCacheObserver::class);
    }
}
