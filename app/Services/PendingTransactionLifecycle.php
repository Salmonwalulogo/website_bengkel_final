<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PendingTransactionLifecycle
{
    public function markPaid(Transaction $transaction): bool
    {
        return DB::transaction(function () use ($transaction): bool {
            $lockedTransaction = Transaction::query()->lockForUpdate()->findOrFail($transaction->id);
            $payment = $lockedTransaction->payment()->lockForUpdate()->first();

            if ($lockedTransaction->status === 'completed' && $payment?->status === 'paid') {
                return false;
            }

            if ($lockedTransaction->status !== 'pending' || ! $payment || $payment->status !== 'pending') {
                throw ValidationException::withMessages([
                    'status' => 'Hanya pembayaran berstatus menunggu yang dapat dikonfirmasi.',
                ]);
            }

            $amount = (float) $payment->amount;
            $total = (float) $lockedTransaction->total_payment;

            if ($amount < $total) {
                throw ValidationException::withMessages([
                    'amount' => 'Jumlah pembayaran belum mencukupi total transaksi.',
                ]);
            }

            $payment->update([
                'status' => 'paid',
                'payment_date' => now(),
            ]);
            $lockedTransaction->update([
                'status' => 'completed',
                'paid_amount' => $amount,
                'change_amount' => max(0, $amount - $total),
            ]);

            return true;
        });
    }

    public function cancel(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction): void {
            $lockedTransaction = Transaction::query()->lockForUpdate()->findOrFail($transaction->id);

            if ($lockedTransaction->status === 'cancelled') {
                return;
            }

            if ($lockedTransaction->status !== 'pending') {
                throw ValidationException::withMessages([
                    'status' => 'Transaksi yang sudah selesai atau dibayar memerlukan proses pengembalian dana.',
                ]);
            }

            if ((float) $lockedTransaction->paid_amount > 0
                || $lockedTransaction->payment()->where('status', 'paid')->exists()) {
                throw ValidationException::withMessages([
                    'status' => 'Transaksi yang sudah dibayar memerlukan proses pengembalian dana dan tidak dapat dibatalkan di sini.',
                ]);
            }

            foreach ($lockedTransaction->details as $detail) {
                if (! $detail->product_id) {
                    continue;
                }

                $product = Product::query()->lockForUpdate()->findOrFail($detail->product_id);
                $product->increment('stok', $detail->quantity);
            }

            $lockedTransaction->payment()
                ->whereIn('status', ['pending', 'failed'])
                ->update(['status' => 'cancelled']);

            $lockedTransaction->update(['status' => 'cancelled']);
        });
    }
}
