<?php

namespace App\Modules\Catalog\Support;

use App\Modules\Catalog\Models\Category;
use App\Support\HtmlExcerpt;
use App\Support\Storage\PublicStorageUrl;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CategorySeo
{
    /**
     * @return array<string, mixed>
     */
    public static function forCategoryShow(Category $category): array
    {
        $canonical = filled($category->canonical)
            ? (string) $category->canonical
            : url('/categories/'.$category->publicSlug());

        $image = $category->image?->publicUrl();
        $ogImage = $image !== '' ? PublicStorageUrl::normalize($image) : null;

        $metaDescription = filled($category->meta_description)
            ? (string) $category->meta_description
            : HtmlExcerpt::plainText($category->description, 160);

        $keywords = is_array($category->meta_keywords)
            ? implode(', ', $category->meta_keywords)
            : (string) ($category->meta_keywords ?? '');

        return [
            'title' => filled($category->meta_title) ? $category->meta_title : $category->title,
            'description' => $metaDescription,
            'keywords' => $keywords,
            'canonical' => $canonical,
            'og_image' => $ogImage,
            'og_type' => 'website',
        ];
    }

    /**
     * @param  LengthAwarePaginator<int, array<string, mixed>>|Collection<int, array<string, mixed>>  $items
     * @return array<string, mixed>
     */
    public static function itemListJsonLd(Category $category, $items, string $canonical): array
    {
        $collection = $items instanceof LengthAwarePaginator
            ? $items->getCollection()
            : collect($items);

        $itemUrl = match ($category->type) {
            Category::TYPE_COURSE => fn (array $row) => url('/courses/'.$row['slug']),
            Category::TYPE_PRODUCT => fn (array $row) => url('/books/'.$row['slug']),
            default => fn (array $row) => $canonical,
        };

        return [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => $category->title,
            'url' => $canonical,
            'numberOfItems' => $collection->count(),
            'itemListElement' => $collection->values()->map(
                fn (array $row, int $index) => [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'url' => $itemUrl($row),
                    'name' => $row['title'],
                ],
            )->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function breadcrumbJsonLd(Category $category): array
    {
        $showUrl = url('/categories/'.$category->publicSlug());

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'خانه',
                    'item' => url('/'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => $category->title,
                    'item' => $showUrl,
                ],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $seo
     * @return array<string, mixed>
     */
    public static function collectionPageJsonLd(array $seo, string $appName): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => $seo['title'],
            'description' => $seo['description'] ?? null,
            'url' => $seo['canonical'],
            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => $appName,
                'url' => url('/'),
            ],
        ];
    }
}
