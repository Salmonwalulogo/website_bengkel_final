<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
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

    public function test_cashier_can_confirm_a_pending_payment_and_admins_are_notified(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $secondAdmin = User::factory()->create(['role' => 'admin']);
        [, $transaction, $payment] = $this->createPendingProductTransaction();
        $cashier = User::findOrFail($transaction->user_id);

        $this->actingAs($cashier)
            ->get(route('kasir.payments'))
            ->assertOk()
            ->assertSee(route('kasir.payments.confirm', $payment))
            ->assertSee('Konfirmasi');

        $this->actingAs($cashier)
            ->patch(route('kasir.payments.confirm', $payment))
            ->assertRedirect(route('kasir.payments'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'paid']);
        $this->assertNotNull($payment->fresh()->payment_date);
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'completed',
            'paid_amount' => 200,
            'change_amount' => 0,
        ]);
        foreach ([$admin, $secondAdmin] as $recipient) {
            $this->assertDatabaseHas('notifications', [
                'user_id' => $recipient->id,
                'title' => 'Pembayaran dikonfirmasi kasir',
            ]);
        }
        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin->id,
            'message' => "Kasir {$cashier->name} mengonfirmasi pembayaran {$payment->payment_code} untuk invoice {$transaction->invoice_number} ({$transaction->customer->name}) sebesar Rp 200.",
        ]);
    }

    public function test_repeated_payment_confirmation_does_not_send_duplicate_notifications(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [, $transaction, $payment] = $this->createPendingProductTransaction();
        $cashier = User::findOrFail($transaction->user_id);

        $this->actingAs($cashier)
            ->patch(route('kasir.payments.confirm', $payment))
            ->assertRedirect(route('kasir.payments'));
        $this->patch(route('kasir.payments.confirm', $payment))
            ->assertRedirect(route('kasir.payments'))
            ->assertSessionHas('info');

        $this->assertSame(1, \App\Models\Notification::where('user_id', $admin->id)->count());
        $this->assertDatabaseHas('transactions', ['id' => $transaction->id, 'status' => 'completed']);
    }

    public function test_cashier_cannot_confirm_an_underpaid_transaction(): void
    {
        [, $transaction, $payment] = $this->createPendingProductTransaction();
        $payment->update(['amount' => 199]);
        $cashier = User::findOrFail($transaction->user_id);

        $this->actingAs($cashier)
            ->from(route('kasir.payments'))
            ->patch(route('kasir.payments.confirm', $payment))
            ->assertRedirect(route('kasir.payments'))
            ->assertSessionHasErrors('amount');

        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'pending']);
        $this->assertDatabaseHas('transactions', ['id' => $transaction->id, 'status' => 'pending']);
    }

    public function test_admin_can_only_view_payments_and_cannot_confirm_them(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [, , $payment] = $this->createPendingProductTransaction();

        $this->actingAs($admin)
            ->get(route('admin.payments.index'))
            ->assertOk()
            ->assertDontSee('Tambah Pembayaran');

        $this->patch(route('kasir.payments.confirm', $payment))->assertForbidden();
        $this->assertFalse(Route::has('admin.payments.create'));
        $this->assertFalse(Route::has('admin.payments.edit'));
        $this->assertFalse(Route::has('admin.payments.store'));
        $this->assertFalse(Route::has('admin.payments.update'));
    }

    public function test_new_payment_without_payment_date_is_shown_before_old_payments(): void
    {
        $cashier = User::factory()->create(['role' => 'kasir']);

        for ($index = 1; $index <= 10; $index++) {
            $createdAt = now()->subDays(20)->addMinutes($index);
            $transaction = Transaction::create([
                'user_id' => $cashier->id,
                'subtotal' => 100,
                'total_payment' => 100,
                'payment_method' => 'cash',
                'status' => 'completed',
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            Payment::create([
                'transaction_id' => $transaction->id,
                'payment_code' => "PAY-OLD-{$index}",
                'amount' => 100,
                'payment_date' => $createdAt,
                'status' => 'paid',
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }

        $newTransaction = Transaction::create([
            'user_id' => $cashier->id,
            'subtotal' => 230,
            'total_payment' => 230,
            'payment_method' => 'transfer',
            'status' => 'pending',
        ]);
        $newPayment = Payment::create([
            'transaction_id' => $newTransaction->id,
            'payment_code' => 'PAY-NEWEST',
            'amount' => 230,
            'payment_date' => null,
            'status' => 'pending',
        ]);

        $this->actingAs($cashier)
            ->get(route('kasir.payments'))
            ->assertOk()
            ->assertSee($newPayment->payment_code)
            ->assertSee('PAY-OLD-10');

        $this->get(route('kasir.payments', ['page' => 2]))
            ->assertOk()
            ->assertSee('PAY-OLD-1')
            ->assertDontSee($newPayment->payment_code);
    }

    public function test_admin_cannot_complete_a_transaction_to_confirm_its_payment(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [, $transaction, $payment] = $this->createPendingProductTransaction();

        $this->actingAs($admin)
            ->from(route('admin.transactions.index'))
            ->patch(route('admin.transactions.updateStatus', $transaction), ['status' => 'completed'])
            ->assertRedirect(route('admin.transactions.index'))
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('transactions', ['id' => $transaction->id, 'status' => 'pending']);
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'pending']);
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
