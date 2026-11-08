<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashierNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_dashboard_links_to_available_cashier_pages(): void
    {
        $cashier = User::factory()->create(['role' => 'kasir']);

        $this->actingAs($cashier)
            ->get(route('kasir.dashboard'))
            ->assertOk()
            ->assertSee('Pesanan Layanan')
            ->assertSee('Penjualan Produk')
            ->assertSee('berbagai layanan')
            ->assertSee(route('kasir.orders'))
            ->assertSee(route('kasir.pos'))
            ->assertSee(route('kasir.payments'))
            ->assertSee(route('kasir.invoices'))
            ->assertSee(route('profile.edit'));
    }
}
