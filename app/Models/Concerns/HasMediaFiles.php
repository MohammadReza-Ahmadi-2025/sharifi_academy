<?php

namespace App\Models\Concerns;

use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * فایل‌های پلی‌مورفیک روی جدول `media` (Spatie Media Library).
 *
 * مدل باید `Spatie\MediaLibrary\HasMedia` را implement کند.
 */
trait HasMediaFiles
{
    use InteractsWithMedia;
}
