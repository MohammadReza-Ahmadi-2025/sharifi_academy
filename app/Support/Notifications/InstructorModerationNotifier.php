<?php

namespace App\Support\Notifications;

use App\Models\User;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\Enrollment;
use App\Support\Notifications\Messages\CourseModerationResultNotification;
use App\Support\Notifications\Messages\CourseSubmittedForReviewNotification;
use App\Support\Notifications\Messages\EnrollmentConfirmedNotification;
use App\Support\Notifications\Messages\EnrollmentPendingAdminNotification;
use App\Support\Notifications\Messages\ResumeModerationResultNotification;
use App\Support\Notifications\Messages\ResumeSubmittedForReviewNotification;
use Illuminate\Support\Collection;

final class InstructorModerationNotifier
{
    public static function courseSubmittedForReview(Course $course): void
    {
        self::moderators()->each(
            fn (User $admin) => $admin->notify(new CourseSubmittedForReviewNotification($course)),
        );
    }

    public static function courseModerationResult(Course $course, bool $approved): void
    {
        $creator = $course->creator;
        if (! $creator instanceof User) {
            return;
        }

        $creator->notify(new CourseModerationResultNotification($course, $approved));
    }

    public static function resumeSubmittedForReview(User $instructor): void
    {
        self::moderators()->each(
            fn (User $admin) => $admin->notify(new ResumeSubmittedForReviewNotification($instructor)),
        );
    }

    public static function resumeModerationResult(User $instructor, bool $approved, ?string $note = null): void
    {
        $instructor->notify(new ResumeModerationResultNotification($approved, $note));
    }

    public static function enrollmentConfirmed(Enrollment $enrollment): void
    {
        $student = $enrollment->user;
        if (! $student instanceof User) {
            return;
        }

        $enrollment->loadMissing('courseClass.course');
        $student->notify(new EnrollmentConfirmedNotification($enrollment));
    }

    public static function enrollmentPendingForAdmin(Enrollment $enrollment): void
    {
        User::query()
            ->permission('enrollments-index')
            ->get()
            ->each(fn (User $admin) => $admin->notify(new EnrollmentPendingAdminNotification($enrollment)));
    }

    /**
     * @return Collection<int, User>
     */
    private static function moderators(): Collection
    {
        return User::query()
            ->permission('instructor-moderation-index')
            ->get();
    }
}
