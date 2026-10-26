<?php

namespace App\Modules\Courses\Support;

use App\Modules\AdobeConnect\Support\AdobeConnectJoinUrl;
use App\Modules\Courses\Models\ClassSession;
use App\Modules\Courses\Models\CourseClass;

final class ClassSessionJoinUrl
{
    public static function externalTarget(ClassSession $session, CourseClass $courseClass): ?string
    {
        if ($courseClass->usesVideoLink()) {
            $url = trim((string) $session->video_url);

            return $url !== '' ? $url : null;
        }

        return AdobeConnectJoinUrl::forSession($session, $courseClass);
    }

    public static function joinLabel(CourseClass $courseClass): string
    {
        return $courseClass->usesVideoLink()
            ? 'تماشای ویدیو'
            : 'ورود به Adobe Connect';
    }
}
