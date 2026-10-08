<?php

namespace App\Modules\Catalog\Support;

use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Models\ProductVariant;
use App\Modules\Files\Support\FilePresenter;

class ProductPresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function forList(Product $product): array
    {
        $product->loadMissing(['brand', 'variants']);

        return [
            'id' => $product->id,
            'title' => $product->title,
            'slug' => $product->slug,
            'sku' => $product->sku,
            'brand_title' => $product->brand?->title,
            'status' => (bool) $product->status,
            'is_visible' => (bool) $product->is_visible,
            'variants_count' => $product->variants->count(),
            'sort' => $product->sort,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function forForm(Product $product): array
    {
        $product->loadMissing(['categories', 'gallery']);

        return [
            'id' => $product->id,
            'title' => $product->title,
            'slug' => $product->slug,
            'sku' => $product->sku,
            'short_description' => $product->short_description,
            'description' => $product->description,
            'brand_id' => $product->brand_id,
            'model' => $product->model,
            'status' => (bool) $product->status,
            'is_visible' => (bool) $product->is_visible,
            'sort' => $product->sort,
            'category_ids' => $product->categories->pluck('id')->all(),
            'meta_title' => $product->meta_title ?? '',
            'meta_description' => $product->meta_description ?? '',
            'meta_keywords' => self::metaKeywordsForInput($product->meta_keywords),
            'canonical' => $product->canonical ?? '',
            'features' => $product->features ?? '',
            'gallery' => $product->gallery
                ->map(fn ($file) => FilePresenter::forUploader($file))
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    /**
     * @param  array<int, string>|null  $keywords
     */
    public static function metaKeywordsForInput(?array $keywords): string
    {
        if ($keywords === null || $keywords === []) {
            return '';
        }

        return implode('، ', $keywords);
    }

    public static function variantForAdmin(ProductVariant $variant): array
    {
        $variant->loadMissing(['currentPrice', 'inventory', 'attributeValues.attribute']);

        $price = $variant->currentPrice;

        return [
            'id' => $variant->id,
            'title' => $variant->title,
            'sku' => $variant->sku,
            'barcode' => $variant->barcode,
            'status' => (bool) $variant->status,
            'display_mode' => $variant->display_mode?->value ?? 'normal',
            'display_mode_label' => $variant->display_mode?->label(),
            'sort' => $variant->sort,
            'price' => $price?->price,
            'final_price' => $price?->final_price ?? $price?->price,
            'discount_type' => $price?->discount_type,
            'discount_value' => $price?->discount_value,
            'sale_start' => null,
            'sale_end' => null,
            'stock' => $variant->inventory?->stock ?? 0,
            'min_stock' => (int) ($variant->inventory?->min_stock ?? 0),
            'label' => $variant->label,
            'attributes_text' => $variant->attributes_text,
            'attribute_value_ids' => $variant->attributeValues->pluck('id')->values()->all(),
            'attributes' => $variant->attributeValues
                ->mapWithKeys(fn ($value) => [(string) $value->attribute_id => (string) $value->id])
                ->all(),
            'is_default' => $variant->attributeValues->isEmpty(),
        ];
    }
}
