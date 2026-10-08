<?php

namespace App\Modules\Home\Services;

use App\Modules\Files\Services\MorphFileSyncService;
use App\Modules\Home\Enums\BannerLocation;
use App\Modules\Home\Models\Banner;
use App\Modules\Home\Models\HomeSectionTab;
use Illuminate\Http\Request;

class HomeSectionBannerSync
{
    public function __construct(
        private readonly HomeSectionSyncService $sectionSync,
    ) {}

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    public function syncTab(HomeSectionTab $tab, array $rows, Request $request, int $tabIndex): void
    {
        if ($tab->content_type !== 'banner') {
            return;
        }

        $previousIds = $tab->items()
            ->where('item_type', Banner::class)
            ->pluck('item_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $keptIds = [];

        foreach (array_values($rows) as $sortOrder => $row) {
            if (! is_array($row)) {
                continue;
            }

            $banner = null;

            if (! empty($row['id'])) {
                $banner = Banner::query()->find((int) $row['id']);
            }

            if (! $banner) {
                $banner = Banner::query()->create([
                    'title' => $row['title'] ?? null,
                    'subtitle' => $row['subtitle'] ?? null,
                    'button_text' => $row['button_text'] ?? null,
                    'button_link' => $row['button_link'] ?? null,
                    'starts_at' => $row['starts_at'] ?? null,
                    'ends_at' => $row['ends_at'] ?? null,
                    'location' => BannerLocation::Section,
                ]);
            } else {
                $banner->update([
                    'title' => $row['title'] ?? null,
                    'subtitle' => $row['subtitle'] ?? null,
                    'button_text' => $row['button_text'] ?? null,
                    'button_link' => $row['button_link'] ?? null,
                    'starts_at' => $row['starts_at'] ?? null,
                    'ends_at' => $row['ends_at'] ?? null,
                    'location' => BannerLocation::Section,
                ]);
            }

            $imageKey = "tabs.{$tabIndex}.banners.{$sortOrder}.image";
            $this->syncBannerImage($banner, $request, $imageKey);

            $keptIds[] = $banner->id;
        }

        foreach (array_diff($previousIds, $keptIds) as $orphanId) {
            Banner::query()
                ->whereKey($orphanId)
                ->where('location', BannerLocation::Section)
                ->delete();
        }

        $this->sectionSync->syncBannerItems($tab, $keptIds);
    }

    protected function syncBannerImage(Banner $banner, Request $request, string $key): void
    {
        $manifest = $request->input($key, []);
        $row = is_array($manifest) && isset($manifest[0]) && is_array($manifest[0]) ? $manifest[0] : [];
        $removeKey = 'remove_'.str_replace('.', '_', $key);

        app(MorphFileSyncService::class)->syncSingleImage(
            $banner,
            $request->file("{$key}.0.file"),
            $row,
            $request->boolean($removeKey),
            MorphFileSyncService::TYPE_IMAGE,
        );
    }
}
