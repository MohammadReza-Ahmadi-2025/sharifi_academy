<?php

namespace App\Modules\AdobeConnect\Console;

use App\Modules\AdobeConnect\Models\AdobeRoom;
use App\Modules\AdobeConnect\Services\SyncAdobeConnectClassInstructorsService;
use App\Modules\AdobeConnect\Services\SyncAdobeConnectEnrollmentService;
use App\Modules\AdobeConnect\Services\SyncAdobeRoomFromApiService;
use App\Modules\AdobeConnect\Services\SyncAdobeSessionAttendanceService;
use App\Modules\AdobeConnect\Services\SyncAdobeSessionRecordingsService;
use App\Modules\AdobeConnect\Support\AdobeConnectConfig;
use App\Modules\AdobeConnect\Support\AdobeConnectSessionSco;
use App\Modules\Courses\Enums\CourseClassOnlinePlatform;
use App\Modules\Courses\Models\ClassSession;
use App\Modules\Courses\Models\CourseClass;
use Illuminate\Console\Command;

class SyncAdobeConnectCommand extends Command
{
    protected $signature = 'adobe-connect:sync
                            {--rooms : Sync meeting url/path for all Adobe rooms}
                            {--recordings : Import recordings for sessions that have Adobe rooms}
                            {--enrollments : Sync confirmed enrollments to Adobe users + room access}
                            {--instructors : Sync class instructors to Adobe as meeting hosts}
                            {--attendance : Sync session attendance from Adobe meeting reports}
                            {--class= : Limit enrollments/instructors/attendance sync to a course_class id}
                            {--session= : Limit attendance sync to a class_session id}
                            {--only-missing : For enrollments, skip users already linked to Adobe}';

    protected $description = 'Sync Adobe Connect rooms, recordings, enrollments, instructors, and attendance via XML API';

    public function handle(
        AdobeConnectConfig $config,
        SyncAdobeRoomFromApiService $roomSync,
        SyncAdobeSessionRecordingsService $recordingSync,
        SyncAdobeConnectEnrollmentService $enrollmentSync,
        SyncAdobeConnectClassInstructorsService $instructorSync,
        SyncAdobeSessionAttendanceService $attendanceSync,
    ): int {
        if (! $config->isApiConfigured()) {
            $this->error('Configure ADOBE_CONNECT_BASE_URL, ADOBE_CONNECT_LOGIN, and ADOBE_CONNECT_API_TOKEN in .env');

            return self::FAILURE;
        }

        $rooms = (bool) $this->option('rooms');
        $recordings = (bool) $this->option('recordings');
        $enrollments = (bool) $this->option('enrollments');
        $instructors = (bool) $this->option('instructors');
        $attendance = (bool) $this->option('attendance');

        if (! $rooms && ! $recordings && ! $enrollments && ! $instructors && ! $attendance) {
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

        if ($enrollments || $instructors) {
            $classQuery = CourseClass::query()
                ->where('online_platform', CourseClassOnlinePlatform::AdobeConnect);

            $classId = $this->option('class');
            if (filled($classId)) {
                $classQuery->whereKey((int) $classId);
            }

            $classes = $classQuery->orderBy('id')->get();
            $onlyMissing = (bool) $this->option('only-missing');

            foreach ($classes as $courseClass) {
                if ($enrollments) {
                    $stats = $enrollmentSync->syncConfirmedForClass($courseClass, $onlyMissing);
                    $this->line(sprintf(
                        'Class #%d enrollments: linked=%d created=%d failed=%d skipped=%d',
                        $courseClass->id,
                        $stats['linked'],
                        $stats['created'],
                        $stats['failed'],
                        $stats['skipped'],
                    ));
                }

                if ($instructors) {
                    $stats = $instructorSync->sync($courseClass);
                    $this->line(sprintf(
                        'Class #%d instructors: linked=%d created=%d failed=%d skipped=%d',
                        $courseClass->id,
                        $stats['linked'],
                        $stats['created'],
                        $stats['failed'],
                        $stats['skipped'],
                    ));
                }
            }
        }

        if ($attendance) {
            $sessionQuery = ClassSession::query()
                ->with(['courseClass', 'adobeRooms', 'courseClass.classLevelAdobeRooms'])
                ->whereHas('courseClass', fn ($q) => $q->where('online_platform', CourseClassOnlinePlatform::AdobeConnect));

            $sessionId = $this->option('session');
            $classId = $this->option('class');
            if (filled($sessionId)) {
                $sessionQuery->whereKey((int) $sessionId);
            }
            if (filled($classId)) {
                $sessionQuery->where('course_class_id', (int) $classId);
            }

            foreach ($sessionQuery->orderBy('id')->get() as $session) {
                if (AdobeConnectSessionSco::meetingScoId($session) === null) {
                    $this->warn("Session #{$session->id}: no Adobe room sco-id");

                    continue;
                }

                try {
                    $stats = $attendanceSync->sync($session, true);
                    $this->line(sprintf(
                        'Session #%d attendance: present=%d absent=%d skipped=%d',
                        $session->id,
                        $stats['present'],
                        $stats['absent'],
                        $stats['skipped'],
                    ));
                } catch (\Throwable $e) {
                    $this->warn("Session #{$session->id}: {$e->getMessage()}");
                }
            }
        }

        $this->info('Adobe Connect sync finished.');

        return self::SUCCESS;
    }
}
