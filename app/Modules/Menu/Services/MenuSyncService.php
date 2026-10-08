<?php

namespace App\Modules\Menu\Services;

use App\Modules\Menu\Models\Menu;
use App\Modules\Menu\Models\MenuItem;

class MenuSyncService
{
    /**
     * @param  list<array<string, mixed>>  $items
     */
    public function syncItems(Menu $menu, array $items): void
    {
        $keptIds = [];

        foreach (array_values($items) as $index => $row) {
            $payload = [
                'title' => $row['title'],
                'type' => $row['type'],
                'url' => $row['url'] ?? null,
                'reference_id' => $row['reference_id'] ?? null,
                'sort' => $index,
                'status' => (bool) ($row['status'] ?? true),
                'target_blank' => (bool) ($row['target_blank'] ?? false),
            ];

            if (! empty($row['id'])) {
                $item = MenuItem::query()
                    ->where('menu_id', $menu->id)
                    ->whereKey($row['id'])
                    ->first();

                if ($item) {
                    $item->update($payload);
                    $keptIds[] = $item->id;
                }

                continue;
            }

            $item = $menu->allItems()->create($payload);
            $keptIds[] = $item->id;
        }

        $menu->allItems()->whereNotIn('id', $keptIds)->delete();
    }
}
