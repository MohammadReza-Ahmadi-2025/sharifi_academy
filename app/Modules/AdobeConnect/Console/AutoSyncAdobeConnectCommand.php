<?php

namespace App\Modules\AdobeConnect\Console;

use App\Modules\AdobeConnect\Services\AutoSyncAdobeConnectSessionsService;
use App\Modules\AdobeConnect\Support\AdobeConnectConfig;
use Illuminate\Console\Command;

class AutoSyncAdobeConnectCommand extends Command
{
    protected $signature = 'adobe-connect:auto-sync
                            {--hours= : Override lookback window in hours}
                            {--no-recordings : Skip recording import}
                            {--no-attendance : Skip attendance sync}';

    protected $description = 'Auto-sync Adobe recordings and attendance for recently ended sessions';

    public function handle(
        AdobeConnectConfig $config,
        AutoSyncAdobeConnectSessionsService $autoSync,
    ): int {
        if (! $config->isApiConfigured()) {
            $this->warn('Adobe Connect API is not configured; skipping auto-sync.');

            return self::SUCCESS;
        }

        if (! $config->autoSyncEnabled() && ! $this->option('hours')) {
            $this->warn('Adobe Connect auto-sync is disabled (ADOBE_CONNECT_AUTO_SYNC_ENABLED=false).');

            return self::SUCCESS;
        }

        $hours = filled($this->option('hours'))
            ? max(1, (int) $this->option('hours'))
            : $config->autoSyncWithinHours();

        $stats = $autoSync->syncRecent(
            $hours,
            ! (bool) $this->option('no-recordings'),
            ! (bool) $this->option('no-attendance'),
        );

        $this->info(sprintf(
            'Auto-sync done (last %dh): sessions=%d recordings=%d (failed=%d) attendance=%d (failed=%d)',
            $hours,
            $stats['sessions'],
            $stats['recordings_synced'],
            $stats['recordings_failed'],
            $stats['attendance_synced'],
            $stats['attendance_failed'],
        ));

        return self::SUCCESS;
    }
}
