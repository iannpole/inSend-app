<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Wishlist extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'wishlists';

    protected $fillable = [
        'user_id',
        'product_id',
    ];

    // ─── Relations ──────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    // ─── Scopes ─────────────────────────────────────────────

    public function scopeByUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }
}
