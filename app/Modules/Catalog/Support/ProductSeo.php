<?php

namespace App\Modules\Catalog\Support;

use App\Modules\Catalog\Models\Product;
use App\Support\HtmlExcerpt;
use App\Support\Storage\PublicStorageUrl;

class ProductSeo
{
    /**
     * @return array<string, mixed>
     */
    public static function metaForProduct(Product $product): array
    {
        $imageUrl = $product->relationLoaded('mainImage') && $product->mainImage
            ? $product->mainImage->publicUrl()
            : null;

        $canonical = filled($product->canonical)
            ? (string) $product->canonical
            : url('/books/'.$product->publicSlug());

        $description = filled($product->meta_description)
            ? (string) $product->meta_description
            : ($product->short_description ?: HtmlExcerpt::plainText($product->description, 160));

        $keywords = is_array($product->meta_keywords)
            ? implode('، ', $product->meta_keywords)
            : (string) ($product->meta_keywords ?? '');

        $ogImage = $imageUrl !== '' && $imageUrl !== null
            ? PublicStorageUrl::normalize($imageUrl)
            : null;

        return [
            'title' => filled($product->meta_title) ? $product->meta_title : $product->title,
            'description' => $description !== '' ? $description : null,
            'keywords' => $keywords !== '' ? $keywords : null,
            'canonical' => $canonical,
            'og_image' => $ogImage,
            'og_type' => 'product',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function breadcrumbJsonLd(Product $product): array
    {
        $canonical = filled($product->canonical)
            ? (string) $product->canonical
            : url('/books/'.$product->publicSlug());

        $product->loadMissing('categories');
        $category = $product->leafCategory();

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
                'item' => url('/categories/'.$category->publicSlug()),
            ];
            $items[] = [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => $product->title,
                'item' => $canonical,
            ];
        } else {
            $items[] = [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => $product->title,
                'item' => $canonical,
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
    public static function productJsonLd(Product $product, string $appName): array
    {
        $canonical = filled($product->canonical)
            ? (string) $product->canonical
            : url('/books/'.$product->publicSlug());

        $product->loadMissing(['mainImage', 'brand']);

        $description = filled($product->meta_description)
            ? (string) $product->meta_description
            : ($product->short_description ?: HtmlExcerpt::plainText($product->description, 200));

        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->title,
            'description' => $description !== '' ? $description : null,
            'url' => $canonical,
            'sku' => $product->sku ?: null,
            'brand' => $product->brand ? [
                '@type' => 'Brand',
                'name' => $product->brand->title,
            ] : null,
        ];

        $imageUrl = $product->mainImage?->publicUrl();
        if ($imageUrl !== null && $imageUrl !== '') {
            $normalized = PublicStorageUrl::normalize($imageUrl);
            $data['image'] = str_starts_with($normalized, 'http') ? $normalized : url($normalized);
        }

        $price = is_numeric($product->min_effective_price) ? (int) $product->min_effective_price : 0;
        if ($price > 0) {
            $data['offers'] = [
                '@type' => 'Offer',
                'url' => $canonical,
                'priceCurrency' => 'IRR',
                'price' => $price,
                'availability' => ((int) ($product->available_stock ?? 0) > 0)
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
                'seller' => [
                    '@type' => 'Organization',
                    'name' => $appName,
                ],
            ];
        }

        return array_filter($data, fn ($value) => $value !== null && $value !== '');
    }

    /**
     * @return array<string, mixed>
     */
    public static function webPageJsonLd(Product $product, string $appName): array
    {
        $seo = self::metaForProduct($product);

        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => $seo['title'],
            'url' => $seo['canonical'],
            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => $appName,
                'url' => url('/'),
            ],
        ];
    }
}
