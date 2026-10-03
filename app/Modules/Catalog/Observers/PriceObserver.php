<?php

namespace App\Modules\Catalog\Observers;

use App\Modules\Catalog\Models\Price;

class PriceObserver
{
    public function creating(Price $price): void
    {
        if ($price->starts_at === null) {
            $price->starts_at = now();
        }
    }

    public function updating(Price $price): void
    {
        if (! $price->isDirty(['price', 'final_price', 'discount_type', 'discount_value'])) {
            return;
        }

        if ($price->ends_at !== null && $price->ends_at->isPast()) {
            return;
        }

        throw new \RuntimeException(
            'قیمت فعال را مستقیم ویرایش نکنید؛ از VariantPriceService برای ثبت تاریخچه استفاده کنید.',
        );
    }
}
