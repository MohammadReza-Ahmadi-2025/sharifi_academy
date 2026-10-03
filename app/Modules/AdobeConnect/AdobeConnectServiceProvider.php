<?php

namespace App\Modules\AdobeConnect;

use Illuminate\Support\ServiceProvider;

class AdobeConnectServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
    }
}
