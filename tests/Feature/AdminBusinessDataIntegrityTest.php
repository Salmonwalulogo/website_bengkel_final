<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBusinessDataIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_deleting_a_product_archives_it_and_preserves_sales_history(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $cashier = User::factory()->create(['role' => 'kasir']);
        $category = Category::create(['name' => 'Peralatan']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Lampu Tenda',
            'harga' => 25000,
            'stok' => 4,
            'status' => 'active',
            'is_active' => true,
        ]);
        $transaction = Transaction::create([
            'user_id' => $cashier->id,
            'invoice_number' => 'TEST-ORDER-001',
            'total_payment' => 25000,
            'status' => 'completed',
        ]);
        $transaction->details()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 1,
            'price' => 25000,
            'subtotal' => 25000,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.products.destroy', $product))
            ->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'status' => 'inactive',
            'is_active' => false,
        ]);
        $this->assertDatabaseHas('transaction_details', [
            'transaction_id' => $transaction->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_category_in_use_cannot_be_deleted_from_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Perlengkapan Acara']);
        Product::create([
            'category_id' => $category->id,
            'name' => 'Tenda',
            'harga' => 1000,
            'stok' => 1,
            'status' => 'inactive',
            'is_active' => false,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.categories.index'))
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHasErrors('category');

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
        $this->assertDatabaseHas('products', ['category_id' => $category->id]);
    }

    public function test_admin_cannot_delete_a_user_with_business_history(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);
        Booking::create([
            'user_id' => $customer->id,
            'service_id' => \App\Models\Service::create([
                'name' => 'Sewa Tenda',
                'category' => 'Penyewaan',
                'harga' => 1000,
                'status' => 'active',
            ])->id,
            'booking_date' => now()->toDateString(),
            'booking_time' => '10:00',
            'request_details' => 'Satu unit untuk acara',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->delete(route('admin.users.destroy', $customer))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHasErrors('user');

        $this->assertDatabaseHas('users', ['id' => $customer->id]);
        $this->assertDatabaseHas('bookings', ['user_id' => $customer->id]);
    }

    public function test_transaction_and_payment_records_cannot_be_deleted_from_admin_routes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $transaction = Transaction::create([
            'user_id' => User::factory()->create(['role' => 'kasir'])->id,
            'invoice_number' => 'TEST-KEEP-001',
            'total_payment' => 10000,
            'status' => 'completed',
        ]);

        $this->actingAs($admin)
            ->delete("/admin/transactions/{$transaction->id}")
            ->assertMethodNotAllowed();

        $this->assertDatabaseHas('transactions', ['id' => $transaction->id]);
        $this->assertFalse(app('router')->has('admin.payments.destroy'));
    }
}
