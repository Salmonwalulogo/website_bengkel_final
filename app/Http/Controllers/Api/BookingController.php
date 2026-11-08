<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Booking::with(['user:id,name,email,no_hp', 'vehicle', 'service']);

        if ($user->isCustomer()) {
            $query->where('user_id', $user->id);
        }

        $bookings = $query->latest()->get();

        return response()->json(['data' => $bookings]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'user_id' => [Rule::requiredIf(fn () => ! $user->isCustomer() && blank($request->input('vehicle_id'))), 'nullable', Rule::exists('users', 'id')->where(
                fn ($query) => $query->where('role', 'customer')->orWhere('id', $user->id)
            )],
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'service_id' => ['required', Rule::exists('services', 'id')->where(fn ($query) => $query->where('status', 'active'))],
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'booking_time' => ['required', 'date_format:H:i'],
            'request_details' => ['nullable', 'string', 'max:3000'],
            'complaint' => ['nullable', 'string', 'max:3000'],
        ]);

        $vehicle = isset($validated['vehicle_id']) ? Vehicle::findOrFail($validated['vehicle_id']) : null;
        $ownerId = $user->isCustomer() ? $user->id : ($validated['user_id'] ?? $vehicle?->user_id);

        if ($vehicle && (int) $vehicle->user_id !== (int) $ownerId) {
            throw ValidationException::withMessages([
                'vehicle_id' => 'Kendaraan tidak dimiliki oleh pelanggan yang dipilih.',
            ]);
        }

        $booking = Booking::create([
            'user_id' => $ownerId,
            'vehicle_id' => $vehicle?->id,
            'service_id' => $validated['service_id'],
            'booking_date' => $validated['booking_date'],
            'booking_time' => $validated['booking_time'],
            'request_details' => $validated['request_details'] ?? $validated['complaint'] ?? null,
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'Permintaan layanan berhasil dibuat', 'data' => $booking->load(['vehicle', 'service'])], 201);
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        $user = $request->user();

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,confirmed,in_progress,completed,cancelled'],
            'notes' => ['nullable', 'string'],
            'quoted_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        if ($booking->transaction()->exists()
            && array_key_exists('quoted_price', $validated)
            && (float) ($validated['quoted_price'] ?? 0) !== (float) ($booking->quoted_price ?? 0)) {
            throw ValidationException::withMessages([
                'quoted_price' => 'Harga tidak dapat diubah setelah pembayaran dicatat.',
            ]);
        }

        if ($validated['status'] === 'confirmed'
            && ($validated['quoted_price'] ?? $booking->quoted_price) === null
            && (float) $booking->service->harga <= 0) {
            throw ValidationException::withMessages([
                'quoted_price' => 'Tetapkan harga konfirmasi sebelum menyetujui permintaan layanan ini.',
            ]);
        }

        $booking->update($validated);

        return response()->json(['message' => 'Status permintaan layanan diperbarui', 'data' => $booking->load(['vehicle', 'service'])]);
    }
}
