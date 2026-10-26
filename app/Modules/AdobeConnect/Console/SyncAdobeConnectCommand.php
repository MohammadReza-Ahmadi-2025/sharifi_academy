<?php

namespace App\Modules\AdobeConnect\Console;

use App\Modules\AdobeConnect\Models\AdobeRoom;
use App\Modules\AdobeConnect\Services\SyncAdobeRoomFromApiService;
use App\Modules\AdobeConnect\Services\SyncAdobeSessionRecordingsService;
use App\Modules\AdobeConnect\Support\AdobeConnectConfig;
use App\Modules\Courses\Models\ClassSession;
use Illuminate\Console\Command;

class SyncAdobeConnectCommand extends Command
{
    protected $signature = 'adobe-connect:sync
                            {--rooms : Sync meeting url/path for all Adobe rooms}
                            {--recordings : Import recordings for sessions that have Adobe rooms}';

    protected $description = 'Sync Adobe Connect rooms and recordings via XML API';

    public function handle(
        AdobeConnectConfig $config,
        SyncAdobeRoomFromApiService $roomSync,
        SyncAdobeSessionRecordingsService $recordingSync,
    ): int {
        if (! $config->isApiConfigured()) {
            $this->error('Configure ADOBE_CONNECT_BASE_URL, ADOBE_CONNECT_LOGIN, and ADOBE_CONNECT_API_TOKEN in .env');

            return self::FAILURE;
        }

        $rooms = (bool) $this->option('rooms');
        $recordings = (bool) $this->option('recordings');

        if (! $rooms && ! $recordings) {
            $rooms = true;
            $recordings = true;
        }

        if ($rooms) {
            $bar = $this->output->createProgressBar(AdobeRoom::query()->count());
            AdobeRoom::query()->orderBy('id')->chunkById(50, function ($chunk) use ($roomSync, $bar): void {
                foreach ($chunk as $room) {
                    try {
                        $roomSync->sync($room);
                    } catch (\Throwable $e) {
                        $this->warn("Room #{$room->id}: {$e->getMessage()}");
                    }
                    $bar->advance();
                }
            });
            $bar->finish();
            $this->newLine();
        }

        if ($recordings) {
            $sessions = ClassSession::query()
                ->whereHas('adobeRooms')
                ->orWhereHas('courseClass.classLevelAdobeRooms')
                ->with(['courseClass', 'adobeRooms'])
                ->orderBy('id')
                ->get();

            foreach ($sessions as $session) {
                try {
                    $count = count($recordingSync->syncForSession($session));
                    $this->line("Session #{$session->id}: {$count} recording(s)");
                } catch (\Throwable $e) {
                    $this->warn("Session #{$session->id}: {$e->getMessage()}");
                }
            }
        }

        $this->info('Adobe Connect sync finished.');

        return self::SUCCESS;
    }
}
