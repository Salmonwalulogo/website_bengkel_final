<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Product;
use App\Models\TransactionDetail;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

class CashierDashboardController extends Controller
{
    public function index()
    {
        return view('backend.cashier.dashboard', [
            'pendingOrders' => Booking::whereIn('status', ['pending', 'confirmed'])->count(),
            'todayPayments' => Payment::where('status', 'paid')
                ->whereDate('payment_date', Carbon::today())
                ->count(),
            'totalTransactions' => Transaction::count(),
        ]);
    }

    public function orders()
    {
        $bookings = Booking::with(['user', 'vehicle', 'service', 'transaction.payment'])
            ->latest('booking_date')
            ->paginate(10);

        return view('backend.cashier.orders', compact('bookings'));
    }

    public function payServiceBooking(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'payment_method' => ['required', 'in:cash,qris,debit,kredit,ewallet,transfer'],
        ]);

        $transaction = DB::transaction(function () use ($booking, $validated, $request) {
            $booking = Booking::with('service')->lockForUpdate()->findOrFail($booking->id);

            if ($booking->status !== 'confirmed') {
                throw ValidationException::withMessages([
                    'booking' => 'Pembayaran hanya dapat dicatat untuk permintaan yang sudah dikonfirmasi admin.',
                ]);
            }

            $existingTransaction = Transaction::where('booking_id', $booking->id)->first();
            if ($existingTransaction) {
                return $existingTransaction;
            }

            $amount = (float) ($booking->quoted_price ?? $booking->service->harga);
            if ($amount <= 0) {
                throw ValidationException::withMessages([
                    'booking' => 'Admin harus menetapkan harga layanan sebelum kasir memproses pembayaran.',
                ]);
            }

            $transaction = Transaction::create([
                'user_id' => $request->user()->id,
                'customer_id' => $booking->user_id,
                'booking_id' => $booking->id,
                'checkout_token' => (string) Str::uuid(),
                'subtotal' => $amount,
                'discount' => 0,
                'tax' => 0,
                'other_fee' => 0,
                'total_payment' => $amount,
                'paid_amount' => $amount,
                'change_amount' => 0,
                'payment_method' => $validated['payment_method'],
                'status' => 'completed',
            ]);

            Payment::create([
                'transaction_id' => $transaction->id,
                'payment_code' => 'PAY-' . strtoupper(Str::random(12)),
                'amount' => $amount,
                'payment_date' => now(),
                'status' => 'paid',
            ]);

            return $transaction;
        });

        return redirect()->route('kasir.orders')
            ->with('success', 'Pembayaran layanan tercatat dengan invoice ' . $transaction->invoice_number . '.');
    }

    public function payments()
    {
        $payments = Payment::with('transaction')
            ->latest('payment_date')
            ->paginate(10);

        return view('backend.cashier.payments', compact('payments'));
    }

    public function invoices()
    {
        $transactions = Transaction::with(['user', 'customer', 'booking.service'])
            ->latest()
            ->paginate(10);

        return view('backend.cashier.invoices', compact('transactions'));
    }

    public function pos()
    {
        $products = $this->activePosProducts();

        $customers = User::where('role', 'customer')->orderBy('name')->get();

        return view('backend.cashier.pos', [
            'products' => $products,
            'customers' => $customers,
            'transactionNumber' => Transaction::nextInvoiceNumber(),
            'receiptNumber' => Transaction::nextReceiptNumber(),
        ]);
    }

    public function productCatalog()
    {
        $products = $this->activePosProducts()->map(fn (Product $product) => [
            'id' => $product->id,
            'code' => $product->product_code,
            'barcode' => $product->barcode,
            'name' => $product->name,
            'price' => (float) $product->harga,
            'stock' => $product->stok,
        ])->values();

        return response()->json($products)->header('Cache-Control', 'no-store, private');
    }

    private function activePosProducts()
    {
        return Product::query()
            ->where('status', 'active')
            ->where('is_active', true)
            ->where('stok', '>', 0)
            ->orderBy('name')
            ->get();
    }

    public function storeTransaction(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'customer')),
            ],
            'items' => ['required', 'array', 'min:1'],
            'checkout_token' => ['required', 'uuid'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_nominal' => ['nullable', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'other_fee' => ['nullable', 'numeric', 'min:0'],
            'paid_amount' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:cash,qris,debit,kredit,ewallet,transfer'],
        ]);

        $existingTransaction = Transaction::where('checkout_token', $validated['checkout_token'])
            ->where('user_id', $request->user()->id)
            ->first();
        if ($existingTransaction) {
            return redirect()->route('kasir.pos')->with('receipt', $existingTransaction->load(['user', 'customer', 'details']));
        }

        try {
            $transaction = DB::transaction(function () use ($validated, $request) {
            $subtotal = 0;
            $details = [];
            $itemsByProduct = collect($validated['items'])
                ->groupBy('product_id')
                ->map(fn ($items) => [
                    'product_id' => (int) $items->first()['product_id'],
                    'quantity' => $items->sum('quantity'),
                ])
                ->values();

            if ($validated['customer_id'] ?? null) {
                User::whereKey($validated['customer_id'])
                    ->where('role', 'customer')
                    ->firstOrFail();
            }

            foreach ($itemsByProduct as $item) {
                $product = Product::whereKey($item['product_id'])->lockForUpdate()->firstOrFail();
                if ((float) $product->harga < 0) {
                    throw ValidationException::withMessages([
                        'items' => "Harga produk {$product->name} tidak valid.",
                    ]);
                }
                if (!$product->isActive() || $product->stok < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => "Stok {$product->name} tidak mencukupi.",
                    ]);
                }

                $lineSubtotal = round((float) $product->harga * $item['quantity']);
                $subtotal += $lineSubtotal;
                $details[] = compact('product', 'item', 'lineSubtotal');
            }

            $discountPercent = (float) ($validated['discount_percent'] ?? 0);
            $discountNominal = (float) ($validated['discount_nominal'] ?? 0);
            $discount = min($subtotal, round(($subtotal * $discountPercent / 100) + $discountNominal));
            $tax = round((float) ($validated['tax'] ?? 0));
            $otherFee = round((float) ($validated['other_fee'] ?? 0));
            $grandTotal = max(0, $subtotal - $discount + $tax + $otherFee);
            $paidAmount = round((float) $validated['paid_amount']);

            if ($paidAmount < $grandTotal) {
                throw ValidationException::withMessages([
                    'paid_amount' => 'Pembayaran kurang dari total transaksi.',
                ]);
            }

            $transaction = Transaction::create([
                'user_id' => $request->user()->id,
                'checkout_token' => $validated['checkout_token'],
                'customer_id' => $validated['customer_id'] ?? null,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'other_fee' => $otherFee,
                'total_payment' => $grandTotal,
                'paid_amount' => $paidAmount,
                'change_amount' => $paidAmount - $grandTotal,
                'payment_method' => $validated['payment_method'],
                'status' => 'completed',
            ]);

            foreach ($details as $detail) {
                $detail['product']->decrement('stok', $detail['item']['quantity']);
                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $detail['product']->id,
                    'product_code' => $detail['product']->product_code,
                    'product_name' => $detail['product']->name,
                    'quantity' => $detail['item']['quantity'],
                    'price' => $detail['product']->harga,
                    'subtotal' => $detail['lineSubtotal'],
                ]);
            }

            Payment::create([
                'transaction_id' => $transaction->id,
                'payment_code' => 'PAY-' . strtoupper(Str::random(12)),
                'amount' => $paidAmount,
                'payment_date' => now(),
                'status' => 'paid',
            ]);

                return $transaction->load(['user', 'customer', 'details']);
            });
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        if ($this->usesServerPrinter()) {
            try {
                $this->sendEscPosReceipt($transaction);
            } catch (\Throwable $exception) {
                $printerQueue = $this->resolveActivePrinterQueue();

                Log::error('ESC/POS receipt printing failed after checkout.', [
                    'transaction_id' => $transaction->id,
                    'printer_queue' => $printerQueue,
                    'exception' => $exception->getMessage(),
                ]);

                return redirect()->route('kasir.pos')
                    ->with('receipt', $transaction)
                    ->with('error', 'Transaksi berhasil, tetapi struk gagal dicetak. Periksa printer aktif yang dipilih dan coba cetak ulang.');
            }

            return redirect()->route('kasir.pos')
                ->with('receipt', $transaction)
                ->with('status', 'Transaksi berhasil dan struk telah dikirim ke printer aktif.');
        }

        return redirect()->route('kasir.pos')
            ->with('receipt', $transaction)
            ->with('status', 'Transaksi berhasil. Klik CETAK STRUK untuk mencetak dari perangkat kasir.');
    }

    public function printReceipt(Transaction $transaction)
    {
        $transaction->load(['user', 'customer', 'details', 'booking.service']);

        if (! $this->usesServerPrinter()) {
            return response()
                ->view('backend.cashier.print-receipt', ['transaction' => $transaction])
                ->header('Cache-Control', 'no-store, private');
        }

        try {
            $this->sendEscPosReceipt($transaction);
        } catch (\Throwable $exception) {
            $printerQueue = $this->resolveActivePrinterQueue();

            Log::error('ESC/POS receipt reprint failed.', [
                'transaction_id' => $transaction->id,
                'printer_queue' => $printerQueue,
                'exception' => $exception->getMessage(),
            ]);

            return redirect()->route('kasir.pos')
                ->with('receipt', $transaction)
                ->with('error', 'Struk gagal dikirim ke printer aktif. Pastikan printer yang dipilih sudah tersedia dan antrean sesuai.');
        }

        return redirect()->route('kasir.pos')
            ->with('receipt', $transaction)
            ->with('status', 'Struk berhasil dikirim ke printer aktif.');
    }

    private function usesServerPrinter(): bool
    {
        $driver = config('escpos.driver');

        if (! in_array($driver, ['browser', 'windows'], true)) {
            throw new \LogicException('ESCPOS_DRIVER must be either browser or windows.');
        }

        if ($driver === 'windows' && PHP_OS_FAMILY !== 'Windows') {
            throw new \RuntimeException('The Windows ESC/POS printer driver requires a Windows server.');
        }

        return $driver === 'windows';
    }

    private function resolveActivePrinterQueue(): string
    {
        $defaultQueue = trim((string) (config('escpos.windows_queue') ?? 'POS80-NEWW'));

        try {
            $selectedQueue = trim((string) DB::table('settings')
                ->where('key', 'thermal_printer_queue')
                ->value('value'));

            if ($selectedQueue !== '') {
                return $selectedQueue;
            }
        } catch (\Throwable $exception) {
            Log::debug('Unable to read thermal printer queue from settings table.', [
                'exception' => $exception->getMessage(),
            ]);
        }

        return $defaultQueue !== '' ? $defaultQueue : 'POS80-NEWW';
    }

    private function sendEscPosReceipt(Transaction $transaction): void
    {
        $transaction->load(['user', 'customer', 'details', 'booking.service']);
        $printerQueue = $this->resolveActivePrinterQueue();
        $printer = new Printer(new WindowsPrintConnector($printerQueue));
        $safeText = static fn ($value) => preg_replace('/[\x00-\x1F\x7F]/', ' ', (string) $value) ?? '';
        $lineWidth = 42;
        $separator = str_repeat('-', $lineWidth) . "\n";
        $formatMoney = static fn ($amount) => 'Rp ' . number_format((float) $amount, 0, ',', '.');

        try {
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setEmphasis(true);
            $printer->text("UD RUMAH DAUN\n");
            $printer->text("Bukti Pembayaran\n");
            $printer->setEmphasis(false);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text($separator);
            $printer->setEmphasis(true);
            $printer->text("No. Transaksi: {$transaction->invoice_number}\n");
            $printer->text("No. Struk: {$transaction->receipt_number}\n");
            $printer->setEmphasis(false);
            $printer->text("Tanggal: {$transaction->created_at->format('d/m/Y H:i')}\n");
            $printer->text('Pelanggan: ' . $safeText(optional($transaction->customer)->name ?? 'Umum') . "\n");
            $printer->text($separator);

            foreach ($transaction->details as $detail) {
                $printer->setEmphasis(true);
                $printer->text($safeText($detail->product_name ?? optional($detail->product)->name ?? 'Produk') . "\n");
                $printer->setEmphasis(false);
                $itemPrice = sprintf(
                    "%d x Rp %s",
                    $detail->quantity,
                    number_format((float) $detail->price, 0, ',', '.')
                );
                $itemTotal = $formatMoney($detail->subtotal);
                $padding = max(1, $lineWidth - strlen($itemPrice) - strlen($itemTotal));
                $printer->text($itemPrice . str_repeat(' ', $padding) . $itemTotal . "\n");
            }

            if ($transaction->booking?->service) {
                $service = $transaction->booking->service;
                $serviceAmount = $transaction->total_payment;
                $printer->setEmphasis(true);
                $printer->text($safeText($service->name) . "\n");
                $printer->setEmphasis(false);
                $itemPrice = '1 x ' . $formatMoney($serviceAmount);
                $itemTotal = $formatMoney($serviceAmount);
                $padding = max(1, $lineWidth - strlen($itemPrice) - strlen($itemTotal));
                $printer->text($itemPrice . str_repeat(' ', $padding) . $itemTotal . "\n");
            }

            $printer->text($separator);
            foreach ([
                'Subtotal' => $transaction->subtotal,
                'Diskon' => $transaction->discount,
                'Pajak' => $transaction->tax,
                'Biaya lain' => $transaction->other_fee,
            ] as $label => $amount) {
                $value = $formatMoney($amount);
                $padding = max(1, $lineWidth - strlen($label) - strlen($value));
                $printer->text($label . str_repeat(' ', $padding) . $value . "\n");
            }
            $printer->setEmphasis(true);
            $total = $formatMoney($transaction->total_payment);
            $padding = max(1, $lineWidth - strlen('TOTAL') - strlen($total));
            $printer->text('TOTAL' . str_repeat(' ', $padding) . $total . "\n");
            $paymentLabel = $safeText($transaction->payment_method_label);
            $paidAmount = $formatMoney($transaction->paid_amount);
            $printer->text($paymentLabel . str_repeat(
                ' ',
                max(1, $lineWidth - strlen($paymentLabel) - strlen($paidAmount))
            ) . $paidAmount . "\n");
            $printer->text('Kembalian' . str_repeat(
                ' ',
                max(1, $lineWidth - strlen('Kembalian') - strlen($formatMoney($transaction->change_amount)))
            ) . $formatMoney($transaction->change_amount) . "\n");
            $printer->setEmphasis(false);
            $printer->text($separator);
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("\nTerima kasih telah berbelanja.\n");
            $printer->feed(3);
            $printer->cut();
        } finally {
            $printer->close();
        }
    }
}