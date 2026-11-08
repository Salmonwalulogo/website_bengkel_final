<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_cancelling_a_pending_transaction_restores_stock_and_cancels_payment(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [$product, $transaction, $payment] = $this->createPendingProductTransaction();

        $this->actingAs($admin)
            ->from(route('admin.transactions.index'))
            ->patch(route('admin.transactions.updateStatus', $transaction), ['status' => 'cancelled'])
            ->assertRedirect(route('admin.transactions.index'));

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stok' => 6]);
        $this->assertDatabaseHas('transactions', ['id' => $transaction->id, 'status' => 'cancelled']);
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'cancelled']);
    }

    public function test_payment_confirmation_completes_the_transaction_consistently(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [, $transaction, $payment] = $this->createPendingProductTransaction();

        $this->actingAs($admin)
            ->from(route('admin.payments.index'))
            ->put(route('admin.payments.update', $payment), [
                'amount' => 200,
                'payment_date' => now()->format('Y-m-d\TH:i'),
                'status' => 'paid',
            ])
            ->assertRedirect(route('admin.payments.index'));

        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'paid']);
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'completed',
            'paid_amount' => 200,
            'change_amount' => 0,
        ]);
    }

    public function test_payment_cannot_complete_an_underpaid_transaction(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [, $transaction, $payment] = $this->createPendingProductTransaction();

        $this->actingAs($admin)
            ->from(route('admin.payments.edit', $payment))
            ->put(route('admin.payments.update', $payment), [
                'amount' => 199,
                'payment_date' => now()->format('Y-m-d\TH:i'),
                'status' => 'paid',
            ])
            ->assertRedirect(route('admin.payments.edit', $payment))
            ->assertSessionHasErrors('amount');

        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'pending']);
        $this->assertDatabaseHas('transactions', ['id' => $transaction->id, 'status' => 'pending']);
    }

    public function test_admin_payment_record_updates_the_completed_transaction_totals(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $transaction = Transaction::create([
            'user_id' => User::factory()->create(['role' => 'kasir'])->id,
            'subtotal' => 200,
            'total_payment' => 200,
            'payment_method' => 'cash',
            'status' => 'completed',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.payments.store'), [
                'transaction_id' => $transaction->id,
                'amount' => 250,
                'payment_date' => now()->format('Y-m-d\TH:i'),
            ])
            ->assertRedirect(route('admin.payments.index'));

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'paid_amount' => 250,
            'change_amount' => 50,
        ]);
        $this->assertDatabaseHas('payments', [
            'transaction_id' => $transaction->id,
            'amount' => 250,
            'status' => 'paid',
        ]);
    }

    /**
     * @return array{Product, Transaction, Payment}
     */
    private function createPendingProductTransaction(): array
    {
        $cashier = User::factory()->create(['role' => 'kasir']);
        $customer = User::factory()->create(['role' => 'customer']);
        $category = Category::create(['name' => 'Perlengkapan']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Lampu Tenda',
            'harga' => 100,
            'stok' => 4,
            'status' => 'active',
            'is_active' => true,
        ]);
        $transaction = Transaction::create([
            'user_id' => $cashier->id,
            'customer_id' => $customer->id,
            'subtotal' => 200,
            'total_payment' => 200,
            'payment_method' => 'transfer',
            'status' => 'pending',
        ]);
        $transaction->details()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 2,
            'price' => 100,
            'subtotal' => 200,
        ]);
        $payment = Payment::create([
            'transaction_id' => $transaction->id,
            'payment_code' => 'PAY-TEST-001',
            'amount' => 200,
            'payment_date' => now(),
            'status' => 'pending',
        ]);

        return [$product, $transaction, $payment];
    }
}
