<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Home\Models\HomeSection;
use App\Modules\Home\Models\HomeSectionTab;
use App\Modules\Profile\Models\Resume;
use App\Modules\Profile\Support\ResumeModeration;
use App\Services\Home\HomePageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HomeInstructorsSectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_includes_instructors_with_resume_url(): void
    {
        Role::findOrCreate('instructor');

        $instructor = User::factory()->create([
            'name' => 'علی',
            'family' => 'استادی',
            'status' => 1,
        ]);
        $instructor->assignRole('instructor');
        ResumeModeration::markApproved($instructor);

        $section = HomeSection::query()->create([
            'title' => 'اساتید',
            'status' => true,
            'sort' => 5,
            'settings' => ['section_template' => 'instructors'],
        ]);

        $tab = HomeSectionTab::query()->create([
            'section_id' => $section->id,
            'title' => 'default',
            'content_type' => 'instructor',
            'source_type' => 'manual',
            'limit' => 8,
            'sort_order' => 0,
            'is_active' => true,
            'settings' => [],
        ]);

        $tab->items()->create([
            'item_type' => User::class,
            'item_id' => $instructor->id,
            'sort_order' => 0,
        ]);

        HomePageService::flush();

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Home')
                ->has('sections')
                ->where('sections', function ($sections) use ($instructor) {
                    foreach ($sections as $sectionRow) {
                        if (($sectionRow['settings']['section_template'] ?? null) !== 'instructors') {
                            continue;
                        }
                        $firstItem = $sectionRow['tabs'][0]['items'][0] ?? null;

                        return $firstItem !== null
                            && ($firstItem['title'] ?? '') === 'علی استادی'
                            && str_contains((string) ($firstItem['profile_url'] ?? ''), '/instructors/'.$instructor->id);
                    }

                    return false;
                })
            );
    }

    public function test_manual_instructor_tab_excludes_unapproved_instructors(): void
    {
        Role::findOrCreate('instructor');

        $approved = User::factory()->create(['name' => 'تأیید', 'family' => 'شده', 'status' => 1]);
        $approved->assignRole('instructor');
        ResumeModeration::markApproved($approved);

        $pending = User::factory()->create(['name' => 'در', 'family' => 'انتظار', 'status' => 1]);
        $pending->assignRole('instructor');

        $section = HomeSection::query()->create([
            'title' => 'اساتید',
            'status' => true,
            'sort' => 6,
            'settings' => ['section_template' => 'instructors'],
        ]);

        $tab = HomeSectionTab::query()->create([
            'section_id' => $section->id,
            'title' => 'default',
            'content_type' => 'instructor',
            'source_type' => 'manual',
            'limit' => 8,
            'sort_order' => 0,
            'is_active' => true,
            'settings' => [],
        ]);

        $tab->items()->createMany([
            ['item_type' => User::class, 'item_id' => $pending->id, 'sort_order' => 0],
            ['item_type' => User::class, 'item_id' => $approved->id, 'sort_order' => 1],
        ]);

        HomePageService::flush();

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('sections', function ($sections) use ($approved, $pending) {
                    foreach ($sections as $sectionRow) {
                        if (($sectionRow['settings']['section_template'] ?? null) !== 'instructors') {
                            continue;
                        }
                        $items = $sectionRow['tabs'][0]['items'] ?? [];
                        $titles = collect($items)->pluck('title')->all();

                        return count($items) === 1
                            && in_array('تأیید شده', $titles, true)
                            && ! in_array('در انتظار', $titles, true);
                    }

                    return false;
                })
            );
    }

    public function test_public_resume_endpoint_returns_grouped_entries(): void
    {
        Role::findOrCreate('instructor');

        $instructor = User::factory()->create(['status' => 1]);
        $instructor->assignRole('instructor');
        ResumeModeration::markApproved($instructor);

        Resume::query()->create([
            'user_id' => $instructor->id,
            'type' => 'experience',
            'title' => 'مدرس CATIA',
            'organization' => 'آکادمی شریفی',
            'display_order' => 0,
        ]);

        $this->getJson(route('instructors.resume', $instructor))
            ->assertOk()
            ->assertJsonPath('sections.0.type', 'experience')
            ->assertJsonPath('sections.0.items.0.title', 'مدرس CATIA');
    }

    public function test_public_resume_is_hidden_for_non_instructors(): void
    {
        $user = User::factory()->create(['status' => 1]);

        $this->getJson(route('instructors.resume', $user))->assertNotFound();
        $this->get(route('instructors.show', $user))->assertNotFound();
    }

    public function test_public_resume_page_renders_for_instructor(): void
    {
        Role::findOrCreate('instructor');

        $instructor = User::factory()->create(['status' => 1, 'name' => 'رضا', 'family' => 'مدرس']);
        $instructor->assignRole('instructor');
        ResumeModeration::markApproved($instructor);

        Resume::query()->create([
            'user_id' => $instructor->id,
            'type' => 'education',
            'title' => 'دکتری',
            'organization' => 'دانشگاه شریف',
            'display_order' => 0,
        ]);

        $this->get(route('instructors.show', $instructor))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/InstructorResume/Show')
                ->where('profile.fullName', 'رضا مدرس')
                ->where('profile.sections.0.label', 'تحصیلات')
                ->where('structuredData.person.@type', 'Person')
                ->where('structuredData.person.name', 'رضا مدرس')
                ->where('structuredData.profilePage.@type', 'ProfilePage')
                ->has('structuredData.breadcrumb')
            );
    }
}
