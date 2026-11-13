<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CashierPosTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_can_open_a_browser_printable_receipt(): void
    {
        config(['escpos.driver' => 'browser']);

        $cashier = User::factory()->create(['role' => 'kasir']);
        $category = Category::create(['name' => 'Retail']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Produk Cetak',
            'harga' => 10000,
            'stok' => 5,
            'status' => 'active',
            'is_active' => true,
        ]);
        $transaction = Transaction::create([
            'user_id' => $cashier->id,
            'subtotal' => 10000,
            'paid_amount' => 10000,
            'total_payment' => 10000,
            'status' => 'completed',
            'payment_method' => 'cash',
        ]);
        $transaction->details()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 1,
            'price' => 10000,
            'subtotal' => 10000,
        ]);

        $this->actingAs($cashier)
            ->post(route('kasir.pos.print', $transaction))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertSee($transaction->receipt_number)
            ->assertSee('Produk Cetak')
            ->assertSee('id="printed-at"', false)
            ->assertSee("window.addEventListener('beforeprint', updatePrintedAt)", false)
            ->assertSee("window.addEventListener('load'", false)
            ->assertSee('window.print()', false);
    }

    public function test_invoice_timestamps_are_rendered_in_the_cashier_browser_timezone(): void
    {
        config(['escpos.driver' => 'browser']);

        $cashier = User::factory()->create(['role' => 'kasir']);
        $transaction = Transaction::create([
            'user_id' => $cashier->id,
            'subtotal' => 10000,
            'paid_amount' => 10000,
            'total_payment' => 10000,
            'status' => 'completed',
            'payment_method' => 'cash',
            'created_at' => '2026-11-08 03:29:00',
        ]);

        $this->actingAs($cashier)
            ->get(route('kasir.invoices'))
            ->assertOk()
            ->assertSee('Tanggal (waktu lokal)')
            ->assertSee('class="invoice-local-time" datetime="' . $transaction->created_at->toISOString() . '"', false)
            ->assertSee("new Intl.DateTimeFormat('id-ID'", false);
    }

    public function test_cashier_can_open_pos_and_complete_a_transaction(): void
    {
        $cashier = User::factory()->create(['role' => 'kasir']);
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Retail']);
        $product = Product::create([
            'category_id' => $category->id,
            'product_code' => 'TEST001',
            'barcode' => '899000000001',
            'name' => 'Produk Test',
            'harga' => 10000,
            'stok' => 5,
            'status' => 'active',
            'is_active' => true,
        ]);

        $this->actingAs($cashier)
            ->get(route('kasir.pos'))
            ->assertOk()
            ->assertSee('<h1 class="text-white">Transaksi Pembayaran</h1>', false)
            ->assertSee('Transaksi Pembayaran Kasir');

        $checkoutToken = (string) Str::uuid();
        $response = $this->actingAs($cashier)->post(route('kasir.pos.store'), [
            'checkout_token' => $checkoutToken,
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
            'discount_percent' => 10,
            'discount_nominal' => 0,
            'tax' => 0,
            'other_fee' => 0,
            'paid_amount' => 20000,
            'payment_method' => 'cash',
        ]);

        $response->assertRedirect(route('kasir.pos'));
        $this->assertDatabaseHas('transactions', [
            'user_id' => $cashier->id,
            'invoice_number' => 'TRX-' . now()->format('Ymd') . '-000001',
            'total_payment' => 18000,
            'paid_amount' => 20000,
            'change_amount' => 2000,
        ]);
        $this->assertDatabaseHas('transaction_details', [
            'product_id' => $product->id,
            'quantity' => 2,
            'subtotal' => 20000,
        ]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stok' => 3]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin->id,
            'title' => 'Pembayaran dikonfirmasi kasir',
        ]);

        $this->actingAs($cashier)->post(route('kasir.pos.store'), [
            'checkout_token' => $checkoutToken,
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
            'paid_amount' => 20000,
            'payment_method' => 'cash',
        ]);
        $this->assertDatabaseCount('transactions', 1);
        $this->assertSame(1, \App\Models\Notification::where('user_id', $admin->id)->count());
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stok' => 3]);
    }

    public function test_each_transaction_receipt_gets_a_different_number_matching_the_pos_preview(): void
    {
        $cashier = User::factory()->create(['role' => 'kasir']);
        $category = Category::create(['name' => 'Jasa']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Layanan Test',
            'harga' => 10000,
            'stok' => 5,
            'status' => 'active',
            'is_active' => true,
        ]);

        $this->actingAs($cashier)->get(route('kasir.pos'))->assertOk();
        $firstPreview = \App\Models\Transaction::nextInvoiceNumber();
        $firstReceiptPreview = \App\Models\Transaction::nextReceiptNumber();

        $firstResponse = $this->post(route('kasir.pos.store'), [
            'checkout_token' => (string) Str::uuid(),
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'paid_amount' => 10000,
            'payment_method' => 'cash',
        ]);
        $firstResponse->assertRedirect(route('kasir.pos'));

        $firstNumber = \App\Models\Transaction::latest('id')->value('invoice_number');
        $firstReceiptNumber = \App\Models\Transaction::latest('id')->value('receipt_number');
        $this->assertSame($firstPreview, $firstNumber);
        $this->assertSame($firstReceiptPreview, $firstReceiptNumber);
        $this->get(route('kasir.pos'))
            ->assertSee($firstReceiptNumber)
            ->assertSee('id="receipt-printed-at"', false)
            ->assertSee('Tanggal cetak');

        $secondPreview = \App\Models\Transaction::nextInvoiceNumber();
        $secondReceiptPreview = \App\Models\Transaction::nextReceiptNumber();
        $this->post(route('kasir.pos.store'), [
            'checkout_token' => (string) Str::uuid(),
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'paid_amount' => 10000,
            'payment_method' => 'cash',
        ])->assertRedirect(route('kasir.pos'));

        $secondNumber = \App\Models\Transaction::latest('id')->value('invoice_number');
        $secondReceiptNumber = \App\Models\Transaction::latest('id')->value('receipt_number');
        $this->assertSame($secondPreview, $secondNumber);
        $this->assertSame($secondReceiptPreview, $secondReceiptNumber);
        $this->assertNotSame($firstNumber, $secondNumber);
        $this->assertNotSame($firstReceiptNumber, $secondReceiptNumber);
        $this->assertSame(2, \App\Models\Transaction::whereIn('invoice_number', [$firstNumber, $secondNumber])->count());
        $this->assertSame(2, \App\Models\Transaction::whereIn('receipt_number', [$firstReceiptNumber, $secondReceiptNumber])->count());
    }

    public function test_cashier_product_catalog_reflects_admin_product_changes(): void
    {
        $cashier = User::factory()->create(['role' => 'kasir']);
        $category = Category::create(['name' => 'Retail']);
        $product = Product::create([
            'category_id' => $category->id,
            'product_code' => 'SYNC001',
            'barcode' => '899000000010',
            'name' => 'Produk Lama',
            'harga' => 10000,
            'stok' => 5,
            'status' => 'active',
            'is_active' => true,
        ]);
        $inactiveProduct = Product::create([
            'category_id' => $category->id,
            'product_code' => 'SYNC002',
            'name' => 'Produk Nonaktif',
            'harga' => 20000,
            'stok' => 5,
            'status' => 'inactive',
            'is_active' => false,
        ]);

        $product->update(['name' => 'Produk Baru', 'harga' => 15000, 'stok' => 3]);

        $this->actingAs($cashier)
            ->getJson(route('kasir.pos.products'))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertJsonFragment([
                'id' => $product->id,
                'name' => 'Produk Baru',
                'price' => 15000,
                'stock' => 3,
            ])
            ->assertJsonMissing(['id' => $inactiveProduct->id]);

        $inactiveProduct->update(['status' => 'active', 'is_active' => true, 'stok' => 0]);

        $this->actingAs($cashier)
            ->getJson(route('kasir.pos.products'))
            ->assertJsonMissing(['id' => $inactiveProduct->id]);
    }

    public function test_cashier_cannot_complete_transaction_when_payment_is_insufficient(): void
    {
        $cashier = User::factory()->create(['role' => 'kasir']);
        $category = Category::create(['name' => 'Retail']);
        $product = Product::create([
            'category_id' => $category->id,
            'product_code' => 'TEST002',
            'barcode' => '899000000002',
            'name' => 'Produk Test 2',
            'harga' => 10000,
            'stok' => 5,
            'status' => 'active',
            'is_active' => true,
        ]);

        $response = $this->actingAs($cashier)->post(route('kasir.pos.store'), [
            'checkout_token' => (string) Str::uuid(),
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'paid_amount' => 5000,
            'payment_method' => 'cash',
        ]);

        $response->assertSessionHasErrors('paid_amount');
        $this->assertDatabaseCount('transactions', 0);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stok' => 5]);
    }
}
