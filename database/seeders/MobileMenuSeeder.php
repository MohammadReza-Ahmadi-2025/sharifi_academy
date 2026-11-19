<?php

namespace Database\Seeders;

use App\Modules\Catalog\Services\SiteCourseCategoryNavService;
use App\Modules\Menu\Enums\MenuLocation;
use App\Modules\Menu\Models\Menu;
use Illuminate\Database\Seeder;

class MobileMenuSeeder extends Seeder
{
    public function run(): void
    {
        $desired = [
            ['title' => 'خانه', 'url' => '/', 'icon' => 'home', 'sort' => 0],
            ['title' => 'دسته بندی', 'url' => '#course-categories', 'icon' => 'layout-grid', 'sort' => 10],
            ['title' => 'افزودن پست', 'url' => '#create-post', 'icon' => 'plus', 'sort' => 20],
            ['title' => 'پست‌ها', 'url' => '/posts', 'icon' => 'newspaper', 'sort' => 30],
            ['title' => 'پروفایل', 'url' => '/profile', 'icon' => 'user-round', 'sort' => 40],
        ];

        $existing = Menu::query()
            ->where('location', MenuLocation::Mobile)
            ->orderBy('sort')
            ->get();

        // Replace legacy «دوره» slot with add-post / ensure 5 canonical tabs.
        foreach ($existing as $menu) {
            if (in_array($menu->title, ['دوره', 'دوره‌ها'], true) || $menu->url === '/#courses') {
                $menu->update([
                    'title' => 'افزودن پست',
                    'url' => '#create-post',
                    'icon' => 'plus',
                    'sort' => 20,
                    'status' => true,
                ]);
            }
        }

        foreach ($desired as $row) {
            $match = Menu::query()
                ->where('location', MenuLocation::Mobile)
                ->where(function ($query) use ($row) {
                    $query->where('title', $row['title'])->orWhere('url', $row['url']);
                })
                ->first();

            if ($match) {
                $match->update([
                    'title' => $row['title'],
                    'url' => $row['url'],
                    'icon' => $row['icon'],
                    'sort' => $row['sort'],
                    'status' => true,
                ]);

                continue;
            }

            Menu::query()->create([
                'title' => $row['title'],
                'url' => $row['url'],
                'icon' => $row['icon'],
                'location' => MenuLocation::Mobile,
                'sort' => $row['sort'],
                'status' => true,
            ]);
        }

        SiteCourseCategoryNavService::flush();
    }
}
