<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_add_a_product_to_the_cart(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $product = $this->createProduct();

        $response = $this->actingAs($customer)->post(route('cart.add', $product), [
            'quantity' => 2,
            'vehicle_brand' => 'Honda Vario',
        ]);

        $response
            ->assertRedirect()
            ->assertSessionHas('cart', [$product->id => 2]);

        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee($product->name)
            ->assertSee('value="Honda Vario"', false)
            ->assertSee('href="' . route('customer.dashboard') . '"', false)
            ->assertSee('Kembali')
            ->assertSee('2');
    }

    public function test_customer_cannot_add_a_nonpositive_quantity(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $product = $this->createProduct();

        $this->actingAs($customer)
            ->post(route('cart.add', $product), [
                'quantity' => 0,
                'vehicle_brand' => 'Honda Vario',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('quantity')
            ->assertSessionMissing('cart');
    }

    public function test_customer_dashboard_shows_a_link_to_the_cart_with_its_item_count(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $product = $this->createProduct();

        $this->actingAs($customer)
            ->withSession(['cart' => [$product->id => 2]])
            ->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSee(route('cart.index'))
            ->assertSee('Keranjang (2)');
    }

    public function test_customer_product_catalog_uses_its_own_dashboard_view(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $product = $this->createProduct();
        Vehicle::create([
            'user_id' => $customer->id,
            'plate_number' => 'DH 1234 AB',
            'brand' => 'Honda',
            'model' => 'Vario',
        ]);

        $this->actingAs($customer)
            ->get(route('customer.products'))
            ->assertOk()
            ->assertSee('Katalog Produk')
            ->assertSee($product->name)
            ->assertSee('data-bs-toggle="modal"', false)
            ->assertSee('name="quantity"', false)
            ->assertSee('Kendaraan untuk produk ini')
            ->assertSee('Honda Vario (DH 1234 AB)')
            ->assertDontSee('Produk <span>Kami</span>', false);

        $this->get(route('produk'))
            ->assertOk()
            ->assertSee('Produk <span>Kami</span>', false)
            ->assertSee('Honda Vario (DH 1234 AB)')
            ->assertSee($product->name);
    }

    public function test_customer_can_buy_a_product_without_changing_the_cart(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $product = $this->createProduct(['harga' => 25000, 'stok' => 5]);
        $otherProduct = $this->createProduct(['name' => 'Busi Motor', 'stok' => 3]);
        $cart = [$otherProduct->id => 1];

        $this->actingAs($customer)
            ->withSession(['cart' => $cart])
            ->get(route('cart.buy-now', $product))
            ->assertOk()
            ->assertSee('Beli Sekarang')
            ->assertSee(route('customer.products'))
            ->assertSee('Kembali ke produk');

        $response = $this->post(route('cart.buy-now.checkout', $product), [
            'quantity' => 2,
            'payment_method' => 'qris',
            'vehicle_brand' => 'Honda Vario 160',
        ]);

        $response
            ->assertRedirect(route('customer.transactions'))
            ->assertSessionHas('cart', $cart);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $customer->id,
            'total_payment' => 50000,
            'payment_method' => 'qris',
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('transaction_details', [
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 25000,
            'vehicle_info' => 'Honda Vario 160',
        ]);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stok' => 3,
        ]);
        $this->assertDatabaseHas('products', [
            'id' => $otherProduct->id,
            'stok' => 3,
        ]);
    }

    public function test_buy_now_rejects_a_quantity_above_available_stock(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $product = $this->createProduct(['stok' => 1]);

        $this->actingAs($customer)
            ->post(route('cart.buy-now.checkout', $product), [
                'quantity' => 2,
                'payment_method' => 'cash',
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseCount('transactions', 0);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stok' => 1,
        ]);
    }

    public function test_customer_can_checkout_the_cart_with_a_payment_method(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $product = $this->createProduct(['harga' => 18000, 'stok' => 4]);

        $this->actingAs($customer)->post(route('cart.add', $product), [
            'quantity' => 2,
            'vehicle_brand' => 'Honda Vario',
        ]);

        $response = $this->post(route('cart.checkout'), [
            'payment_method' => 'transfer',
            'vehicle_brand' => [$product->id => 'Honda Vario'],
        ]);

        $response
            ->assertRedirect(route('customer.transactions'))
            ->assertSessionMissing('cart');

        $this->assertDatabaseHas('transactions', [
            'user_id' => $customer->id,
            'total_payment' => 36000,
            'payment_method' => 'transfer',
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stok' => 2,
        ]);
        $this->assertDatabaseHas('transaction_details', [
            'product_id' => $product->id,
            'vehicle_info' => 'Honda Vario',
        ]);
    }

    public function test_automotive_products_can_be_added_without_vehicle_information(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $product = $this->createProduct();

        $this->actingAs($customer)
            ->post(route('cart.add', $product), ['quantity' => 1])
            ->assertRedirect()
            ->assertSessionHas('cart', [$product->id => 1]);

        $this->post(route('cart.checkout'), ['payment_method' => 'cash'])
            ->assertRedirect(route('customer.transactions'));

        $this->assertDatabaseHas('transaction_details', [
            'product_id' => $product->id,
            'vehicle_info' => null,
        ]);
    }

    public function test_automotive_vehicle_information_remains_optional_when_added_from_public_catalog(): void
    {
        $product = $this->createProduct();

        $this->get(route('produk'))
            ->assertOk()
            ->assertSee('Merek dan model kendaraan')
            ->assertSee('Kendaraan bersifat opsional');

        $this->post(route('cart.add', $product), ['quantity' => 1])
            ->assertRedirect()
            ->assertSessionHas('cart', [$product->id => 1]);
    }

    public function test_automotive_products_can_still_include_optional_vehicle_information(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $product = $this->createProduct();

        $this->actingAs($customer)->post(route('cart.add', $product), [
            'quantity' => 1,
            'vehicle_brand' => 'Yamaha NMAX',
        ])->assertRedirect()->assertSessionHas('cart', [$product->id => 1]);
        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee("vehicle_brand[{$product->id}]", false)
            ->assertSee("vehicle_id[{$product->id}]", false)
            ->assertSee('value="Yamaha NMAX"', false);

        $this->assertDatabaseCount('transactions', 0);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stok' => 10]);

        $this->post(route('cart.checkout'), ['payment_method' => 'cash'])
            ->assertRedirect(route('customer.transactions'));

        $this->assertDatabaseHas('transaction_details', [
            'product_id' => $product->id,
            'vehicle_info' => 'Yamaha NMAX',
        ]);

        $this->actingAs($customer)->get(route('customer.transactions'))
            ->assertOk()
            ->assertSee('Kendaraan: Yamaha NMAX');

        $admin = User::factory()->create(['role' => 'admin']);
        $transaction = \App\Models\Transaction::where('customer_id', $customer->id)->latest('id')->firstOrFail();
        $this->actingAs($admin)->get(route('admin.transactions.show', $transaction))
            ->assertOk()
            ->assertSee('Kendaraan: Yamaha NMAX');
    }

    public function test_customer_can_select_a_registered_vehicle_for_an_automotive_product(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $otherCustomer = User::factory()->create(['role' => 'customer']);
        $product = $this->createProduct();
        $vehicle = Vehicle::create([
            'user_id' => $customer->id,
            'plate_number' => 'DH 1234 AB',
            'brand' => 'Honda',
            'model' => 'Beat',
        ]);
        $otherVehicle = Vehicle::create([
            'user_id' => $otherCustomer->id,
            'plate_number' => 'DH 5678 CD',
            'brand' => 'Yamaha',
            'model' => 'NMAX',
        ]);

        $this->actingAs($customer)->post(route('cart.add', $product), [
            'quantity' => 1,
            'vehicle_id' => $vehicle->id,
        ]);
        $this->post(route('cart.checkout'), [
            'payment_method' => 'cash',
            'vehicle_id' => [$product->id => $vehicle->id],
        ])->assertRedirect(route('customer.transactions'));

        $this->assertDatabaseHas('transaction_details', [
            'product_id' => $product->id,
            'vehicle_info' => 'Honda Beat (DH 1234 AB)',
        ]);

        $anotherProduct = $this->createProduct(['product_code' => 'TEST-OTHER']);
        $this->actingAs($customer)->post(route('cart.add', $anotherProduct), [
            'quantity' => 1,
            'vehicle_brand' => 'Honda Beat',
        ]);
        $this->post(route('cart.checkout'), [
            'payment_method' => 'cash',
            'vehicle_id' => [$anotherProduct->id => $otherVehicle->id],
        ])->assertSessionHasErrors("vehicle_id.{$anotherProduct->id}");
    }

    public function test_general_products_do_not_require_a_vehicle(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $generalCategory = Category::create(['name' => 'Perlengkapan Rumah']);
        $product = $this->createProduct(['category_id' => $generalCategory->id]);

        $this->actingAs($customer)
            ->get(route('customer.products'))
            ->assertOk()
            ->assertSee('name="quantity"', false)
            ->assertDontSee('name="vehicle_brand"', false);

        $this->actingAs($customer)
            ->post(route('cart.add', $product), ['quantity' => 1])
            ->assertRedirect()
            ->assertSessionHas('cart', [$product->id => 1]);
        $this->post(route('cart.checkout'), ['payment_method' => 'cash'])
            ->assertRedirect(route('customer.transactions'));

        $this->assertDatabaseHas('transaction_details', [
            'product_id' => $product->id,
            'vehicle_info' => null,
        ]);
    }

    public function test_guest_can_add_an_automotive_product_without_vehicle_info(): void
    {
        $product = $this->createProduct();

        $this->get(route('produk'))
            ->assertOk()
            ->assertSee('Merek dan model kendaraan');

        $this->post(route('cart.add', $product), ['quantity' => 1])
            ->assertRedirect()
            ->assertSessionHas('cart', [$product->id => 1])
            ->assertSessionHas('cart_vehicle_info', [$product->id => null]);

        $this->post(route('cart.add', $product), [
            'quantity' => 1,
            'vehicle_brand' => 'Suzuki Satria',
        ])->assertRedirect()
            ->assertSessionHas('cart', [$product->id => 2])
            ->assertSessionHas('cart_vehicle_info', [$product->id => 'Suzuki Satria']);
    }

    public function test_cashier_confirms_customer_product_order_and_customer_gets_a_notification(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $cashier = User::factory()->create(['role' => 'kasir']);
        $customer = User::factory()->create(['role' => 'customer']);
        $product = $this->createProduct();

        $this->actingAs($customer)->post(route('cart.add', $product), [
            'quantity' => 1,
            'vehicle_brand' => 'Honda Beat',
        ]);
        $this->assertDatabaseCount('notifications', 0);

        $this->post(route('cart.checkout'), [
            'payment_method' => 'cash',
            'vehicle_brand' => [$product->id => 'Honda Beat'],
        ])->assertRedirect(route('customer.transactions'))
            ->assertSessionHas('success', fn (string $message) => str_contains($message, 'menunggu konfirmasi kasir'));

        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin->id,
            'title' => 'Pesanan produk baru',
            'type' => 'payment',
            'is_read' => false,
        ]);

        $transaction = \App\Models\Transaction::where('customer_id', $customer->id)->firstOrFail();
        $payment = $transaction->payment;

        $this->actingAs($cashier)
            ->get(route('kasir.payments'))
            ->assertOk()
            ->assertSee($payment->payment_code)
            ->assertSee('Konfirmasi')
            ->assertSee('aria-label="1 pembayaran menunggu konfirmasi"', false);

        $this->patch(route('kasir.payments.confirm', $payment))
            ->assertRedirect(route('kasir.payments'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'completed',
        ]);
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'paid',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $customer->id,
            'title' => 'Pesanan Anda dikonfirmasi',
            'is_read' => false,
        ]);

        $this->actingAs($customer)
            ->get(route('customer.notifications'))
            ->assertOk()
            ->assertSee('Pesanan Anda dikonfirmasi')
            ->assertSee($transaction->invoice_number)
            ->assertSee('aria-label="1 notifikasi belum dibaca"', false);
    }

    private function createProduct(array $attributes = []): Product
    {
        $category = Category::create([
            'name' => 'Suku Cadang',
            'description' => 'Produk untuk pengujian',
        ]);

        return Product::create(array_merge([
            'category_id' => $category->id,
            'name' => 'Oli Mesin',
            'harga' => 25000,
            'stok' => 10,
            'status' => 'active',
            'is_active' => true,
            'visibility' => 'both',
        ], $attributes));
    }
}
