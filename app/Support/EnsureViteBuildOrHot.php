<?php

namespace App\Support;

/**
 * اگر Vite خاموش باشد ولی public/hot مانده باشد، Laravel هنوز حالت dev را فعال می‌کند
 * و مرورگر/درخواست‌ها گیر می‌کنند. فایل stale را حذف می‌کنیم تا از build استفاده شود.
 */
final class EnsureViteBuildOrHot
{
    public static function reconcile(): void
    {
        $hotPath = public_path('hot');

        if (! is_file($hotPath)) {
            return;
        }

        $url = trim((string) @file_get_contents($hotPath));

        if ($url !== '' && self::devServerReachable($url)) {
            return;
        }

        @unlink($hotPath);
    }

    private static function devServerReachable(string $url): bool
    {
        $parts = parse_url($url);

        if ($parts === false) {
            return false;
        }

        $host = $parts['host'] ?? '127.0.0.1';
        if ($host === '[::1]' || $host === '::1') {
            $host = '127.0.0.1';
        }

        $port = (int) ($parts['port'] ?? 5173);
        $errno = 0;
        $errstr = '';
        $socket = @fsockopen($host, $port, $errno, $errstr, 0.25);

        if ($socket === false) {
            return false;
        }

        fclose($socket);

        return true;
    }
}
