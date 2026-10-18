<?php

use App\Modules\Catalog\Services\SiteCourseCategoryNavService;
use App\Modules\Menu\Enums\MenuLocation;
use App\Modules\Menu\Models\Menu;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        SiteCourseCategoryNavService::flush();

        Menu::query()
            ->where('location', MenuLocation::Mobile)
            ->where('title', 'دسته بندی')
            ->update(['url' => '#course-categories']);
    }

    public function down(): void
    {
        Menu::query()
            ->where('location', MenuLocation::Mobile)
            ->where('title', 'دسته بندی')
            ->update(['url' => '/#books']);
    }
};
