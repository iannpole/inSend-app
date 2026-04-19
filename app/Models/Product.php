<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Product extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'products';

    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'category',
        'images',    // array of image paths
        'is_active',
        'unit',      // satuan: gram, liter, pcs, dll
    ];

    protected $casts = [
        'price'     => 'float',
        'stock'     => 'integer',
        'is_active' => 'boolean',
        'images'    => 'array',
    ];

    protected $attributes = [
        'is_active' => true,
        'images'    => [],
    ];

    // Scope untuk produk aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope filter by category
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
