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
            'course' => 'دوره',
            'product' => 'محصول (کتاب)',
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
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function sourceTypesForContentType(string $contentType): array
    {
        if ($contentType === 'banner') {
            return ['manual' => 'انتخاب دستی (از لیست بنرها)'];
        }

        return self::sourceTypes();
    }

    /**
     * @return array<string, string>
     */
    public static function sectionLayouts(): array
    {
        return [
            'grid' => 'شبکه کارت',
            'carousel' => 'اسلایدر',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function bannerLayouts(): array
    {
        return [
            'showcase' => 'اسلایدر تمام‌عرض',
            'stacked' => 'پشت‌سرهم',
        ];
    }
}
