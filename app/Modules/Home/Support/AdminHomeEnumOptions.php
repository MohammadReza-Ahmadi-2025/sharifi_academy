<?php

namespace App\Modules\Home\Support;

class AdminHomeEnumOptions
{
    /**
     * @return array<string, string>
     */
    public static function contentTypes(): array
    {
        return [
            'features' => 'گرید سه‌تایی',
            'product' => 'محصول',
            'course' => 'دوره',
            'post' => 'وبلاگ',
            'brand' => 'برند',
            'category' => 'دسته‌بندی',
            'banner' => 'بنر',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function sourceTypes(): array
    {
        return [
            'latest' => 'جدیدترین',
            'oldest' => 'قدیمی‌ترین',
            'best_selling' => 'پرفروش‌ترین',
            'most_viewed' => 'پربازدیدترین',
            'manual' => 'انتخاب دستی',
            'by_category' => 'بر اساس دسته',
            'by_brand' => 'بر اساس برند',
            'section' => 'تنظیمات سکشن',
        ];
    }

    /**
     * @return array<string, string>
     */
    /**
     * @return array<string, string>
     */
    public static function catalogTabContentTypes(): array
    {
        return array_intersect_key(
            self::contentTypes(),
            array_flip(['product', 'course', 'post', 'brand', 'category']),
        );
    }

    public static function sourceTypesForContentType(string $contentType): array
    {
        $all = self::sourceTypes();

        if ($contentType === 'banner') {
            return ['manual' => 'انتخاب دستی (از لیست بنرها)'];
        }

        if ($contentType === 'features') {
            return ['section' => 'تعریف در تنظیمات سکشن'];
        }

        $keys = match ($contentType) {
            'product' => ['latest', 'oldest', 'best_selling', 'most_viewed', 'manual', 'by_category', 'by_brand'],
            'course' => ['latest', 'oldest', 'most_viewed', 'manual', 'by_category'],
            'post' => ['latest', 'oldest', 'most_viewed', 'manual'],
            'brand' => ['latest', 'oldest', 'manual'],
            'category' => ['latest', 'oldest', 'manual'],
            default => ['latest', 'oldest', 'manual'],
        };

        return array_intersect_key($all, array_flip($keys));
    }

    /**
     * @return array<string, string>
     */
    public static function sectionTemplates(): array
    {
        return [
            'hero_split' => 'هیرو کلاسیک (متن چپ، بنر راست)',
            'catalog' => 'لیست کارت (اسلایدر ۵تایی)',
            'features' => 'گرید سه‌تایی',
            'banners' => 'اسلایدر بنر',
        ];
    }

    public static function sectionLayouts(): array
    {
        return [
            'grid' => 'شبکه کارت',
            'carousel' => 'اسلایدر کارت',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function bannerLayouts(): array
    {
        return [
            'showcase' => 'کلاسیک (اسلایدر)',
            'side_by_side' => 'همه کنار هم',
            'grid_2x2' => 'دو به دو',
            'grid_3' => 'سه‌تایی',
            'stacked' => 'تمام‌عرض زیر هم',
        ];
    }
}
