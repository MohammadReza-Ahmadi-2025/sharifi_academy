<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use App\Modules\Profile\Support\ResumeModeration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BackfillProfileUuidsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_fills_missing_profile_uuids(): void
    {
        $user = User::factory()->create();
        $profile = ResumeModeration::ensureProfile($user);
        $profile->forceFill(['uuid' => null])->save();

        $this->artisan('profile:backfill-uuids')
            ->assertSuccessful();

        $profile->refresh();
        $this->assertTrue(Str::isUuid((string) $profile->uuid));
    }

    public function test_dry_run_does_not_write(): void
    {
        $user = User::factory()->create();
        $profile = ResumeModeration::ensureProfile($user);
        $profile->forceFill(['uuid' => null])->save();

        $this->artisan('profile:backfill-uuids', ['--dry-run' => true])
            ->assertSuccessful();

        $profile->refresh();
        $this->assertNull($profile->uuid);
    }
}
