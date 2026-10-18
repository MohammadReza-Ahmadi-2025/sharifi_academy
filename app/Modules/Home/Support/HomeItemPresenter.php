<?php

namespace App\Modules\Home\Support;

use App\Models\User;
use App\Modules\Blog\Models\Post;
use App\Modules\Catalog\Models\Brand;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Product;
use App\Modules\Courses\Enums\CourseClassStatus;
use App\Modules\Courses\Models\Course;
use App\Modules\Home\Models\Banner;
use App\Modules\Users\Support\UserAvatarMedia;
use Illuminate\Support\Collection;

class HomeItemPresenter
{
    /**
     * @param  Collection<int, mixed>|iterable<int, mixed>  $items
     * @return list<array<string, mixed>>
     */
    public static function present(iterable $items, string $contentType): array
    {
        return collect($items)
            ->map(fn ($item) => self::presentOne($item, $contentType))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function presentOne(mixed $item, string $contentType): ?array
    {
        if ($item instanceof Product) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'slug' => $item->slug,
                'description' => $item->short_description,
                'price' => (int) ($item->min_effective_price ?? 0),
                'image_url' => $item->mainImage?->publicUrl(),
            ];
        }

        if ($item instanceof Course) {
            $minPrice = $item->relationLoaded('courseClasses')
                ? $item->courseClasses->min('price')
                : null;

            return [
                'id' => $item->id,
                'title' => $item->title,
                'slug' => $item->slug,
                'description' => $item->short_description,
                'price' => is_numeric($minPrice) ? (int) $minPrice : 0,
                'image_url' => $item->mainImage?->publicUrl(),
                'is_featured' => (bool) $item->is_featured,
            ];
        }

        if ($item instanceof Post) {
            $intro = filled($item->excerpt) ? $item->excerpt : $item->content;

            return [
                'id' => $item->id,
                'title' => $item->title,
                'slug' => $item->slug,
                'description' => $intro,
                'description_format' => 'html',
                'image_url' => $item->featuredImage?->publicUrl(),
                'published_at' => $item->published_at?->toIso8601String(),
            ];
        }

        if ($item instanceof Brand) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'slug' => $item->slug,
                'image_url' => null,
            ];
        }

        if ($item instanceof Category) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'slug' => $item->slug,
                'image_url' => $item->image?->publicUrl(),
            ];
        }

        if ($item instanceof Banner) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'subtitle' => $item->subtitle,
                'button_text' => $item->button_text,
                'button_link' => $item->button_link,
                'image_url' => $item->file?->publicUrl(),
            ];
        }

        if ($item instanceof User) {
            $fullName = trim(($item->name ?? '').' '.($item->family ?? '')) ?: 'استاد';
            $avatar = UserAvatarMedia::payload($item);

            return [
                'id' => $item->id,
                'title' => $fullName,
                'subtitle' => $item->profile?->headline,
                'image_url' => $avatar['url'] ?? null,
                'resume_url' => route('instructors.resume', $item),
                'profile_url' => route('instructors.show', $item),
            ];
        }

        return null;
    }

    /**
     * @return list<string>
     */
    public static function courseEagerRelations(): array
    {
        return [
            'mainImage',
            'courseClasses' => function ($query) {
                $query->whereIn('status', [CourseClassStatus::Upcoming, CourseClassStatus::Ongoing])
                    ->orderBy('price');
            },
        ];
    }
}
