<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'username',
        'no_hp',
        'role',
        'photo',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // ============ RELASI ============

    // Kendaraan milik user
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'user_id');
    }

    // Booking yang dibuat user (customer)
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'user_id');
    }

    // Transaksi user
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'user_id');
    }

    public function customerTransactions()
    {
        return $this->hasMany(Transaction::class, 'customer_id');
    }

    // Notifikasi user
    public function notifications()
    {
        return $this->hasMany(\App\Models\Notification::class, 'user_id');
    }

    // Audit logs
    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'user_id');
    }

    // Helper: cek role
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isCustomer()
    {
        return $this->role === 'customer';
    }

    public function isKasir()
    {
        return $this->role === 'kasir';
    }

    public function hasBusinessHistory(): bool
    {
        return $this->transactions()->exists()
            || $this->customerTransactions()->exists()
            || $this->bookings()->exists()
            || $this->vehicles()->exists()
            || $this->auditLogs()->exists();
    }
}