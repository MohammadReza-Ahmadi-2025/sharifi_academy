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
     * @return array{linked: int, created: int, failed: int, skipped: int, errors: list<string>}
     */
    public function sync(CourseClass $courseClass): array
    {
        $stats = ['linked' => 0, 'created' => 0, 'failed' => 0, 'skipped' => 0, 'errors' => []];

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
            $stats[$outcome['status']]++;
            if ($outcome['error'] !== null) {
                $stats['errors'][] = $outcome['error'];
            }
        }

        return $stats;
    }

    /**
     * @return array{status: 'linked'|'created'|'failed'|'skipped', error: ?string}
     */
    public function syncUser(CourseClass $courseClass, User $user): array
    {
        if (! $courseClass->usesAdobeConnect() || ! $this->config->isApiConfigured()) {
            return ['status' => 'skipped', 'error' => null];
        }

        $credentials = AdobeConnectUserCredentials::forUser($user);
        if ($credentials === null) {
            return [
                'status' => 'failed',
                'error' => "استاد #{$user->id}: موبایل معتبر برای login ادوب ندارد.",
            ];
        }

        try {
            [$principalId, $created] = $this->principals->ensure($user, $credentials);
            $this->meetingAccess->grantForClass($courseClass, $principalId, 'host');

            return [
                'status' => $created ? 'created' : 'linked',
                'error' => null,
            ];
        } catch (Throwable $exception) {
            Log::warning('Adobe Connect instructor sync failed', [
                'course_class_id' => $courseClass->id,
                'user_id' => $user->id,
                'message' => $exception->getMessage(),
            ]);

            return [
                'status' => 'failed',
                'error' => "استاد {$credentials->login}: ".$exception->getMessage(),
            ];
        }
    }
}
