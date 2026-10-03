<?php

namespace App\Modules\Settings\Support;

class SiteSettingDefinitions
{
    /**
     * @return array<string, array{type: string, group: string, label: string, autoload: bool, default: mixed}>
     */
    public static function all(): array
    {
        return config('site_settings.definitions', []);
    }

    public static function label(string $key): string
    {
        return self::all()[$key]['label'] ?? $key;
    }

    public static function groupLabel(string $group): string
    {
        return config("site_settings.groups.{$group}") ?? $group;
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function groupOptions(): array
    {
        $groups = config('site_settings.groups', []);

        return collect($groups)
            ->map(fn (string $label, string $value) => ['value' => $value, 'label' => $label])
            ->values()
            ->all();
    }
}
