<?php

use App\Modules\Settings\Models\SiteSetting;
use App\Modules\Settings\Services\SiteSettingsService;
use App\Modules\Settings\Support\SiteSettingDefinitions;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $definition = SiteSettingDefinitions::all()['adobe_connect_base_url'] ?? null;

        if ($definition === null) {
            return;
        }

        SiteSetting::query()->firstOrCreate(
            ['key' => 'adobe_connect_base_url'],
            [
                'value' => SiteSetting::serializeValue($definition['default'], $definition['type']),
                'type' => $definition['type'],
                'group' => $definition['group'],
                'autoload' => (bool) ($definition['autoload'] ?? false),
            ],
        );

        app(SiteSettingsService::class)->forgetCache();
    }

    public function down(): void
    {
        //
    }
};
