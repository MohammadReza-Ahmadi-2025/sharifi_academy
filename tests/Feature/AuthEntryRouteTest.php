<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthEntryRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_redirects_to_login(): void
    {
        $this->get('/register')
            ->assertRedirect('/login');
    }

    public function test_login_shows_otp_auth_page(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Auth/OtpAuth'));
    }
}
