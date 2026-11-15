<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Notification;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashierNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_can_open_a_booking_detail_from_their_order_list(): void
    {
        $cashier = User::factory()->create(['role' => 'kasir']);
        $customer = User::factory()->create(['role' => 'customer']);
        $service = Service::create([
            'name' => 'Servis Berkala',
            'category' => 'Servis',
            'harga' => 150000,
            'status' => 'active',
        ]);
        $booking = Booking::create([
            'user_id' => $customer->id,
            'service_id' => $service->id,
            'booking_date' => now()->addDay()->toDateString(),
            'booking_time' => '09:00',
            'request_details' => 'Periksa rem depan',
            'status' => 'confirmed',
        ]);

        $this->actingAs($cashier)
            ->get(route('kasir.orders'))
            ->assertOk()
            ->assertSee(route('kasir.orders.show', $booking))
            ->assertSee('Melihat Detail');

        $this->get(route('kasir.orders.show', $booking))
            ->assertOk()
            ->assertSee('Detail Booking')
            ->assertSee($customer->name)
            ->assertSee('Servis Berkala')
            ->assertSee('Periksa rem depan');

        $this->actingAs($customer)
            ->get(route('kasir.orders.show', $booking))
            ->assertForbidden();
    }

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

    public function test_cashier_can_view_and_mark_only_own_order_notifications_as_read(): void
    {
        $cashier = User::factory()->create(['role' => 'kasir']);
        $anotherCashier = User::factory()->create(['role' => 'kasir']);
        $notification = Notification::create([
            'user_id' => $cashier->id,
            'title' => 'Permintaan layanan baru',
            'message' => 'Pelanggan memesan layanan.',
            'type' => 'booking',
            'is_read' => false,
        ]);
        $anotherNotification = Notification::create([
            'user_id' => $anotherCashier->id,
            'title' => 'Notifikasi kasir lain',
            'message' => 'Tidak boleh terlihat.',
            'type' => 'booking',
            'is_read' => false,
        ]);

        $this->actingAs($cashier)
            ->get(route('kasir.notifications'))
            ->assertOk()
            ->assertSee('Permintaan layanan baru')
            ->assertDontSee('Notifikasi kasir lain')
            ->assertSee(route('kasir.notifications.read', $notification));

        $this->post(route('kasir.notifications.read', $notification))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('notifications', ['id' => $notification->id, 'is_read' => true]);
        $this->assertDatabaseHas('notifications', ['id' => $anotherNotification->id, 'is_read' => false]);
    }
}
