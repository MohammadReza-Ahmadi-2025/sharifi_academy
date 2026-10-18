<?php

namespace App\Support;

use App\Models\User;
use App\Modules\Menu\Services\SiteHeaderNavigationService;

class SiteNavigation
{
    /**
     * @return array{items: list<array<string, mixed>>, cta: null}
     */
    public static function forUser(?User $user): array
    {
        return app(SiteHeaderNavigationService::class)->build($user);
    }
}
