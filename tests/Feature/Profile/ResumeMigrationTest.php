<?php

namespace Tests\Feature\Profile;

use App\Modules\Profile\Models\Resume;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResumeMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_have_many_resume_entries(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::where('mobile', '09120000003')->firstOrFail();

        Resume::query()->create([
            'user_id' => $user->id,
            'type' => 'education',
            'title' => 'کارشناسی مهندسی نرم‌افزار',
            'organization' => 'دانشگاه تهران',
            'start_date' => '2018-09-23',
            'end_date' => '2022-06-30',
            'display_order' => 1,
        ]);

        Resume::query()->create([
            'user_id' => $user->id,
            'type' => 'experience',
            'title' => 'توسعه‌دهنده لاراول',
            'organization' => 'شرکت نمونه',
            'start_date' => '2022-07-01',
            'is_current' => true,
            'display_order' => 2,
        ]);

        $user->refresh();

        $this->assertCount(2, $user->resumes);
        $this->assertSame('education', $user->resumes->first()?->type);
        $this->assertTrue($user->resumes->last()?->is_current);
    }
}
