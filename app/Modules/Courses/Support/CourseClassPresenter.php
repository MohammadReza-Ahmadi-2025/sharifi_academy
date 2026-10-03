<?php

namespace App\Modules\Courses\Support;

use App\Modules\Courses\Models\CourseClass;

class CourseClassPresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function forList(CourseClass $courseClass): array
    {
        return [
            'id' => $courseClass->id,
            'name' => $courseClass->name,
            'code' => $courseClass->code,
            'status' => $courseClass->status?->value,
            'status_label' => $courseClass->status?->label(),
            'delivery_type' => $courseClass->delivery_type?->value,
            'delivery_type_label' => $courseClass->delivery_type_label,
            'price' => $courseClass->price,
            'capacity' => $courseClass->capacity,
            'is_public' => $courseClass->is_public,
            'sessions_count' => $courseClass->class_sessions_count ?? $courseClass->classSessions()->count(),
            'enrollments_count' => $courseClass->enrollments_count ?? $courseClass->enrollments()->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function forForm(CourseClass $courseClass): array
    {
        return [
            'id' => $courseClass->id,
            'name' => $courseClass->name,
            'code' => $courseClass->code,
            'status' => $courseClass->status?->value,
            'delivery_type' => $courseClass->delivery_type?->value,
            'capacity' => $courseClass->capacity,
            'price' => $courseClass->price,
            'registration_starts_at' => $courseClass->registration_starts_at?->format('Y-m-d\TH:i'),
            'registration_ends_at' => $courseClass->registration_ends_at?->format('Y-m-d\TH:i'),
            'starts_at' => $courseClass->starts_at?->format('Y-m-d\TH:i'),
            'ends_at' => $courseClass->ends_at?->format('Y-m-d\TH:i'),
            'location' => $courseClass->location,
            'description' => $courseClass->description,
            'is_public' => $courseClass->is_public,
            'instructors' => $courseClass->classInstructors->map(fn ($row) => [
                'user_id' => $row->user_id,
                'role' => $row->role?->value,
                'is_primary' => $row->is_primary,
                'share_percentage' => $row->share_percentage,
                'user_label' => $row->user?->name.' '.$row->user?->family,
            ])->values()->all(),
        ];
    }
}
