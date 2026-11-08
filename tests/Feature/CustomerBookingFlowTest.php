<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerBookingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_form_presents_general_services_without_requiring_a_vehicle(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $vehicle = Vehicle::create([
            'user_id' => $customer->id,
            'plate_number' => 'B 1234 ABC',
            'brand' => 'Honda',
            'model' => 'Vario',
        ]);
        Service::create([
            'name' => 'Servis Berkala',
            'description' => 'Pemeriksaan dan perawatan rutin kendaraan.',
            'harga' => 150000,
            'duration' => '2 jam',
            'status' => 'active',
        ]);

        $this->actingAs($customer)
            ->get(route('customer.booking.create'))
            ->assertOk()
            ->assertSee('Pilih layanan')
            ->assertSee('Servis Berkala')
            ->assertSee('Pemeriksaan dan perawatan rutin kendaraan.')
            ->assertSee('2 jam')
            ->assertSee('Tentukan jadwal')
            ->assertSee('Kendaraan (opsional)')
            ->assertSee(route('customer.bookings'));
    }

    public function test_customer_can_submit_booking_from_the_ordered_flow(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $vehicle = Vehicle::create([
            'user_id' => $customer->id,
            'plate_number' => 'B 5678 XYZ',
            'brand' => 'Yamaha',
            'model' => 'NMAX',
        ]);
        $service = Service::create([
            'name' => 'Ganti Oli',
            'harga' => 80000,
            'status' => 'active',
        ]);

        $this->actingAs($customer)
            ->post(route('customer.booking.store'), [
                'vehicle_id' => $vehicle->id,
                'service_id' => $service->id,
                'booking_date' => now()->addDay()->toDateString(),
                'booking_time' => '09:30',
                'request_details' => 'Periksa suara mesin',
            ])
            ->assertRedirect(route('customer.bookings'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('bookings', [
            'user_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'service_id' => $service->id,
            'booking_time' => '09:30',
            'request_details' => 'Periksa suara mesin',
        ]);
    }

    public function test_customer_can_request_a_service_without_registering_a_vehicle(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $service = Service::create([
            'name' => 'Sewa Tenda',
            'category' => 'Penyewaan',
            'harga' => 0,
            'status' => 'active',
        ]);

        $this->actingAs($customer)
            ->post(route('customer.booking.store'), [
                'service_id' => $service->id,
                'booking_date' => now()->addDay()->toDateString(),
                'booking_time' => '10:00',
                'request_details' => 'Tenda untuk acara keluarga, 2 hari.',
            ])
            ->assertRedirect(route('customer.bookings'));

        $this->assertDatabaseHas('bookings', [
            'user_id' => $customer->id,
            'service_id' => $service->id,
            'vehicle_id' => null,
            'request_details' => 'Tenda untuk acara keluarga, 2 hari.',
        ]);
    }

    public function test_service_request_requires_a_customer_account(): void
    {
        $this->get(route('customer.booking.create'))
            ->assertRedirect(route('login'));
    }

    public function test_public_service_catalog_comes_from_database_and_requires_customer_login_to_order(): void
    {
        $service = Service::create([
            'name' => 'Sewa Lampu Acara',
            'category' => 'Penyewaan',
            'harga' => 0,
            'status' => 'active',
        ]);

        $this->get(route('layanan'))
            ->assertOk()
            ->assertSee('Sewa Lampu Acara')
            ->assertSee('Daftar')
            ->assertSee(route('customer.booking.create', ['service' => $service->id]));
    }

    public function test_admin_must_set_a_quote_before_confirming_a_zero_price_service(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);
        $service = Service::create([
            'name' => 'Pangkas Rambut',
            'category' => 'Perawatan Pribadi',
            'harga' => 0,
            'status' => 'active',
        ]);
        $booking = $customer->bookings()->create([
            'service_id' => $service->id,
            'booking_date' => now()->addDay()->toDateString(),
            'booking_time' => '11:00',
            'status' => 'pending',
        ]);
        $payload = [
            'user_id' => $customer->id,
            'vehicle_id' => '',
            'service_id' => $service->id,
            'booking_date' => now()->addDay()->toDateString(),
            'booking_time' => '11:00',
            'request_details' => '',
            'quoted_price' => '',
            'notes' => '',
            'status' => 'confirmed',
        ];

        $this->actingAs($admin)
            ->from(route('admin.bookings.edit', $booking))
            ->put(route('admin.bookings.update', $booking), $payload)
            ->assertRedirect(route('admin.bookings.edit', $booking))
            ->assertSessionHasErrors('quoted_price');

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'pending']);

        $payload['quoted_price'] = 45000;
        $this->actingAs($admin)
            ->put(route('admin.bookings.update', $booking), $payload)
            ->assertRedirect(route('admin.bookings.index'));

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'confirmed',
            'quoted_price' => 45000,
        ]);
    }

    public function test_removing_a_customer_vehicle_keeps_its_service_request(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $vehicle = Vehicle::create([
            'user_id' => $customer->id,
            'plate_number' => 'B 4321 XYZ',
            'brand' => 'Toyota',
            'model' => 'Pickup',
        ]);
        $service = Service::create([
            'name' => 'Cuci Mobil',
            'category' => 'Otomotif',
            'harga' => 0,
            'status' => 'active',
        ]);
        $booking = $customer->bookings()->create([
            'vehicle_id' => $vehicle->id,
            'service_id' => $service->id,
            'booking_date' => now()->addDay()->toDateString(),
            'booking_time' => '11:30',
            'status' => 'pending',
        ]);

        $this->actingAs($customer)
            ->delete(route('customer.vehicles.destroy', $vehicle))
            ->assertRedirect();

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'vehicle_id' => null,
        ]);
    }
}
