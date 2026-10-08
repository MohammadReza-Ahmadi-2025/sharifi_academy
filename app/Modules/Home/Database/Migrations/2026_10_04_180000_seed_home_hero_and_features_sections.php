<?php

use App\Modules\Home\Models\HomeSection;
use App\Modules\Home\Models\HomeSectionTab;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (HomeSection::query()->whereHas('tabs', fn ($q) => $q->where('content_type', 'features'))->exists()) {
            return;
        }

        $features = HomeSection::query()->create([
            'title' => 'ویژگی‌ها',
            'status' => true,
            'sort' => 7,
            'settings' => [
                'features' => [
                    [
                        'label' => 'کلاس آنلاین زنده',
                        'text' => 'ورود به جلسه از پنل دانشجو با لینک Adobe Connect.',
                    ],
                    [
                        'label' => 'کتاب تخصصی',
                        'text' => 'خرید و دانلود امن PDF کتاب‌های آموزشی آکادمی.',
                    ],
                    [
                        'label' => 'اساتید مجرب',
                        'text' => 'هر دوره یک استاد مسئول دارد و جلسات را خودش مدیریت می‌کند.',
                    ],
                ],
            ],
        ]);

        HomeSectionTab::query()->create([
            'section_id' => $features->id,
            'title' => 'default',
            'content_type' => 'features',
            'source_type' => 'section',
            'limit' => 0,
            'sort_order' => 0,
            'is_active' => true,
            'settings' => [],
        ]);
    }

    public function down(): void
    {
        HomeSection::query()->where('title', 'ویژگی‌ها')->delete();
    }
};
