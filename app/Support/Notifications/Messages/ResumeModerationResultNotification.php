<?php

namespace App\Support\Notifications\Messages;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ResumeModerationResultNotification extends Notification
{
    use Queueable;

    public function __construct(
        public bool $approved,
        public ?string $note = null,
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
                'title' => 'رزومه تایید شد',
                'message' => 'رزومه شما تایید شد و در سایت عمومی نمایش داده می‌شود.',
                'url' => '/instructor/resume',
            ];
        }

        $message = 'رزومه شما تایید نشد. لطفاً اصلاح و دوباره ارسال کنید.';
        if (filled($this->note)) {
            $message .= ' توضیح ادمین: '.$this->note;
        }

        return [
            'title' => 'رزومه تایید نشد',
            'message' => $message,
            'url' => '/instructor/resume',
        ];
    }
}
