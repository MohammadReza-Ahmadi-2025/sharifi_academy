<?php

namespace App\Modules\Menu\Support;

use App\Modules\Menu\Enums\MenuItemType;
use App\Modules\Menu\Enums\MenuLocation;

class AdminMenuEnumOptions
{
    /**
     * @return list<array{value: string, label: string}>
     */
    public static function locations(): array
    {
        return self::mapToList(MenuLocation::options());
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function itemTypes(): array
    {
        return self::mapToList(MenuItemType::options());
    }

    /**
     * @param  array<string, string>  $map
     * @return list<array{value: string, label: string}>
     */
    private static function mapToList(array $map): array
    {
        return collect($map)
            ->map(fn (string $label, string $value) => ['value' => $value, 'label' => $label])
            ->values()
            ->all();
    }
}
