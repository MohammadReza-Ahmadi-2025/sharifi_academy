<?php

namespace App\Modules\Settings\Support;

use App\Modules\Settings\Services\SiteSettingsService;
use App\Support\Storage\PublicStorageUrl;

class SiteSettingsInertiaPayload
{
    /**
     * تنظیمات عمومی برای فرانت (با URL تصاویر و کلیدهای لازم برای head).
     *
     * @return array<string, mixed>
     */
    public static function build(SiteSettingsService $service): array
    {
        $definitions = SiteSettingDefinitions::all();
        $payload = [];

        foreach ($service->autoload() as $key => $value) {
            $type = $definitions[$key]['type'] ?? 'text';
            $payload[$key] = self::normalizeValue($type, $value);
        }

        foreach (['google_analytics_id', 'google_search_console'] as $integrationKey) {
            if (! array_key_exists($integrationKey, $payload)) {
                $type = $definitions[$integrationKey]['type'] ?? 'text';
                $payload[$integrationKey] = self::normalizeValue(
                    $type,
                    $service->get($integrationKey),
                );
            }
        }

        return $payload;
    }

    private static function normalizeValue(string $type, mixed $value): mixed
    {
        if ($type === 'image') {
            if (! is_string($value) || $value === '') {
                return null;
            }

            return PublicStorageUrl::forPath($value);
        }

        return $value;
    }
}
