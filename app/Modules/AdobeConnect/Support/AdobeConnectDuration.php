<?php

namespace App\Modules\AdobeConnect\Support;

final class AdobeConnectDuration
{
    public static function toSeconds(string $duration): int
    {
        $duration = trim($duration);
        if ($duration === '') {
            return 0;
        }

        if (preg_match('/^(\d+):(\d{2}):(\d{2})(?:\.(\d+))?$/', $duration, $matches) === 1) {
            $hours = (int) $matches[1];
            $minutes = (int) $matches[2];
            $seconds = (int) $matches[3];

            return ($hours * 3600) + ($minutes * 60) + $seconds;
        }

        return 0;
    }
}
