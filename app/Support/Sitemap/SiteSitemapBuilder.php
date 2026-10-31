<?php

namespace App\Support\Sitemap;

use App\Models\User;
use App\Modules\Blog\Models\Post;
use App\Modules\Catalog\Models\Brand;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Product;
use App\Modules\Courses\Models\Course;
use App\Modules\Pages\Models\Page;
use App\Modules\Profile\Enums\ResumeModerationStatus;
use App\Support\Search\SiteSearchUrls;
use Illuminate\Support\Collection;

final class SiteSitemapBuilder
{
    /**
     * @return list<SitemapUrl>
     */
    public function urls(): array
    {
        return $this->collect()->all();
    }

    /**
     * @return Collection<int, SitemapUrl>
     */
    public function collect(): Collection
    {
        return collect()
            ->merge($this->staticUrls())
            ->merge($this->courseUrls())
            ->merge($this->productUrls())
            ->merge($this->brandUrls())
            ->merge($this->categoryUrls())
            ->merge($this->blogUrls())
            ->merge($this->pageUrls())
            ->merge($this->instructorUrls())
            ->values();
    }

    /**
     * @return list<SitemapUrl>
     */
    private function staticUrls(): array
    {
        return [
            new SitemapUrl(route('home'), now(), 'daily', '1.0'),
            new SitemapUrl(route('blog.index'), now(), 'daily', '0.8'),
        ];
    }

    /**
     * @return Collection<int, SitemapUrl>
     */
    private function courseUrls(): Collection
    {
        return Course::query()
            ->published()
            ->orderBy('id')
            ->get(['id', 'slug', 'updated_at', 'published_at'])
            ->map(fn (Course $course) => new SitemapUrl(
                url(SiteSearchUrls::course($course)),
                $course->updated_at ?? $course->published_at,
                'weekly',
                '0.9',
            ));
    }

    /**
     * @return Collection<int, SitemapUrl>
     */
    private function productUrls(): Collection
    {
        return Product::query()
            ->active()
            ->visible()
            ->orderBy('id')
            ->get(['id', 'slug', 'updated_at'])
            ->map(fn (Product $product) => new SitemapUrl(
                url(SiteSearchUrls::product($product)),
                $product->updated_at,
                'weekly',
                '0.8',
            ));
    }

    /**
     * @return Collection<int, SitemapUrl>
     */
    private function brandUrls(): Collection
    {
        return Brand::query()
            ->where('status', true)
            ->orderBy('id')
            ->get(['id', 'slug', 'updated_at'])
            ->map(fn (Brand $brand) => new SitemapUrl(
                url(SiteSearchUrls::brand($brand)),
                $brand->updated_at,
                'weekly',
                '0.6',
            ));
    }

    /**
     * @return Collection<int, SitemapUrl>
     */
    private function categoryUrls(): Collection
    {
        return Category::query()
            ->active()
            ->whereIn('type', [Category::TYPE_COURSE, Category::TYPE_PRODUCT, Category::TYPE_BLOG])
            ->orderBy('id')
            ->get(['id', 'slug', 'type', 'updated_at'])
            ->map(fn (Category $category) => new SitemapUrl(
                url(SiteSearchUrls::category($category)),
                $category->updated_at,
                'weekly',
                '0.7',
            ));
    }

    /**
     * @return Collection<int, SitemapUrl>
     */
    private function blogUrls(): Collection
    {
        return Post::query()
            ->published()
            ->orderBy('id')
            ->get(['id', 'slug', 'updated_at', 'published_at'])
            ->map(fn (Post $post) => new SitemapUrl(
                url(SiteSearchUrls::post($post)),
                $post->updated_at ?? $post->published_at,
                'weekly',
                '0.8',
            ));
    }

    /**
     * @return Collection<int, SitemapUrl>
     */
    private function pageUrls(): Collection
    {
        return Page::query()
            ->active()
            ->orderBy('id')
            ->get(['id', 'slug', 'updated_at'])
            ->map(fn (Page $page) => new SitemapUrl(
                route('pages.show', $page),
                $page->updated_at,
                'monthly',
                '0.6',
            ));
    }

    /**
     * @return Collection<int, SitemapUrl>
     */
    private function instructorUrls(): Collection
    {
        return User::query()
            ->role('instructor')
            ->where('status', 1)
            ->whereHas('profile', fn ($query) => $query->where(
                'resume_moderation_status',
                ResumeModerationStatus::Approved->value,
            ))
            ->orderBy('id')
            ->get(['id', 'updated_at'])
            ->map(fn (User $user) => new SitemapUrl(
                route('instructors.show', $user),
                $user->updated_at,
                'monthly',
                '0.5',
            ));
    }
}
