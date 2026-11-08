<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartItemActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_update_and_remove_forms_use_the_supported_http_methods(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $product = $this->createProduct();

        $this->actingAs($customer)
            ->withSession(['cart' => [$product->id => 1]])
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee(route('cart.update', $product))
            ->assertSee('name="_method" value="PATCH"', false)
            ->assertSee(route('cart.remove', $product))
            ->assertSee('name="_method" value="DELETE"', false);
    }

    public function test_customer_can_update_and_remove_cart_items(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $product = $this->createProduct();

        $this->actingAs($customer)
            ->withSession(['cart' => [$product->id => 1]])
            ->post(route('cart.update', $product), [
                '_method' => 'PATCH',
                'quantity' => 2,
            ])
            ->assertRedirect()
            ->assertSessionHas('cart', [$product->id => 2]);

        $this->post(route('cart.remove', $product), [
            '_method' => 'DELETE',
        ])
            ->assertRedirect()
            ->assertSessionHas('cart', []);
    }

    private function createProduct(): Product
    {
        $category = Category::create(['name' => 'Suku Cadang']);

        return Product::create([
            'category_id' => $category->id,
            'name' => 'Oli Mesin',
            'harga' => 25000,
            'stok' => 10,
            'status' => 'active',
            'is_active' => true,
            'visibility' => Product::VISIBILITY_BOTH,
        ]);
    }
}
