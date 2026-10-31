<?php

namespace App\Modules\Assessments\Policies;

use App\Models\User;
use App\Modules\Assessments\Models\Quiz;
use App\Modules\Courses\Enums\EnrollmentStatus;
use App\Modules\Courses\Models\Enrollment;
use App\Modules\Courses\Support\CourseOwnership;

class QuizPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('quizzes-index') || $user->can('student-panel-access');
    }

    public function view(User $user, Quiz $quiz): bool
    {
        if ($user->can('quizzes-index') && CourseOwnership::canManage($user, $quiz->course)) {
            return true;
        }

        return $this->studentCanTake($user, $quiz);
    }

    public function create(User $user): bool
    {
        return $user->can('quizzes-create');
    }

    public function update(User $user, Quiz $quiz): bool
    {
        return $user->can('quizzes-update') && CourseOwnership::canManage($user, $quiz->course);
    }

    public function delete(User $user, Quiz $quiz): bool
    {
        return $user->can('quizzes-delete') && CourseOwnership::canManage($user, $quiz->course);
    }

    public function attempt(User $user, Quiz $quiz): bool
    {
        return $this->studentCanTake($user, $quiz);
    }

    private function studentCanTake(User $user, Quiz $quiz): bool
    {
        if (! $user->can('student-panel-access') || ! $quiz->is_published) {
            return false;
        }

        $classIds = $quiz->course?->courseClasses()->pluck('id') ?? collect();

        return Enrollment::query()
            ->whereIn('course_class_id', $classIds)
            ->where('user_id', $user->id)
            ->where('status', EnrollmentStatus::Confirmed)
            ->exists();
    }
}
