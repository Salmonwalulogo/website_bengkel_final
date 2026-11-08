<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::query()
            ->with('transaction:id,invoice_number')
            ->latest('payment_date')
            ->get()
            ->map(fn (Payment $payment) => [
                'id' => $payment->id,
                'transaction_id' => $payment->transaction_id,
                'invoice_number' => $payment->transaction?->invoice_number,
                'payment_code' => $payment->payment_code,
                'amount' => (float) $payment->amount,
                'status' => $payment->status,
                'payment_date' => $payment->payment_date?->toIso8601String(),
            ]);

        return response()->json(['data' => $payments]);
    }
}
