<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_web_admin_routes_require_admin_role(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');

        $this->actingAs(User::factory()->create(['role' => 'kasir']))
            ->get('/admin/dashboard')
            ->assertForbidden();
    }

    public function test_web_cashier_and_customer_routes_reject_other_roles(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($customer)
            ->get('/kasir/dashboard')
            ->assertForbidden();

        $this->actingAs($admin)
            ->get('/customer/dashboard')
            ->assertForbidden();
    }

    public function test_admin_dashboard_uses_house_brand_and_grouped_navigation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('UD Rumah Daun')
            ->assertSee('Data Usaha')
            ->assertSee('Operasional')
            ->assertSee('Pengelolaan')
            ->assertSee('Identitas & Logo', false);
    }

    public function test_customer_dashboard_points_to_general_service_requests(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)
            ->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSee('Lihat Layanan')
            ->assertSee('Pesan Layanan')
            ->assertSee(route('customer.services'))
            ->assertSee(route('customer.bookings'));
    }
}
