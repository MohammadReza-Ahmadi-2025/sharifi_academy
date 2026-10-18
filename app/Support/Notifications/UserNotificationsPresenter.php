<?php

namespace App\Support\Notifications;

use App\Models\User;
use App\Support\PersianDate;
use Illuminate\Notifications\DatabaseNotification;

class UserNotificationsPresenter
{
    /**
     * @return array{
     *     unread_count: int,
     *     items: list<array{
     *         id: string,
     *         title: string,
     *         message: string,
     *         created_at: string|null,
     *         read: bool,
     *         url: string|null
     *     }>
     * }|null
     */
    public static function forUser(?User $user): ?array
    {
        if ($user === null) {
            return null;
        }

        $items = $user->notifications()
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn (DatabaseNotification $notification): array => self::present($notification))
            ->values()
            ->all();

        return [
            'unread_count' => $user->unreadNotifications()->count(),
            'items' => $items,
        ];
    }

    /**
     * @return array{
     *     id: string,
     *     title: string,
     *     message: string,
     *     created_at: string|null,
     *     read: bool
     * }
     */
    private static function present(DatabaseNotification $notification): array
    {
        /** @var array<string, mixed> $data */
        $data = $notification->data;

        $title = self::stringValue($data['title'] ?? $data['subject'] ?? null);
        $message = self::stringValue($data['message'] ?? $data['body'] ?? $data['text'] ?? null);

        if ($title === '' && $message !== '') {
            $title = 'اعلان';
        }

        if ($message === '' && $title !== '') {
            $message = $title;
        }

        if ($title === '' && $message === '') {
            $title = 'اعلان سیستم';
            $message = 'جزئیات این اعلان در دسترس نیست.';
        }

        $url = self::stringValue($data['url'] ?? null);

        return [
            'id' => (string) $notification->id,
            'title' => $title,
            'message' => $message,
            'created_at' => PersianDate::formatDateTime($notification->created_at),
            'read' => $notification->read_at !== null,
            'url' => $url !== '' ? $url : null,
        ];
    }

    private static function stringValue(mixed $value): string
    {
        if (! is_string($value)) {
            return '';
        }

        return trim($value);
    }
}
