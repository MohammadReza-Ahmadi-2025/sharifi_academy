<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\MarkNotificationReadRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function markRead(MarkNotificationReadRequest $request, string $notification): RedirectResponse
    {
        $row = $this->findOwnedNotification($request, $notification);

        if ($row->read_at === null) {
            $row->markAsRead();
        }

        return back();
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        $user->unreadNotifications->markAsRead();

        return back();
    }

    private function findOwnedNotification(Request $request, string $notificationId): DatabaseNotification
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        $row = $user->notifications()->whereKey($notificationId)->first();

        abort_if($row === null, 404);

        return $row;
    }
}
