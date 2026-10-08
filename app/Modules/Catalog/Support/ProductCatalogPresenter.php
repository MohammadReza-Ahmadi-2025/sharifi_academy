<?php

namespace App\Modules\Catalog\Support;

use App\Modules\Catalog\Models\Product;

class ProductCatalogPresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function forCard(Product $product): array
    {
        $product->loadMissing(['mainImage', 'brand']);

        $imageUrl = $product->mainImage?->publicUrl();
        $price = $product->min_effective_price;

        return [
            'id' => $product->id,
            'title' => $product->title,
            'slug' => $product->slug,
            'description' => $product->short_description,
            'brand_title' => $product->brand?->title,
            'price' => is_numeric($price) && (int) $price > 0 ? (int) $price : null,
            'compare_price' => $product->card_compare_price > 0 ? (int) $product->card_compare_price : null,
            'image_url' => $imageUrl !== '' ? $imageUrl : null,
        ];
    }
}
