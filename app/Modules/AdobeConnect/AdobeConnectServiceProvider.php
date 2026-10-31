<?php

namespace App\Modules\AdobeConnect;

use App\Modules\AdobeConnect\Console\AutoSyncAdobeConnectCommand;
use App\Modules\AdobeConnect\Console\SyncAdobeConnectCommand;
use App\Modules\AdobeConnect\Models\AdobeRecording;
use App\Modules\AdobeConnect\Models\AdobeRoom;
use App\Modules\AdobeConnect\Policies\AdobeRecordingPolicy;
use App\Modules\AdobeConnect\Policies\AdobeRoomPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AdobeConnectServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            base_path('config/adobe_connect.php'),
            'adobe_connect',
        );
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
        Gate::policy(AdobeRoom::class, AdobeRoomPolicy::class);
        Gate::policy(AdobeRecording::class, AdobeRecordingPolicy::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                SyncAdobeConnectCommand::class,
                AutoSyncAdobeConnectCommand::class,
            ]);
        }
    }
}
