<?php

namespace Tests\Feature;

use App\Services\Home\HomePageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeTestimonialsSectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_includes_seeded_testimonials_section(): void
    {
        HomePageService::flush();

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Home')
                ->has('sections')
                ->where('sections', function ($sections) {
                    foreach ($sections as $section) {
                        if (($section['settings']['section_template'] ?? null) === 'testimonials') {
                            $tab = $section['tabs'][0] ?? null;

                            return $tab !== null
                                && ($tab['testimonials'][0]['name'] ?? '') === 'امیر حسینی';
                        }
                    }

                    return false;
                })
            );
    }
}
