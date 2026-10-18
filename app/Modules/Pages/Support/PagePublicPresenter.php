<?php

namespace App\Modules\Pages\Support;

use App\Modules\Pages\Models\Page;
use App\Modules\Settings\Services\SiteSettingsService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PagePublicPresenter
{
    public static function ensurePublic(Page $page): void
    {
        if (! $page->is_active) {
            throw new NotFoundHttpException;
        }
    }

    /**
     * @return array<string, mixed>
     */
    public static function forPage(Page $page, SiteSettingsService $settings, string $appName): array
    {
        $seo = PageSeo::metaForPage($page, $settings, $appName);

        return [
            'id' => $page->id,
            'title' => $page->title,
            'slug' => $page->publicSlug(),
            'content' => $page->content,
            'seo' => $seo,
        ];
    }
}
