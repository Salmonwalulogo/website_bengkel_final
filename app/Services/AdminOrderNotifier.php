<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;

class AdminOrderNotifier
{
    public function serviceBooking(Booking $booking): void
    {
        $booking->loadMissing(['user', 'service']);

        $this->notifyAdmins(
            'Permintaan layanan baru',
            "{$booking->user->name} memesan layanan {$booking->service->name} untuk {$booking->booking_date->format('d/m/Y')} pukul {$booking->booking_time}.",
            'booking',
        );
    }

    public function productOrder(Transaction $transaction): void
    {
        $transaction->loadMissing(['customer', 'details']);

        $products = $transaction->details
            ->map(fn ($detail) => ($detail->product_name ?? 'Produk') . ' (' . $detail->quantity . ')')
            ->implode(', ');

        $this->notifyAdmins(
            'Pesanan produk baru',
            "{$transaction->customer->name} mengirim pesanan {$transaction->invoice_number}: {$products}.",
            'payment',
        );
    }

    public function paymentConfirmed(Payment $payment, User $cashier): void
    {
        $payment->loadMissing('transaction.customer');

        $transaction = $payment->transaction;
        $customerName = $transaction->customer?->name ?? 'Pelanggan umum';
        $amount = number_format((float) $payment->amount, 0, ',', '.');
        $confirmationMessage = "Kasir {$cashier->name} mengonfirmasi pembayaran {$payment->payment_code} untuk invoice {$transaction->invoice_number} ({$customerName}) sebesar Rp {$amount}.";

        $this->notifyAdmins(
            'Pembayaran dikonfirmasi kasir',
            $confirmationMessage,
            'payment',
        );

        if ($transaction->customer) {
            Notification::create([
                'user_id' => $transaction->customer->id,
                'title' => 'Pesanan Anda dikonfirmasi',
                'message' => "Pembayaran pesanan {$transaction->invoice_number} telah dikonfirmasi oleh kasir {$cashier->name}. Kode pembayaran: {$payment->payment_code}. Total: Rp {$amount}.",
                'type' => 'payment',
                'is_read' => false,
            ]);
        }
    }

    private function notifyAdmins(string $title, string $message, string $type): void
    {
        $this->notifyUsersWithRole('admin', $title, $message, $type);
    }

    private function notifyUsersWithRole(string $role, string $title, string $message, string $type): void
    {
        User::query()
            ->where('role', $role)
            ->each(fn (User $user) => Notification::create([
                'user_id' => $user->id,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'is_read' => false,
            ]));
    }
}
