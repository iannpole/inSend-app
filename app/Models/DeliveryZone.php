<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class DeliveryZone extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'delivery_zones';

    protected $fillable = [
        'name',         // "Jakarta Selatan", "Tangerang"
        'slug',
        'base_fee',     // ongkir dasar zona ini
        'per_km_fee',   // tambahan per km
        'min_order',    // minimum order untuk zona ini
        'max_distance', // max jarak (km) dari toko
        'is_active',
        'estimated_time', // "30-60 menit"
    ];

    protected $casts = [
        'base_fee'     => 'float',
        'per_km_fee'   => 'float',
        'min_order'    => 'float',
        'max_distance' => 'float',
        'is_active'    => 'boolean',
    ];

    protected $attributes = [
        'is_active'    => true,
        'base_fee'     => 10000,
        'per_km_fee'   => 2000,
        'max_distance' => 15,
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
