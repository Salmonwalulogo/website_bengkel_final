<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use App\Models\Product;
use App\Models\Payment;
use App\Services\PendingTransactionLifecycle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::with(['user', 'customer', 'booking.service'])
                                  ->latest()
                                  ->paginate(10);
        return view('backend.admin.transactions.index', compact('transactions'));
    }

    public function create()
    {
        $customers = User::where('role', 'customer')->get();
        $products = Product::where('status', 'active')->where('stok', '>', 0)->get();
        return view('backend.admin.transactions.create', compact('customers', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'customer')),
            ],
            'payment_method' => 'required|in:cash,qris,debit,kredit,ewallet,transfer',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:0',
        ]);

        DB::beginTransaction();

        try {
            $totalPayment = 0;
            $details = [];

            foreach ($validated['products'] as $item) {
                if ((int) $item['quantity'] === 0) {
                    continue;
                }

                $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                
                if ($product->stok < $item['quantity']) {
                    throw new \Exception("Stok {$product->name} tidak mencukupi");
                }

                $subtotal = $product->harga * $item['quantity'];
                $totalPayment += $subtotal;

                $details[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->harga,
                    'subtotal' => $subtotal,
                ];

                $product->decrement('stok', $item['quantity']);
            }

            if ($details === []) {
                throw new \Exception('Pilih minimal satu produk untuk membuat transaksi.');
            }

            // Buat transaksi
            $transaction = Transaction::create([
                'user_id' => $request->user()->id,
                'customer_id' => $validated['user_id'],
                'subtotal' => $totalPayment,
                'total_payment' => $totalPayment,
                'payment_method' => $validated['payment_method'],
                'status' => 'pending',
            ]);

            // Simpan detail transaksi
            foreach ($details as $detail) {
                $detail['transaction_id'] = $transaction->id;
                TransactionDetail::create($detail);
            }

            Payment::create([
                'transaction_id' => $transaction->id,
                'payment_code' => 'PAY-' . date('Ymd') . '-' . strtoupper(Str::random(6)),
                'amount' => $totalPayment,
                'payment_date' => now(),
                'status' => 'pending',
            ]);

            DB::commit();

            return redirect()->route('admin.transactions.index')
                ->with('success', 'Transaksi berhasil dibuat. Invoice: ' . $transaction->invoice_number);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['user', 'customer', 'booking.service', 'details.product']);
        return view('backend.admin.transactions.show', compact('transaction'));
    }

    public function updateStatus(
        Request $request,
        Transaction $transaction,
        PendingTransactionLifecycle $lifecycle
    )
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,completed,cancelled',
        ]);

        if ($validated['status'] === 'cancelled') {
            $lifecycle->cancel($transaction);
        } else {
            $lifecycle->markPaid($transaction);
        }

        return back()->with('success', 'Status pesanan berhasil diperbarui.');
    }

}