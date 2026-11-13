<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use App\Services\AdminOrderNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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

        $vehicleInfoByProduct = $request->session()->get('cart_vehicle_info', []);
        $items = collect($availableCart)->map(function ($quantity, $productId) use ($products, $vehicleInfoByProduct) {
            $product = $products->get($productId);
            return $product ? [
                'product' => $product,
                'quantity' => $quantity,
                'subtotal' => $product->harga * $quantity,
                'is_automotive' => $product->isAutomotive(),
                'vehicle_info' => $vehicleInfoByProduct[$product->id] ?? '',
            ] : null;
        })->filter()->values();

        return view('backend.cart.index', [
            'items' => $items,
            'total' => $items->sum('subtotal'),
            'vehicles' => $request->user()?->role === 'customer'
                ? $request->user()->vehicles()->orderBy('brand')->orderBy('model')->get()
                : collect(),
        ]);
    }

    public function add(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->status === 'active' && $product->is_active, 404);
        abort_unless($this->canAccessProduct($product, $request->user()), 404);

        $validated = $request->validate([
            'quantity' => ['sometimes', 'required', 'integer', 'min:1'],
            'vehicle_id' => ['nullable', 'integer'],
            'vehicle_brand' => ['nullable', 'string', 'max:255'],
        ]);
        $quantity = (int) ($validated['quantity'] ?? 1);
        $vehicleInfo = null;
        if ($product->isAutomotive()) {
            $vehicle = null;
            if ($request->filled('vehicle_id')) {
                if ($request->user()?->role !== 'customer') {
                    throw ValidationException::withMessages([
                        'vehicle_id' => 'Silakan tulis merek dan model kendaraan.',
                    ]);
                }

                $vehicle = $request->user()->vehicles()->find($validated['vehicle_id']);
                if (! $vehicle) {
                    throw ValidationException::withMessages([
                        'vehicle_id' => 'Pilih kendaraan yang terdaftar pada akun Anda.',
                    ]);
                }
            }

            $vehicleInfo = $vehicle
                ? trim("{$vehicle->brand} {$vehicle->model} ({$vehicle->plate_number})")
                : trim((string) ($validated['vehicle_brand'] ?? ''));

            $vehicleInfo = $vehicleInfo === '' ? null : $vehicleInfo;
        }

        $cart = $request->session()->get('cart', []);
        $currentQuantity = (int) ($cart[$product->id] ?? 0);

        if ($currentQuantity + $quantity > $product->stok) {
            return back()->withInput()->with('error', "Stok {$product->name} tidak mencukupi.");
        }

        $cart[$product->id] = $currentQuantity + $quantity;
        $request->session()->put('cart', $cart);
        if ($product->isAutomotive()) {
            $vehicleInfoByProduct = $request->session()->get('cart_vehicle_info', []);
            $vehicleInfoByProduct[$product->id] = $vehicleInfo;
            $request->session()->put('cart_vehicle_info', $vehicleInfoByProduct);
        }

        return back()->with('success', "{$product->name} ditambahkan ke keranjang.");
    }

    public function buyNow(Request $request, Product $product): View|RedirectResponse
    {
        abort_unless($product->status === 'active' && $product->is_active, 404);
        abort_unless($this->canAccessProduct($product, $request->user()), 404);

        if ($product->stok < 1) {
            return redirect()->route('customer.products')->with('error', "{$product->name} sedang habis.");
        }

        $vehicles = $request->user()?->vehicles()
            ->orderBy('brand')
            ->orderBy('model')
            ->get() ?? collect();

        return view('backend.cart.buy-now', compact('product', 'vehicles'));
    }

    public function checkoutNow(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->status === 'active' && $product->is_active, 404);
        abort_unless($this->canAccessProduct($product, $request->user()), 404);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
            'payment_method' => 'required|in:cash,qris,ewallet,transfer',
            'vehicle_id' => [
                'nullable',
                'integer',
                Rule::exists('vehicles', 'id')->where('user_id', $request->user()->id),
            ],
            'vehicle_brand' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $transaction = $this->createTransaction(
                $request->user(),
                [$product->id => (int) $validated['quantity']],
                $validated['payment_method'],
                [$product->id => $validated['vehicle_id'] ?? null],
                [$product->id => $validated['vehicle_brand'] ?? null],
                false,
            );
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        } catch (QueryException $exception) {
            report($exception);
            return back()->withInput()->with('error', 'Pesanan belum dapat diproses karena ada kendala pada sistem. Silakan coba kembali beberapa saat lagi.');
        } catch (\RuntimeException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->route('customer.transactions')
            ->with('success', "Pesanan {$transaction->invoice_number} diterima dan menunggu konfirmasi kasir.");
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->status === 'active' && $product->is_active, 404);
        abort_unless($this->canAccessProduct($product, $request->user()), 404);

        $quantity = (int) $request->input('quantity');
        $cart = $request->session()->get('cart', []);

        if ($quantity <= 0) {
            unset($cart[$product->id]);
            $vehicleInfoByProduct = $request->session()->get('cart_vehicle_info', []);
            unset($vehicleInfoByProduct[$product->id]);
            $request->session()->put('cart_vehicle_info', $vehicleInfoByProduct);
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
        $vehicleInfoByProduct = $request->session()->get('cart_vehicle_info', []);
        unset($vehicleInfoByProduct[$product->id]);
        $request->session()->put('cart_vehicle_info', $vehicleInfoByProduct);
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
            'vehicle_id' => ['sometimes', 'array'],
            'vehicle_id.*' => [
                'nullable',
                'integer',
                Rule::exists('vehicles', 'id')->where('user_id', $request->user()->id),
            ],
            'vehicle_brand' => ['sometimes', 'array'],
            'vehicle_brand.*' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $transaction = $this->createTransaction(
                $request->user(),
                $cart,
                $validated['payment_method'],
                $validated['vehicle_id'] ?? [],
                array_replace(
                    $request->session()->get('cart_vehicle_info', []),
                    $validated['vehicle_brand'] ?? [],
                ),
            );
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        } catch (QueryException $exception) {
            report($exception);
            return redirect()->route('cart.index')
                ->with('error', 'Pesanan belum dapat diproses karena ada kendala pada sistem. Silakan coba kembali beberapa saat lagi.');
        } catch (\RuntimeException $exception) {
            return redirect()->route('cart.index')->with('error', $exception->getMessage());
        }

        $request->session()->forget('cart');
        $request->session()->forget('cart_vehicle_info');
        return redirect()->route('customer.transactions')->with('success', "Pesanan {$transaction->invoice_number} diterima dan menunggu konfirmasi kasir.");
    }

    /**
     * @param array<int|string, int|string> $cart
     * @param array<int|string, int|string|null> $vehicleIds
     * @param array<int|string, string|null> $vehicleBrands
     */
    private function createTransaction(
        User $user,
        array $cart,
        string $paymentMethod,
        array $vehicleIds = [],
        array $vehicleBrands = [],
        bool $useProductErrorKeys = true,
    ): Transaction
    {
        return DB::transaction(function () use ($user, $cart, $paymentMethod, $vehicleIds, $vehicleBrands, $useProductErrorKeys) {
            $total = 0;
            $details = [];

            foreach ($cart as $productId => $quantity) {
                $product = Product::with('category')->whereKey($productId)->lockForUpdate()->firstOrFail();
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
                $vehicleInfo = $this->resolveVehicleInfo(
                    $user,
                    $product,
                    $vehicleIds[$productId] ?? null,
                    $vehicleBrands[$productId] ?? null,
                    $useProductErrorKeys ? (string) $productId : '',
                );
                $details[] = compact('product', 'quantity', 'subtotal', 'vehicleInfo');
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
                    'product_code' => $detail['product']->product_code,
                    'product_name' => $detail['product']->name,
                    'vehicle_info' => $detail['vehicleInfo'],
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

            app(AdminOrderNotifier::class)->productOrder($transaction);

            return $transaction;
        });
    }

    private function resolveVehicleInfo(
        User $user,
        Product $product,
        int|string|null $vehicleId,
        ?string $vehicleBrand,
        string $errorKey,
    ): ?string {
        if (! $product->isAutomotive()) {
            return null;
        }

        if ($vehicleId) {
            $vehicle = $user->vehicles()->whereKey($vehicleId)->first();
            if ($vehicle) {
                return trim("{$vehicle->brand} {$vehicle->model} ({$vehicle->plate_number})");
            }

            throw ValidationException::withMessages([
                $errorKey === '' ? 'vehicle_id' : "vehicle_id.{$errorKey}" => 'Kendaraan yang dipilih tidak ditemukan pada akun Anda.',
            ]);
        }

        $vehicleBrand = trim((string) $vehicleBrand);
        if ($vehicleBrand === '') {
            return null;
        }

        return $vehicleBrand;
    }

    private function canAccessProduct(Product $product, ?User $user): bool
    {
        return $user?->role === 'customer'
            ? $product->isVisibleToCustomers()
            : $product->isVisibleToPublic();
    }
}
