<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        $user = $request->user();

        if ($user->isCustomer()) {
            return response()->json([
                'vehicles_count' => Vehicle::where('user_id', $user->id)->count(),
                'active_bookings' => Booking::where('user_id', $user->id)->whereIn('status', ['pending', 'confirmed', 'in_progress'])->count(),
                'completed_bookings' => Booking::where('user_id', $user->id)->where('status', 'completed')->count(),
                'total_transactions' => Transaction::where(function ($query) use ($user) {
                    $query->where('user_id', $user->id)->orWhere('customer_id', $user->id);
                })->count(),
            ]);
        }

        if ($user->isKasir()) {
            return response()->json([
                'pending_orders' => Booking::whereIn('status', ['pending', 'confirmed'])->count(),
                'today_payments' => Payment::where('status', 'paid')->whereDate('payment_date', now()->toDateString())->count(),
                'total_transactions' => Transaction::count(),
            ]);
        }

        $totalCustomers = User::where('role', 'customer')->count();
        $totalVehicles = Vehicle::count();
        $totalBookings = Booking::count();
        $totalTransactions = Transaction::count();
        $totalIncome = (float) Transaction::where('status', 'completed')->sum('total_payment');

        return response()->json([
            'total_customers' => $totalCustomers,
            'total_vehicles' => $totalVehicles,
            'total_bookings' => $totalBookings,
            'total_transactions' => $totalTransactions,
            'total_income' => $totalIncome,
        ]);
    }
}
