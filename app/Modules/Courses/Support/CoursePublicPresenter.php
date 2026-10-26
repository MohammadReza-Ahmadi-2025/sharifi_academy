<?php

namespace App\Modules\Courses\Support;

use App\Models\User;
use App\Modules\Comments\Support\PublicCommentThreads;
use App\Modules\Courses\Enums\CourseClassDeliveryType;
use App\Modules\Courses\Enums\CourseClassOnlinePlatform;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use Illuminate\Support\Collection;

class CoursePublicPresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function forPage(Course $course, ?User $viewer = null): array
    {
        $course->loadMissing([
            'mainImage',
            'gallery',
            'categories',
            'courseClasses' => fn ($query) => $query
                ->public()
                ->orderBy('starts_at')
                ->with(['instructors']),
        ]);

        $publicClasses = $course->courseClasses;
        $prices = $publicClasses->pluck('price')->filter(fn ($price) => is_numeric($price) && $price > 0);
        $priceFrom = $prices->isNotEmpty() ? (int) $prices->min() : null;

        $imageUrl = $course->mainImage?->publicUrl();
        $canonical = filled($course->canonical)
            ? (string) $course->canonical
            : url('/courses/'.$course->publicSlug());

        $instructors = self::uniqueInstructors($publicClasses);
        $comments = PublicCommentThreads::approvedFor($course);

        return [
            'id' => $course->id,
            'title' => $course->title,
            'slug' => $course->publicSlug(),
            'short_description' => $course->short_description,
            'description' => $course->description,
            'level' => $course->level?->value,
            'level_label' => $course->level?->label(),
            'duration_minutes' => $course->duration_minutes,
            'duration_label' => self::formatDuration($course->duration_minutes),
            'is_featured' => $course->is_featured,
            'published_at' => $course->published_at?->toIso8601String(),
            'categories' => $course->categories->map(fn ($category) => [
                'id' => $category->id,
                'title' => $category->title,
                'slug' => $category->slug,
            ])->values()->all(),
            'image_url' => $imageUrl !== '' ? $imageUrl : null,
            'gallery' => $course->gallery
                ->map(fn ($file) => [
                    'id' => $file->id,
                    'url' => $file->publicUrl(),
                    'alt' => $course->title,
                ])
                ->values()
                ->all(),
            'price_from' => $priceFrom,
            'classes' => $publicClasses->map(function (CourseClass $class) {
                $schedule = CourseClassPublicSchedule::forClass($class);

                $deliveryType = $class->delivery_type;
                $showOnlinePlatform = in_array($deliveryType, [
                    CourseClassDeliveryType::Online,
                    CourseClassDeliveryType::Hybrid,
                ], true);

                return [
                    'id' => $class->id,
                    'name' => $class->name,
                    'delivery_type_label' => $class->delivery_type_label,
                    'online_platform_label' => $showOnlinePlatform
                        ? ($class->online_platform?->label() ?? CourseClassOnlinePlatform::AdobeConnect->label())
                        : null,
                    'price' => is_numeric($class->price) ? (int) $class->price : null,
                    'location' => $class->location,
                    'schedule_status' => $schedule['status'],
                    'schedule_status_label' => $schedule['status_label'],
                    'schedule_range_label' => $schedule['range_label'],
                    'starts_at' => $schedule['starts_at'],
                    'ends_at' => $schedule['ends_at'],
                ];
            })->values()->all(),
            'instructors' => $instructors,
            'seo' => CourseSeo::metaForCourse($course),
            'comments' => $comments['items'],
            'comments_summary' => $comments['summary'],
            'enrollment' => CourseEnrollmentUiPresenter::forCourse($course, $viewer),
        ];
    }

    /**
     * @param  Collection<int, CourseClass>  $classes
     * @return list<array<string, mixed>>
     */
    private static function uniqueInstructors(Collection $classes): array
    {
        $seen = [];
        $list = [];

        foreach ($classes as $class) {
            foreach ($class->instructors as $instructor) {
                if (isset($seen[$instructor->id])) {
                    continue;
                }

                $seen[$instructor->id] = true;
                $list[] = [
                    'id' => $instructor->id,
                    'name' => trim(($instructor->name ?? '').' '.($instructor->family ?? '')),
                ];
            }
        }

        return $list;
    }

    public static function formatDuration(?int $minutes): ?string
    {
        if ($minutes === null || $minutes <= 0) {
            return null;
        }

        if ($minutes < 60) {
            return $minutes.' دقیقه';
        }

        $hours = intdiv($minutes, 60);
        $rest = $minutes % 60;

        if ($rest === 0) {
            return $hours.' ساعت';
        }

        return $hours.' ساعت و '.$rest.' دقیقه';
    }

    public static function ensurePublished(Course $course): void
    {
        abort_unless($course->status === CourseStatus::Published, 404);
        abort_if($course->trashed(), 404);
    }
}
