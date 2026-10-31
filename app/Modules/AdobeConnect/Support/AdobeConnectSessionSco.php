<?php

namespace App\Modules\AdobeConnect\Support;

use App\Modules\AdobeConnect\Enums\AdobeRoomStatus;
use App\Modules\AdobeConnect\Models\AdobeRoom;
use App\Modules\Courses\Models\ClassSession;

final class AdobeConnectSessionSco
{
    public static function meetingScoId(ClassSession $session): ?string
    {
        $session->loadMissing(['adobeRooms', 'courseClass.classLevelAdobeRooms']);

        $sessionRoom = $session->relationLoaded('adobeRooms')
            ? $session->adobeRooms->first(fn (AdobeRoom $room) => $room->status === AdobeRoomStatus::Active)
            : $session->adobeRooms()->active()->first();

        if ($sessionRoom instanceof AdobeRoom && filled($sessionRoom->sco_id)) {
            return (string) $sessionRoom->sco_id;
        }

        $courseClass = $session->courseClass;
        if ($courseClass === null) {
            return null;
        }

        $classRoom = $courseClass->relationLoaded('classLevelAdobeRooms')
            ? $courseClass->classLevelAdobeRooms->first(fn (AdobeRoom $room) => $room->status === AdobeRoomStatus::Active)
            : $courseClass->classLevelAdobeRooms()->active()->first();

        if ($classRoom instanceof AdobeRoom && filled($classRoom->sco_id)) {
            return (string) $classRoom->sco_id;
        }

        return null;
    }
}
