<?php

namespace App\Modules\Settings\Support;

use App\Modules\Settings\Models\SiteSetting;
use App\Support\Storage\PublicStorageUrl;

class SiteSettingPresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function forList(SiteSetting $setting): array
    {
        $value = $setting->castValue();

        return [
            'id' => $setting->id,
            'key' => $setting->key,
            'label' => SiteSettingDefinitions::label($setting->key),
            'group' => $setting->group,
            'group_label' => SiteSettingDefinitions::groupLabel((string) $setting->group),
            'type' => $setting->type,
            'value_preview' => self::preview($setting->type, $value),
            'autoload' => $setting->autoload,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function forForm(SiteSetting $setting): array
    {
        $value = $setting->castValue();

        return [
            'id' => $setting->id,
            'key' => $setting->key,
            'label' => SiteSettingDefinitions::label($setting->key),
            'group' => $setting->group,
            'group_label' => SiteSettingDefinitions::groupLabel((string) $setting->group),
            'type' => $setting->type,
            'autoload' => $setting->autoload,
            'value' => self::formValue($setting->type, $value),
            'image_url' => $setting->type === 'image' && is_string($value) && $value !== ''
                ? PublicStorageUrl::forPath($value)
                : null,
        ];
    }

    private static function preview(string $type, mixed $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        return match ($type) {
            'switch' => $value ? 'فعال' : 'غیرفعال',
            'image' => is_string($value) ? basename($value) : '—',
            'textarea' => mb_strlen((string) $value) > 60
                ? mb_substr((string) $value, 0, 60).'…'
                : (string) $value,
            default => (string) $value,
        };
    }

    private static function formValue(string $type, mixed $value): mixed
    {
        if ($type === 'switch') {
            return (bool) $value;
        }

        return $value;
    }
}
