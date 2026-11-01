<?php

use App\Modules\Settings\Services\SiteSettingsService;
use App\Support\SafeCallable;

if (! function_exists('site_setting')) {
    function site_setting(string $key, mixed $default = null): mixed
    {
        return SafeCallable::run(
            "site_setting:{$key}",
            fn () => app(SiteSettingsService::class)->get($key, $default),
            $default,
        );
    }
}
