<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_log_in_with_username_and_use_the_token(): void
    {
        $user = User::factory()->create([
            'username' => 'admin-test',
            'role' => 'admin',
            'password' => 'secret-password',
        ]);

        $response = $this->postJson('/api/login', [
            'username' => $user->username,
            'password' => 'secret-password',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('user.username', 'admin-test')
            ->assertJsonPath('user.role', 'admin')
            ->assertJsonStructure(['token', 'user' => ['id', 'email', 'username', 'role']]);

        $token = $response->json('token');

        $this->withToken($token)
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('user.id', $user->id);

        $this->withToken($token)
            ->postJson('/api/logout')
            ->assertOk();

        $this->assertNull(PersonalAccessToken::findToken($token));
    }

    public function test_user_can_log_in_with_email(): void
    {
        $user = User::factory()->create([
            'email' => 'kasir-test@example.test',
            'username' => 'kasir-test',
            'role' => 'kasir',
            'password' => 'secret-password',
        ]);

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'secret-password',
        ])
            ->assertOk()
            ->assertJsonPath('user.username', 'kasir-test')
            ->assertJsonPath('user.role', 'kasir')
            ->assertJsonStructure(['token']);
    }

    public function test_api_login_rejects_invalid_credentials(): void
    {
        $user = User::factory()->create(['username' => 'admin-test']);

        $this->postJson('/api/login', [
            'username' => $user->username,
            'password' => 'wrong-password',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('errors.login.0', 'Kredensial tidak valid.');
    }

    public function test_api_registration_always_creates_a_customer(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Public Registration',
            'email' => 'public-registration@example.test',
            'username' => 'public-registration',
            'role' => 'admin',
            'password' => 'secret-password',
        ]);

        $response->assertCreated()->assertJsonPath('user.role', 'customer');
        $this->assertDatabaseHas('users', [
            'email' => 'public-registration@example.test',
            'role' => 'customer',
        ]);
    }

    public function test_api_registration_rejects_passwords_shorter_than_eight_characters(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Short Password',
            'email' => 'short-password@example.test',
            'password' => '1234567',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('password');

        $this->assertDatabaseMissing('users', ['email' => 'short-password@example.test']);
    }

    public function test_protected_api_routes_require_a_valid_token(): void
    {
        $this->getJson('/api/bookings')->assertUnauthorized();
        $this->getJson('/api/me')->assertUnauthorized();
    }
}
