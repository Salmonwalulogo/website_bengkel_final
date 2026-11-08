<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $cart = $request->session()->get('cart', []);
        $query = Product::with('category')
            ->whereIn('id', array_keys($cart))
            ->where('status', 'active')
            ->where('is_active', true);
        if ($request->user()?->role === 'customer') {
            $query->forCustomerCatalog();
        } else {
            $query->forPublicCatalog();
        }
        $products = $query->get()->keyBy('id');
        $availableCart = collect($cart)
            ->filter(fn ($quantity, $productId) => $products->has((int) $productId))
            ->all();

        if (count($availableCart) !== count($cart)) {
            $request->session()->put('cart', $availableCart);
            $request->session()->flash('warning', 'Produk yang tidak lagi tersedia telah dihapus dari keranjang.');
        }

        $items = collect($availableCart)->map(function ($quantity, $productId) use ($products) {
            $product = $products->get($productId);
            return $product ? [
                'product' => $product,
                'quantity' => $quantity,
                'subtotal' => $product->harga * $quantity,
            ] : null;
        })->filter()->values();

        return view('backend.cart.index', [
            'items' => $items,
            'total' => $items->sum('subtotal'),
        ]);
    }

    public function add(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->status === 'active' && $product->is_active, 404);
        abort_unless($this->canAccessProduct($product, $request->user()), 404);

        $quantity = max(1, (int) $request->input('quantity', 1));
        $cart = $request->session()->get('cart', []);
        $currentQuantity = (int) ($cart[$product->id] ?? 0);

        if ($currentQuantity + $quantity > $product->stok) {
            return back()->with('error', "Stok {$product->name} tidak mencukupi.");
        }

        $cart[$product->id] = $currentQuantity + $quantity;
        $request->session()->put('cart', $cart);

        return back()->with('success', "{$product->name} ditambahkan ke keranjang.");
    }

    public function buyNow(Request $request, Product $product): View|RedirectResponse
    {
        abort_unless($product->status === 'active' && $product->is_active, 404);
        abort_unless($this->canAccessProduct($product, $request->user()), 404);

        if ($product->stok < 1) {
            return redirect()->route('customer.products')->with('error', "{$product->name} sedang habis.");
        }

        return view('backend.cart.buy-now', compact('product'));
    }

    public function checkoutNow(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->status === 'active' && $product->is_active, 404);
        abort_unless($this->canAccessProduct($product, $request->user()), 404);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
            'payment_method' => 'required|in:cash,qris,ewallet,transfer',
        ]);

        try {
            $transaction = $this->createTransaction(
                $request->user(),
                [$product->id => (int) $validated['quantity']],
                $validated['payment_method'],
            );
        } catch (\RuntimeException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->route('customer.transactions')
            ->with('success', "Pesanan {$transaction->invoice_number} diterima dan menunggu diproses admin.");
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->status === 'active' && $product->is_active, 404);
        abort_unless($this->canAccessProduct($product, $request->user()), 404);

        $quantity = (int) $request->input('quantity');
        $cart = $request->session()->get('cart', []);

        if ($quantity <= 0) {
            unset($cart[$product->id]);
        } elseif ($quantity <= $product->stok) {
            $cart[$product->id] = $quantity;
        } else {
            return back()->with('error', "Jumlah {$product->name} melebihi stok tersedia.");
        }

        $request->session()->put('cart', $cart);
        return back()->with('success', 'Keranjang diperbarui.');
    }

    public function remove(Request $request, Product $product): RedirectResponse
    {
        $cart = $request->session()->get('cart', []);
        unset($cart[$product->id]);
        $request->session()->put('cart', $cart);
        return back()->with('success', 'Produk dihapus dari keranjang.');
    }

    public function checkout(Request $request): RedirectResponse
    {
        abort_unless(Auth::check() && Auth::user()->role === 'customer', 403);

        $cart = $request->session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Keranjang masih kosong.');
        }

        $validated = $request->validate([
            'payment_method' => 'required|in:cash,qris,ewallet,transfer',
        ]);

        try {
            $transaction = $this->createTransaction(
                $request->user(),
                $cart,
                $validated['payment_method'],
            );
        } catch (\RuntimeException $exception) {
            return redirect()->route('cart.index')->with('error', $exception->getMessage());
        }

        $request->session()->forget('cart');
        return redirect()->route('customer.transactions')->with('success', "Pesanan {$transaction->invoice_number} diterima dan menunggu diproses admin.");
    }

    /**
     * @param array<int|string, int|string> $cart
     */
    private function createTransaction(User $user, array $cart, string $paymentMethod): Transaction
    {
        return DB::transaction(function () use ($user, $cart, $paymentMethod) {
            $total = 0;
            $details = [];

            foreach ($cart as $productId => $quantity) {
                $product = Product::whereKey($productId)->lockForUpdate()->firstOrFail();
                $quantity = (int) $quantity;

                if ($quantity < 1 || $product->status !== 'active' || ! $product->is_active) {
                    throw new \RuntimeException("Produk {$product->name} tidak tersedia.");
                }

                if (! $product->isVisibleToCustomers()) {
                    throw new \RuntimeException("Produk {$product->name} tidak tersedia untuk akun pelanggan.");
                }

                if ($product->stok < $quantity) {
                    throw new \RuntimeException("Stok {$product->name} tidak mencukupi.");
                }

                $subtotal = $product->harga * $quantity;
                $total += $subtotal;
                $details[] = compact('product', 'quantity', 'subtotal');
                $product->decrement('stok', $quantity);
            }

            $transaction = Transaction::create([
                'user_id' => $user->id,
                'customer_id' => $user->id,
                'total_payment' => $total,
                'payment_method' => $paymentMethod,
                'status' => 'pending',
            ]);

            foreach ($details as $detail) {
                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $detail['product']->id,
                    'quantity' => $detail['quantity'],
                    'price' => $detail['product']->harga,
                    'subtotal' => $detail['subtotal'],
                ]);
            }

            Payment::create([
                'transaction_id' => $transaction->id,
                'payment_code' => 'PAY-' . date('Ymd') . '-' . strtoupper(Str::random(6)),
                'amount' => $total,
                'status' => 'pending',
            ]);

            return $transaction;
        });
    }

    private function canAccessProduct(Product $product, ?User $user): bool
    {
        return $user?->role === 'customer'
            ? $product->isVisibleToCustomers()
            : $product->isVisibleToPublic();
    }
}
