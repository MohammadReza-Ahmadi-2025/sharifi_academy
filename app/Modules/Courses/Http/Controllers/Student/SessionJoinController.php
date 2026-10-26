<?php

namespace App\Modules\Courses\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Modules\Courses\Models\ClassSession;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Support\ClassSessionJoinUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SessionJoinController extends Controller
{
    public function __invoke(Request $request, CourseClass $courseClass, ClassSession $classSession): RedirectResponse
    {
        abort_unless($classSession->course_class_id === $courseClass->id, 404);

        $this->authorize('joinSession', $classSession);

        $classSession->loadMissing(['adobeRooms']);
        $courseClass->loadMissing(['classLevelAdobeRooms']);

        $target = ClassSessionJoinUrl::externalTarget($classSession, $courseClass);

        abort_if($target === null || $target === '', 404, 'لینک کلاس آنلاین برای این جلسه تنظیم نشده است.');

        return redirect()->away($target);
    }
}
