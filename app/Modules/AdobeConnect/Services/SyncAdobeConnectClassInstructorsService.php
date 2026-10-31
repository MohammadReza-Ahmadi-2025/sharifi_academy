<?php

namespace App\Modules\AdobeConnect\Services;

use App\Models\User;
use App\Modules\AdobeConnect\Support\AdobeConnectConfig;
use App\Modules\AdobeConnect\Support\AdobeConnectUserCredentials;
use App\Modules\Courses\Models\CourseClass;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncAdobeConnectClassInstructorsService
{
    public function __construct(
        private readonly AdobeConnectConfig $config,
        private readonly EnsureAdobeConnectPrincipalService $principals,
        private readonly GrantAdobeMeetingAccessService $meetingAccess,
    ) {}

    /**
     * @return array{linked: int, created: int, failed: int, skipped: int}
     */
    public function sync(CourseClass $courseClass): array
    {
        $stats = ['linked' => 0, 'created' => 0, 'failed' => 0, 'skipped' => 0];

        if (! $courseClass->usesAdobeConnect() || ! $this->config->isApiConfigured()) {
            return $stats;
        }

        $courseClass->loadMissing('classInstructors.user');

        foreach ($courseClass->classInstructors as $row) {
            $user = $row->user;
            if ($user === null) {
                $stats['skipped']++;

                continue;
            }

            $outcome = $this->syncUser($courseClass, $user);
            $stats[$outcome]++;
        }

        return $stats;
    }

    /**
     * @return 'linked'|'created'|'failed'|'skipped'
     */
    public function syncUser(CourseClass $courseClass, User $user): string
    {
        if (! $courseClass->usesAdobeConnect() || ! $this->config->isApiConfigured()) {
            return 'skipped';
        }

        $credentials = AdobeConnectUserCredentials::forUser($user);
        if ($credentials === null) {
            return 'skipped';
        }

        try {
            [$principalId, $created] = $this->principals->ensure($user, $credentials);
            if ($principalId === null) {
                return 'failed';
            }

            $this->meetingAccess->grantForClass($courseClass, $principalId, 'host');

            return $created ? 'created' : 'linked';
        } catch (Throwable $exception) {
            Log::warning('Adobe Connect instructor sync failed', [
                'course_class_id' => $courseClass->id,
                'user_id' => $user->id,
                'message' => $exception->getMessage(),
            ]);

            return 'failed';
        }
    }
}
