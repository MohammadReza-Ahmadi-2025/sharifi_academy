<?php

namespace App\Modules\Catalog\Support;

use App\Modules\Catalog\Models\Attribute;

class VariantAttributeCatalog
{
    /**
     * ویژگی‌های فعالِ واریانت برای پنل (رنگ، سایز، …) با مقادیرشان.
     *
     * @return list<array{
     *   id: int,
     *   name: string,
     *   slug: string,
     *   type: string,
     *   values: list<array{id: int, value: string, slug: string, code: string|null}>
     * }>
     */
    public static function forAdmin(): array
    {
        return Attribute::query()
            ->where('is_variant', true)
            ->where('is_active', true)
            ->with([
                'values' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('sort')
                    ->orderBy('id'),
            ])
            ->orderBy('sort')
            ->orderBy('id')
            ->get()
            ->map(fn (Attribute $attribute) => [
                'id' => $attribute->id,
                'name' => $attribute->name,
                'slug' => $attribute->slug,
                'type' => $attribute->type,
                'values' => $attribute->values
                    ->map(fn ($value) => [
                        'id' => $value->id,
                        'value' => $value->value,
                        'slug' => $value->slug,
                        'code' => $value->code,
                    ])
                    ->values()
                    ->all(),
            ])
            ->values()
            ->all();
    }
}
