<?php

namespace App\Modules\Menu\Enums;

enum MenuItemType: string
{
    case External = 'external';
    case Internal = 'internal';
    case CategoryProduct = 'category_product';
    case CategoryCourse = 'category_course';
    case Page = 'page';

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::External->value => 'لینک خارجی',
            self::Internal->value => 'مسیر داخلی',
            self::CategoryProduct->value => 'دسته کتاب',
            self::CategoryCourse->value => 'دسته دوره',
            self::Page->value => 'صفحه ثابت',
        ];
    }
}
