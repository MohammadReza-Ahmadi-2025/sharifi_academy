<?php

namespace App\Modules\Courses\Support;

use App\Modules\Courses\Enums\SessionAttendanceStatus;

class AdminSessionAttendanceOptions
{
    /**
     * @return list<array{value: string, label: string}>
     */
    public static function statuses(): array
    {
        return collect(SessionAttendanceStatus::cases())
            ->map(fn (SessionAttendanceStatus $status) => [
                'value' => $status->value,
                'label' => $status->label(),
            ])
            ->values()
            ->all();
    }
}
