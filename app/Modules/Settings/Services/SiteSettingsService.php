<?php

namespace App\Modules\Settings\Services;

use App\Modules\Settings\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;

class SiteSettingsService
{
    private const CACHE_KEY = 'site_settings_autoload';

    public function get(string $key, mixed $default = null): mixed
    {
        $autoload = $this->autoload();

        if (array_key_exists($key, $autoload)) {
            return $autoload[$key];
        }

        $setting = SiteSetting::query()->where('key', $key)->first();

        if ($setting === null) {
            return $default;
        }

        return $setting->castValue();
    }

    /**
     * @return array<string, mixed>
     */
    public function autoload(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return SiteSetting::query()
                ->where('autoload', true)
                ->get()
                ->mapWithKeys(fn (SiteSetting $setting) => [$setting->key => $setting->castValue()])
                ->all();
        });
    }

    public function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
