<?php

namespace App\Modules\AdobeConnect\Services;

use App\Modules\AdobeConnect\Support\AdobeConnectConfig;
use App\Modules\AdobeConnect\Support\AdobeConnectUserCredentials;
use App\Modules\AdobeConnect\Support\AdobeEnrollmentSyncResult;
use App\Modules\Courses\Enums\EnrollmentStatus;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Models\Enrollment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncAdobeConnectEnrollmentService
{
    public function __construct(
        private readonly AdobeConnectConfig $config,
        private readonly EnsureAdobeConnectPrincipalService $principals,
        private readonly GrantAdobeMeetingAccessService $meetingAccess,
    ) {}

    public function sync(Enrollment $enrollment): AdobeEnrollmentSyncResult
    {
        if (! $enrollment->isConfirmed()) {
            return AdobeEnrollmentSyncResult::skipped('');
        }

        if (! $this->config->isApiConfigured()) {
            return AdobeEnrollmentSyncResult::skipped('');
        }

        $enrollment->loadMissing(['user', 'courseClass.course']);
        $user = $enrollment->user;
        $courseClass = $enrollment->courseClass;

        if ($user === null || $courseClass === null || ! $courseClass->usesAdobeConnect()) {
            return AdobeEnrollmentSyncResult::skipped('');
        }

        $credentials = AdobeConnectUserCredentials::forUser($user);
        if ($credentials === null) {
            return AdobeEnrollmentSyncResult::skipped('');
        }

        try {
            [$principalId, $created] = $this->principals->ensure($user, $credentials);
            if ($principalId === null) {
                return AdobeEnrollmentSyncResult::failed($credentials->login);
            }

            $this->meetingAccess->grantForClass($courseClass, $principalId, 'view');

            return $created
                ? AdobeEnrollmentSyncResult::created($credentials->login)
                : AdobeEnrollmentSyncResult::linked($credentials->login);
        } catch (Throwable $exception) {
            Log::warning('Adobe Connect enrollment sync failed', [
                'enrollment_id' => $enrollment->id,
                'user_id' => $user->id,
                'message' => $exception->getMessage(),
            ]);

            return AdobeEnrollmentSyncResult::failed($credentials->login);
        }
    }

    /**
     * @return array{synced: int, linked: int, created: int, failed: int, skipped: int, messages: list<string>}
     */
    public function syncConfirmedForClass(CourseClass $courseClass, bool $onlyMissing = false): array
    {
        $stats = [
            'synced' => 0,
            'linked' => 0,
            'created' => 0,
            'failed' => 0,
            'skipped' => 0,
            'messages' => [],
        ];

        if (! $courseClass->usesAdobeConnect() || ! $this->config->isApiConfigured()) {
            return $stats;
        }

        /** @var Collection<int, Enrollment> $enrollments */
        $enrollments = Enrollment::query()
            ->where('course_class_id', $courseClass->id)
            ->where('status', EnrollmentStatus::Confirmed)
            ->with('user')
            ->orderBy('id')
            ->get();

        foreach ($enrollments as $enrollment) {
            if ($onlyMissing && filled($enrollment->user?->adobe_connect_principal_id)) {
                $stats['skipped']++;

                continue;
            }

            $result = $this->sync($enrollment);
            match ($result->status) {
                'linked' => $stats['linked']++,
                'created' => $stats['created']++,
                'failed' => $stats['failed']++,
                default => $stats['skipped']++,
            };

            if (in_array($result->status, ['linked', 'created', 'failed'], true)) {
                $stats['synced']++;
                if ($result->message !== '') {
                    $stats['messages'][] = $result->message;
                }
            }
        }

        return $stats;
    }
}
