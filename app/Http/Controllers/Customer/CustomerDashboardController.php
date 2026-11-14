<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Notification;
use App\Models\Product;
use App\Models\Service;
use App\Models\Transaction;
use App\Services\AdminOrderNotifier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CustomerDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return view('backend.customer.dashboard', [
            'transactionCount' => $user->customerTransactions()->count(),
            'serviceCount' => Service::where('status', 'active')->count(),
            'activeBookingCount' => $user->bookings()->whereIn('status', ['pending', 'confirmed', 'in_progress'])->count(),
            'cartCount' => array_sum(session('cart', [])),
        ]);
    }

    public function products()
    {
        $products = Product::where('status', 'active')
            ->where('is_active', true)
            ->forCustomerCatalog()
            ->with('category')
            ->latest()
            ->paginate(12);

        return view('backend.customer.products', [
            'products' => $products,
            'vehicles' => Auth::user()->vehicles()->orderBy('brand')->orderBy('model')->get(),
        ]);
    }

    public function services()
    {
        $services = Service::where('status', 'active')
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy(fn (Service $service) => $service->category ?: 'Lainnya');

        return view('backend.customer.services', compact('services'));
    }

    public function vehicles()
    {
        $vehicles = Auth::user()->vehicles()->latest()->paginate(10);
        return view('backend.customer.vehicles', compact('vehicles'));
    }

    public function storeVehicle(Request $request)
    {
        $validated = $request->validate([
            'plate_number' => 'required|string|max:20',
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'color' => 'nullable|string|max:50',
        ]);

        Auth::user()->vehicles()->create($validated);
        return back()->with('success', 'Kendaraan berhasil ditambahkan.');
    }

    public function destroyVehicle($vehicle)
    {
        Auth::user()->vehicles()->findOrFail($vehicle)->delete();
        return back()->with('success', 'Kendaraan berhasil dihapus.');
    }

    public function createBooking()
    {
        $vehicles = Auth::user()->vehicles()->orderBy('plate_number')->get();
        $services = Service::where('status', 'active')->orderBy('name')->get();
        return view('backend.customer.booking-create', [
            'vehicles' => $vehicles,
            'services' => $services,
            'selectedService' => request()->query('service'),
        ]);
    }

    public function storeBooking(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'service_id' => [
                'required',
                Rule::exists('services', 'id')->where(fn ($query) => $query->where('status', 'active')),
            ],
            'booking_date' => 'required|date|after_or_equal:today',
            'booking_time' => 'required|date_format:H:i',
            'request_details' => 'nullable|string|max:3000',
        ]);

        $service = Service::findOrFail($validated['service_id']);

        if (! $service->isAutomotive()) {
            $validated['vehicle_id'] = null;
        }

        if (!empty($validated['vehicle_id'])) {
            $vehicleOwned = Auth::user()->vehicles()->whereKey($validated['vehicle_id'])->exists();
            abort_unless($vehicleOwned, 403);
        }

        DB::transaction(function () use ($validated) {
            $booking = Auth::user()->bookings()->create($validated);
            app(AdminOrderNotifier::class)->serviceBooking($booking);
        });

        return redirect()->route('customer.bookings')->with('success', 'Permintaan layanan berhasil dikirim dan menunggu konfirmasi admin.');
    }

    public function bookings()
    {
        $bookings = Auth::user()->bookings()
            ->with(['vehicle', 'service', 'transaction.payment'])
            ->latest('booking_date')
            ->paginate(10);
        return view('backend.customer.bookings', compact('bookings'));
    }

    public function createBookingPayment(Booking $booking)
    {
        abort_unless($booking->user_id === Auth::id(), 404);
        $booking->load(['service', 'transaction.payment']);

        abort_unless(
            in_array($booking->status, ['confirmed', 'completed'], true),
            422,
            'Pembayaran tersedia setelah harga dan pesanan dikonfirmasi admin.'
        );

        $settings = DB::table('settings')->pluck('value', 'key')->all();
        $amount = (float) ($booking->quoted_price ?? $booking->service->harga);

        return view('backend.customer.booking-payment', compact('booking', 'settings', 'amount'));
    }

    public function storeBookingPayment(Request $request, Booking $booking)
    {
        abort_unless($booking->user_id === Auth::id(), 404);

        $validated = $request->validate([
            'payment_method' => ['required', Rule::in(['transfer', 'qris'])],
            'proof_image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        $booking->load('service');
        abort_unless(
            in_array($booking->status, ['confirmed', 'completed'], true),
            422,
            'Pembayaran tersedia setelah harga dan pesanan dikonfirmasi admin.'
        );

        $settings = DB::table('settings')->pluck('value', 'key')->all();
        if ($validated['payment_method'] === 'transfer'
            && (empty($settings['bank_name']) || empty($settings['bank_account_number']) || empty($settings['bank_account_name']))) {
            throw ValidationException::withMessages([
                'payment_method' => 'Informasi rekening transfer belum diatur oleh admin.',
            ]);
        }
        if ($validated['payment_method'] === 'qris' && empty($settings['qris_image'])) {
            throw ValidationException::withMessages([
                'payment_method' => 'QRIS belum diatur oleh admin.',
            ]);
        }

        $proofPath = $request->file('proof_image')->store('payment-proofs', 'local');
        $oldProofPath = null;
        try {
            DB::transaction(function () use ($booking, $validated, $request, $proofPath, &$oldProofPath) {
                $lockedBooking = Booking::query()->lockForUpdate()->findOrFail($booking->id);
                if (! in_array($lockedBooking->status, ['confirmed', 'completed'], true)) {
                    throw ValidationException::withMessages([
                        'booking' => 'Pesanan ini tidak dapat dibayar karena statusnya sudah berubah.',
                    ]);
                }
                $lockedBooking->load('service');
                $amount = (float) ($lockedBooking->quoted_price ?? $lockedBooking->service->harga);
                if ($amount <= 0) {
                    throw ValidationException::withMessages([
                        'booking' => 'Admin harus menetapkan harga layanan sebelum pembayaran.',
                    ]);
                }

                $transaction = $lockedBooking->transaction()->lockForUpdate()->first();
                if ($transaction && ($transaction->status !== 'pending'
                    || $transaction->payment()->where('status', 'paid')->exists())) {
                    throw ValidationException::withMessages([
                        'booking' => 'Pembayaran untuk pesanan ini sudah selesai atau tidak dapat diubah.',
                    ]);
                }

                if (! $transaction) {
                    $transaction = $lockedBooking->transaction()->create([
                        'user_id' => $request->user()->id,
                        'customer_id' => $request->user()->id,
                        'checkout_token' => (string) Str::uuid(),
                        'subtotal' => $amount,
                        'discount' => 0,
                        'tax' => 0,
                        'other_fee' => 0,
                        'total_payment' => $amount,
                        'paid_amount' => 0,
                        'change_amount' => 0,
                        'payment_method' => $validated['payment_method'],
                        'status' => 'pending',
                    ]);
                } else {
                    $transaction->update(['payment_method' => $validated['payment_method']]);
                }

                $payment = $transaction->payment()->lockForUpdate()->first();
                if ($payment && $payment->status !== 'pending') {
                    throw ValidationException::withMessages([
                        'booking' => 'Status pembayaran sudah berubah dan tidak dapat diperbarui.',
                    ]);
                }

                $oldProofPath = $payment?->proof_image;
                $paymentData = [
                    'amount' => $amount,
                    'payment_date' => null,
                    'status' => 'pending',
                    'proof_image' => $proofPath,
                ];
                if ($payment) {
                    $payment->update($paymentData);
                } else {
                    $transaction->payment()->create(array_merge($paymentData, [
                        'payment_code' => 'PAY-' . strtoupper(Str::random(12)),
                    ]));
                }
            });
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($proofPath);
            throw $exception;
        }

        if ($oldProofPath) {
            Storage::disk('local')->delete($oldProofPath);
        }

        return redirect()->route('customer.bookings')
            ->with('success', 'Bukti pembayaran terkirim. Pembayaran menunggu verifikasi kasir.');
    }

    public function cancelBooking(Booking $booking)
    {
        abort_unless($booking->user_id === Auth::id(), 404);
        abort_unless(
            in_array($booking->status, ['pending', 'confirmed'], true) && !$booking->transaction()->exists(),
            422,
            'Permintaan yang sudah diproses pembayarannya tidak dapat dibatalkan melalui akun pelanggan.'
        );

        $booking->update(['status' => 'cancelled']);

        return redirect()->route('customer.bookings')->with('success', 'Permintaan layanan dibatalkan.');
    }

    public function transactions()
    {
        $transactions = Auth::user()->customerTransactions()->with(['payment', 'booking.service', 'details'])->latest()->paginate(10);
        return view('backend.customer.transactions', compact('transactions'));
    }

    public function createTransactionPayment(Transaction $transaction)
    {
        $transaction = Auth::user()->customerTransactions()
            ->whereNull('booking_id')
            ->with(['payment', 'details'])
            ->findOrFail($transaction->id);

        abort_unless(
            $transaction->status === 'pending' && $transaction->payment?->status === 'pending',
            422,
            'Pembayaran ini sudah selesai atau tidak dapat diubah.'
        );

        $settings = DB::table('settings')->pluck('value', 'key')->all();
        $amount = (float) $transaction->total_payment;

        return view('backend.customer.transaction-payment', compact('transaction', 'settings', 'amount'));
    }

    public function storeTransactionPayment(Request $request, Transaction $transaction)
    {
        $transaction = Auth::user()->customerTransactions()
            ->whereNull('booking_id')
            ->findOrFail($transaction->id);

        $validated = $request->validate([
            'payment_method' => ['required', Rule::in(['transfer', 'qris'])],
            'proof_image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        $settings = DB::table('settings')->pluck('value', 'key')->all();
        if ($validated['payment_method'] === 'transfer'
            && (empty($settings['bank_name']) || empty($settings['bank_account_number']) || empty($settings['bank_account_name']))) {
            throw ValidationException::withMessages([
                'payment_method' => 'Informasi rekening transfer belum diatur oleh admin.',
            ]);
        }
        if ($validated['payment_method'] === 'qris' && empty($settings['qris_image'])) {
            throw ValidationException::withMessages([
                'payment_method' => 'QRIS belum diatur oleh admin.',
            ]);
        }

        $proofPath = $request->file('proof_image')->store('payment-proofs', 'local');
        $oldProofPath = null;
        try {
            DB::transaction(function () use ($transaction, $validated, $proofPath, &$oldProofPath) {
                $lockedTransaction = Transaction::query()
                    ->where('customer_id', Auth::id())
                    ->whereNull('booking_id')
                    ->lockForUpdate()
                    ->findOrFail($transaction->id);
                $payment = $lockedTransaction->payment()->lockForUpdate()->first();

                if ($lockedTransaction->status !== 'pending' || ! $payment || $payment->status !== 'pending') {
                    throw ValidationException::withMessages([
                        'payment' => 'Pembayaran ini sudah selesai atau tidak dapat diubah.',
                    ]);
                }

                $oldProofPath = $payment->proof_image;
                $payment->update([
                    'amount' => $lockedTransaction->total_payment,
                    'payment_date' => null,
                    'status' => 'pending',
                    'proof_image' => $proofPath,
                ]);
                $lockedTransaction->update(['payment_method' => $validated['payment_method']]);
            });
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($proofPath);
            throw $exception;
        }

        if ($oldProofPath) {
            Storage::disk('local')->delete($oldProofPath);
        }

        return redirect()->route('customer.transactions')
            ->with('success', 'Bukti pembayaran terkirim. Pembayaran menunggu verifikasi kasir.');
    }

    public function notifications()
    {
        $notifications = Auth::user()->notifications()->latest()->paginate(15);
        return view('backend.customer.notifications', compact('notifications'));
    }

    public function markNotificationAsRead($notification)
    {
        Auth::user()->notifications()->whereKey($notification)->update(['is_read' => true]);
        return back()->with('success', 'Notifikasi ditandai sudah dibaca.');
    }
}