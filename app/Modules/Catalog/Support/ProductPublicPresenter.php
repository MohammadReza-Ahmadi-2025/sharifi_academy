<?php

namespace App\Modules\Catalog\Support;

use App\Modules\Catalog\Models\Product;
use App\Support\Storage\PublicStorageUrl;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductPublicPresenter
{
    public static function ensureVisible(Product $product): void
    {
        if (! $product->status || ! $product->is_visible) {
            throw new NotFoundHttpException;
        }
    }

    /**
     * @return array<string, mixed>
     */
    public static function forPage(Product $product): array
    {
        $product->loadMissing(['mainImage', 'brand', 'categories']);

        $imageUrl = $product->mainImage?->publicUrl();
        $price = is_numeric($product->min_effective_price) ? (int) $product->min_effective_price : null;

        return [
            'id' => $product->id,
            'title' => $product->title,
            'slug' => $product->publicSlug(),
            'short_description' => $product->short_description,
            'description' => $product->description,
            'sku' => $product->sku,
            'brand' => $product->brand ? [
                'id' => $product->brand->id,
                'title' => $product->brand->title,
                'slug' => $product->brand->slug,
                'url' => '/brands/'.$product->brand->slug,
            ] : null,
            'categories' => $product->categories->map(fn ($category) => [
                'id' => $category->id,
                'title' => $category->title,
                'slug' => $category->publicSlug(),
            ])->values()->all(),
            'price' => $price !== null && $price > 0 ? $price : null,
            'compare_price' => $product->card_compare_price > 0 ? (int) $product->card_compare_price : null,
            'image_url' => $imageUrl !== '' && $imageUrl !== null
                ? PublicStorageUrl::normalize($imageUrl)
                : null,
            'in_stock' => ((int) ($product->available_stock ?? 0)) > 0,
            'seo' => ProductSeo::metaForProduct($product),
        ];
    }
}
