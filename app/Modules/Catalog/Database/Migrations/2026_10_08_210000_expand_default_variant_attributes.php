<?php

use App\Modules\Catalog\Models\Attribute;
use App\Modules\Catalog\Models\AttributeValue;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Attribute::query()
            ->where('slug', 'material')
            ->update(['is_variant' => true]);

        $definitions = [
            'color' => [
                ['value' => 'مشکی', 'code' => '#111827'],
                ['value' => 'سفید', 'code' => '#F9FAFB'],
                ['value' => 'آبی', 'code' => '#2563EB'],
                ['value' => 'قرمز', 'code' => '#DC2626'],
                ['value' => 'سبز', 'code' => '#16A34A'],
                ['value' => 'قهوه‌ای', 'code' => '#92400E'],
                ['value' => 'طلایی', 'code' => '#CA8A04'],
                ['value' => 'نقره‌ای', 'code' => '#9CA3AF'],
                ['value' => 'خاکستری', 'code' => '#6B7280'],
                ['value' => 'صورتی', 'code' => '#EC4899'],
                ['value' => 'شفاف', 'code' => '#E5E7EB'],
            ],
            'coating' => [
                'attribute' => [
                    'name' => 'پوشش',
                    'slug' => 'coating',
                    'type' => 'select',
                    'is_variant' => true,
                    'is_filterable' => true,
                    'sort' => 25,
                ],
                'values' => [
                    'بدون پوشش',
                    'آنتی‌رفلکس',
                    'آبی',
                    'فوتوکرومیک',
                    'پولاریزه',
                ],
            ],
        ];

        $this->upsertColorValues($definitions['color']);

        $coating = $definitions['coating'];
        $attribute = Attribute::query()->firstOrCreate(
            ['slug' => $coating['attribute']['slug']],
            [
                ...$coating['attribute'],
                'is_visible_on_product' => true,
                'is_active' => true,
            ],
        );

        $sort = 0;
        foreach ($coating['values'] as $value) {
            $sort++;
            AttributeValue::query()->firstOrCreate(
                [
                    'attribute_id' => $attribute->id,
                    'value' => $value,
                ],
                [
                    'slug' => Str::slug($value) !== '' ? Str::slug($value) : 'coating-'.$sort,
                    'is_active' => true,
                    'sort' => $sort,
                ],
            );
        }
    }

    /**
     * @param  list<array{value: string, code: string}>  $rows
     */
    private function upsertColorValues(array $rows): void
    {
        $color = Attribute::query()->where('slug', 'color')->first();

        if ($color === null) {
            return;
        }

        $sort = (int) AttributeValue::query()->where('attribute_id', $color->id)->max('sort');

        foreach ($rows as $row) {
            $sort++;
            $value = AttributeValue::query()->firstOrCreate(
                [
                    'attribute_id' => $color->id,
                    'value' => $row['value'],
                ],
                [
                    'slug' => Str::slug($row['value']) !== '' ? Str::slug($row['value']) : 'color-'.$sort,
                    'code' => $row['code'],
                    'is_active' => true,
                    'sort' => $sort,
                ],
            );

            if (blank($value->code)) {
                $value->update(['code' => $row['code']]);
            }
        }
    }

    public function down(): void
    {
        // دادهٔ کاتالوگ را در rollback حذف نمی‌کنیم.
    }
};
