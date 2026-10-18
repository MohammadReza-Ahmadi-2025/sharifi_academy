<?php

namespace Tests\Feature\Instructor;

use App\Models\User;
use App\Modules\Profile\Enums\ResumeModerationStatus;
use App\Modules\Profile\Models\Skill;
use App\Modules\Profile\Support\ResumeModeration;
use App\Modules\Roles\Support\DefaultRolePermissions;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstructorSkillsSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        DefaultRolePermissions::sync();
    }

    public function test_instructor_can_sync_skills(): void
    {
        $instructor = User::where('mobile', '09120000002')->firstOrFail();

        $skillA = Skill::query()->create(['name' => 'PHP']);
        $skillB = Skill::query()->create(['name' => 'Laravel']);

        $this->actingAs($instructor)
            ->put('/instructor/resume/skills', [
                'skills' => [
                    ['skill_id' => $skillA->id, 'level' => 4],
                    ['skill_id' => $skillB->id, 'level' => null],
                ],
            ])
            ->assertRedirect();

        $instructor->refresh()->load('skills');

        $this->assertCount(2, $instructor->skills);
        $this->assertSame(4, (int) $instructor->skills->firstWhere('id', $skillA->id)?->pivot?->level);
    }

    public function test_syncing_skills_after_approval_marks_resume_pending(): void
    {
        $instructor = User::where('mobile', '09120000002')->firstOrFail();
        ResumeModeration::markApproved($instructor);

        $skill = Skill::query()->create(['name' => 'Vue']);

        $this->actingAs($instructor)
            ->put('/instructor/resume/skills', [
                'skills' => [
                    ['skill_id' => $skill->id, 'level' => 3],
                ],
            ])
            ->assertRedirect();

        $instructor->refresh()->load('profile');

        $this->assertSame(ResumeModerationStatus::Pending, ResumeModeration::status($instructor));
    }
}
