<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerBookingPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_submit_qris_proof_for_a_confirmed_booking_and_cashier_can_verify_it(): void
    {
        Storage::fake('local');
        $customer = User::factory()->create(['role' => 'customer']);
        $cashier = User::factory()->create(['role' => 'kasir']);
        $booking = $this->createBooking($customer);
        DB::table('settings')->insert([
            'key' => 'qris_image',
            'value' => 'payment/qris.png',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($customer)
            ->get(route('customer.bookings.payment', $booking))
            ->assertOk()
            ->assertSee('175.000')
            ->assertSee('QRIS');

        $this->actingAs($customer)
            ->post(route('customer.bookings.payment.store', $booking), [
                'payment_method' => 'qris',
                'proof_image' => UploadedFile::fake()->image('proof.png'),
            ])
            ->assertRedirect(route('customer.bookings'))
            ->assertSessionHas('success');

        $transaction = Transaction::query()->where('booking_id', $booking->id)->firstOrFail();
        $payment = Payment::query()->where('transaction_id', $transaction->id)->firstOrFail();
        $this->assertSame('pending', $transaction->status);
        $this->assertSame('qris', $transaction->payment_method);
        $this->assertSame('pending', $payment->status);
        $this->assertNull($payment->payment_date);
        Storage::disk('local')->assertExists($payment->proof_image);

        $this->actingAs($cashier)
            ->get(route('kasir.payments'))
            ->assertOk()
            ->assertSee(route('kasir.payments.proof', $payment));

        $this->get(route('kasir.payments.proof', $payment))->assertOk();

        $this->actingAs($cashier)
            ->patch(route('kasir.payments.confirm', $payment))
            ->assertRedirect(route('kasir.payments'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'paid']);
        $this->assertDatabaseHas('transactions', ['id' => $transaction->id, 'status' => 'completed']);
    }

    public function test_customer_transfer_payment_requires_configured_account_details(): void
    {
        Storage::fake('local');
        $customer = User::factory()->create(['role' => 'customer']);
        $booking = $this->createBooking($customer);

        $this->actingAs($customer)
            ->from(route('customer.bookings.payment', $booking))
            ->post(route('customer.bookings.payment.store', $booking), [
                'payment_method' => 'transfer',
                'proof_image' => UploadedFile::fake()->image('proof.png'),
            ])
            ->assertRedirect(route('customer.bookings.payment', $booking))
            ->assertSessionHasErrors('payment_method');

        $this->assertDatabaseCount('transactions', 0);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_customer_cannot_submit_cash_as_an_online_payment_or_pay_another_customers_booking(): void
    {
        Storage::fake('local');
        $customer = User::factory()->create(['role' => 'customer']);
        $otherCustomer = User::factory()->create(['role' => 'customer']);
        $booking = $this->createBooking($otherCustomer);

        $this->actingAs($customer)
            ->post(route('customer.bookings.payment.store', $booking), [
                'payment_method' => 'qris',
                'proof_image' => UploadedFile::fake()->image('proof.png'),
            ])
            ->assertNotFound();

        $this->actingAs($otherCustomer)
            ->from(route('customer.bookings.payment', $booking))
            ->post(route('customer.bookings.payment.store', $booking), [
                'payment_method' => 'cash',
                'proof_image' => UploadedFile::fake()->image('proof.png'),
            ])
            ->assertRedirect(route('customer.bookings.payment', $booking))
            ->assertSessionHasErrors('payment_method');

        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_admin_can_configure_transfer_details_and_qris_image(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.settings.general'))
            ->assertOk()
            ->assertSee('Pembayaran Online');

        $this->actingAs($admin)
            ->put(route('admin.settings.general.update'), [
                'nama_bengkel' => 'UD Rumah Daun',
                'bank_name' => 'Bank Contoh',
                'bank_account_number' => '1234567890',
                'bank_account_name' => 'UD Rumah Daun',
                'qris_image' => UploadedFile::fake()->image('qris.png'),
            ])
            ->assertRedirect();

        $qrisPath = DB::table('settings')->where('key', 'qris_image')->value('value');
        $this->assertSame('Bank Contoh', DB::table('settings')->where('key', 'bank_name')->value('value'));
        $this->assertSame('1234567890', DB::table('settings')->where('key', 'bank_account_number')->value('value'));
        $this->assertSame('UD Rumah Daun', DB::table('settings')->where('key', 'bank_account_name')->value('value'));
        Storage::disk('public')->assertExists($qrisPath);
    }

    public function test_customer_can_submit_product_order_payment_proof_then_cashier_can_verify_it(): void
    {
        Storage::fake('local');
        $customer = User::factory()->create(['role' => 'customer']);
        $cashier = User::factory()->create(['role' => 'kasir']);
        $category = Category::create(['name' => 'Suku Cadang']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Oli Mesin',
            'harga' => 85000,
            'stok' => 3,
            'status' => 'active',
            'is_active' => true,
            'visibility' => Product::VISIBILITY_BOTH,
        ]);
        DB::table('settings')->insert([
            'key' => 'qris_image',
            'value' => 'payment/qris.png',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($customer)
            ->post(route('cart.add', $product), ['quantity' => 1])
            ->assertRedirect();
        $this->post(route('cart.checkout'), ['payment_method' => 'cash'])
            ->assertRedirect(route('customer.transactions'));

        $transaction = Transaction::query()->where('customer_id', $customer->id)->firstOrFail();
        $payment = $transaction->payment;

        $this->get(route('customer.transactions'))
            ->assertOk()
            ->assertSee(route('customer.transactions.payment', $transaction));
        $this->get(route('customer.transactions.payment', $transaction))
            ->assertOk()
            ->assertSee('Oli Mesin')
            ->assertSee('85.000');

        $this->post(route('customer.transactions.payment.store', $transaction), [
            'payment_method' => 'qris',
            'proof_image' => UploadedFile::fake()->image('product-proof.png'),
        ])
            ->assertRedirect(route('customer.transactions'))
            ->assertSessionHas('success');

        $this->assertSame('qris', $transaction->fresh()->payment_method);
        $this->assertSame('pending', $payment->fresh()->status);
        Storage::disk('local')->assertExists($payment->fresh()->proof_image);

        $this->actingAs($cashier)
            ->patch(route('kasir.payments.confirm', $payment))
            ->assertRedirect(route('kasir.payments'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'paid']);
        $this->assertDatabaseHas('transactions', ['id' => $transaction->id, 'status' => 'completed']);
    }

    public function test_cashier_cannot_confirm_customer_transfer_order_without_payment_proof(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $cashier = User::factory()->create(['role' => 'kasir']);
        $category = Category::create(['name' => 'Suku Cadang']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Filter Oli',
            'harga' => 45000,
            'stok' => 2,
            'status' => 'active',
            'is_active' => true,
            'visibility' => Product::VISIBILITY_BOTH,
        ]);

        $this->actingAs($customer)->post(route('cart.add', $product), ['quantity' => 1]);
        $this->post(route('cart.checkout'), ['payment_method' => 'transfer']);
        $payment = Payment::query()->firstOrFail();

        $this->actingAs($cashier)
            ->from(route('kasir.payments'))
            ->patch(route('kasir.payments.confirm', $payment))
            ->assertRedirect(route('kasir.payments'))
            ->assertSessionHasErrors('proof_image');

        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'pending']);
        $this->assertDatabaseHas('transactions', ['id' => $payment->transaction_id, 'status' => 'pending']);
    }

    private function createBooking(User $customer): Booking
    {
        $service = Service::create([
            'name' => 'Servis Berkala',
            'category' => 'Servis',
            'harga' => 150000,
            'status' => 'active',
        ]);

        return Booking::create([
            'user_id' => $customer->id,
            'service_id' => $service->id,
            'booking_date' => now()->addDay()->toDateString(),
            'booking_time' => '09:00',
            'quoted_price' => 175000,
            'status' => 'confirmed',
        ]);
    }
}
