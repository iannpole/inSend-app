<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Order extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'orders';

    // Order status constants
    const STATUS_PENDING          = 'pending';
    const STATUS_AWAITING_PAYMENT = 'awaiting_payment';
    const STATUS_PAID             = 'paid';
    const STATUS_PROCESSING       = 'processing';
    const STATUS_SHIPPED          = 'shipped';
    const STATUS_DELIVERED        = 'delivered';
    const STATUS_CANCELLED        = 'cancelled';
    const STATUS_PAYMENT_EXPIRED  = 'payment_expired';

    // Payment status constants
    const PAYMENT_PENDING  = 'pending';
    const PAYMENT_PAID     = 'paid';
    const PAYMENT_FAILED   = 'failed';
    const PAYMENT_EXPIRED  = 'expired';
    const PAYMENT_REFUNDED = 'refunded';

    protected $fillable = [
        'user_id',
        'order_number',
        'items',            // array: [{ product_id, name, qty, price, unit, subtotal }]
        'subtotal',
        'delivery_fee',
        'discount_amount',
        'total_price',
        'status',
        'shipping_address', // { label, recipient_name, phone, street, city, province, postal_code, lat, lng }
        'address_id',
        'notes',
        'payment_method',
        'payment_status',
        'payment_id',       // Midtrans transaction_id
        'payment_url',      // Midtrans snap_url
        'payment_token',    // Midtrans snap_token
        'promo_id',
        'promo_code',
        'paid_at',
        'shipped_at',
        'delivered_at',
    ];

    protected $casts = [
        'subtotal'        => 'float',
        'delivery_fee'    => 'float',
        'discount_amount' => 'float',
        'total_price'     => 'float',
        'paid_at'         => 'datetime',
        'shipped_at'      => 'datetime',
        'delivered_at'    => 'datetime',
    ];

    protected $attributes = [
        'status'         => self::STATUS_PENDING,
        'payment_status' => self::PAYMENT_PENDING,
        'delivery_fee'   => 0,
        'discount_amount'=> 0,
    ];

    // ─── Relations ──────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ─── Status Helpers ─────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_PAID;
    }

    public function isAwaitingPayment(): bool
    {
        return $this->status === self::STATUS_AWAITING_PAYMENT;
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_AWAITING_PAYMENT,
        ]);
    }

    public function isReviewable(): bool
    {
        return $this->status === self::STATUS_DELIVERED;
    }

    // ─── Generate Order Number ──────────────────────────────

    public static function generateOrderNumber(): string
    {
        $prefix = 'INS';
        $date   = now()->format('Ymd');
        $random = strtoupper(substr(uniqid(), -5));
        return "{$prefix}-{$date}-{$random}";
    }

    // ─── Scopes ─────────────────────────────────────────────

    public function scopeByUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
