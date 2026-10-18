<?php

use App\Modules\Settings\Models\SiteSetting;
use App\Modules\Settings\Services\SiteSettingsService;
use App\Modules\Settings\Support\SiteSettingDefinitions;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $siteName = (string) (SiteSetting::query()->where('key', 'site_name')->value('value') ?: config('app.name'));

        $defaults = [
            'blogs_meta_title' => "وبلاگ | {$siteName}",
            'blogs_meta_description' => "مقالات، آموزش‌ها و اخبار {$siteName}",
            'blogs_meta_keywords' => 'وبلاگ, مقاله, آموزش, مهندسی',
            'blogs_header_title' => 'وبلاگ',
            'blogs_subheader_title' => "مطالب آموزشی و تخصصی {$siteName}",
        ];

        $definitions = SiteSettingDefinitions::all();

        foreach ($defaults as $key => $defaultValue) {
            $definition = $definitions[$key] ?? null;
            if ($definition === null) {
                continue;
            }

            $setting = SiteSetting::query()->firstOrCreate(
                ['key' => $key],
                [
                    'value' => SiteSetting::serializeValue($defaultValue, $definition['type']),
                    'type' => $definition['type'],
                    'group' => $definition['group'],
                    'autoload' => true,
                ],
            );

            if (! filled($setting->value)) {
                $setting->update([
                    'value' => SiteSetting::serializeValue($defaultValue, $definition['type']),
                    'autoload' => true,
                ]);
            } elseif (! $setting->autoload) {
                $setting->update(['autoload' => true]);
            }
        }

        app(SiteSettingsService::class)->forgetCache();
    }

    public function down(): void
    {
        // دادهٔ کاربر حفظ می‌شود.
    }
};
