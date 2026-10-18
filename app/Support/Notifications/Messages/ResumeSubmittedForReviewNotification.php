<?php

namespace App\Support\Notifications\Messages;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ResumeSubmittedForReviewNotification extends Notification
{
    use Queueable;

    public function __construct(public User $instructor) {}

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
        $name = trim(($this->instructor->name ?? '').' '.($this->instructor->family ?? '')) ?: 'استاد';

        return [
            'title' => 'رزومه در انتظار تایید',
            'message' => sprintf('رزومه %s برای بررسی ارسال شده است.', $name),
            'url' => '/admin/instructor-moderation',
        ];
    }
}
