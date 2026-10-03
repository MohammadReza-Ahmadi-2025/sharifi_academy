<?php

use App\Modules\Settings\Services\SiteSettingsService;

if (! function_exists('site_setting')) {
    function site_setting(string $key, mixed $default = null): mixed
    {
        return app(SiteSettingsService::class)->get($key, $default);
    }
}
