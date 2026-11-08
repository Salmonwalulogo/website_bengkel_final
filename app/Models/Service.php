<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'description',
        'harga',
        'duration',
        'status',
    ];

    protected $casts = [
        'harga' => 'decimal:2',
    ];

    // Relasi ke bookings
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'service_id');
    }

    // Relasi ke service_details
    public function serviceDetails()
    {
        return $this->hasManyThrough(
            ServiceDetail::class,
            Booking::class,
            'service_id',       // Foreign key di bookings
            'booking_id',       // Foreign key di service_details
            'id',               // Local key di services
            'id'                // Local key di bookings
        );
    }

    // Helper
    public function getFormattedHargaAttribute()
    {
        return 'Rp ' . number_format($this->harga, 0, ',', '.');
    }
}