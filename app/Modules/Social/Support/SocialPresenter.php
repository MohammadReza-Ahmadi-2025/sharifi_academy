<?php

namespace App\Modules\Social\Support;

use App\Modules\Social\Models\Social;

class SocialPresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function forList(Social $social): array
    {
        return [
            'id' => $social->id,
            'type' => $social->type,
            'type_label' => AdminSocialTypeOptions::map()[$social->type] ?? $social->type,
            'value' => $social->value,
            'label' => $social->label,
            'target_blank' => (bool) $social->target_blank,
            'sort' => $social->sort,
            'is_active' => (bool) $social->is_active,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function forForm(Social $social): array
    {
        return [
            'id' => $social->id,
            'type' => $social->type,
            'value' => $social->value,
            'label' => $social->label ?? '',
            'target_blank' => (bool) $social->target_blank,
            'is_active' => (bool) $social->is_active,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function forSite(Social $social): array
    {
        $typeLabel = AdminSocialTypeOptions::map()[$social->type] ?? $social->type;

        return [
            'id' => $social->id,
            'type' => $social->type,
            'url' => $social->url,
            'label' => filled($social->label) ? $social->label : $typeLabel,
            'target_blank' => (bool) $social->target_blank,
        ];
    }
}
