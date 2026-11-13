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
            'name' => 'Sewa Peralatan',
            'category' => 'Penyewaan',
            'description' => 'Penyediaan peralatan untuk kebutuhan acara.',
            'harga' => 150000,
            'duration' => '2 jam',
            'status' => 'active',
        ]);

        $response = $this->actingAs($customer)
            ->get(route('customer.booking.create'))
            ->assertOk()
            ->assertSee('Pilih layanan')
            ->assertSee('Sewa Peralatan')
            ->assertSee('Penyediaan peralatan untuk kebutuhan acara.')
            ->assertSee('2 jam')
            ->assertSee('Tentukan jadwal')
            ->assertSee('Kendaraan (opsional)')
            ->assertSee('data-automotive="false"', false)
            ->assertSee(route('customer.bookings'))
            ->assertSee(route('customer.dashboard'))
            ->assertSee('Kembali');

        $this->assertSame(1, preg_match('/data-service-vehicle-fields\s+hidden/', $response->getContent()));
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
            'category' => 'Otomotif',
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

    public function test_vehicle_picker_is_visible_when_an_automotive_service_is_preselected(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $automotiveService = Service::create([
            'name' => 'Servis Berkala',
            'category' => 'Otomotif',
            'harga' => 150000,
            'status' => 'active',
        ]);

        $response = $this->actingAs($customer)
            ->get(route('customer.booking.create', ['service' => $automotiveService->id]))
            ->assertOk()
            ->assertSee('data-automotive="true"', false)
            ->assertSee('name="vehicle_id"', false);

        $this->assertSame(0, preg_match('/data-service-vehicle-fields\s+hidden/', $response->getContent()));
    }

    public function test_new_service_booking_creates_an_unread_notification_for_each_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $secondAdmin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);
        $service = Service::create([
            'name' => 'Sewa Tenda',
            'category' => 'Penyewaan',
            'harga' => 0,
            'status' => 'active',
        ]);
        $bookingDate = now()->addDay()->toDateString();

        $this->actingAs($customer)->post(route('customer.booking.store'), [
            'service_id' => $service->id,
            'booking_date' => $bookingDate,
            'booking_time' => '09:30',
            'request_details' => 'Tenda untuk acara keluarga',
        ])->assertRedirect(route('customer.bookings'));

        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin->id,
            'title' => 'Permintaan layanan baru',
            'message' => "{$customer->name} memesan layanan Sewa Tenda untuk " . \Illuminate\Support\Carbon::parse($bookingDate)->format('d/m/Y') . ' pukul 09:30.',
            'type' => 'booking',
            'is_read' => false,
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $secondAdmin->id,
            'title' => 'Permintaan layanan baru',
            'type' => 'booking',
            'is_read' => false,
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

    public function test_non_automotive_service_ignores_a_submitted_vehicle(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $vehicle = Vehicle::create([
            'user_id' => $customer->id,
            'plate_number' => 'B 1234 ABC',
            'brand' => 'Honda',
            'model' => 'Vario',
        ]);
        $service = Service::create([
            'name' => 'Sewa Tenda',
            'category' => 'Penyewaan',
            'harga' => 0,
            'status' => 'active',
        ]);

        $this->actingAs($customer)
            ->post(route('customer.booking.store'), [
                'service_id' => $service->id,
                'vehicle_id' => $vehicle->id,
                'booking_date' => now()->addDay()->toDateString(),
                'booking_time' => '10:00',
            ])
            ->assertRedirect(route('customer.bookings'));

        $this->assertDatabaseHas('bookings', [
            'user_id' => $customer->id,
            'service_id' => $service->id,
            'vehicle_id' => null,
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
            ->assertSee(route('home'))
            ->assertSee('Kembali')
            ->assertSee('Login')
            ->assertDontSee('Daftar')
            ->assertDontSee('Register')
            ->assertSee(route('customer.booking.create', ['service' => $service->id]));
    }

    public function test_customer_is_redirected_to_their_service_catalog_from_the_public_services_page(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)
            ->get(route('layanan'))
            ->assertRedirect(route('customer.services'));
    }

    public function test_customer_can_browse_services_inside_their_dashboard(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $service = Service::create([
            'name' => 'Servis Berkala',
            'category' => 'Otomotif',
            'description' => 'Pemeriksaan dan perawatan kendaraan.',
            'harga' => 150000,
            'duration' => '2 jam',
            'status' => 'active',
        ]);

        $this->actingAs($customer)
            ->get(route('customer.services'))
            ->assertOk()
            ->assertSee('Semua Layanan')
            ->assertSee('AREA PELANGGAN')
            ->assertSee('Servis Berkala')
            ->assertSee('Pemeriksaan dan perawatan kendaraan.')
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
