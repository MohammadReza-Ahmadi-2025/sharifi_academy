<?php

namespace App\Modules\Catalog\Support;

use App\Modules\Catalog\Models\Brand;
use App\Support\HtmlExcerpt;
use App\Support\Search\SiteSearchUrls;
use Illuminate\Support\Collection;

class BrandSeo
{
    /**
     * @return array<string, mixed>
     */
    public static function metaForBrand(Brand $brand): array
    {
        $canonical = filled($brand->canonical)
            ? (string) $brand->canonical
            : url(SiteSearchUrls::brand($brand));

        $description = filled($brand->meta_description)
            ? (string) $brand->meta_description
            : HtmlExcerpt::plainText($brand->description, 160);

        $keywords = is_array($brand->meta_keywords)
            ? implode('، ', $brand->meta_keywords)
            : (string) ($brand->meta_keywords ?? '');

        return [
            'title' => filled($brand->meta_title) ? $brand->meta_title : $brand->title,
            'description' => $description !== '' ? $description : null,
            'keywords' => $keywords !== '' ? $keywords : null,
            'canonical' => $canonical,
            'og_image' => null,
            'og_type' => 'website',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function breadcrumbJsonLd(Brand $brand): array
    {
        $canonical = filled($brand->canonical)
            ? (string) $brand->canonical
            : url(SiteSearchUrls::brand($brand));

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
                    'name' => $brand->title,
                    'item' => $canonical,
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function brandJsonLd(Brand $brand): array
    {
        $seo = self::metaForBrand($brand);

        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'Brand',
            'name' => $brand->title,
            'url' => $seo['canonical'],
            'description' => $seo['description'],
        ];

        if (filled($brand->website)) {
            $data['sameAs'] = [(string) $brand->website];
        }

        return array_filter($data, fn ($value) => $value !== null && $value !== '');
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $products
     * @return array<string, mixed>
     */
    public static function itemListJsonLd(Brand $brand, Collection $products): array
    {
        $seo = self::metaForBrand($brand);

        return [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => 'محصولات '.$brand->title,
            'url' => $seo['canonical'],
            'numberOfItems' => $products->count(),
            'itemListElement' => $products->values()->map(
                fn (array $row, int $index) => [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'url' => url('/books/'.$row['slug']),
                    'name' => $row['title'],
                ],
            )->all(),
        ];
    }

    /**
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
