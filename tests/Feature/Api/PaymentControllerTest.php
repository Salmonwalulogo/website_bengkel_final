<?php

namespace Tests\Feature\Api;

use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_can_view_payment_details_for_the_payment_menu(): void
    {
        $cashier = User::factory()->create(['role' => 'kasir']);
        $transaction = Transaction::create([
            'user_id' => $cashier->id,
            'subtotal' => 50000,
            'total_payment' => 50000,
            'paid_amount' => 50000,
            'change_amount' => 0,
            'payment_method' => 'cash',
            'status' => 'completed',
        ]);
        $payment = Payment::create([
            'transaction_id' => $transaction->id,
            'payment_code' => 'PAY-DEMO-01',
            'amount' => 50000,
            'payment_date' => now(),
            'status' => 'paid',
        ]);
        $token = $cashier->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/payments')
            ->assertOk()
            ->assertJsonPath('data.0.id', $payment->id)
            ->assertJsonPath('data.0.invoice_number', $transaction->invoice_number)
            ->assertJsonPath('data.0.amount', 50000);
    }

    public function test_customer_cannot_view_staff_payment_menu(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token = $customer->createToken('test')->plainTextToken;

        $this->withToken($token)->getJson('/api/payments')->assertForbidden();
    }
}
