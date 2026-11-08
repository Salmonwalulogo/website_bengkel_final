<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_and_customer_catalogs_hide_products_that_are_not_sellable(): void
    {
        $sellable = $this->createProduct('Produk Tersedia', true);
        $unavailable = $this->createProduct('Produk Nonaktif', false);

        $this->get(route('produk'))
            ->assertOk()
            ->assertSee($sellable->name)
            ->assertDontSee($unavailable->name);

        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer)
            ->get(route('customer.products'))
            ->assertOk()
            ->assertSee($sellable->name)
            ->assertDontSee($unavailable->name);
    }

    public function test_admin_product_status_keeps_availability_flag_in_sync(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Suku Cadang']);
        $attributes = [
            'category_id' => $category->id,
            'name' => 'Produk Status Admin',
            'harga' => 25000,
            'stok' => 5,
            'status' => 'active',
        ];

        $this->actingAs($admin)
            ->post(route('admin.products.store'), $attributes)
            ->assertRedirect(route('admin.products.index'));

        $product = Product::where('name', 'Produk Status Admin')->firstOrFail();
        $this->assertTrue($product->is_active);

        $this->put(route('admin.products.update', $product), [
            ...$attributes,
            'status' => 'inactive',
        ])->assertRedirect(route('admin.products.index'));

        $this->assertFalse($product->fresh()->is_active);
    }

    private function createProduct(string $name, bool $isActive): Product
    {
        $category = Category::create(['name' => 'Suku Cadang ' . $name]);

        return Product::create([
            'category_id' => $category->id,
            'name' => $name,
            'harga' => 25000,
            'stok' => 5,
            'status' => 'active',
            'is_active' => $isActive,
            'visibility' => Product::VISIBILITY_BOTH,
        ]);
    }
}
