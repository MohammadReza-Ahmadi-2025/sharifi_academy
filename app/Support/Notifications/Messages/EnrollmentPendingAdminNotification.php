<?php

namespace App\Support\Notifications\Messages;

use App\Modules\Courses\Models\Enrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EnrollmentPendingAdminNotification extends Notification
{
    use Queueable;

    public function __construct(public Enrollment $enrollment) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, string>
     */
    public function toArray(object $notifiable): array
    {
        $enrollment = $this->enrollment;
        $enrollment->loadMissing(['user', 'courseClass.course']);

        $studentName = trim(($enrollment->user?->name ?? '').' '.($enrollment->user?->family ?? '')) ?: 'دانشجو';
        $courseTitle = $enrollment->courseClass?->course?->title ?? 'دوره';
        $className = $enrollment->courseClass?->name ?? 'کلاس';

        return [
            'title' => 'درخواست ثبت‌نام جدید',
            'message' => sprintf('%s برای «%s» (%s) درخواست ثبت‌نام داده است.', $studentName, $courseTitle, $className),
            'url' => '/admin/course-enrollments?status=pending',
        ];
    }
}
