<?php

namespace App\Modules\AdobeConnect\Services;

use App\Modules\AdobeConnect\Support\AdobeConnectConfig;
use App\Modules\AdobeConnect\Support\AdobeConnectSessionSco;
use App\Modules\Courses\Enums\ClassSessionStatus;
use App\Modules\Courses\Enums\CourseClassOnlinePlatform;
use App\Modules\Courses\Models\ClassSession;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class AutoSyncAdobeConnectSessionsService
{
    public function __construct(
        private readonly AdobeConnectConfig $config,
        private readonly SyncAdobeSessionRecordingsService $recordings,
        private readonly SyncAdobeSessionAttendanceService $attendance,
    ) {}

    /**
     * @return array{
     *     sessions: int,
     *     recordings_synced: int,
     *     recordings_failed: int,
     *     attendance_synced: int,
     *     attendance_failed: int
     * }
     */
    public function syncRecent(int $withinHours = 48, bool $recordings = true, bool $attendance = true): array
    {
        $stats = [
            'sessions' => 0,
            'recordings_synced' => 0,
            'recordings_failed' => 0,
            'attendance_synced' => 0,
            'attendance_failed' => 0,
        ];

        if (! $this->config->isApiConfigured()) {
            return $stats;
        }

        $sessions = $this->recentEligibleSessions($withinHours);
        $stats['sessions'] = $sessions->count();

        foreach ($sessions as $session) {
            if ($recordings) {
                try {
                    $saved = $this->recordings->syncForSession($session);
                    $stats['recordings_synced'] += count($saved);
                } catch (Throwable $exception) {
                    $stats['recordings_failed']++;
                    Log::warning('Adobe Connect auto recording sync failed', [
                        'class_session_id' => $session->id,
                        'message' => $exception->getMessage(),
                    ]);
                }
            }

            if ($attendance) {
                try {
                    $this->attendance->sync($session, true);
                    $stats['attendance_synced']++;
                } catch (Throwable $exception) {
                    $stats['attendance_failed']++;
                    Log::warning('Adobe Connect auto attendance sync failed', [
                        'class_session_id' => $session->id,
                        'message' => $exception->getMessage(),
                    ]);
                }
            }
        }

        return $stats;
    }

    /**
     * @return Collection<int, ClassSession>
     */
    private function recentEligibleSessions(int $withinHours): Collection
    {
        $hours = max(1, $withinHours);
        $from = now()->subHours($hours);
        $to = now()->addHour();

        return ClassSession::query()
            ->with(['courseClass', 'adobeRooms', 'courseClass.classLevelAdobeRooms'])
            ->whereNotIn('status', [ClassSessionStatus::Cancelled, ClassSessionStatus::Postponed])
            ->whereHas('courseClass', fn ($q) => $q->where('online_platform', CourseClassOnlinePlatform::AdobeConnect))
            ->where(function ($query) use ($from, $to) {
                $query->whereBetween('scheduled_end_at', [$from, $to])
                    ->orWhere(function ($inner) use ($from, $to) {
                        $inner->whereNull('scheduled_end_at')
                            ->whereBetween('scheduled_start_at', [$from, $to]);
                    })
                    ->orWhere(function ($inner) use ($from, $to) {
                        $inner->whereBetween('actual_end_at', [$from, $to]);
                    });
            })
            ->orderBy('id')
            ->get()
            ->filter(fn (ClassSession $session) => AdobeConnectSessionSco::meetingScoId($session) !== null)
            ->values();
    }
}
