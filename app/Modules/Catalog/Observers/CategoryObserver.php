<?php

namespace App\Modules\Catalog\Observers;

use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Services\SiteCourseCategoryNavService;

class CategoryObserver
{
    public function saved(Category $category): void
    {
        if ($category->type === Category::TYPE_COURSE) {
            SiteCourseCategoryNavService::flush();
        }
    }

    public function deleted(Category $category): void
    {
        if ($category->type === Category::TYPE_COURSE) {
            SiteCourseCategoryNavService::flush();
        }
    }
}
