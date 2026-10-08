<?php

use App\Modules\Menu\Enums\MenuItemType;
use App\Modules\Menu\Enums\MenuLocation;
use App\Modules\Menu\Models\Menu;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (Menu::query()->where('location', MenuLocation::Header)->exists()) {
            return;
        }

        $rows = [
            ['title' => 'خانه', 'url' => '/', 'sort' => 0],
            ['title' => 'دوره‌ها', 'url' => '/#courses', 'sort' => 10],
            ['title' => 'کتاب‌ها', 'url' => '/#books', 'sort' => 20],
            ['title' => 'ورود / ثبت‌نام', 'url' => '/login', 'sort' => 90],
        ];

        foreach ($rows as $row) {
            Menu::query()->create([
                'title' => $row['title'],
                'url' => $row['url'],
                'location' => MenuLocation::Header,
                'sort' => $row['sort'],
                'status' => true,
            ]);
        }
    }

    public function down(): void
    {
        Menu::query()
            ->where('location', MenuLocation::Header)
            ->whereIn('title', ['خانه', 'دوره‌ها', 'کتاب‌ها', 'ورود / ثبت‌نام'])
            ->delete();
    }
};
