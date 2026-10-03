<?php

use App\Modules\Settings\Models\SiteSetting;
use App\Modules\Settings\Support\SiteSettingDefinitions;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $siteName = (string) config('app.name');

        foreach (SiteSettingDefinitions::all() as $key => $definition) {
            $default = $definition['default'];

            if ($key === 'site_name' && $default === null) {
                $default = $siteName;
            }

            if ($key === 'site_description' && $default === null) {
                $default = "آکادمی {$siteName}";
            }

            if ($key === 'seo_meta_title' && $default === null) {
                $default = $siteName;
            }

            if ($key === 'seo_meta_description' && $default === null) {
                $default = "آموزش و دوره‌های {$siteName}";
            }

            SiteSetting::query()->firstOrCreate(
                ['key' => $key],
                [
                    'value' => SiteSetting::serializeValue($default, $definition['type']),
                    'type' => $definition['type'],
                    'group' => $definition['group'],
                    'autoload' => $definition['autoload'],
                ],
            );
        }
    }

    public function down(): void
    {
        SiteSetting::query()->whereIn('key', array_keys(SiteSettingDefinitions::all()))->delete();
    }
};
