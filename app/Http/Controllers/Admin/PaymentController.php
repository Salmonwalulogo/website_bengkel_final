<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with(['transaction.customer', 'transaction.user'])
                          ->latest()
                          ->paginate(10);
        return view('backend.admin.payments.index', compact('payments'));
    }
}