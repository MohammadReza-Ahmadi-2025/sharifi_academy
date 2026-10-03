<?php

use App\Modules\Catalog\Models\Attribute;
use App\Modules\Catalog\Models\AttributeValue;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $definitions = [
            [
                'name' => 'رنگ',
                'slug' => 'color',
                'type' => 'color',
                'is_variant' => true,
                'is_filterable' => true,
                'sort' => 10,
                'values' => ['مشکی', 'سفید', 'آبی', 'قرمز', 'سبز'],
            ],
            [
                'name' => 'سایز',
                'slug' => 'size',
                'type' => 'select',
                'is_variant' => true,
                'is_filterable' => true,
                'sort' => 20,
                'values' => ['XS', 'S', 'M', 'L', 'XL', 'XXL'],
            ],
            [
                'name' => 'جنس',
                'slug' => 'material',
                'type' => 'select',
                'is_variant' => false,
                'is_filterable' => true,
                'sort' => 30,
                'values' => ['چرم', 'پارچه', 'پلاستیک', 'فلز', 'چوب'],
            ],
            [
                'name' => 'زبان',
                'slug' => 'language',
                'type' => 'select',
                'is_variant' => false,
                'is_filterable' => true,
                'sort' => 40,
                'values' => ['فارسی', 'انگلیسی', 'عربی', 'دو‌زبانه'],
            ],
            [
                'name' => 'مدت (ماه)',
                'slug' => 'duration-months',
                'type' => 'number',
                'is_variant' => false,
                'is_filterable' => false,
                'sort' => 50,
                'values' => ['1', '3', '6', '12'],
            ],
        ];

        foreach ($definitions as $definition) {
            $attribute = Attribute::query()->firstOrCreate(
                ['slug' => $definition['slug']],
                [
                    'name' => $definition['name'],
                    'type' => $definition['type'],
                    'is_filterable' => $definition['is_filterable'],
                    'is_visible_on_product' => true,
                    'is_variant' => $definition['is_variant'],
                    'is_active' => true,
                    'sort' => $definition['sort'],
                ],
            );

            $sort = 0;
            foreach ($definition['values'] as $value) {
                $sort++;
                AttributeValue::query()->firstOrCreate(
                    [
                        'attribute_id' => $attribute->id,
                        'slug' => Str::slug($value) !== '' ? Str::slug($value) : 'v-'.$sort,
                    ],
                    [
                        'value' => $value,
                        'is_active' => true,
                        'sort' => $sort,
                    ],
                );
            }
        }
    }

    public function down(): void
    {
        $slugs = ['color', 'size', 'material', 'language', 'duration-months'];

        $attributes = Attribute::query()->whereIn('slug', $slugs)->get();

        foreach ($attributes as $attribute) {
            AttributeValue::query()->where('attribute_id', $attribute->id)->delete();
            $attribute->delete();
        }
    }
};
