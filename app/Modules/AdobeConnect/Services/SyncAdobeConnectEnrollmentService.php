<?php

namespace App\Modules\AdobeConnect\Services;

use App\Models\User;
use App\Modules\AdobeConnect\Enums\AdobeRoomStatus;
use App\Modules\AdobeConnect\Support\AdobeConnectConfig;
use App\Modules\AdobeConnect\Support\AdobeConnectUserCredentials;
use App\Modules\AdobeConnect\Support\AdobeEnrollmentSyncResult;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Models\Enrollment;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncAdobeConnectEnrollmentService
{
    public function __construct(
        private readonly AdobeConnectConfig $config,
        private readonly AdobeConnectApi $api,
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
            [$principalId, $created] = $this->ensureAdobePrincipal($user, $credentials);
            if ($principalId === null) {
                return AdobeEnrollmentSyncResult::failed($credentials->login);
            }

            $this->grantClassMeetingAccess($courseClass, $principalId);

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
     * @return array{0: ?string, 1: bool} principal id, was newly created via API
     */
    private function ensureAdobePrincipal(User $user, AdobeConnectUserCredentials $credentials): array
    {
        $firstName = trim((string) ($user->name ?? ''));
        $lastName = trim((string) ($user->family ?? ''));
        if ($firstName === '') {
            $firstName = 'Student';
        }
        if ($lastName === '') {
            $lastName = 'User';
        }

        $existingId = $this->api->findPrincipalIdByLogin($credentials->login);
        if ($existingId !== null) {
            $this->api->updateUserProfile(
                $existingId,
                $firstName,
                $lastName,
                $credentials->password,
            );
            $this->persistPrincipal($user, $existingId);

            return [$existingId, false];
        }

        if (filled($user->adobe_connect_principal_id)) {
            $storedId = (string) $user->adobe_connect_principal_id;
            try {
                $this->api->updateUserProfile(
                    $storedId,
                    $firstName,
                    $lastName,
                    $credentials->password,
                );
                $this->persistPrincipal($user, $storedId);

                return [$storedId, false];
            } catch (Throwable) {
                // fall through to create / re-find
            }
        }

        try {
            $registered = $this->api->registerUser(
                $credentials->login,
                $credentials->password,
                $firstName,
                $lastName,
            );
            $this->persistPrincipal($user, $registered['principal_id']);

            return [$registered['principal_id'], true];
        } catch (Throwable $exception) {
            Log::warning('Adobe Connect user auto-registration failed', [
                'user_id' => $user->id,
                'login' => $credentials->login,
                'message' => $exception->getMessage(),
            ]);

            $retryFind = $this->api->findPrincipalIdByLogin($credentials->login);
            if ($retryFind !== null) {
                $this->api->updateUserProfile(
                    $retryFind,
                    $firstName,
                    $lastName,
                    $credentials->password,
                );
                $this->persistPrincipal($user, $retryFind);

                return [$retryFind, false];
            }

            return [null, false];
        }
    }

    private function grantClassMeetingAccess(CourseClass $courseClass, string $principalId): void
    {
        $courseClass->loadMissing(['classLevelAdobeRooms', 'classSessions.adobeRooms']);

        $scoIds = [];

        $classRoom = $courseClass->classLevelAdobeRooms()
            ->where('status', AdobeRoomStatus::Active)
            ->first();
        if ($classRoom !== null) {
            $scoIds[] = (string) $classRoom->sco_id;
        }

        foreach ($courseClass->classSessions as $session) {
            $sessionRoom = $session->adobeRooms()->active()->first();
            if ($sessionRoom !== null) {
                $scoIds[] = (string) $sessionRoom->sco_id;
            }
        }

        foreach (array_unique(array_filter($scoIds)) as $scoId) {
            try {
                $this->api->grantMeetingViewAccess($scoId, $principalId);
            } catch (Throwable $exception) {
                Log::warning('Adobe Connect meeting permission failed', [
                    'sco_id' => $scoId,
                    'principal_id' => $principalId,
                    'message' => $exception->getMessage(),
                ]);
            }
        }
    }

    private function persistPrincipal(User $user, string $principalId): void
    {
        $user->forceFill([
            'adobe_connect_principal_id' => $principalId,
            'adobe_connect_synced_at' => now(),
        ])->save();
    }
}
