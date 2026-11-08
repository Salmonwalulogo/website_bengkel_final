<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_id',
        'booking_id',
        'invoice_number',
        'checkout_token',
        'subtotal',
        'discount',
        'tax',
        'other_fee',
        'total_payment',
        'paid_amount',
        'change_amount',
        'status',
        'payment_method',
    ];

    protected $casts = [
        'total_payment' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'other_fee' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
    ];

    // Auto generate invoice number saat create
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (empty($transaction->invoice_number)) {
                $transaction->invoice_number = 'TRX-' . now()->format('Ymd') . '-TMP-' . Str::uuid();
            }
            if (empty($transaction->receipt_number)) {
                $transaction->receipt_number = 'STR-' . now()->format('Ymd') . '-TMP-' . Str::uuid();
            }
        });

        static::created(function ($transaction) {
            $transaction->forceFill([
                'invoice_number' => 'TRX-' . $transaction->created_at->format('Ymd') . '-' . str_pad((string) $transaction->id, 6, '0', STR_PAD_LEFT),
                'receipt_number' => 'STR-' . $transaction->created_at->format('Ymd') . '-' . str_pad((string) $transaction->id, 6, '0', STR_PAD_LEFT),
            ])->saveQuietly();
        });
    }

    public static function nextInvoiceNumber(): string
    {
        $prefix = 'TRX-' . now()->format('Ymd') . '-';
        $lastNumber = (int) static::max('id');

        return $prefix . str_pad((string) ($lastNumber + 1), 6, '0', STR_PAD_LEFT);
    }

    public static function nextReceiptNumber(): string
    {
        $prefix = 'STR-' . now()->format('Ymd') . '-';
        $lastNumber = (int) static::max('id');

        return $prefix . str_pad((string) ($lastNumber + 1), 6, '0', STR_PAD_LEFT);
    }

    // ============ RELASI ============

    // User/customer
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    // Detail transaksi (produk yang dibeli)
    public function details()
    {
        return $this->hasMany(TransactionDetail::class, 'transaction_id');
    }

    // Pembayaran
    public function payment()
    {
        return $this->hasOne(Payment::class, 'transaction_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    // ============ HELPER ============

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'pending' => 'bg-warning text-dark',
            'completed' => 'bg-success',
            'cancelled' => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'pending' => 'Menunggu',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }

    public function getPaymentMethodLabelAttribute()
    {
        return match($this->payment_method) {
            'cash' => 'Tunai',
            'qris' => 'QRIS',
            'debit' => 'Debit',
            'kredit' => 'Kredit',
            'ewallet' => 'E-Wallet',
            'transfer' => 'Transfer',
            default => ucfirst($this->payment_method),
        };
    }
}