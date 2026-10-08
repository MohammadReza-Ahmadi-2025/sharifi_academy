<?php

namespace App\Support\Storage;

class PublicStorageUrl
{
    /**
     * مسیر عمومی فایل روی دیسک public (بدون دامنه) تا روی هر host:port لوکال درست کار کند.
     */
    public static function forPath(?string $path): string
    {
        if (blank($path)) {
            return '';
        }

        $path = ltrim((string) $path, '/');

        if (str_starts_with($path, 'storage/')) {
            return '/'.$path;
        }

        return '/storage/'.$path;
    }

    /**
     * آدرس‌های ذخیره‌شده با APP_URL اشتباه (مثلاً localhost بدون پورت) را به مسیر relative تبدیل می‌کند.
     */
    public static function normalize(?string $url): string
    {
        if (blank($url)) {
            return '';
        }

        $url = trim((string) $url);

        if (str_starts_with($url, '/storage/')) {
            return $url;
        }

        $path = parse_url($url, PHP_URL_PATH);

        if (is_string($path) && str_starts_with($path, '/storage/')) {
            return $path;
        }

        return $url;
    }
}
