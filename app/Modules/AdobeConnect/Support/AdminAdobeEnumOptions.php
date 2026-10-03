<?php

namespace App\Modules\AdobeConnect\Support;

use App\Modules\AdobeConnect\Enums\AdobeRoomAccessType;
use App\Modules\AdobeConnect\Enums\AdobeRoomStatus;
use App\Modules\Courses\Support\AdminEnumOptions;

class AdminAdobeEnumOptions
{
    /**
     * @return list<array{value: string, label: string}>
     */
    public static function accessTypes(): array
    {
        return AdminEnumOptions::fromEnum(AdobeRoomAccessType::class);
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function roomStatuses(): array
    {
        return AdminEnumOptions::fromEnum(AdobeRoomStatus::class);
    }
}
