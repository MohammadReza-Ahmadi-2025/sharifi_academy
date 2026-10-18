<?php

use App\Modules\Menu\Enums\MenuLocation;
use App\Modules\Menu\Models\Menu;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (Menu::query()->where('location', MenuLocation::Mobile)->exists()) {
            return;
        }

        $rows = [
            ['title' => 'خانه', 'url' => '/', 'icon' => 'home', 'sort' => 0],
            ['title' => 'دسته بندی', 'url' => '#course-categories', 'icon' => 'layout-grid', 'sort' => 10],
            ['title' => 'دوره', 'url' => '/#courses', 'icon' => 'graduation-cap', 'sort' => 20],
            ['title' => 'پروفایل', 'url' => '/profile', 'icon' => 'user-round', 'sort' => 30],
        ];

        foreach ($rows as $row) {
            Menu::query()->create([
                'title' => $row['title'],
                'url' => $row['url'],
                'icon' => $row['icon'],
                'location' => MenuLocation::Mobile,
                'sort' => $row['sort'],
                'status' => true,
            ]);
        }
    }

    public function down(): void
    {
        Menu::query()
            ->where('location', MenuLocation::Mobile)
            ->whereIn('title', ['خانه', 'دسته بندی', 'دوره', 'پروفایل'])
            ->delete();
    }
};
