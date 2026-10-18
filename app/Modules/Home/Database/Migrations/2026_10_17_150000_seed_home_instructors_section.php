<?php

use App\Modules\Home\Models\HomeSection;
use App\Modules\Home\Models\HomeSectionTab;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (HomeSection::query()->where('settings->section_template', 'instructors')->exists()) {
            return;
        }

        $section = HomeSection::query()->create([
            'title' => 'اساتید آکادمی شریفی',
            'status' => true,
            'sort' => 22,
            'settings' => [
                'section_template' => 'instructors',
                'catalog_slides_per_view' => 5,
            ],
        ]);

        HomeSectionTab::query()->create([
            'section_id' => $section->id,
            'title' => 'default',
            'content_type' => 'instructor',
            'source_type' => 'latest',
            'limit' => 12,
            'sort_order' => 0,
            'is_active' => true,
            'settings' => [],
        ]);
    }

    public function down(): void
    {
        HomeSection::query()
            ->where('settings->section_template', 'instructors')
            ->where('title', 'اساتید آکادمی شریفی')
            ->delete();
    }
};
