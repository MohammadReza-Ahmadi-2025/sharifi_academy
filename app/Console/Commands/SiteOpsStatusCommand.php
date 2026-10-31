<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class SiteOpsStatusCommand extends Command
{
    protected $signature = 'site:ops:status';

    protected $description = 'Check local/prod readiness: Meilisearch, queue tables, scheduled jobs';

    public function handle(): int
    {
        $this->components->info('وضعیت زیرساخت سایت');

        $this->line('APP_ENV: '.config('app.env'));
        $this->line('QUEUE_CONNECTION: '.config('queue.default'));
        $this->line('SCOUT_DRIVER: '.config('scout.driver'));

        $host = rtrim((string) config('scout.meilisearch.host', 'http://127.0.0.1:7700'), '/');
        try {
            $response = Http::timeout(2)->get($host.'/health');
            if ($response->successful()) {
                $this->components->twoColumnDetail('Meilisearch', '<fg=green>up</> '.$host);
            } else {
                $this->components->twoColumnDetail('Meilisearch', '<fg=red>down</> HTTP '.$response->status());
            }
        } catch (\Throwable $e) {
            $this->components->twoColumnDetail('Meilisearch', '<fg=red>unreachable</> '.$host);
            $this->line('  → docker compose up -d meilisearch');
            $this->line('  → سپس: php artisan site:search:import --fresh');
        }

        $jobsReady = Schema::hasTable('jobs');
        $this->components->twoColumnDetail(
            'Queue jobs table',
            $jobsReady ? '<fg=green>ready</>' : '<fg=red>missing — php artisan queue:table && migrate</>',
        );

        $this->newLine();
        $this->components->info('پروداکشن — کرون و صف');
        $this->line('* * * * * cd '.base_path().' && php artisan schedule:run >> /dev/null 2>&1');
        $this->line('php artisan queue:work --sleep=3 --tries=3 --max-time=3600');
        $this->line('زمان‌بندی فعال: cart:clear-expired-items (هر ۵ دقیقه)، adobe-connect:auto-sync (ساعتی)');

        return self::SUCCESS;
    }
}
