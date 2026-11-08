<?php

namespace Tests\Feature\Api;

use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_create_and_list_bookings(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $vehicle = Vehicle::create([
            'user_id' => $customer->id,
            'plate_number' => 'N 1111 BK',
            'brand' => 'Honda',
            'model' => 'Beat',
            'year' => 2021,
            'color' => 'Hitam',
        ]);
        $service = Service::create([
            'name' => 'Servis Berkala',
            'description' => 'Servis rutin',
            'harga' => 50000,
            'duration' => '30m',
            'status' => 'active',
        ]);

        $token = $customer->createToken('test')->plainTextToken;

        $storeResponse = $this->withToken($token)->postJson('/api/bookings', [
            'vehicle_id' => $vehicle->id,
            'service_id' => $service->id,
            'booking_date' => now()->addDay()->toDateString(),
            'booking_time' => '10:00',
            'complaint' => 'Ganti oli',
        ]);

        $storeResponse->assertCreated()->assertJsonPath('data.status', 'pending');

        $listResponse = $this->withToken($token)->getJson('/api/bookings');
        $listResponse->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_customer_can_create_a_general_service_request_without_a_vehicle(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $service = Service::create([
            'name' => 'Sewa Tenda',
            'category' => 'Penyewaan',
            'harga' => 0,
            'status' => 'active',
        ]);
        $token = $customer->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/bookings', [
            'service_id' => $service->id,
            'booking_date' => now()->addDay()->toDateString(),
            'booking_time' => '10:00',
            'request_details' => 'Tenda untuk acara keluarga',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.vehicle_id', null)
            ->assertJsonPath('data.request_details', 'Tenda untuk acara keluarga')
            ->assertJsonPath('data.status', 'pending');
        $this->assertDatabaseHas('bookings', [
            'user_id' => $customer->id,
            'vehicle_id' => null,
            'service_id' => $service->id,
        ]);
    }

    public function test_admin_or_cashier_can_update_booking_status(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $cashier = User::factory()->create(['role' => 'kasir']);

        $vehicle = Vehicle::create([
            'user_id' => $customer->id,
            'plate_number' => 'N 2222 BK',
            'brand' => 'Yamaha',
            'model' => 'NMAX',
            'year' => 2022,
            'color' => 'Putih',
        ]);
        $service = Service::create([
            'name' => 'Ganti Oli',
            'harga' => 45000,
            'status' => 'active',
        ]);
        $booking = Booking::create([
            'user_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'service_id' => $service->id,
            'booking_date' => now()->toDateString(),
            'booking_time' => '09:00',
            'complaint' => 'Rutin',
            'status' => 'pending',
        ]);

        $token = $cashier->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->patchJson("/api/bookings/{$booking->id}/status", [
            'status' => 'confirmed',
        ]);

        $response->assertOk()->assertJsonPath('data.status', 'confirmed');
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'confirmed']);
    }

    public function test_customer_cannot_book_another_customers_vehicle(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $otherCustomer = User::factory()->create(['role' => 'customer']);
        $vehicle = Vehicle::create([
            'user_id' => $otherCustomer->id,
            'plate_number' => 'N 4444 BK',
            'brand' => 'Honda',
            'model' => 'Beat',
            'year' => 2021,
            'color' => 'Hitam',
        ]);
        $service = Service::create([
            'name' => 'Servis Berkala',
            'harga' => 50000,
            'status' => 'active',
        ]);
        $token = $customer->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/bookings', [
                'vehicle_id' => $vehicle->id,
                'service_id' => $service->id,
                'booking_date' => now()->addDay()->toDateString(),
                'booking_time' => '10:00',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('vehicle_id');

        $this->assertDatabaseCount('bookings', 0);
    }
}
