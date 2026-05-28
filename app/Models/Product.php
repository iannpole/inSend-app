<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Carbon\Carbon;

class Product extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'products';
    protected $primaryKey = '_id';
    protected $keyType = 'string';
    public $incrementing = false;

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->findOrFail($value);
    }
    protected $fillable = [
        'slug',
        'name',
        'category_slug',
        'base_price',
        'sale_price',
        'discount_info',
        'stock_quantity',
        'low_stock_threshold',
        'unit',
        'description',
        'images',
        'attributes',
        'tags',
        'is_active',
        'average_rating',
        'review_count',
    ];

    protected $casts = [
        'base_price'          => 'float',
        'sale_price'          => 'float',
        'stock_quantity'      => 'integer',
        'low_stock_threshold' => 'integer',
        'is_active'           => 'boolean',
        'average_rating'      => 'float',
        'review_count'        => 'integer',
    ];

    protected $attributes = [
        'is_active'           => true,
        'images'              => [],
        'low_stock_threshold' => 10,
        'average_rating'      => 0,
        'review_count'        => 0,
    ];

    // ─── Accessors ───────────────────────────────────────────

    /**
     * Cek apakah diskon sedang aktif (flag aktif + dalam periode)
     */
    public function getIsDiscountedAttribute(): bool
    {
        $info = $this->discount_info;

        if (!$info || !is_array($info) || empty($info['is_active'])) {
            return false;
        }

        // Cek apakah dalam periode diskon
        $now = Carbon::now();
        $start = !empty($info['start_date']) ? Carbon::parse($info['start_date']) : null;
        $end   = !empty($info['end_date'])   ? Carbon::parse($info['end_date'])   : null;

        if ($start && $end) {
            return $now->between($start, $end);
        }

        return (bool) $info['is_active'];
    }

    /**
     * Return harga efektif (sale_price jika diskon aktif, base_price jika tidak)
     */
    public function getEffectivePriceAttribute(): float
    {
        if ($this->is_discounted && $this->sale_price && $this->sale_price < $this->base_price) {
            return $this->sale_price;
        }
        return $this->base_price ?? 0;
    }

    /**
     * Persentase diskon
     */
    public function getDiscountPercentageAttribute(): int
    {
        $info = $this->discount_info;
        return $info['percentage'] ?? 0;
    }

    /**
     * Nama campaign diskon
     */
    public function getCampaignNameAttribute(): string
    {
        $info = $this->discount_info;
        return $info['campaign_name'] ?? '';
    }

    /**
     * Format harga base: Rp 15.000
     */
    public function getFormattedBasePriceAttribute(): string
    {
        return 'Rp ' . number_format($this->base_price ?? 0, 0, ',', '.');
    }

    /**
     * Format harga sale: Rp 7.200
     */
    public function getFormattedSalePriceAttribute(): string
    {
        return 'Rp ' . number_format($this->sale_price ?? 0, 0, ',', '.');
    }

    /**
     * Format harga efektif: Rp 7.200
     */
    public function getFormattedEffectivePriceAttribute(): string
    {
        return 'Rp ' . number_format($this->effective_price, 0, ',', '.');
    }

    // ─── Scopes ──────────────────────────────────────────────

    /**
     * Scope untuk produk aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope filter by category slug
     */
    public function scopeByCategory($query, string $categorySlug)
    {
        return $query->where('category_slug', $categorySlug);
    }

    /**
     * Scope: products with low stock
     */
    public function scopeLowStock($query)
    {
        return $query->whereRaw([
            '$expr' => [
                '$lte' => ['$stock_quantity', '$low_stock_threshold']
            ]
        ]);
    }

    /**
     * Scope: in-stock products only
     */
    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    // ─── Inventory Helpers ──────────────────────────────────

    /**
     * Check if product is low on stock
     */
    public function isLowStock(): bool
    {
        return ($this->stock_quantity ?? 0) <= ($this->low_stock_threshold ?? 10);
    }

    /**
     * Check if product is out of stock
     */
    public function isOutOfStock(): bool
    {
        return ($this->stock_quantity ?? 0) <= 0;
    }

    // ─── Relations ──────────────────────────────────────────

    public function reviews()
    {
        return $this->hasMany(Review::class, 'product_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_slug', 'slug');
    }
}
