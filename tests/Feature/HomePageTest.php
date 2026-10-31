<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_loads(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_home_page_includes_dynamic_sections(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Home')
                ->has('sections')
                ->has('storyGroups')
            );
    }

    public function test_home_page_includes_organization_and_website_json_ld(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Home')
                ->has('seo.canonical')
                ->where('structuredData.organization.@type', 'Organization')
                ->where('structuredData.webSite.@type', 'WebSite')
                ->where('structuredData.webSite.potentialAction.@type', 'SearchAction'));
    }
}
