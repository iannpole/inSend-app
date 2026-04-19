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
            'user_id'          => (string) $this->user_id,
            'items'            => $this->items ?? [],
            'total_price'      => $this->total_price,
            'status'           => $this->status,
            'shipping_address' => $this->shipping_address,
            'notes'            => $this->notes,
            'payment_method'   => $this->payment_method,
            'paid_at'          => $this->paid_at?->toIso8601String(),
            'created_at'       => $this->created_at?->toIso8601String(),
            'updated_at'       => $this->updated_at?->toIso8601String(),
        ];
    }
}
