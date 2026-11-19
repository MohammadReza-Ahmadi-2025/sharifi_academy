<?php

namespace Database\Seeders;

use App\Modules\Settings\Models\SiteSetting;
use App\Modules\Settings\Services\SiteSettingsService;
use App\Modules\Settings\Support\SiteSettingDefinitions;
use Illuminate\Database\Seeder;

class SiteSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $siteName = (string) config('app.name');

        foreach (SiteSettingDefinitions::all() as $key => $definition) {
            $default = $this->defaultFor($key, $definition['default'] ?? null, $siteName);

            SiteSetting::query()->firstOrCreate(
                ['key' => $key],
                [
                    'value' => SiteSetting::serializeValue($default, $definition['type']),
                    'type' => $definition['type'],
                    'group' => $definition['group'],
                    'autoload' => (bool) ($definition['autoload'] ?? false),
                ],
            );
        }

        app(SiteSettingsService::class)->forgetCache();
    }

    private function defaultFor(string $key, mixed $default, string $siteName): mixed
    {
        if ($default !== null) {
            return $default;
        }

        return match ($key) {
            'site_name' => $siteName,
            'site_description' => "آکادمی {$siteName}",
            'seo_meta_title' => $siteName,
            'seo_meta_description' => "آموزش و دوره‌های {$siteName}",
            'blogs_meta_title' => "وبلاگ | {$siteName}",
            'blogs_meta_description' => "مقالات، آموزش‌ها و اخبار {$siteName}",
            'blogs_meta_keywords' => 'وبلاگ, مقاله, آموزش, مهندسی',
            'blogs_header_title' => 'وبلاگ',
            'blogs_subheader_title' => "مطالب آموزشی و تخصصی {$siteName}",
            'posts_meta_title' => "پست‌ها | {$siteName}",
            'posts_meta_description' => "پست‌های تأییدشده کاربران {$siteName}",
            'posts_meta_keywords' => 'پست, انجمن, کاربران',
            'posts_header_title' => 'پست‌ها',
            'posts_subheader_title' => 'آخرین پست‌هایی که کاربران نوشته‌اند و تأیید شده‌اند.',
            default => null,
        };
    }
}
