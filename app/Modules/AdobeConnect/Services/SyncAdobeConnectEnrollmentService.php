<?php

namespace App\Modules\AdobeConnect\Services;

use App\Models\User;
use App\Modules\AdobeConnect\Enums\AdobeRoomStatus;
use App\Modules\AdobeConnect\Support\AdobeConnectConfig;
use App\Modules\AdobeConnect\Support\AdobeConnectJoinUrl;
use App\Modules\AdobeConnect\Support\AdobeConnectUserCredentials;
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

    public function sync(Enrollment $enrollment): void
    {
        if (! $enrollment->isConfirmed()) {
            return;
        }

        if (! $this->config->isApiConfigured()) {
            return;
        }

        $enrollment->loadMissing(['user', 'courseClass.course']);
        $user = $enrollment->user;
        $courseClass = $enrollment->courseClass;

        if ($user === null || $courseClass === null || ! $courseClass->usesAdobeConnect()) {
            return;
        }

        $credentials = AdobeConnectUserCredentials::forUser($user);
        if ($credentials === null) {
            return;
        }

        try {
            $principalId = $this->ensureAdobePrincipal($user, $credentials);
            if ($principalId !== null) {
                $this->grantClassMeetingAccess($courseClass, $principalId);
            }
        } catch (Throwable $exception) {
            Log::warning('Adobe Connect enrollment sync failed', [
                'enrollment_id' => $enrollment->id,
                'user_id' => $user->id,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function ensureAdobePrincipal(User $user, AdobeConnectUserCredentials $credentials): ?string
    {
        if (filled($user->adobe_connect_principal_id)) {
            try {
                $this->api->updateUserPassword((string) $user->adobe_connect_principal_id, $credentials->password);
            } catch (Throwable) {
                // principal may have been removed in Adobe; fall through to re-register
            }
        }

        $existingId = $this->api->findPrincipalIdByLogin($credentials->login);
        if ($existingId !== null) {
            $this->persistPrincipal($user, $existingId);

            return $existingId;
        }

        try {
            $registered = $this->api->registerUser(
                $credentials->login,
                $credentials->password,
                (string) ($user->name ?? ''),
                (string) ($user->family ?? ''),
            );
            $this->persistPrincipal($user, $registered['principal_id']);

            return $registered['principal_id'];
        } catch (Throwable $exception) {
            Log::warning('Adobe Connect user auto-registration failed', [
                'user_id' => $user->id,
                'login' => $credentials->login,
                'message' => $exception->getMessage(),
            ]);

            return null;
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
