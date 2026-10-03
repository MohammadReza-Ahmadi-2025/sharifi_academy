<?php

namespace App\Modules\Home\Support;

use App\Modules\Home\Models\Banner;

class BannerPresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function forList(Banner $banner): array
    {
        return [
            'id' => $banner->id,
            'title' => $banner->title,
            'subtitle' => $banner->subtitle,
            'button_text' => $banner->button_text,
            'button_link' => $banner->button_link,
            'location' => $banner->location?->value,
            'location_label' => $banner->location?->label(),
            'starts_at' => $banner->starts_at?->toIso8601String(),
            'ends_at' => $banner->ends_at?->toIso8601String(),
            'image_url' => $banner->file?->publicUrl(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function forForm(Banner $banner): array
    {
        $file = $banner->file;

        return [
            'id' => $banner->id,
            'title' => $banner->title ?? '',
            'subtitle' => $banner->subtitle ?? '',
            'button_text' => $banner->button_text ?? '',
            'button_link' => $banner->button_link ?? '',
            'location' => $banner->location?->value ?? 'section',
            'starts_at' => $banner->starts_at?->format('Y-m-d\TH:i'),
            'ends_at' => $banner->ends_at?->format('Y-m-d\TH:i'),
            'image' => $file ? [
                'id' => $file->id,
                'url' => $file->publicUrl(),
                'name' => $file->name,
                'size' => $file->size,
                'title' => $file->title,
                'alt' => $file->alt,
            ] : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function forPicker(Banner $banner): array
    {
        return [
            'id' => $banner->id,
            'title' => $banner->title ?: ('بنر #'.$banner->id),
            'image_url' => $banner->file?->publicUrl(),
        ];
    }
}
