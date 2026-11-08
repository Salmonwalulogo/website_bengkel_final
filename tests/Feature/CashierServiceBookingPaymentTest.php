<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashierServiceBookingPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_can_record_payment_for_an_admin_confirmed_non_vehicle_service(): void
    {
        $cashier = User::factory()->create(['role' => 'kasir']);
        $customer = User::factory()->create(['role' => 'customer']);
        $service = Service::create([
            'name' => 'Rental Pickup',
            'category' => 'Transportasi',
            'harga' => 250000,
            'status' => 'active',
        ]);
        $booking = Booking::create([
            'user_id' => $customer->id,
            'service_id' => $service->id,
            'booking_date' => now()->addDay()->toDateString(),
            'booking_time' => '09:00',
            'request_details' => 'Antar barang ke lokasi acara.',
            'quoted_price' => 325000,
            'status' => 'confirmed',
        ]);

        $this->actingAs($cashier)
            ->post(route('kasir.orders.pay', $booking), ['payment_method' => 'transfer'])
            ->assertRedirect(route('kasir.orders'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('transactions', [
            'user_id' => $cashier->id,
            'customer_id' => $customer->id,
            'booking_id' => $booking->id,
            'total_payment' => 325000,
            'payment_method' => 'transfer',
        ]);
        $this->assertDatabaseHas('payments', [
            'amount' => 325000,
            'status' => 'paid',
        ]);
    }

    public function test_cashier_cannot_charge_an_unconfirmed_service_request(): void
    {
        $cashier = User::factory()->create(['role' => 'kasir']);
        $customer = User::factory()->create(['role' => 'customer']);
        $service = Service::create([
            'name' => 'Sewa Lampu',
            'category' => 'Penyewaan',
            'harga' => 100000,
            'status' => 'active',
        ]);
        $booking = Booking::create([
            'user_id' => $customer->id,
            'service_id' => $service->id,
            'booking_date' => now()->addDay()->toDateString(),
            'booking_time' => '09:00',
            'status' => 'pending',
        ]);

        $this->actingAs($cashier)
            ->from(route('kasir.orders'))
            ->post(route('kasir.orders.pay', $booking), ['payment_method' => 'cash'])
            ->assertRedirect(route('kasir.orders'))
            ->assertSessionHasErrors('booking');

        $this->assertDatabaseCount('transactions', 0);
        $this->assertDatabaseCount('payments', 0);
    }
}
