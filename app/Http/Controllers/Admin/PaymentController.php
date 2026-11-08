<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Transaction;
use App\Services\PendingTransactionLifecycle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with('transaction.user')
                          ->latest()
                          ->paginate(10);
        return view('backend.admin.payments.index', compact('payments'));
    }

    public function create()
    {
        $transactions = Transaction::where('status', 'completed')
                                  ->whereDoesntHave('payment')
                                  ->get();
        return view('backend.admin.payments.create', compact('transactions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'transaction_id' => 'required|exists:transactions,id',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'proof_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $validated['payment_code'] = 'PAY-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        $validated['status'] = 'paid';

        if ($request->hasFile('proof_image')) {
            $validated['proof_image'] = $request->file('proof_image')->store('payments', 'public');
        }

        DB::transaction(function () use ($validated): void {
            $transaction = Transaction::query()
                ->where('status', 'completed')
                ->lockForUpdate()
                ->findOrFail($validated['transaction_id']);

            if ($transaction->payment()->exists()) {
                throw ValidationException::withMessages([
                    'transaction_id' => 'Transaksi ini sudah memiliki pembayaran.',
                ]);
            }

            $amount = (float) $validated['amount'];
            $total = (float) $transaction->total_payment;

            if ($amount < $total) {
                throw ValidationException::withMessages([
                    'amount' => 'Jumlah pembayaran belum mencukupi total transaksi.',
                ]);
            }

            Payment::create($validated);
            $transaction->update([
                'paid_amount' => $amount,
                'change_amount' => max(0, $amount - $total),
            ]);
        });

        return redirect()->route('admin.payments.index')
            ->with('success', 'Pembayaran berhasil dicatat.');
    }

    public function edit(Payment $payment)
    {
        return view('backend.admin.payments.edit', compact('payment'));
    }

    public function update(
        Request $request,
        Payment $payment,
        PendingTransactionLifecycle $lifecycle
    )
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'status' => 'required|in:pending,paid,failed,cancelled',
            'proof_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('proof_image')) {
            $validated['proof_image'] = $request->file('proof_image')->store('payments', 'public');
        }

        $previousProof = $payment->proof_image;
        $transactionId = $payment->transaction_id;

        DB::transaction(function () use ($payment, $transactionId, $validated, $lifecycle): void {
            $transaction = Transaction::query()->lockForUpdate()->findOrFail($transactionId);
            $lockedPayment = $transaction->payment()->lockForUpdate()->firstOrFail();

            if ($validated['status'] === 'paid') {
                if ($transaction->status === 'completed'
                    && ($lockedPayment->status !== 'paid'
                        || (float) $validated['amount'] !== (float) $lockedPayment->amount)) {
                    throw ValidationException::withMessages([
                        'status' => 'Pembayaran transaksi selesai tidak dapat diubah tanpa proses pengembalian dana.',
                    ]);
                }

                if ($transaction->status === 'pending' && (float) $validated['amount'] < (float) $transaction->total_payment) {
                    throw ValidationException::withMessages([
                        'amount' => 'Jumlah pembayaran belum mencukupi total transaksi.',
                    ]);
                }

                $lockedPayment->update($validated);
                $lifecycle->markPaid($transaction);
                return;
            }

            if ($transaction->status === 'cancelled') {
                if ($validated['status'] !== $lockedPayment->status
                    || (float) $validated['amount'] !== (float) $lockedPayment->amount) {
                    throw ValidationException::withMessages([
                        'status' => 'Pembayaran transaksi yang dibatalkan tidak dapat diubah.',
                    ]);
                }

                $lockedPayment->update($validated);
                return;
            }

            if ($transaction->status !== 'pending') {
                throw ValidationException::withMessages([
                    'status' => 'Status pembayaran transaksi selesai tidak dapat diubah tanpa proses pengembalian dana.',
                ]);
            }

            if ($validated['status'] === 'cancelled') {
                $lifecycle->cancel($transaction);
                $lockedPayment->update($validated);
                return;
            }

            $lockedPayment->update($validated);
        });

        if ($request->hasFile('proof_image') && $previousProof) {
            Storage::disk('public')->delete($previousProof);
        }

        return redirect()->route('admin.payments.index')
            ->with('success', 'Pembayaran berhasil diperbarui.');
    }

}