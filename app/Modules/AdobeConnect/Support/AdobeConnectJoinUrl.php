<?php

namespace App\Modules\AdobeConnect\Support;

use App\Modules\AdobeConnect\Enums\AdobeRoomStatus;
use App\Modules\AdobeConnect\Models\AdobeRoom;
use App\Modules\Courses\Models\ClassSession;
use App\Modules\Courses\Models\CourseClass;

final class AdobeConnectJoinUrl
{
    public static function forSession(ClassSession $session, CourseClass $courseClass): ?string
    {
        $sessionRoom = $session->relationLoaded('adobeRooms')
            ? $session->adobeRooms->first(fn (AdobeRoom $room) => $room->status === AdobeRoomStatus::Active)
            : $session->adobeRooms()->active()->first();

        if ($sessionRoom instanceof AdobeRoom) {
            return self::forRoom($sessionRoom);
        }

        $classRooms = $courseClass->relationLoaded('classLevelAdobeRooms')
            ? $courseClass->classLevelAdobeRooms
            : null;

        if ($classRooms !== null) {
            $classRoom = $classRooms->first(fn (AdobeRoom $room) => $room->status === AdobeRoomStatus::Active);

            return $classRoom instanceof AdobeRoom ? self::forRoom($classRoom) : null;
        }

        $classRoom = $courseClass->classLevelAdobeRooms()->active()->first();

        return self::forRoom($classRoom);
    }

    public static function forRoom(?AdobeRoom $room): ?string
    {
        if ($room === null) {
            return null;
        }

        if (filled($room->meeting_url)) {
            return trim((string) $room->meeting_url);
        }

        $path = trim((string) $room->url_path);
        if ($path === '') {
            return null;
        }

        $config = app(AdobeConnectConfig::class);
        if ($config->baseUrl() === '') {
            return null;
        }

        return app(AdobeConnectUrlBuilder::class)->meetingJoinUrl($path);
    }
}
