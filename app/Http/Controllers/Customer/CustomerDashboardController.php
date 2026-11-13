<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Notification;
use App\Models\Product;
use App\Models\Service;
use App\Services\AdminOrderNotifier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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