<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class HttpErrorPageTest extends TestCase
{
    public function test_not_found_renders_inertia_error_page(): void
    {
        $this->get('/this-route-does-not-exist')
            ->assertNotFound()
            ->assertInertia(fn ($page) => $page
                ->component('Errors/Show')
                ->where('status', 404)
            );
    }

    public function test_forbidden_renders_inertia_error_page(): void
    {
        Route::get('/_test-http-403', fn () => abort(403));

        $this->get('/_test-http-403')
            ->assertForbidden()
            ->assertInertia(fn ($page) => $page
                ->component('Errors/Show')
                ->where('status', 403)
            );
    }

    public function test_server_error_renders_inertia_error_page(): void
    {
        Route::get('/_test-http-500', fn () => abort(500));

        $this->get('/_test-http-500')
            ->assertStatus(500)
            ->assertInertia(fn ($page) => $page
                ->component('Errors/Show')
                ->where('status', 500)
            );
    }

    public function test_service_unavailable_renders_inertia_error_page(): void
    {
        Route::get('/_test-http-503', fn () => abort(503));

        $this->get('/_test-http-503')
            ->assertStatus(503)
            ->assertInertia(fn ($page) => $page
                ->component('Errors/Show')
                ->where('status', 503)
            );
    }
}
