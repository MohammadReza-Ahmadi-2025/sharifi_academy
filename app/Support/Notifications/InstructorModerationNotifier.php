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
use App\Support\SafeCallable;
use Illuminate\Support\Collection;

final class InstructorModerationNotifier
{
    public static function courseSubmittedForReview(Course $course): void
    {
        self::moderators()->each(
            fn (User $admin) => self::notifySafely(
                $admin,
                new CourseSubmittedForReviewNotification($course),
            ),
        );
    }

    public static function courseModerationResult(Course $course, bool $approved): void
    {
        $creator = $course->creator;
        if (! $creator instanceof User) {
            return;
        }

        self::notifySafely($creator, new CourseModerationResultNotification($course, $approved));
    }

    public static function resumeSubmittedForReview(User $instructor): void
    {
        self::moderators()->each(
            fn (User $admin) => self::notifySafely(
                $admin,
                new ResumeSubmittedForReviewNotification($instructor),
            ),
        );
    }

    public static function resumeModerationResult(User $instructor, bool $approved, ?string $note = null): void
    {
        self::notifySafely($instructor, new ResumeModerationResultNotification($approved, $note));
    }

    public static function enrollmentConfirmed(Enrollment $enrollment): void
    {
        $student = $enrollment->user;
        if (! $student instanceof User) {
            return;
        }

        $enrollment->loadMissing('courseClass.course');
        self::notifySafely($student, new EnrollmentConfirmedNotification($enrollment));
    }

    public static function enrollmentPendingForAdmin(Enrollment $enrollment): void
    {
        SafeCallable::run(
            'notify.enrollment_pending_recipients',
            function () use ($enrollment): void {
                User::query()
                    ->permission('enrollments-index')
                    ->get()
                    ->each(fn (User $admin) => self::notifySafely(
                        $admin,
                        new EnrollmentPendingAdminNotification($enrollment),
                    ));
            },
            null,
        );
    }

    /**
     * @return Collection<int, User>
     */
    private static function moderators(): Collection
    {
        return SafeCallable::run(
            'notify.moderators',
            fn () => User::query()
                ->permission('instructor-moderation-index')
                ->get(),
            collect(),
        );
    }

    private static function notifySafely(User $user, object $notification): void
    {
        SafeCallable::run(
            'notify.'.class_basename($notification),
            fn () => $user->notify($notification),
            null,
        );
    }
}
