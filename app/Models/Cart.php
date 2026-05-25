<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Cart extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'carts';

    protected $fillable = [
        'user_id',
        'items',    // [{ product_id, qty, price_snapshot, name, unit, image, added_at }]
        'notes',
    ];

    protected $casts = [];

    protected $attributes = [
        'items' => [],
    ];

    // ─── Relations ──────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ─── Item Management ────────────────────────────────────

    /**
     * Add item or update qty if already exists
     */
    public function addItem(string $productId, int $qty, float $priceSnapshot, string $name, ?string $unit = null, ?string $image = null): void
    {
        $items = $this->items ?? [];
        $found = false;

        foreach ($items as &$item) {
            if ($item['product_id'] === $productId) {
                $item['qty'] += $qty;
                $item['price_snapshot'] = $priceSnapshot; // update harga terbaru
                $found = true;
                break;
            }
        }

        if (!$found) {
            $items[] = [
                'product_id'     => $productId,
                'name'           => $name,
                'unit'           => $unit,
                'image'          => $image,
                'qty'            => $qty,
                'price_snapshot' => $priceSnapshot,
                'added_at'       => now()->toIso8601String(),
            ];
        }

        $this->items = $items;
        $this->save();
    }

    /**
     * Update item qty
     */
    public function updateItemQty(string $productId, int $qty): bool
    {
        $items = $this->items ?? [];
        $found = false;

        foreach ($items as &$item) {
            if ($item['product_id'] === $productId) {
                $item['qty'] = $qty;
                $found = true;
                break;
            }
        }

        if ($found) {
            $this->items = $items;
            $this->save();
        }

        return $found;
    }

    /**
     * Remove item from cart
     */
    public function removeItem(string $productId): bool
    {
        $items = $this->items ?? [];
        $original = count($items);

        $items = array_values(array_filter($items, fn($item) => $item['product_id'] !== $productId));

        if (count($items) < $original) {
            $this->items = $items;
            $this->save();
            return true;
        }

        return false;
    }

    /**
     * Clear all items
     */
    public function clearItems(): void
    {
        $this->items = [];
        $this->save();
    }

    // ─── Calculations ───────────────────────────────────────

    /**
     * Calculate cart subtotal
     */
    public function getSubtotal(): float
    {
        $items = $this->items ?? [];
        return array_reduce($items, function ($carry, $item) {
            return $carry + (($item['price_snapshot'] ?? 0) * ($item['qty'] ?? 0));
        }, 0);
    }

    /**
     * Get total item count
     */
    public function getItemCount(): int
    {
        $items = $this->items ?? [];
        return array_reduce($items, function ($carry, $item) {
            return $carry + ($item['qty'] ?? 0);
        }, 0);
    }
}
