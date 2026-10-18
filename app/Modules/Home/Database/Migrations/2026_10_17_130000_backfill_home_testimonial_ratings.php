<?php

use App\Modules\Home\Models\HomeSectionTab;
use App\Services\Home\HomePageService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    private const DEFAULT_RATINGS = [5, 5, 4, 5, 4, 5];

    public function up(): void
    {
        HomeSectionTab::query()
            ->where('content_type', 'testimonials')
            ->each(function (HomeSectionTab $tab) {
                $settings = is_array($tab->settings) ? $tab->settings : [];
                $rows = $settings['testimonials'] ?? [];
                if (! is_array($rows) || $rows === []) {
                    return;
                }

                $changed = false;
                foreach (array_values($rows) as $index => &$row) {
                    if (! is_array($row)) {
                        continue;
                    }
                    if (! isset($row['rating'])) {
                        $row['rating'] = self::DEFAULT_RATINGS[$index] ?? 5;
                        $changed = true;
                    }
                }
                unset($row);

                if ($changed) {
                    $settings['testimonials'] = array_values($rows);
                    $tab->update(['settings' => $settings]);
                }
            });

        HomePageService::flush();
    }

    public function down(): void
    {
        //
    }
};
