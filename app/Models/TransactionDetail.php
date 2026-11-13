<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionDetail extends Model
{
    use HasFactory;

    protected $table = 'transaction_details';

    protected $fillable = [
        'transaction_id',
        'product_id',
        'product_code',
        'product_name',
        'vehicle_info',
        'quantity',
        'price',
        'subtotal',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'quantity' => 'integer',
    ];

    // Relasi ke transaction
    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    // Relasi ke product
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    // Auto hitung subtotal saat save
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($detail) {
            $detail->subtotal = $detail->quantity * $detail->price;
        });
    }
}