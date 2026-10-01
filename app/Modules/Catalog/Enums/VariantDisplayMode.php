<?php

namespace App\Modules\Catalog\Enums;

enum VariantDisplayMode: string
{
    case NORMAL = 'normal';
    case CALL_FOR_PRICE = 'call_for_price';
    case OUT_OF_STOCK = 'out_of_stock';

    public function label(): string
    {
        return match ($this) {
            self::NORMAL => 'نمایش قیمت',
            self::CALL_FOR_PRICE => 'تماس بگیرید',
            self::OUT_OF_STOCK => 'ناموجود',
        };
    }
}
