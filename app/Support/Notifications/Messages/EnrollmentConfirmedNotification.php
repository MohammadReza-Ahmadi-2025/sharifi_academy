<?php

namespace App\Support\Notifications\Messages;

use App\Modules\Courses\Models\Enrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EnrollmentConfirmedNotification extends Notification
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
        $courseTitle = $this->enrollment->courseClass?->course?->title ?? 'دوره';

        return [
            'title' => 'ثبت‌نام قطعی شد',
            'message' => sprintf('ثبت‌نام شما در «%s» تایید شد. جلسات را در پنل دانشجو ببینید.', $courseTitle),
            'url' => '/student/dashboard',
        ];
    }
}
