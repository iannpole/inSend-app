<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => (string) $this->_id,
            'order_number'     => $this->order_number,
            'user_id'          => (string) $this->user_id,
            'items'            => $this->items ?? [],
            'subtotal'         => $this->subtotal ?? $this->total_price,
            'delivery_fee'     => $this->delivery_fee ?? 0,
            'discount_amount'  => $this->discount_amount ?? 0,
            'total_price'      => $this->total_price,
            'status'           => $this->status,
            'payment_status'   => $this->payment_status ?? 'pending',
            'payment_method'   => $this->payment_method,
            'payment_url'      => $this->payment_url,
            'payment_token'    => $this->payment_token,
            'promo_code'       => $this->promo_code,
            'shipping_address' => $this->shipping_address,
            'address_id'       => $this->address_id,
            'notes'            => $this->notes,
            'paid_at'          => $this->paid_at?->toIso8601String(),
            'shipped_at'       => $this->shipped_at?->toIso8601String(),
            'delivered_at'     => $this->delivered_at?->toIso8601String(),
            'created_at'       => $this->created_at?->toIso8601String(),
            'updated_at'       => $this->updated_at?->toIso8601String(),
        ];
    }
}
