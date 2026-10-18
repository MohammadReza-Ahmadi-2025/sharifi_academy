<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;

class MarkNotificationReadRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $notificationId = $this->route('notification');

        if ($user === null || ! is_string($notificationId) || $notificationId === '') {
            return false;
        }

        return $user->notifications()->whereKey($notificationId)->exists();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
