<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $images = $this->images ?? [];
        $imageUrls = array_map(fn($img) => asset('storage/' . $img), $images);

        return [
            'id'          => (string) $this->_id,
            'name'        => $this->name,
            'description' => $this->description,
            'price'       => $this->price,
            'stock'       => $this->stock,
            'category'    => $this->category,
            'unit'        => $this->unit,
            'is_active'   => $this->is_active,
            'images'      => $imageUrls,
            'created_at'  => $this->created_at?->toIso8601String(),
            'updated_at'  => $this->updated_at?->toIso8601String(),
        ];
    }
}
