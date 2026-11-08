<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApiTransactionRecorder
{
    public function record(User $cashier, array $data): Transaction
    {
        return $this->recordOrder($cashier, $data, false);
    }

    public function recordCustomerOrder(User $customer, array $data): Transaction
    {
        return $this->recordOrder($customer, $data, true);
    }

    private function recordOrder(User $actor, array $data, bool $customerOrder): Transaction
    {
        return DB::transaction(function () use ($actor, $data, $customerOrder) {
            $itemsByProduct = collect($data['items'])
                ->groupBy('product_id')
                ->map(fn ($items, $productId) => [
                    'product_id' => (int) $productId,
                    'quantity' => $items->sum('quantity'),
                ]);

            $products = Product::query()
                ->whereIn('id', $itemsByProduct->pluck('product_id'))
                ->where('status', 'active')
                ->where('is_active', true)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($products->count() !== $itemsByProduct->count()) {
                throw ValidationException::withMessages([
                    'items' => 'Satu atau beberapa produk tidak aktif atau tidak ditemukan.',
                ]);
            }

            $subtotal = 0.0;
            foreach ($itemsByProduct as $item) {
                $product = $products->get($item['product_id']);
                if ($product->stok < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => "Stok {$product->name} tidak mencukupi.",
                    ]);
                }
            }

            foreach ($data['items'] as $item) {
                $product = $products->get((int) $item['product_id']);
                $lineSubtotal = round((float) $product->harga * $item['quantity'], 2);
                $subtotal += $lineSubtotal;
            }

            $discount = round((float) ($data['discount'] ?? 0), 2);
            $tax = round((float) ($data['tax'] ?? 0), 2);
            if ($discount > $subtotal) {
                throw ValidationException::withMessages([
                    'discount' => 'Diskon tidak boleh melebihi subtotal berdasarkan harga produk.',
                ]);
            }

            $totalPayment = round($subtotal - $discount + $tax, 2);
            $paidAmount = round((float) $data['paid_amount'], 2);
            if (! $customerOrder && $paidAmount < $totalPayment) {
                throw ValidationException::withMessages([
                    'paid_amount' => 'Pembayaran kurang dari total transaksi yang dihitung server.',
                ]);
            }

            $transaction = Transaction::create([
                'user_id' => $actor->id,
                'customer_id' => $customerOrder ? $actor->id : ($data['customer_id'] ?? null),
                'checkout_token' => $data['client_uuid'] ?? null,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total_payment' => $totalPayment,
                'paid_amount' => $paidAmount,
                'change_amount' => round($paidAmount - $totalPayment, 2),
                'status' => $customerOrder ? 'pending' : 'completed',
                'payment_method' => strtolower($data['payment_method']),
            ]);

            foreach ($data['items'] as $item) {
                $product = $products->get((int) $item['product_id']);
                $price = (float) $product->harga;
                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'product_code' => $product->product_code,
                    'product_name' => $product->name,
                    'quantity' => $item['quantity'],
                    'price' => $price,
                    'subtotal' => round($price * $item['quantity'], 2),
                ]);
            }

            foreach ($itemsByProduct as $item) {
                $products->get($item['product_id'])->decrement('stok', $item['quantity']);
            }

            Payment::create([
                'transaction_id' => $transaction->id,
                'payment_code' => 'PAY-' . strtoupper(bin2hex(random_bytes(4))),
                'amount' => $customerOrder ? $totalPayment : $paidAmount,
                'payment_date' => $customerOrder ? null : now(),
                'status' => $customerOrder ? 'pending' : 'paid',
            ]);

            return $transaction;
        });
    }
}
