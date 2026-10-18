<?php

namespace App\Modules\Courses\Support;

use App\Modules\Courses\Models\Course;
use App\Modules\Files\Support\FilePresenter;

class CoursePresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function forList(Course $course): array
    {
        $creator = $course->relationLoaded('creator') ? $course->creator : null;

        return [
            'id' => $course->id,
            'title' => $course->title,
            'slug' => $course->slug,
            'code' => $course->code,
            'status' => $course->status?->value,
            'status_label' => $course->status?->label(),
            'creator_name' => $creator
                ? trim(($creator->name ?? '').' '.($creator->family ?? ''))
                : null,
            'level' => $course->level?->value,
            'level_label' => $course->level?->label(),
            'is_featured' => $course->is_featured,
            'classes_count' => $course->course_classes_count ?? $course->courseClasses()->count(),
            'published_at' => $course->published_at?->toIso8601String(),
            'created_at' => $course->created_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function forForm(Course $course): array
    {
        $course->loadMissing('gallery');

        return [
            'id' => $course->id,
            'title' => $course->title,
            'slug' => $course->slug,
            'code' => $course->code,
            'short_description' => $course->short_description,
            'description' => $course->description,
            'meta_title' => $course->meta_title ?? '',
            'meta_description' => $course->meta_description ?? '',
            'meta_keywords' => $course->meta_keywords ?? '',
            'canonical' => $course->canonical ?? '',
            'level' => $course->level?->value,
            'duration_minutes' => $course->duration_minutes,
            'status' => $course->status?->value,
            'is_featured' => $course->is_featured,
            'display_order' => $course->display_order,
            'published_at' => $course->published_at?->format('Y-m-d'),
            'category_ids' => $course->categories->pluck('id')->values()->all(),
            'gallery' => $course->gallery
                ->map(fn ($file) => FilePresenter::forUploader($file))
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function forShow(Course $course): array
    {
        $creator = $course->relationLoaded('creator') ? $course->creator : null;

        return array_merge(self::forList($course), [
            'short_description' => $course->short_description,
            'description' => $course->description,
            'duration_minutes' => $course->duration_minutes,
            'display_order' => $course->display_order,
            'category_labels' => $course->categories->pluck('title')->values()->all(),
            'updated_at' => $course->updated_at?->toIso8601String(),
            'creator_label' => $creator
                ? trim(($creator->name ?? '').' '.($creator->family ?? ''))
                : null,
        ]);
    }
}
