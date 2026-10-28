<?php

namespace App\Modules\Pages\Support;

use App\Modules\Pages\Models\Page;
use App\Modules\Settings\Services\SiteSettingsService;
use App\Support\HtmlExcerpt;

class PageSeo
{
    /**
     * @return array{title: string, description: string|null, keywords: string|null, canonical: string}
     */
    public static function metaForPage(Page $page, SiteSettingsService $settings, string $appName): array
    {
        $title = filled($page->meta_title) ? (string) $page->meta_title : $page->title;
        $description = filled($page->meta_description)
            ? (string) $page->meta_description
            : HtmlExcerpt::plainText((string) $page->content, 160);

        $keywords = null;
        if (is_array($page->meta_keywords) && $page->meta_keywords !== []) {
            $keywords = implode(', ', $page->meta_keywords);
        }

        $canonical = filled($page->canonical)
            ? (string) $page->canonical
            : url('/pages/'.$page->publicSlug());

        return [
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords,
            'canonical' => $canonical,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function breadcrumbJsonLd(Page $page): array
    {
        $pageUrl = filled($page->canonical)
            ? (string) $page->canonical
            : url('/pages/'.$page->publicSlug());

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
                    'name' => $page->title,
                    'item' => $pageUrl,
                ],
            ],
        ];
    }

    /**
     * @param  array{title: string, description: string|null, canonical: string}  $seo
     * @return array<string, mixed>
     */
    public static function webPageJsonLd(array $seo, Page $page): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => $page->title,
            'description' => $seo['description'],
            'url' => $seo['canonical'],
        ];
    }
}
