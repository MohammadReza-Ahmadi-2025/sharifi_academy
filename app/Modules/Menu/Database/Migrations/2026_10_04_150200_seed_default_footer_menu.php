<?php

use App\Modules\Menu\Enums\MenuItemType;
use App\Modules\Menu\Enums\MenuLocation;
use App\Modules\Menu\Models\Menu;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $menu = Menu::query()->create([
            'title' => 'لینک‌های مفید',
            'url' => null,
            'location' => MenuLocation::Footer,
            'sort' => 10,
            'status' => true,
        ]);

        foreach (
            [
                ['title' => 'خانه', 'type' => MenuItemType::Internal, 'url' => '/', 'sort' => 0],
                ['title' => 'دوره‌ها', 'type' => MenuItemType::Internal, 'url' => '/#courses', 'sort' => 1],
                ['title' => 'کتاب‌ها', 'type' => MenuItemType::Internal, 'url' => '/#books', 'sort' => 2],
            ] as $row
        ) {
            $menu->allItems()->create([
                ...$row,
                'status' => true,
            ]);
        }
    }

    public function down(): void
    {
        Menu::query()->where('title', 'لینک‌های مفید')->delete();
    }
};
