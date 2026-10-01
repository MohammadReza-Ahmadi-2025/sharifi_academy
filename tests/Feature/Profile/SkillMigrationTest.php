<?php

namespace Tests\Feature\Profile;

use App\Modules\Catalog\Models\Category;
use App\Modules\Profile\Models\Skill;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SkillMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_attach_skills_with_level(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::where('mobile', '09120000003')->firstOrFail();

        $category = Category::query()->create([
            'title' => 'نرم‌افزار',
            'slug' => 'software-skills',
            'type' => 'service',
            'status' => 1,
        ]);

        $skill = Skill::query()->create([
            'name' => 'Laravel',
            'category_id' => $category->id,
        ]);

        $user->skills()->attach($skill->id, ['level' => 5]);

        $user->refresh();

        $this->assertCount(1, $user->skills);
        $this->assertSame('Laravel', $user->skills->first()?->name);
        $this->assertSame(5, $user->skills->first()?->pivot?->level);
        $this->assertCount(1, $category->skills);
    }
}
