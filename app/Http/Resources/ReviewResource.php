<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => (string) $this->_id,
            'user_id'      => (string) $this->user_id,
            'order_id'     => $this->order_id,
            'product_id'   => $this->product_id,
            'rating'       => $this->rating,
            'comment'      => $this->comment,
            'images'       => $this->images ?? [],
            'created_at'   => $this->created_at?->toIso8601String(),
            'updated_at'   => $this->updated_at?->toIso8601String(),
        ];
    }
}
