<?php

namespace App\Support\Notifications\Messages;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ResumeSubmittedForReviewNotification extends Notification
{
    use Queueable;

    public function __construct(public User $submitter) {}

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
        $name = trim(($this->submitter->name ?? '').' '.($this->submitter->family ?? '')) ?: 'کاربر';
        $role = $this->submitter->hasRole('instructor')
            ? 'استاد'
            : ($this->submitter->hasRole('student') ? 'دانشجو' : 'کاربر');

        return [
            'title' => 'رزومه در انتظار تایید',
            'message' => sprintf('رزومه %s (%s) برای بررسی ارسال شده است.', $name, $role),
            'url' => '/admin/instructor-moderation',
        ];
    }
}
