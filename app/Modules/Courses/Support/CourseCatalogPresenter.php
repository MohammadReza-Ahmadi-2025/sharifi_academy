<?php

namespace App\Modules\Courses\Support;

use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;

class CourseCatalogPresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function forCard(Course $course): array
    {
        $course->loadMissing(['mainImage', 'courseClasses' => fn ($q) => $q->public()]);

        $minPrice = $course->min_public_price;
        if (! is_numeric($minPrice) || (int) $minPrice <= 0) {
            $minPrice = null;
        }

        $comparePrice = null;
        if ($course->relationLoaded('courseClasses') && $course->courseClasses->isNotEmpty()) {
            /** @var CourseClass|null $cheapest */
            $cheapest = $course->courseClasses
                ->sortBy(fn (CourseClass $class) => CourseClassPricing::effectivePrice($class) ?: PHP_INT_MAX)
                ->first();

            if ($cheapest !== null) {
                $effective = CourseClassPricing::effectivePrice($cheapest);
                $list = CourseClassPricing::listPrice($cheapest);
                if ($effective > 0 && $list > $effective) {
                    $minPrice = $effective;
                    $comparePrice = $list;
                } elseif ($effective > 0 && $minPrice === null) {
                    $minPrice = $effective;
                }
            }
        }

        $imageUrl = $course->mainImage?->publicUrl();

        return [
            'id' => $course->id,
            'title' => $course->title,
            'slug' => $course->publicSlug(),
            'description' => $course->short_description,
            'level_label' => $course->level?->label(),
            'price_from' => $minPrice !== null ? (int) $minPrice : null,
            'compare_price' => $comparePrice,
            'image_url' => $imageUrl !== '' ? $imageUrl : null,
            'is_featured' => (bool) $course->is_featured,
        ];
    }
}
