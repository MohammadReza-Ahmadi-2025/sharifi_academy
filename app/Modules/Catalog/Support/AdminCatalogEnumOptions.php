<?php

namespace App\Modules\Catalog\Support;

use BackedEnum;

class AdminCatalogEnumOptions
{
    /**
     * @param  class-string<BackedEnum&object{label(): string}>  $enumClass
     * @return list<array{value: string, label: string}>
     */
    public static function fromEnum(string $enumClass): array
    {
        return array_map(
            static fn (BackedEnum $case) => [
                'value' => $case->value,
                'label' => method_exists($case, 'label') ? $case->label() : $case->name,
            ],
            $enumClass::cases(),
        );
    }
}
