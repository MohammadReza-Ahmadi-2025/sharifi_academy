<?php

namespace App\Modules\Courses\Support;

use App\Modules\Courses\Models\ClassSession;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait EnsuresNestedCourseAccess
{
    protected function courseOrFail(int|string $courseId): Course
    {
        return Course::query()->findOrFail($courseId);
    }

    protected function courseClassOrFail(Course $course, int|string $courseClassId): CourseClass
    {
        $courseClass = CourseClass::query()->findOrFail($courseClassId);

        if ($courseClass->course_id !== $course->id) {
            throw new NotFoundHttpException;
        }

        return $courseClass;
    }

    protected function classSessionOrFail(Course $course, CourseClass $courseClass, int|string $sessionId): ClassSession
    {
        $session = ClassSession::query()->findOrFail($sessionId);

        if ($session->course_class_id !== $courseClass->id || $courseClass->course_id !== $course->id) {
            throw new NotFoundHttpException;
        }

        return $session;
    }
}
