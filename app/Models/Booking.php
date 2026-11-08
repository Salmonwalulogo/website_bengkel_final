<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vehicle_id',
        'service_id',
        'booking_date',
        'booking_time',
        'request_details',
        'quoted_price',
        'complaint',
        'status',
        'notes',
    ];

    protected $casts = [
        'booking_date' => 'date',
    ];

    // ============ RELASI ============

    // Customer (user yang booking)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Kendaraan yang diservis
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    // Layanan yang dipilih
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    // Detail servis
    public function serviceDetails()
    {
        return $this->hasMany(ServiceDetail::class, 'booking_id');
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'booking_id');
    }

    // ============ HELPER ============

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'pending' => 'bg-warning text-dark',
            'confirmed' => 'bg-info text-dark',
            'in_progress' => 'bg-primary',
            'completed' => 'bg-success',
            'cancelled' => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'pending' => 'Menunggu',
            'confirmed' => 'Dikonfirmasi',
            'in_progress' => 'Sedang Dikerjakan',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }
}