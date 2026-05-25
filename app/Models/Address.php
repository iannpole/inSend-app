<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Address extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'addresses';

    protected $fillable = [
        'user_id',
        'label',            // "Rumah", "Kantor", "Kos"
        'recipient_name',
        'phone',
        'street',
        'city',
        'province',
        'postal_code',
        'district',
        'lat',
        'lng',
        'is_default',
        'notes',
    ];

    protected $casts = [
        'lat'        => 'float',
        'lng'        => 'float',
        'is_default' => 'boolean',
    ];

    protected $attributes = [
        'is_default' => false,
    ];

    // ─── Relations ──────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ─── Scopes ─────────────────────────────────────────────

    public function scopeByUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    // ─── Helpers ────────────────────────────────────────────

    /**
     * Get full address string
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->street,
            $this->district,
            $this->city,
            $this->province,
            $this->postal_code,
        ]);
        return implode(', ', $parts);
    }

    /**
     * Convert to shipping_address format for orders
     */
    public function toShippingAddress(): array
    {
        return [
            'label'          => $this->label,
            'recipient_name' => $this->recipient_name,
            'phone'          => $this->phone,
            'street'         => $this->street,
            'city'           => $this->city,
            'province'       => $this->province,
            'postal_code'    => $this->postal_code,
            'district'       => $this->district,
            'lat'            => $this->lat,
            'lng'            => $this->lng,
        ];
    }

    /**
     * Has coordinates
     */
    public function hasCoordinates(): bool
    {
        return !empty($this->lat) && !empty($this->lng);
    }
}
