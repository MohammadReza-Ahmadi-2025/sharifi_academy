<?php

namespace App\Modules\Courses\Support;

use App\Modules\Courses\Models\Course;
use App\Support\HtmlExcerpt;
use App\Support\Storage\PublicStorageUrl;

class CourseSeo
{
    /**
     * @return array<string, mixed>
     */
    public static function metaForCourse(Course $course): array
    {
        $imageUrl = $course->relationLoaded('mainImage') && $course->mainImage
            ? $course->mainImage->publicUrl()
            : null;

        $canonical = filled($course->canonical)
            ? (string) $course->canonical
            : url('/courses/'.$course->publicSlug());

        $description = filled($course->meta_description)
            ? (string) $course->meta_description
            : ($course->short_description ?: HtmlExcerpt::plainText($course->description, 160));

        $keywords = is_array($course->meta_keywords)
            ? implode('، ', $course->meta_keywords)
            : (string) ($course->meta_keywords ?? '');

        $ogImage = $imageUrl !== '' && $imageUrl !== null
            ? PublicStorageUrl::normalize($imageUrl)
            : null;

        return [
            'title' => filled($course->meta_title) ? $course->meta_title : $course->title,
            'description' => $description !== '' ? $description : null,
            'keywords' => $keywords !== '' ? $keywords : null,
            'canonical' => $canonical,
            'og_image' => $ogImage,
            'og_type' => 'website',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function breadcrumbJsonLd(Course $course): array
    {
        $courseUrl = url('/courses/'.$course->publicSlug());
        $category = $course->relationLoaded('categories')
            ? $course->categories->first()
            : null;

        $items = [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'خانه',
                'item' => url('/'),
            ],
        ];

        if ($category !== null) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => $category->title,
                'item' => url('/categories/'.$category->slug),
            ];
            $items[] = [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => $course->title,
                'item' => $courseUrl,
            ];
        } else {
            $items[] = [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => $course->title,
                'item' => $courseUrl,
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $instructors
     * @return array<string, mixed>
     */
    public static function courseJsonLd(
        Course $course,
        string $appName,
        ?int $priceFrom,
        array $instructors,
    ): array {
        $canonical = filled($course->canonical)
            ? (string) $course->canonical
            : url('/courses/'.$course->publicSlug());

        $imageUrl = $course->relationLoaded('mainImage') && $course->mainImage
            ? $course->mainImage->publicUrl()
            : null;

        $description = filled($course->meta_description)
            ? (string) $course->meta_description
            : ($course->short_description ?: HtmlExcerpt::plainText($course->description, 200));

        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'Course',
            'name' => $course->title,
            'description' => $description !== '' ? $description : null,
            'url' => $canonical,
            'provider' => [
                '@type' => 'Organization',
                'name' => $appName,
                'url' => url('/'),
            ],
        ];

        if ($imageUrl !== '' && $imageUrl !== null) {
            $data['image'] = PublicStorageUrl::normalize($imageUrl);
        }

        if ($course->code) {
            $data['courseCode'] = $course->code;
        }

        if ($course->level?->label()) {
            $data['educationalLevel'] = $course->level->label();
        }

        if ($instructors !== []) {
            $data['instructor'] = array_map(
                fn (array $row) => [
                    '@type' => 'Person',
                    'name' => $row['name'],
                ],
                $instructors,
            );
        }

        if ($priceFrom !== null && $priceFrom > 0) {
            $data['offers'] = [
                '@type' => 'Offer',
                'price' => $priceFrom,
                'priceCurrency' => 'IRR',
                'availability' => 'https://schema.org/InStock',
                'url' => $canonical,
            ];
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public static function webPageJsonLd(Course $course, string $appName): array
    {
        $canonical = filled($course->canonical)
            ? (string) $course->canonical
            : url('/courses/'.$course->publicSlug());

        $title = filled($course->meta_title) ? $course->meta_title : $course->title;

        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => $title,
            'url' => $canonical,
            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => $appName,
                'url' => url('/'),
            ],
        ];
    }
}
