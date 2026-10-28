<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Adobe Connect XML API
    |--------------------------------------------------------------------------
    | مستندات: https://helpx.adobe.com/adobe-connect/webservices/getting-started-connect-web-services.html
    |
    | ADOBE_CONNECT_API_TOKEN = رمز عبور حساب سرویس (یا توکن API همان اکانت در Connect Hosted).
    */

    'base_url' => env('ADOBE_CONNECT_BASE_URL'),

    'login' => env('ADOBE_CONNECT_LOGIN'),

    'api_token' => env('ADOBE_CONNECT_API_TOKEN'),

    'session_cache_key' => 'adobe_connect.breeze_session',

    'session_ttl_minutes' => (int) env('ADOBE_CONNECT_SESSION_TTL', 50),

    'http_timeout_seconds' => (int) env('ADOBE_CONNECT_HTTP_TIMEOUT', 30),

    /*
    | پوشهٔ My Meetings در Adobe — اگر خالی باشد از sco-shortcuts (user-meetings) خوانده می‌شود.
    */
    'meetings_folder_id' => env('ADOBE_CONNECT_MEETINGS_FOLDER_ID'),

    /** ساخت خودکار اتاق Adobe هنگام ایجاد/ویرایش کلاس آنلاین */
    /** @var list<string> پوشه‌های محتمل برای ساخت کاربر (principal-update) */
    'users_folder_ids' => array_values(array_filter(array_map(
        static fn (string $id): string => trim($id),
        explode(',', (string) env('ADOBE_CONNECT_USERS_FOLDER_IDS', '')),
    ))),

];
