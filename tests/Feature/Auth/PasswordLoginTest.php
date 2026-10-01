<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_mobile_and_password(): void
    {
        $user = User::factory()->create([
            'mobile' => '09125555555',
            'password' => Hash::make('secret-pass'),
        ]);

        $this->post('/auth/password/login', [
            'phone' => '09125555555',
            'password' => 'secret-pass',
        ])->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
    }

    public function test_password_login_does_not_create_new_user(): void
    {
        $this->post('/auth/password/login', [
            'phone' => '09126666666',
            'password' => 'secret-pass',
        ])->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', ['mobile' => '09126666666']);
        $this->assertFalse(Auth::check());
    }

    public function test_wrong_password_is_rejected(): void
    {
        User::factory()->create([
            'mobile' => '09127777777',
            'password' => Hash::make('correct'),
        ]);

        $this->post('/auth/password/login', [
            'phone' => '09127777777',
            'password' => 'wrong',
        ])->assertSessionHasErrors('password');

        $this->assertFalse(Auth::check());
    }
}
