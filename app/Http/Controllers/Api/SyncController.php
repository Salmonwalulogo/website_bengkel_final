<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Transaction;
use App\Models\Vehicle;
use App\Services\ApiTransactionRecorder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SyncController extends Controller
{
    public function batchSync(Request $request)
    {
        $user = $request->user();

        if ($user->isCustomer() && $request->input('transactions', []) !== []) {
            return response()->json(['message' => 'Customer tidak diizinkan menyinkronkan transaksi kasir.'], 403);
        }

        $validated = $request->validate([
            'vehicles' => ['sometimes', 'array'],
            'vehicles.*.client_id' => ['nullable', 'string'],
            'vehicles.*.user_id' => ['nullable', Rule::exists('users', 'id')->where(
                fn ($query) => $query->where('role', 'customer')->orWhere('id', $user->id)
            )],
            'vehicles.*.plate_number' => ['required', 'string', 'max:20'],
            'vehicles.*.brand' => ['required', 'string', 'max:100'],
            'vehicles.*.model' => ['required', 'string', 'max:100'],
            'vehicles.*.year' => ['nullable', 'integer'],
            'vehicles.*.color' => ['nullable', 'string', 'max:50'],
            'bookings' => ['sometimes', 'array'],
            'bookings.*.client_id' => ['nullable', 'string'],
            'bookings.*.user_id' => ['nullable', Rule::exists('users', 'id')->where(
                fn ($query) => $query->where('role', 'customer')->orWhere('id', $user->id)
            )],
            'bookings.*.vehicle_id' => ['required', 'exists:vehicles,id'],
            'bookings.*.service_id' => ['required', Rule::exists('services', 'id')->where(fn ($query) => $query->where('status', 'active'))],
            'bookings.*.booking_date' => ['required', 'date'],
            'bookings.*.booking_time' => ['nullable', 'date_format:H:i'],
            'bookings.*.complaint' => ['nullable', 'string', 'max:2000'],
            'transactions' => ['sometimes', 'array'],
            'transactions.*.client_uuid' => ['nullable', 'string', 'max:255'],
            'transactions.*.client_id' => ['nullable', 'string', 'max:255'],
            'transactions.*.customer_id' => ['nullable', Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'customer'))],
            'transactions.*.subtotal' => ['sometimes', 'numeric', 'min:0'],
            'transactions.*.discount' => ['nullable', 'numeric', 'min:0'],
            'transactions.*.tax' => ['nullable', 'numeric', 'min:0'],
            'transactions.*.total_payment' => ['sometimes', 'numeric', 'min:0'],
            'transactions.*.paid_amount' => ['required', 'numeric', 'min:0'],
            'transactions.*.payment_method' => ['sometimes', 'string', Rule::in(['cash', 'qris', 'ewallet', 'transfer'])],
            'transactions.*.items' => ['required', 'array', 'min:1'],
            'transactions.*.items.*.product_id' => ['required', 'exists:products,id'],
            'transactions.*.items.*.item_name' => ['sometimes', 'string', 'max:255'],
            'transactions.*.items.*.quantity' => ['required', 'integer', 'min:1'],
            'transactions.*.items.*.price' => ['sometimes', 'numeric', 'min:0'],
        ]);

        $vehicles = $validated['vehicles'] ?? [];
        $bookings = $validated['bookings'] ?? [];
        $transactions = $validated['transactions'] ?? [];

        $syncedVehicles = [];
        $syncedBookings = [];
        $syncedTransactions = [];

        // 1. Sync Vehicles
        foreach ($vehicles as $v) {
            $ownerId = $user->isCustomer() ? $user->id : ($v['user_id'] ?? $user->id);
            $plateNumber = strtoupper($v['plate_number']);
            $vehicle = Vehicle::where('plate_number', $plateNumber)->first();

            if ($user->isCustomer() && $vehicle && (int) $vehicle->user_id !== (int) $user->id) {
                abort(403, 'Anda tidak diizinkan mengubah kendaraan milik pengguna lain.');
            }

            $attributes = [
                'user_id' => $ownerId,
                'brand' => $v['brand'],
                'model' => $v['model'],
                'year' => $v['year'] ?? date('Y'),
                'color' => $v['color'] ?? '-',
            ];

            if ($vehicle) {
                $vehicle->update($attributes);
            } else {
                $vehicle = Vehicle::create(['plate_number' => $plateNumber, ...$attributes]);
            }

            $syncedVehicles[] = [
                'client_id' => $v['client_id'] ?? null,
                'server_id' => $vehicle->id,
            ];
        }

        // 2. Sync Bookings
        foreach ($bookings as $b) {
            $vehicle = Vehicle::findOrFail($b['vehicle_id']);
            $ownerId = $user->isCustomer() ? $user->id : ($b['user_id'] ?? $vehicle->user_id);

            if ((int) $vehicle->user_id !== (int) $ownerId) {
                throw ValidationException::withMessages([
                    'bookings' => 'Kendaraan tidak dimiliki oleh pelanggan yang dipilih.',
                ]);
            }

            $booking = Booking::create([
                'user_id' => $ownerId,
                'vehicle_id' => $b['vehicle_id'],
                'service_id' => $b['service_id'],
                'booking_date' => $b['booking_date'],
                'booking_time' => $b['booking_time'] ?? date('H:i'),
                'complaint' => $b['complaint'] ?? '-',
                'status' => 'pending',
            ]);
            $syncedBookings[] = [
                'client_id' => $b['client_id'] ?? null,
                'server_id' => $booking->id,
            ];
        }

        // 3. Sync Transactions
        foreach ($transactions as $t) {
            if ($user->isCustomer()) {
                continue;
            }

            $uuid = $t['client_uuid'] ?? ($t['client_id'] ?? null);
            if (! empty($uuid)) {
                $existing = Transaction::where('user_id', $user->id)
                    ->where('checkout_token', $uuid)
                    ->first();
                if ($existing) {
                    $syncedTransactions[] = [
                        'client_id' => $uuid,
                        'server_id' => $existing->id,
                    ];
                    continue;
                }
            }

            $transaction = app(ApiTransactionRecorder::class)->record($user, [
                ...$t,
                'client_uuid' => $uuid,
                'payment_method' => $t['payment_method'] ?? 'cash',
            ]);

            $syncedTransactions[] = [
                'client_id' => $uuid,
                'server_id' => $transaction->id,
            ];
        }

        return response()->json([
            'message' => 'Sinkronisasi berhasil',
            'synced' => [
                'vehicles' => $syncedVehicles,
                'bookings' => $syncedBookings,
                'transactions' => $syncedTransactions,
            ],
        ]);
    }
}
