<?php

namespace App\Modules\Stories\Support;

use App\Modules\Stories\Enums\StoryStatus;

class AdminStoryEnumOptions
{
    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function statusOptions(): array
    {
        return array_map(
            fn (StoryStatus $status) => [
                'value' => $status->value,
                'label' => $status->label(),
            ],
            StoryStatus::cases(),
        );
    }
}
