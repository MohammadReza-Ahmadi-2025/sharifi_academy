<?php

namespace App\Modules\Catalog;

use App\Modules\Catalog\Models\Price;
use App\Modules\Catalog\Observers\PriceObserver;
use App\Modules\Catalog\Services\InventoryService;
use App\Modules\Catalog\Services\VariantPriceService;
use Illuminate\Support\ServiceProvider;

class CatalogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(InventoryService::class);
        $this->app->singleton(VariantPriceService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
        Price::observe(PriceObserver::class);
    }
}
