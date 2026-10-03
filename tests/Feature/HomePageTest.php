<?php

namespace Tests\Feature;

use Database\Seeders\CourseDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_loads(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_home_page_includes_dynamic_sections_and_courses_from_database(): void
    {
        $this->seed(CourseDemoSeeder::class);

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Home')
                ->has('hero.badge')
                ->has('hero.title')
                ->has('sections.0.tabs.0.content_type')
                ->where('sections.0.tabs.0.content_type', 'course')
                ->where('sections.0.tabs.0.items.0.slug', 'laravel-zero-to-hero')
            );
    }
}
