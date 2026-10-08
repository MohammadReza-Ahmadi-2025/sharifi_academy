<?php

namespace App\Modules\Stories\Support;

use App\Modules\Stories\Enums\StoryStatus;
use App\Modules\Stories\Enums\StoryType;
use App\Modules\Stories\Enums\StoryVisibility;

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

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function typeOptions(): array
    {
        return array_map(
            fn (StoryType $type) => [
                'value' => $type->value,
                'label' => $type->label(),
            ],
            StoryType::cases(),
        );
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function visibilityOptions(): array
    {
        return array_map(
            fn (StoryVisibility $visibility) => [
                'value' => $visibility->value,
                'label' => $visibility->label(),
            ],
            StoryVisibility::cases(),
        );
    }
}
