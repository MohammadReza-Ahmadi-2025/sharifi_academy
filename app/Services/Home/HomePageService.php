<?php

namespace App\Services\Home;

use App\Modules\Catalog\Models\Product;
use App\Modules\Courses\Enums\CourseClassStatus;
use App\Modules\Courses\Models\Course;
use App\Modules\Home\Services\HomeCacheInvalidator;
use App\Modules\Home\Services\Resolvers\BannerResolver;
use App\Modules\Home\Services\Resolvers\SectionResolver;
use Illuminate\Support\Facades\Cache;

class HomePageService
{
    /**
     * @return array<string, mixed>
     */
    public function data(): array
    {
        return Cache::remember('site:home:payload', 600, function () {
            $sections = app(SectionResolver::class)->resolve();

            $payload = [
                'hero' => $this->hero(),
                'sections' => $sections,
                'banners' => app(BannerResolver::class)->resolve(),
            ];

            if ($sections === []) {
                $payload['courses'] = $this->courses();
                $payload['books'] = $this->books();
            } else {
                $payload['courses'] = [];
                $payload['books'] = [];
            }

            return $payload;
        });
    }

    public static function flush(): void
    {
        Cache::forget('site:home:payload');
        app(HomeCacheInvalidator::class)->forgetBanners();
        app(HomeCacheInvalidator::class)->forgetSectionsSkeleton();
    }

    /**
     * @return array<string, string|null>
     */
    private function hero(): array
    {
        return [
            'badge' => site_setting('home_hero_badge', 'آکادمی تخصصی مهندسی'),
            'title' => site_setting('home_hero_title', 'مهارت مهندسی را عملی یاد بگیرید'),
            'subtitle' => site_setting(
                'home_hero_subtitle',
                'دوره‌های کلاس‌محور با استاد، منابع کتابی مکمل، و پنل دانشجو برای ورود سریع به هر جلسه — همه در یک مسیر آموزشی منسجم.',
            ),
            'cta_primary' => site_setting('home_hero_cta_primary', 'مشاهده دوره‌ها'),
            'cta_secondary' => site_setting('home_hero_cta_secondary', 'ورود / ثبت‌نام'),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function courses(): array
    {
        $courses = Course::query()
            ->published()
            ->orderedForDisplay()
            ->with(['mainImage', 'courseClasses' => function ($query) {
                $query->whereIn('status', [CourseClassStatus::Upcoming, CourseClassStatus::Ongoing])
                    ->orderBy('price');
            }])
            ->limit(12)
            ->get();

        return $courses->map(function (Course $course) {
            $minPrice = $course->courseClasses->min('price');

            return [
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug,
                'description' => $course->short_description,
                'price' => is_numeric($minPrice) ? (int) $minPrice : 0,
                'image_url' => $course->mainImage?->publicUrl(),
                'is_featured' => (bool) $course->is_featured,
            ];
        })->values()->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function books(): array
    {
        return Product::query()
            ->active()
            ->visible()
            ->withCardPricing()
            ->with('mainImage')
            ->orderBy('sort')
            ->orderByDesc('id')
            ->limit(12)
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'title' => $product->title,
                'slug' => $product->slug,
                'description' => $product->short_description,
                'price' => (int) ($product->min_effective_price ?? 0),
                'image_url' => $product->mainImage?->publicUrl(),
            ])
            ->values()
            ->all();
    }
}
