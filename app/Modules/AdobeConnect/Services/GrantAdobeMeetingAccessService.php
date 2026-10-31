<?php

namespace App\Modules\AdobeConnect\Services;

use App\Modules\AdobeConnect\Enums\AdobeRoomStatus;
use App\Modules\Courses\Models\CourseClass;
use Illuminate\Support\Facades\Log;
use Throwable;

class GrantAdobeMeetingAccessService
{
    public function __construct(
        private readonly AdobeConnectApi $api,
    ) {}

    /**
     * @param  'view'|'host'|'mini-host'  $permission
     */
    public function grantForClass(CourseClass $courseClass, string $principalId, string $permission = 'view'): void
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
                $this->api->grantMeetingPermission($scoId, $principalId, $permission);
            } catch (Throwable $exception) {
                Log::warning('Adobe Connect meeting permission failed', [
                    'sco_id' => $scoId,
                    'principal_id' => $principalId,
                    'permission' => $permission,
                    'message' => $exception->getMessage(),
                ]);
            }
        }
    }
}
