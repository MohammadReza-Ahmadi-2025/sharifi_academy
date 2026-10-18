<?php

use App\Modules\Home\Models\HomeSection;
use App\Modules\Home\Models\HomeSectionTab;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (HomeSection::query()->where('settings->section_template', 'testimonials')->exists()) {
            return;
        }

        $testimonials = [
            [
                'name' => 'امیر حسینی',
                'role' => 'مهندس طراحی صنعتی',
                'quote' => 'دوره CATIA آکادمی شریفی کاملاً پروژه‌محور بود؛ بعد از چند جلسه توانستم مدل سه‌بعدی قطعه واقعی کارگاه را خودم بسازم.',
                'rating' => 5,
            ],
            [
                'name' => 'سارا محمدی',
                'role' => 'کارشناس شبیه‌سازی CFD',
                'quote' => 'جلسات زنده Adobe Connect و پشتیبانی استاد باعث شد حتی با شغل تمام‌وقت، مسیر یادگیری را از دست ندهم.',
                'rating' => 5,
            ],
            [
                'name' => 'رضا کریمی',
                'role' => 'فارغ‌التحصیل تحلیل مهندسی',
                'quote' => 'سرفصل‌ها منظم و قابل پیگیری است؛ از تمرین‌های هفتگی تا فایل‌های مرجع، همه چیز برای ورود به بازار کار آماده شده.',
                'rating' => 4,
            ],
            [
                'name' => 'نرگس احمدی',
                'role' => 'مدیر فنی تیم طراحی',
                'quote' => 'برای تیم خودمان چند دوره ثبت‌نام کردیم؛ کیفیت تدریس و عمق مثال‌های صنعتی از انتظارمان بالاتر بود.',
                'rating' => 5,
            ],
            [
                'name' => 'حسین مرادی',
                'role' => 'طراح CAD',
                'quote' => 'پنل دانشجو ساده است؛ لینک کلاس، جزوه و تمرین‌ها یکجا هست و نیازی به پیگیری پراکنده در گروه‌ها نیست.',
                'rating' => 4,
            ],
            [
                'name' => 'مریم علوی',
                'role' => 'دانشجوی ارشد مکانیک',
                'quote' => 'آکادمی شریفی برای من پل ارتباط تئوری دانشگاه با نرم‌افزارهایی بود که در صنعت واقعاً استفاده می‌شود.',
                'rating' => 5,
            ],
        ];

        $section = HomeSection::query()->create([
            'title' => 'نظر کاربران درباره آکادمی شریفی',
            'status' => true,
            'sort' => 25,
            'settings' => [
                'section_template' => 'testimonials',
            ],
        ]);

        HomeSectionTab::query()->create([
            'section_id' => $section->id,
            'title' => 'default',
            'content_type' => 'testimonials',
            'source_type' => 'section',
            'limit' => 0,
            'sort_order' => 0,
            'is_active' => true,
            'settings' => [
                'testimonials' => $testimonials,
            ],
        ]);
    }

    public function down(): void
    {
        HomeSection::query()
            ->where('settings->section_template', 'testimonials')
            ->where('title', 'نظر کاربران درباره آکادمی شریفی')
            ->delete();
    }
};
