<?php

namespace App\Modules\Courses\Support;

use App\Modules\Courses\Models\Course;

class CourseCatalogPresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function forCard(Course $course): array
    {
        $course->loadMissing(['mainImage']);

        $minPrice = $course->min_public_price;
        if (! is_numeric($minPrice) || (int) $minPrice <= 0) {
            $minPrice = null;
        }

        $imageUrl = $course->mainImage?->publicUrl();

        return [
            'id' => $course->id,
            'title' => $course->title,
            'slug' => $course->slug,
            'description' => $course->short_description,
            'level_label' => $course->level?->label(),
            'price_from' => $minPrice !== null ? (int) $minPrice : null,
            'image_url' => $imageUrl !== '' ? $imageUrl : null,
            'is_featured' => (bool) $course->is_featured,
        ];
    }
}
