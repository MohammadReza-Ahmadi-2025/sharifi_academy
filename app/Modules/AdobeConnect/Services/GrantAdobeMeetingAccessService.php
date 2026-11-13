<?php

namespace App\Modules\AdobeConnect\Services;

use App\Modules\AdobeConnect\Enums\AdobeRoomStatus;
use App\Modules\Courses\Models\CourseClass;
use RuntimeException;

class GrantAdobeMeetingAccessService
{
    public function __construct(
        private readonly AdobeConnectApi $api,
    ) {}

    /**
     * @param  'view'|'host'|'mini-host'  $permission
     *
     * @throws RuntimeException when no active meeting room exists or Adobe rejects the permission
     */
    public function grantForClass(CourseClass $courseClass, string $principalId, string $permission = 'view'): void
    {
        $scoIds = $this->activeMeetingScoIds($courseClass);

        if ($scoIds === []) {
            throw new RuntimeException(
                'اتاق Adobe Connect فعال برای این کلاس پیدا نشد؛ اول اتاق را بسازید یا همگام کنید.',
            );
        }

        foreach ($scoIds as $scoId) {
            $this->api->grantMeetingPermission($scoId, $principalId, $permission);
        }
    }

    /**
     * Remove principal access from all active class/session meetings.
     *
     * @throws RuntimeException when Adobe rejects the remove permission
     */
    public function revokeForClass(CourseClass $courseClass, string $principalId): void
    {
        $scoIds = $this->activeMeetingScoIds($courseClass);

        if ($scoIds === []) {
            return;
        }

        foreach ($scoIds as $scoId) {
            $this->api->revokeMeetingAccess($scoId, $principalId);
        }
    }

    /**
     * @return list<string>
     */
    private function activeMeetingScoIds(CourseClass $courseClass): array
    {
        $courseClass->unsetRelation('classLevelAdobeRooms');
        $courseClass->unsetRelation('classSessions');
        $courseClass->load(['classLevelAdobeRooms', 'classSessions.adobeRooms']);

        $scoIds = [];

        $classRoom = $courseClass->classLevelAdobeRooms
            ->first(fn ($room) => $room->status === AdobeRoomStatus::Active);
        if ($classRoom !== null) {
            $scoIds[] = (string) $classRoom->sco_id;
        }

        foreach ($courseClass->classSessions as $session) {
            $sessionRoom = $session->adobeRooms
                ->first(fn ($room) => $room->status === AdobeRoomStatus::Active);
            if ($sessionRoom !== null) {
                $scoIds[] = (string) $sessionRoom->sco_id;
            }
        }

        return array_values(array_unique(array_filter($scoIds)));
    }
}
