<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_can_create_transaction_via_api(): void
    {
        $cashier = User::factory()->create(['role' => 'kasir']);
        $category = Category::create(['name' => 'Sparepart']);
        $product = Product::create([
            'category_id' => $category->id,
            'product_code' => 'SP01',
            'name' => 'Busi NGK',
            'harga' => 25000,
            'stok' => 10,
            'status' => 'active',
            'is_active' => true,
        ]);

        $token = $cashier->createToken('test')->plainTextToken;
        $clientUuid = (string) Str::uuid();

        $response = $this->withToken($token)->postJson('/api/transactions', [
            'client_uuid' => $clientUuid,
            'items' => [
                [
                    'product_id' => $product->id,
                    'item_name' => 'Busi NGK',
                    'quantity' => 2,
                    'price' => 25000,
                ],
            ],
            'subtotal' => 50000,
            'discount' => 0,
            'tax' => 0,
            'total_payment' => 50000,
            'paid_amount' => 50000,
            'payment_method' => 'cash',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('transactions', ['total_payment' => 50000]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stok' => 8]);

        // Idempotency re-send check
        $idempotentResponse = $this->withToken($token)->postJson('/api/transactions', [
            'client_uuid' => $clientUuid,
            'items' => [
                [
                    'product_id' => $product->id,
                    'item_name' => 'Busi NGK',
                    'quantity' => 2,
                    'price' => 25000,
                ],
            ],
            'subtotal' => 50000,
            'total_payment' => 50000,
            'paid_amount' => 50000,
            'payment_method' => 'cash',
        ]);

        $idempotentResponse->assertOk()->assertJsonPath('message', 'Transaksi sudah pernah diproses');
        $this->assertDatabaseCount('transactions', 1);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stok' => 8]);
    }

    public function test_customer_can_only_view_their_own_transactions(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $anotherCustomer = User::factory()->create(['role' => 'customer']);
        $ownTransaction = Transaction::create([
            'user_id' => $customer->id,
            'total_payment' => 10000,
            'payment_method' => 'cash',
            'status' => 'completed',
        ]);
        $otherTransaction = Transaction::create([
            'user_id' => $anotherCustomer->id,
            'total_payment' => 20000,
            'payment_method' => 'cash',
            'status' => 'completed',
        ]);
        $token = $customer->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/transactions')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $ownTransaction->id);

        $this->withToken($token)
            ->getJson("/api/transactions/{$ownTransaction->id}")
            ->assertOk();

        $this->withToken($token)
            ->getJson("/api/transactions/{$otherTransaction->id}")
            ->assertNotFound();
    }

    public function test_customer_order_uses_server_prices_and_remains_pending(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $category = Category::create(['name' => 'Aksesoris']);
        $product = Product::create([
            'category_id' => $category->id,
            'product_code' => 'ACC01',
            'name' => 'Kampas rem',
            'harga' => 40000,
            'stok' => 4,
            'status' => 'active',
            'is_active' => true,
        ]);
        $token = $customer->createToken('test')->plainTextToken;
        $clientUuid = (string) Str::uuid();

        $this->withToken($token)
            ->postJson('/api/transactions', [
                'items' => [[
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'price' => 1,
                ]],
                'payment_method' => 'qris',
                'client_uuid' => $clientUuid,
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.subtotal', '80000.00')
            ->assertJsonPath('data.total_payment', '80000.00')
            ->assertJsonPath('data.paid_amount', '0.00');

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stok' => 2]);
        $this->assertDatabaseHas('payments', [
            'transaction_id' => 1,
            'amount' => 80000,
            'status' => 'pending',
        ]);
        $this->withToken($token)
            ->postJson('/api/transactions', [
                'items' => [['product_id' => $product->id, 'quantity' => 2]],
                'payment_method' => 'qris',
                'client_uuid' => $clientUuid,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Transaksi sudah pernah diproses');
        $this->assertDatabaseCount('transactions', 1);
    }

    public function test_customer_cannot_submit_cashier_transaction_amounts(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $missingProductId = ((int) Product::max('id')) + 1;
        $token = $customer->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/transactions', [
                'items' => [['product_id' => $missingProductId, 'quantity' => 1]],
                'payment_method' => 'qris',
                'paid_amount' => 100000,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['paid_amount', 'items.0.product_id']);
    }

    public function test_api_uses_server_product_prices_and_rejects_underpayment(): void
    {
        $cashier = User::factory()->create(['role' => 'kasir']);
        $category = Category::create(['name' => 'Oli']);
        $product = Product::create([
            'category_id' => $category->id,
            'product_code' => 'OLI02',
            'name' => 'Oli Mesin',
            'harga' => 25000,
            'stok' => 5,
            'status' => 'active',
            'is_active' => true,
        ]);
        $token = $cashier->createToken('test')->plainTextToken;
        $request = [
            'items' => [[
                'product_id' => $product->id,
                'item_name' => 'Harga palsu',
                'quantity' => 1,
                'price' => 1,
            ]],
            'subtotal' => 1,
            'discount' => 0,
            'tax' => 0,
            'total_payment' => 1,
            'paid_amount' => 25000,
            'payment_method' => 'cash',
        ];

        $response = $this->withToken($token)->postJson('/api/transactions', $request);

        $response->assertCreated()
            ->assertJsonPath('data.subtotal', '25000.00')
            ->assertJsonPath('data.total_payment', '25000.00');
        $this->assertDatabaseHas('transaction_details', [
            'product_id' => $product->id,
            'product_name' => 'Oli Mesin',
            'price' => 25000,
            'subtotal' => 25000,
        ]);
        $this->assertDatabaseHas('payments', [
            'transaction_id' => $response->json('data.id'),
            'amount' => 25000,
            'status' => 'paid',
        ]);

        $request['paid_amount'] = 1;
        $request['client_uuid'] = (string) Str::uuid();
        $this->withToken($token)
            ->postJson('/api/transactions', $request)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('paid_amount');

        $this->assertDatabaseCount('transactions', 1);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stok' => 4]);
    }
}
