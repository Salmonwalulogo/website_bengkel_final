<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashierServiceBookingPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_can_record_payment_for_an_admin_confirmed_non_vehicle_service(): void
    {
        $cashier = User::factory()->create(['role' => 'kasir']);
        $admin = User::factory()->create(['role' => 'admin']);
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
        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin->id,
            'title' => 'Pembayaran dikonfirmasi kasir',
        ]);
    }

    public function test_cashier_can_confirm_payment_for_a_completed_service_booking(): void
    {
        $cashier = User::factory()->create(['role' => 'kasir']);
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);
        $service = Service::create([
            'name' => 'Servis Selesai',
            'category' => 'Servis',
            'harga' => 150000,
            'status' => 'active',
        ]);
        $booking = Booking::create([
            'user_id' => $customer->id,
            'service_id' => $service->id,
            'booking_date' => now()->toDateString(),
            'booking_time' => '09:00',
            'quoted_price' => 175000,
            'status' => 'completed',
        ]);

        $this->actingAs($cashier)
            ->get(route('kasir.orders'))
            ->assertOk()
            ->assertSee('Konfirmasi pembayaran · Rp 175.000')
            ->assertSee(route('kasir.orders.pay', $booking));

        $this->post(route('kasir.orders.pay', $booking), ['payment_method' => 'cash'])
            ->assertRedirect(route('kasir.orders'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('payments', [
            'amount' => 175000,
            'status' => 'paid',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin->id,
            'title' => 'Pembayaran dikonfirmasi kasir',
        ]);
    }

    public function test_cashier_can_confirm_an_existing_pending_payment_for_a_service_booking(): void
    {
        $cashier = User::factory()->create(['role' => 'kasir']);
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);
        $service = Service::create([
            'name' => 'Servis Menunggu Pembayaran',
            'category' => 'Servis',
            'harga' => 150000,
            'status' => 'active',
        ]);
        $booking = Booking::create([
            'user_id' => $customer->id,
            'service_id' => $service->id,
            'booking_date' => now()->toDateString(),
            'booking_time' => '09:00',
            'quoted_price' => 150000,
            'status' => 'completed',
        ]);
        $transaction = Transaction::create([
            'user_id' => $cashier->id,
            'customer_id' => $customer->id,
            'booking_id' => $booking->id,
            'subtotal' => 150000,
            'total_payment' => 150000,
            'payment_method' => 'transfer',
            'status' => 'pending',
        ]);
        $payment = Payment::create([
            'transaction_id' => $transaction->id,
            'payment_code' => 'PAY-SERVICE-PENDING',
            'amount' => 150000,
            'payment_date' => now(),
            'status' => 'pending',
        ]);

        $this->actingAs($cashier)
            ->post(route('kasir.orders.pay', $booking), ['payment_method' => 'cash'])
            ->assertRedirect(route('kasir.orders'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'paid']);
        $this->assertDatabaseHas('transactions', ['id' => $transaction->id, 'status' => 'completed']);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin->id,
            'title' => 'Pembayaran dikonfirmasi kasir',
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

    public function test_cashier_cannot_record_service_payment_with_debit_or_credit(): void
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
            'status' => 'confirmed',
            'quoted_price' => 250000,
        ]);

        foreach (['debit', 'kredit'] as $paymentMethod) {
            $this->actingAs($cashier)
                ->from(route('kasir.orders'))
                ->post(route('kasir.orders.pay', $booking), ['payment_method' => $paymentMethod])
                ->assertRedirect(route('kasir.orders'))
                ->assertSessionHasErrors('payment_method');
        }

        $this->assertDatabaseCount('transactions', 0);
        $this->assertDatabaseCount('payments', 0);
    }
}
