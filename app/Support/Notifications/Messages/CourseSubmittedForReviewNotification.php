<?php

namespace App\Support\Notifications\Messages;

use App\Modules\Courses\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CourseSubmittedForReviewNotification extends Notification
{
    use Queueable;

    public function __construct(public Course $course) {}

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
        return [
            'title' => 'دوره در انتظار تایید',
            'message' => sprintf('دوره «%s» برای انتشار ارسال شده است.', $this->course->title),
            'url' => '/admin/instructor-moderation',
        ];
    }
}
