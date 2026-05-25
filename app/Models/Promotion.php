<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Promotion extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'promotions';

    const TYPE_PERCENTAGE    = 'percentage';
    const TYPE_FIXED         = 'fixed';
    const TYPE_FREE_SHIPPING = 'free_shipping';

    protected $fillable = [
        'code',
        'name',
        'description',
        'type',           // percentage | fixed | free_shipping
        'value',          // discount value (percentage or nominal)
        'min_order',      // minimum order amount
        'max_discount',   // maximum discount cap (for percentage)
        'usage_limit',    // total usage limit
        'used_count',     // current usage count
        'per_user_limit', // usage limit per user
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'value'          => 'float',
        'min_order'      => 'float',
        'max_discount'   => 'float',
        'usage_limit'    => 'integer',
        'used_count'     => 'integer',
        'per_user_limit' => 'integer',
        'is_active'      => 'boolean',
        'start_date'     => 'datetime',
        'end_date'       => 'datetime',
    ];

    protected $attributes = [
        'is_active'      => true,
        'used_count'     => 0,
        'per_user_limit' => 1,
    ];

    // ─── Scopes ─────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeValid($query)
    {
        $now = now();
        return $query->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('start_date')
                  ->orWhere('start_date', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', $now);
            });
    }

    // ─── Validation Helpers ─────────────────────────────────

    /**
     * Check if promo is valid for a given order amount
     */
    public function isValid(float $orderAmount): bool
    {
        if (!$this->is_active) return false;

        // Check date range
        $now = now();
        if ($this->start_date && $now->lt($this->start_date)) return false;
        if ($this->end_date && $now->gt($this->end_date)) return false;

        // Check usage limit
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) return false;

        // Check minimum order
        if ($this->min_order && $orderAmount < $this->min_order) return false;

        return true;
    }

    /**
     * Calculate discount amount
     */
    public function calculateDiscount(float $subtotal, float $deliveryFee = 0): float
    {
        switch ($this->type) {
            case self::TYPE_PERCENTAGE:
                $discount = $subtotal * ($this->value / 100);
                if ($this->max_discount) {
                    $discount = min($discount, $this->max_discount);
                }
                return round($discount);

            case self::TYPE_FIXED:
                return min($this->value, $subtotal); // Can't exceed order

            case self::TYPE_FREE_SHIPPING:
                return $deliveryFee;

            default:
                return 0;
        }
    }

    /**
     * Get formatted display string
     */
    public function getDisplayValueAttribute(): string
    {
        switch ($this->type) {
            case self::TYPE_PERCENTAGE:
                $text = "{$this->value}%";
                if ($this->max_discount) {
                    $text .= ' (maks Rp ' . number_format($this->max_discount, 0, ',', '.') . ')';
                }
                return $text;

            case self::TYPE_FIXED:
                return 'Rp ' . number_format($this->value, 0, ',', '.');

            case self::TYPE_FREE_SHIPPING:
                return 'Gratis Ongkir';

            default:
                return '-';
        }
    }
}
