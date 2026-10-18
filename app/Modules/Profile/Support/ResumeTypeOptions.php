<?php

namespace App\Modules\Profile\Support;

final class ResumeTypeOptions
{
    /**
     * @return list<array{value: string, label: string}>
     */
    public static function forSelect(): array
    {
        return [
            ['value' => 'education', 'label' => 'تحصیلات'],
            ['value' => 'experience', 'label' => 'سوابق شغلی'],
            ['value' => 'certificate', 'label' => 'گواهینامه'],
            ['value' => 'award', 'label' => 'جوایز'],
            ['value' => 'project', 'label' => 'پروژه'],
        ];
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::forSelect(), 'value');
    }

    public static function labelFor(string $type): string
    {
        foreach (self::forSelect() as $option) {
            if ($option['value'] === $type) {
                return $option['label'];
            }
        }

        return $type;
    }
}
