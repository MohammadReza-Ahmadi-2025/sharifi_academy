<?php

use App\Modules\Home\Models\HomeSection;
use App\Modules\Home\Models\HomeSectionTab;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $sectionIds = HomeSectionTab::query()
            ->where('content_type', 'hero')
            ->pluck('section_id');

        if ($sectionIds->isEmpty()) {
            return;
        }

        HomeSectionTab::query()->whereIn('section_id', $sectionIds)->delete();
        HomeSection::query()->whereIn('id', $sectionIds)->delete();
    }

    public function down(): void
    {
        // hero is restored via site settings + static HomeHeroSection, not CMS
    }
};
