<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
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
        ]);

        $response
            ->assertRedirect()
            ->assertSessionHas('cart', [$product->id => 2]);

        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee($product->name)
            ->assertSee('2');
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

        $this->actingAs($customer)
            ->get(route('customer.products'))
            ->assertOk()
            ->assertSee('Katalog Produk')
            ->assertSee($product->name)
            ->assertDontSee('Produk <span>Kami</span>', false);

        $this->get(route('produk'))
            ->assertOk()
            ->assertSee('Produk <span>Kami</span>', false)
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
            ->assertSee('Beli Sekarang');

        $response = $this->post(route('cart.buy-now.checkout', $product), [
            'quantity' => 2,
            'payment_method' => 'qris',
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
        ]);

        $response = $this->post(route('cart.checkout'), [
            'payment_method' => 'transfer',
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
