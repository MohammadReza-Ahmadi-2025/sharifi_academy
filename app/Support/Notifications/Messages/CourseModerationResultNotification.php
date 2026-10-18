<?php

namespace App\Support\Notifications\Messages;

use App\Modules\Courses\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CourseModerationResultNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Course $course,
        public bool $approved,
    ) {}

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
        if ($this->approved) {
            return [
                'title' => 'دوره تایید شد',
                'message' => sprintf('دوره «%s» توسط ادمین منتشر شد.', $this->course->title),
                'url' => '/instructor/courses',
            ];
        }

        return [
            'title' => 'دوره نیاز به اصلاح دارد',
            'message' => sprintf('دوره «%s» تایید نشد و به پیش‌نویس برگشت.', $this->course->title),
            'url' => '/instructor/courses',
        ];
    }
}
