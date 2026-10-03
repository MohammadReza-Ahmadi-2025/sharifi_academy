<?php

namespace App\Modules\Courses\Support;

use BackedEnum;

class AdminEnumOptions
{
    /**
     * @param  class-string<BackedEnum>  $enumClass
     * @return list<array{value: string, label: string}>
     */
    public static function fromEnum(string $enumClass, string $labelMethod = 'label'): array
    {
        $options = [];

        foreach ($enumClass::cases() as $case) {
            $options[] = [
                'value' => $case->value,
                'label' => method_exists($case, $labelMethod) ? $case->{$labelMethod}() : $case->value,
            ];
        }

        return $options;
    }
}
