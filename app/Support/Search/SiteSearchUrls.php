<?php

namespace App\Support\Search;

use App\Modules\Blog\Models\Post;
use App\Modules\Catalog\Models\Brand;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Product;
use App\Modules\Courses\Models\Course;
use App\Support\SeoSlug;

final class SiteSearchUrls
{
    public static function course(Course $course): string
    {
        return '/courses/'.$course->publicSlug();
    }

    public static function product(Product $product): string
    {
        return '/books/'.$product->publicSlug();
    }

    public static function post(Post $post): string
    {
        return '/blog/'.$post->publicSlug();
    }

    public static function brand(Brand $brand): string
    {
        return '/brands/'.SeoSlug::forUrl((string) $brand->slug);
    }

    public static function category(Category $category): string
    {
        $slug = $category->publicSlug();

        if ($category->type === Category::TYPE_BLOG) {
            return '/blog/categories/'.$slug;
        }

        return '/categories/'.$slug;
    }
}
