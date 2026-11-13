<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\ApiTransactionRecorder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Transaction::with(['user:id,name,email', 'customer:id,name,email', 'details.product', 'payment']);

        if ($user->isCustomer()) {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)->orWhere('customer_id', $user->id);
            });
        }

        $transactions = $query->latest()->get();

        return response()->json(['data' => $transactions]);
    }

    public function show(Request $request, Transaction $transaction)
    {
        if ($request->user()->isCustomer()) {
            abort_unless(
                $transaction->user_id === $request->user()->id
                    || $transaction->customer_id === $request->user()->id,
                404
            );
        }

        return response()->json(['data' => $transaction->load(['user', 'customer', 'details.product', 'payment'])]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $customerOrder = $user->isCustomer();
        $validated = $request->validate([
            'customer_id' => $customerOrder
                ? ['prohibited']
                : ['nullable', Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'customer'))],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'subtotal' => $customerOrder ? ['prohibited'] : ['sometimes', 'numeric', 'min:0'],
            'discount' => $customerOrder ? ['prohibited'] : ['nullable', 'numeric', 'min:0'],
            'tax' => $customerOrder ? ['prohibited'] : ['nullable', 'numeric', 'min:0'],
            'total_payment' => $customerOrder ? ['prohibited'] : ['sometimes', 'numeric', 'min:0'],
            'paid_amount' => $customerOrder
                ? ['prohibited']
                : ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', 'string', Rule::in(['cash', 'qris', 'ewallet', 'transfer'])],
            'client_uuid' => ['nullable', 'uuid'],
        ]);

        if ($customerOrder) {
            $validated['paid_amount'] = 0;
        }

        // Idempotency keys are private to the staff account that created them.
        if (! empty($validated['client_uuid'])) {
            $existing = Transaction::where('user_id', $user->id)
                ->where('checkout_token', $validated['client_uuid'])
                ->first();
            if ($existing) {
                return response()->json([
                    'message' => 'Transaksi sudah pernah diproses',
                    'data' => $existing->load(['details', 'payment']),
                ], 200);
            }
        }

        $recorder = app(ApiTransactionRecorder::class);
        $transaction = $customerOrder
            ? $recorder->recordCustomerOrder($user, $validated)
            : $recorder->record($user, $validated);

        return response()->json([
            'message' => 'Transaksi berhasil dibuat',
            'data' => $transaction->load(['details', 'payment']),
        ], 201);
    }
}
