<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_catalog_excludes_customer_only_products(): void
    {
        $this->createProduct('Produk Publik', Product::VISIBILITY_PUBLIC);
        $this->createProduct('Produk Customer', Product::VISIBILITY_CUSTOMER);
        $this->createProduct('Produk Bersama', Product::VISIBILITY_BOTH);

        $this->get(route('produk'))
            ->assertOk()
            ->assertSee('Produk Publik')
            ->assertSee('Produk Bersama')
            ->assertDontSee('Produk Customer');
    }

    public function test_customer_catalog_excludes_public_only_products(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $this->createProduct('Produk Publik', Product::VISIBILITY_PUBLIC);
        $this->createProduct('Produk Customer', Product::VISIBILITY_CUSTOMER);
        $this->createProduct('Produk Bersama', Product::VISIBILITY_BOTH);

        $this->actingAs($customer)
            ->get(route('customer.products'))
            ->assertOk()
            ->assertSee('Produk Customer')
            ->assertSee('Produk Bersama')
            ->assertDontSee('Produk Publik');
    }

    public function test_products_default_to_the_public_catalog(): void
    {
        $category = Category::create(['name' => 'Suku Cadang']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Produk Default Publik',
            'harga' => 25000,
            'stok' => 5,
            'status' => 'active',
            'is_active' => true,
        ]);

        $this->assertSame(Product::VISIBILITY_PUBLIC, $product->fresh()->visibility);

        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer)
            ->get(route('customer.products'))
            ->assertOk()
            ->assertDontSee('Produk Default Publik');
    }

    public function test_customer_only_products_are_hidden_from_public_api(): void
    {
        $product = $this->createProduct('Produk Customer', Product::VISIBILITY_CUSTOMER);

        $this->getJson(route('api.products.index'))
            ->assertOk()
            ->assertJsonMissing(['name' => 'Produk Customer']);

        $this->getJson(route('api.products.show', $product))
            ->assertNotFound();

        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer)
            ->getJson(route('api.products.index'))
            ->assertOk()
            ->assertJsonFragment(['name' => 'Produk Customer']);

        $this->getJson(route('api.products.show', $product))
            ->assertOk()
            ->assertJsonPath('data.name', 'Produk Customer');
    }

    public function test_customer_only_products_cannot_be_added_to_a_guest_cart(): void
    {
        $product = $this->createProduct('Produk Customer', Product::VISIBILITY_CUSTOMER);

        $this->post(route('cart.add', $product), ['quantity' => 1])
            ->assertNotFound();
        $this->patch(route('cart.update', $product), ['quantity' => 2])
            ->assertNotFound();

        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer)
            ->post(route('cart.add', $product), [
                'quantity' => 1,
                'vehicle_brand' => 'Honda Vario',
            ])
            ->assertRedirect()
            ->assertSessionHas('cart', [$product->id => 1]);
    }

    public function test_customer_cannot_purchase_a_public_only_product(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $product = $this->createProduct('Produk Publik', Product::VISIBILITY_PUBLIC);

        $this->actingAs($customer)
            ->get(route('cart.buy-now', $product))
            ->assertNotFound();

        $this->post(route('cart.buy-now.checkout', $product), [
            'quantity' => 1,
            'payment_method' => 'cash',
        ])->assertNotFound();

        $this->assertDatabaseCount('transactions', 0);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stok' => 5,
        ]);
    }

    public function test_cart_removes_products_the_signed_in_customer_cannot_access(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $publicProduct = $this->createProduct('Produk Publik', Product::VISIBILITY_PUBLIC);
        $customerProduct = $this->createProduct('Produk Customer', Product::VISIBILITY_CUSTOMER);

        $this->actingAs($customer)
            ->withSession(['cart' => [
                $publicProduct->id => 1,
                $customerProduct->id => 2,
            ]])
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee($customerProduct->name)
            ->assertDontSee($publicProduct->name)
            ->assertSessionHas('cart', [$customerProduct->id => 2])
            ->assertSessionHas('warning');
    }

    public function test_admin_can_assign_product_catalog_visibility(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Suku Cadang']);

        $response = $this->actingAs($admin)->post(route('admin.products.store'), [
            'category_id' => $category->id,
            'name' => 'Produk Rahasia Customer',
            'description' => 'Produk khusus customer',
            'harga' => 25000,
            'stok' => 4,
            'status' => 'active',
            'is_active' => 1,
            'visibility' => Product::VISIBILITY_CUSTOMER,
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $product = Product::where('name', 'Produk Rahasia Customer')->firstOrFail();
        $this->assertSame(Product::VISIBILITY_CUSTOMER, $product->visibility);

        $this->get(route('admin.products.edit', $product))
            ->assertOk()
            ->assertSee('name="visibility"', false);
    }

    private function createProduct(string $name, string $visibility): Product
    {
        $category = Category::firstOrCreate(['name' => 'Suku Cadang']);

        return Product::create([
            'category_id' => $category->id,
            'name' => $name,
            'harga' => 25000,
            'stok' => 5,
            'status' => 'active',
            'is_active' => true,
            'visibility' => $visibility,
        ]);
    }
}
