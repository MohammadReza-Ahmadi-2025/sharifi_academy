<?php

namespace App\Modules\Courses\Support;

use App\Modules\Courses\Models\ClassSession;

class ClassSessionPresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function forList(ClassSession $session): array
    {
        return [
            'id' => $session->id,
            'session_number' => $session->session_number,
            'title' => $session->title,
            'status' => $session->status?->value,
            'status_label' => $session->status_label,
            'scheduled_start_at' => $session->scheduled_start_at?->toIso8601String(),
            'scheduled_end_at' => $session->scheduled_end_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function forForm(ClassSession $session): array
    {
        return [
            'id' => $session->id,
            'session_number' => $session->session_number,
            'title' => $session->title,
            'description' => $session->description,
            'status' => $session->status?->value,
            'scheduled_start_at' => $session->scheduled_start_at?->format('Y-m-d\TH:i'),
            'scheduled_end_at' => $session->scheduled_end_at?->format('Y-m-d\TH:i'),
            'actual_start_at' => $session->actual_start_at?->format('Y-m-d\TH:i'),
            'actual_end_at' => $session->actual_end_at?->format('Y-m-d\TH:i'),
            'timezone' => $session->timezone,
            'cancelled_at' => $session->cancelled_at?->format('Y-m-d\TH:i'),
            'cancellation_reason' => $session->cancellation_reason,
        ];
    }
}
