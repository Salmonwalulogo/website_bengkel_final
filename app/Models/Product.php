<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    public const VISIBILITY_PUBLIC = 'public';
    public const VISIBILITY_CUSTOMER = 'customer';
    public const VISIBILITY_BOTH = 'both';

    protected $fillable = [
        'category_id',
        'name',
        'product_code',
        'barcode',
        'description',
        'harga',
        'stok',
        'foto',
        'status',
        'is_active',
        'visibility',
    ];

    protected $casts = [
        'harga' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relasi ke category
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    // Relasi ke transaction_details
    public function transactionDetails()
    {
        return $this->hasMany(TransactionDetail::class, 'product_id');
    }

    // Helper: cek apakah produk aktif
    public function isActive()
    {
        return $this->status === 'active' && $this->is_active;
    }

    public function isAutomotive(): bool
    {
        return $this->category?->isAutomotive() ?? false;
    }

    public function scopeForPublicCatalog(Builder $query): Builder
    {
        return $query->whereIn('visibility', [self::VISIBILITY_PUBLIC, self::VISIBILITY_BOTH]);
    }

    public function scopeForCustomerCatalog(Builder $query): Builder
    {
        return $query->whereIn('visibility', [self::VISIBILITY_CUSTOMER, self::VISIBILITY_BOTH]);
    }

    public function isVisibleToPublic(): bool
    {
        return in_array($this->visibility, [self::VISIBILITY_PUBLIC, self::VISIBILITY_BOTH], true);
    }

    public function isVisibleToCustomers(): bool
    {
        return in_array($this->visibility, [self::VISIBILITY_CUSTOMER, self::VISIBILITY_BOTH], true);
    }

    // Helper: format harga
    public function getFormattedHargaAttribute()
    {
        return 'Rp ' . number_format($this->harga, 0, ',', '.');
    }
}