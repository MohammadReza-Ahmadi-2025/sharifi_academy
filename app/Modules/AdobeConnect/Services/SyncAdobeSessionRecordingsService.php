<?php

namespace App\Modules\AdobeConnect\Services;

use App\Modules\AdobeConnect\Enums\AdobeRoomStatus;
use App\Modules\AdobeConnect\Models\AdobeRecording;
use App\Modules\AdobeConnect\Models\AdobeRoom;
use App\Modules\AdobeConnect\Support\AdobeConnectConfig;
use App\Modules\AdobeConnect\Support\AdobeConnectDuration;
use App\Modules\AdobeConnect\Support\AdobeConnectUrlBuilder;
use App\Modules\Courses\Models\ClassSession;
use App\Modules\Courses\Models\CourseClass;
use Illuminate\Support\Carbon;

class SyncAdobeSessionRecordingsService
{
    public function __construct(
        private readonly AdobeConnectConfig $config,
        private readonly AdobeConnectApi $api,
        private readonly AdobeConnectUrlBuilder $urls,
    ) {}

    /**
     * @return list<AdobeRecording>
     */
    public function syncForSession(ClassSession $session, ?CourseClass $courseClass = null): array
    {
        if (! $this->config->isApiConfigured()) {
            throw new \RuntimeException('برای سینک ضبط‌ها، تنظیمات Adobe Connect در .env کامل نیست.');
        }

        $courseClass ??= $session->courseClass;
        if ($courseClass === null) {
            throw new \RuntimeException('کلاس جلسه مشخص نیست.');
        }

        $room = $this->resolveMeetingRoom($session, $courseClass);
        if ($room === null) {
            throw new \RuntimeException('اتاق Adobe فعالی با sco-id برای این جلسه یا کلاس پیدا نشد.');
        }

        $archives = $this->api->meetingRecordingArchives((string) $room->sco_id);
        $saved = [];

        foreach ($archives as $archive) {
            $urlPath = trim($archive['url_path']);
            if ($urlPath === '' || ! $this->belongsToSession($archive, $session, $room)) {
                continue;
            }

            $existing = AdobeRecording::query()
                ->where('sco_id', $archive['sco_id'])
                ->first();
            if ($existing !== null && $existing->class_session_id !== $session->id) {
                continue;
            }

            $name = (string) ($archive['name'] ?: 'ضبط جلسه');
            $enhanced = $this->api->recordingUsesEnhancedAv($archive['sco_id']);
            $playbackUrl = $this->urls->recordingPlaybackUrl($urlPath);
            $downloadUrl = $enhanced
                ? $this->urls->recordingMp4DownloadUrl($urlPath, $name.'.mp4')
                : null;

            $recording = AdobeRecording::query()->updateOrCreate(
                ['sco_id' => $archive['sco_id']],
                [
                    'class_session_id' => $session->id,
                    'name' => $name,
                    'url_path' => $urlPath,
                    'playback_url' => $playbackUrl,
                    'download_url' => $downloadUrl,
                    'duration_seconds' => AdobeConnectDuration::toSeconds($archive['duration']),
                    'recorded_at' => $this->parseDate($archive['date_begin']),
                    'is_public' => true,
                    'is_passcode_protected' => false,
                    'passcode' => null,
                ],
            );

            $saved[] = $recording;
        }

        AdobeRoom::query()
            ->whereKey($room->id)
            ->update(['last_synced_at' => now()]);

        return $saved;
    }

    private function resolveMeetingRoom(ClassSession $session, CourseClass $courseClass): ?AdobeRoom
    {
        $sessionRoom = $session->adobeRooms()
            ->where('status', AdobeRoomStatus::Active)
            ->first();

        if ($sessionRoom instanceof AdobeRoom) {
            return $sessionRoom;
        }

        return $courseClass->classLevelAdobeRooms()
            ->where('status', AdobeRoomStatus::Active)
            ->first();
    }

    /**
     * @param  array{date_begin: string|null, date_end: string|null}  $archive
     */
    private function belongsToSession(array $archive, ClassSession $session, AdobeRoom $room): bool
    {
        if ($room->class_session_id === $session->id) {
            return true;
        }

        $recordedAt = $this->parseDate($archive['date_begin']);
        $startsAt = $session->actual_start_at ?? $session->scheduled_start_at;
        $endsAt = $session->actual_end_at ?? $session->scheduled_end_at;

        if ($recordedAt === null || $startsAt === null || $endsAt === null) {
            return false;
        }

        return $recordedAt->between(
            $startsAt->copy()->subHours(2),
            $endsAt->copy()->addHours(2),
        );
    }

    private function parseDate(?string $value): ?Carbon
    {
        if (! filled($value)) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }
}
