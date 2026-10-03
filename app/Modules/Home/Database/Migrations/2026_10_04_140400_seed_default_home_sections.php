<?php

use App\Modules\Home\Models\HomeSection;
use App\Modules\Home\Models\HomeSectionTab;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $courses = HomeSection::query()->create([
            'title' => 'دوره‌های آموزشی',
            'status' => true,
            'sort' => 10,
            'settings' => [
                'layout' => 'grid',
            ],
        ]);

        HomeSectionTab::query()->create([
            'section_id' => $courses->id,
            'title' => 'default',
            'content_type' => 'course',
            'source_type' => 'latest',
            'limit' => 8,
            'sort_order' => 0,
            'is_active' => true,
            'settings' => [
                'view_all_url' => '/courses',
            ],
        ]);

        $books = HomeSection::query()->create([
            'title' => 'کتاب‌های آکادمی',
            'status' => true,
            'sort' => 20,
            'settings' => [
                'layout' => 'grid',
            ],
        ]);

        HomeSectionTab::query()->create([
            'section_id' => $books->id,
            'title' => 'default',
            'content_type' => 'product',
            'source_type' => 'latest',
            'limit' => 8,
            'sort_order' => 0,
            'is_active' => true,
            'settings' => [
                'view_all_url' => '/books',
            ],
        ]);

        $blog = HomeSection::query()->create([
            'title' => 'وبلاگ',
            'status' => true,
            'sort' => 30,
            'settings' => [
                'layout' => 'grid',
            ],
        ]);

        HomeSectionTab::query()->create([
            'section_id' => $blog->id,
            'title' => 'default',
            'content_type' => 'post',
            'source_type' => 'latest',
            'limit' => 4,
            'sort_order' => 0,
            'is_active' => true,
            'settings' => [
                'view_all_url' => '/blog',
            ],
        ]);
    }

    public function down(): void
    {
        HomeSection::query()->delete();
    }
};
