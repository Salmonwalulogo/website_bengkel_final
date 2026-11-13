<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    // Relasi ke products
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public function isAutomotive(): bool
    {
        return Str::contains(Str::lower($this->name), [
            'otomotif',
            'kendaraan',
            'suku cadang',
            'ban',
            'oli',
            'pelumas',
            'motor',
            'mobil',
            'bengkel',
        ]);
    }
}