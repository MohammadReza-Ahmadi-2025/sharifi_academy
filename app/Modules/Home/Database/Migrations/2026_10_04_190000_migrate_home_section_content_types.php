<?php

use App\Modules\Home\Models\HomeSection;
use App\Modules\Home\Models\HomeSectionTab;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        HomeSection::query()
            ->where('settings->layout', 'hero')
            ->each(function (HomeSection $section) {
                $settings = $section->settings ?? [];
                unset($settings['layout']);
                $section->update(['settings' => $settings]);

                $section->tabs()->update([
                    'content_type' => 'hero',
                    'source_type' => 'manual',
                ]);
            });

        HomeSection::query()
            ->where('settings->layout', 'features')
            ->each(function (HomeSection $section) {
                $settings = $section->settings ?? [];
                unset($settings['layout']);
                $section->update(['settings' => $settings]);

                $tab = $section->tabs()->first();
                if ($tab) {
                    $tab->update([
                        'content_type' => 'features',
                        'source_type' => 'section',
                    ]);
                } else {
                    HomeSectionTab::query()->create([
                        'section_id' => $section->id,
                        'title' => 'default',
                        'content_type' => 'features',
                        'source_type' => 'section',
                        'limit' => 0,
                        'sort_order' => 0,
                        'is_active' => true,
                        'settings' => [],
                    ]);
                }
            });
    }

    public function down(): void
    {
        // irreversible content-type migration
    }
};
