<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserAccountStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_disable_and_reenable_a_customer_and_disabling_revokes_api_tokens(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);
        $token = $customer->createToken('customer-app');

        $this->actingAs($admin)
            ->patch(route('admin.users.updateStatus', $customer), ['is_active' => false])
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', ['id' => $customer->id, 'is_active' => false]);
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $token->accessToken->id]);

        $this->patch(route('admin.users.updateStatus', $customer), ['is_active' => true])
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', ['id' => $customer->id, 'is_active' => true]);
    }

    public function test_admin_user_list_shows_customer_account_status_controls(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->create(['role' => 'customer']);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Nonaktifkan akun')
            ->assertSee('Aktif');
    }

    public function test_admin_cannot_change_status_of_non_customer_accounts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $cashier = User::factory()->create(['role' => 'kasir']);

        $this->actingAs($admin)
            ->patch(route('admin.users.updateStatus', $cashier), ['is_active' => false])
            ->assertNotFound();

        $this->assertDatabaseHas('users', ['id' => $cashier->id, 'is_active' => true]);
    }

    public function test_disabled_customer_cannot_login_via_web_or_api(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'is_active' => false,
        ]);

        $this->post('/login', [
            'email' => $customer->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();

        $this->postJson('/api/login', [
            'email' => $customer->email,
            'password' => 'password',
        ])->assertUnprocessable();
    }

    public function test_disabled_customer_is_blocked_from_existing_web_session_and_api_token(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'is_active' => false,
        ]);
        $token = $customer->createToken('customer-app')->plainTextToken;

        $this->actingAs($customer)
            ->get(route('dashboard'))
            ->assertRedirect(route('login'));

        $this->assertGuest();

        $this->withToken($token)
            ->getJson('/api/me')
            ->assertForbidden()
            ->assertJson(['message' => 'Akun ini telah dinonaktifkan.']);
    }
}
