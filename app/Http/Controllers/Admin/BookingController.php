<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    public function index()
    {
        $bookings = Booking::with(['user', 'vehicle', 'service'])
                          ->latest()
                          ->paginate(10);
        return view('backend.admin.bookings.index', compact('bookings'));
    }

    public function create()
    {
        $customers = User::where('role', 'customer')->get();
        $services = Service::where('status', 'active')->get();
        $vehicles = Vehicle::with('user')->get();
        return view('backend.admin.bookings.create', compact('customers', 'services', 'vehicles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'customer')),
            ],
            'vehicle_id' => [
                'nullable',
                Rule::exists('vehicles', 'id')->where(fn ($query) => $query->where('user_id', $request->input('user_id'))),
            ],
            'service_id' => ['required', Rule::exists('services', 'id')->where(fn ($query) => $query->where('status', 'active'))],
            'booking_date' => 'required|date',
            'booking_time' => 'required|date_format:H:i',
            'request_details' => 'nullable|string|max:3000',
            'quoted_price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:2000',
            'status' => 'required|in:pending,confirmed,in_progress,completed,cancelled',
        ]);

        if ($validated['status'] === 'confirmed' && ($validated['quoted_price'] ?? null) === null
            && (float) Service::findOrFail($validated['service_id'])->harga <= 0) {
            return back()->withErrors(['quoted_price' => 'Tetapkan harga konfirmasi sebelum menyetujui permintaan layanan ini.'])->withInput();
        }

        Booking::create($validated);

        return redirect()->route('admin.bookings.index')
            ->with('success', 'Permintaan layanan berhasil ditambahkan.');
    }

    public function show(Booking $booking)
    {
        $booking->load(['user', 'vehicle', 'service']);
        return view('backend.admin.bookings.show', compact('booking'));
    }

    public function edit(Booking $booking)
    {
        $customers = User::where('role', 'customer')->get();
        $services = Service::where('status', 'active')
            ->orWhere('id', $booking->service_id)
            ->get();
        $vehicles = Vehicle::with('user')->get();
        return view('backend.admin.bookings.edit', compact('booking', 'customers', 'services', 'vehicles'));
    }

    public function update(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'user_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'customer')),
            ],
            'vehicle_id' => [
                'nullable',
                Rule::exists('vehicles', 'id')->where(fn ($query) => $query->where('user_id', $request->input('user_id'))),
            ],
            'service_id' => [
                'required',
                Rule::exists('services', 'id')->where(fn ($query) => $query
                    ->where('status', 'active')
                    ->orWhere('id', $booking->service_id)),
            ],
            'booking_date' => 'required|date',
            'booking_time' => 'required|date_format:H:i',
            'request_details' => 'nullable|string|max:3000',
            'quoted_price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:2000',
            'status' => 'required|in:pending,confirmed,in_progress,completed,cancelled',
        ]);

        if ($validated['status'] === 'confirmed' && ($validated['quoted_price'] ?? null) === null
            && (float) Service::findOrFail($validated['service_id'])->harga <= 0) {
            return back()->withErrors(['quoted_price' => 'Tetapkan harga konfirmasi sebelum menyetujui permintaan layanan ini.'])->withInput();
        }

        if ($booking->transaction()->exists()
            && (float) ($validated['quoted_price'] ?? 0) !== (float) ($booking->quoted_price ?? 0)) {
            return back()->withErrors(['quoted_price' => 'Harga tidak dapat diubah setelah pembayaran dicatat.'])->withInput();
        }

        $booking->update($validated);

        return redirect()->route('admin.bookings.index')
            ->with('success', 'Booking berhasil diperbarui.');
    }

    public function destroy(Booking $booking)
    {
        if (! in_array($booking->status, ['pending', 'confirmed'], true)
            || $booking->transaction()->exists()) {
            return back()->withErrors([
                'booking' => 'Permintaan yang sudah dikerjakan atau dibayar tidak dapat dihapus atau dibatalkan.',
            ]);
        }

        $booking->update(['status' => 'cancelled']);

        return redirect()->route('admin.bookings.index')
            ->with('success', 'Permintaan layanan dibatalkan. Riwayat tetap tersimpan.');
    }
}