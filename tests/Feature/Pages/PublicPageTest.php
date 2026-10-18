<?php

namespace Tests\Feature\Pages;

use App\Modules\Pages\Models\Page;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_page_is_public(): void
    {
        $this->seed(DatabaseSeeder::class);

        $page = Page::query()->create([
            'title' => 'درباره ما',
            'slug' => 'about-us',
            'content' => '<p>متن صفحه</p>',
            'is_active' => true,
            'meta_title' => 'درباره ما',
        ]);

        $this->get('/pages/'.$page->slug)
            ->assertOk()
            ->assertInertia(fn ($inertia) => $inertia
                ->component('Public/Page/Show')
                ->where('page.title', 'درباره ما')
                ->has('page.seo.canonical')
                ->has('structuredData.breadcrumb')
            );
    }

    public function test_inactive_page_returns_not_found(): void
    {
        $this->seed(DatabaseSeeder::class);

        $page = Page::query()->create([
            'title' => 'مخفی',
            'slug' => 'hidden-page',
            'content' => null,
            'is_active' => false,
        ]);

        $this->get('/pages/'.$page->slug)->assertNotFound();
    }
}
