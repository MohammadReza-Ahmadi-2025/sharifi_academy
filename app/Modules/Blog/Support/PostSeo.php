<?php

namespace App\Modules\Blog\Support;

use App\Modules\Blog\Models\Post;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Support\CategorySeo;
use App\Modules\Settings\Services\SiteSettingsService;
use App\Support\HtmlExcerpt;
use App\Support\Storage\PublicStorageUrl;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PostSeo
{
    public static function blogCategoryCanonical(Category $category): string
    {
        if (filled($category->canonical)) {
            return (string) $category->canonical;
        }

        return url('/blog/categories/'.$category->publicSlug());
    }

    /**
     * @return array<string, mixed>
     */
    public static function metaForBlogCategory(Category $category): array
    {
        $seo = CategorySeo::forCategoryShow($category);
        $seo['canonical'] = self::blogCategoryCanonical($category);

        return $seo;
    }

    /**
     * @return array<string, mixed>
     */
    public static function blogCategoryBreadcrumbJsonLd(Category $category, SiteSettingsService $settings): array
    {
        $blogLabel = filled($settings->get('blogs_header_title'))
            ? (string) $settings->get('blogs_header_title')
            : 'وبلاگ';

        $categoryUrl = self::blogCategoryCanonical($category);

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
                    'name' => $blogLabel,
                    'item' => url('/blog'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => $category->title,
                    'item' => $categoryUrl,
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function blogCategoryWebPageJsonLd(array $seo, string $appName): array
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

    /**
     * @return array<string, mixed>
     */
    public static function indexMeta(SiteSettingsService $settings, string $appName): array
    {
        $canonical = url('/blog');
        $title = filled($settings->get('blogs_meta_title'))
            ? (string) $settings->get('blogs_meta_title')
            : "وبلاگ {$appName}";

        $description = filled($settings->get('blogs_meta_description'))
            ? (string) $settings->get('blogs_meta_description')
            : (filled($settings->get('site_description'))
                ? (string) $settings->get('site_description')
                : "مقالات و مطالب آموزشی {$appName}");

        $keywords = filled($settings->get('blogs_meta_keywords'))
            ? (string) $settings->get('blogs_meta_keywords')
            : (string) ($settings->get('seo_meta_keywords') ?? '');

        $ogImage = self::defaultOgImage($settings);

        return [
            'title' => $title,
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
    public static function metaForPost(Post $post, string $appName): array
    {
        $canonical = filled($post->canonical)
            ? (string) $post->canonical
            : url('/blog/'.$post->publicSlug());

        $imageUrl = $post->relationLoaded('featuredImage') && $post->featuredImage
            ? $post->featuredImage->publicUrl()
            : null;

        $description = filled($post->meta_description)
            ? (string) $post->meta_description
            : HtmlExcerpt::plainText($post->excerpt ?: $post->content, 160);

        $keywords = is_array($post->meta_keywords)
            ? implode('، ', $post->meta_keywords)
            : (string) ($post->meta_keywords ?? '');

        $ogImage = $imageUrl !== '' && $imageUrl !== null
            ? PublicStorageUrl::normalize($imageUrl)
            : null;

        return [
            'title' => filled($post->meta_title) ? $post->meta_title : $post->title,
            'description' => $description !== '' ? $description : null,
            'keywords' => $keywords !== '' ? $keywords : null,
            'canonical' => $canonical,
            'og_image' => $ogImage,
            'og_type' => 'article',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function indexBreadcrumbJsonLd(SiteSettingsService $settings): array
    {
        $blogLabel = filled($settings->get('blogs_header_title'))
            ? (string) $settings->get('blogs_header_title')
            : 'وبلاگ';

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
                    'name' => $blogLabel,
                    'item' => url('/blog'),
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function indexWebPageJsonLd(array $seo, string $appName): array
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

    /**
     * @param  LengthAwarePaginator<int, array<string, mixed>>|Collection<int, array<string, mixed>>  $posts
     * @return array<string, mixed>
     */
    public static function indexItemListJsonLd($posts, string $canonical): array
    {
        $collection = $posts instanceof LengthAwarePaginator
            ? $posts->getCollection()
            : collect($posts);

        return [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => 'مقالات وبلاگ',
            'url' => $canonical,
            'numberOfItems' => $collection->count(),
            'itemListElement' => $collection->values()->map(
                fn (array $row, int $index) => [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'url' => url('/blog/'.$row['slug']),
                    'name' => $row['title'],
                ],
            )->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function postBreadcrumbJsonLd(Post $post, SiteSettingsService $settings): array
    {
        $blogLabel = filled($settings->get('blogs_header_title'))
            ? (string) $settings->get('blogs_header_title')
            : 'وبلاگ';

        $postUrl = url('/blog/'.$post->publicSlug());
        $category = $post->relationLoaded('categories')
            ? $post->categories->first()
            : null;

        $items = [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'خانه',
                'item' => url('/'),
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => $blogLabel,
                'item' => url('/blog'),
            ],
        ];

        if ($category instanceof Category) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => $category->title,
                'item' => self::blogCategoryCanonical($category),
            ];
            $items[] = [
                '@type' => 'ListItem',
                'position' => 4,
                'name' => $post->title,
                'item' => $postUrl,
            ];
        } else {
            $items[] = [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => $post->title,
                'item' => $postUrl,
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function blogPostingJsonLd(Post $post, array $seo, string $appName): array
    {
        $imageUrl = $post->relationLoaded('featuredImage') && $post->featuredImage
            ? $post->featuredImage->publicUrl()
            : null;

        $authorName = $post->relationLoaded('author') && $post->author
            ? trim($post->author->name.' '.$post->author->family)
            : $appName;

        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $post->title,
            'description' => $seo['description'] ?? null,
            'url' => $seo['canonical'],
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $seo['canonical'],
            ],
            'datePublished' => $post->published_at?->toIso8601String(),
            'dateModified' => $post->updated_at?->toIso8601String(),
            'author' => [
                '@type' => 'Person',
                'name' => $authorName !== '' ? $authorName : $appName,
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => $appName,
                'url' => url('/'),
            ],
        ];

        if ($imageUrl !== '' && $imageUrl !== null) {
            $data['image'] = [PublicStorageUrl::normalize($imageUrl)];
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public static function postWebPageJsonLd(array $seo, string $appName): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
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

    private static function defaultOgImage(SiteSettingsService $settings): ?string
    {
        $og = $settings->get('seo_og_image');
        if (is_string($og) && $og !== '') {
            return PublicStorageUrl::normalize($og);
        }

        $logo = $settings->get('site_logo');
        if (is_string($logo) && $logo !== '') {
            return PublicStorageUrl::normalize($logo);
        }

        return null;
    }
}
