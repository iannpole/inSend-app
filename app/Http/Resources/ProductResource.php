<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $images = $this->images ?? [];
        $imageUrls = array_map(function ($img) {
            if (str_starts_with($img, 'http')) return $img;
            return asset('storage/' . $img);
        }, $images);

        return [
            'id'                  => (string) $this->_id,
            'slug'                => $this->slug,
            'name'                => $this->name,
            'description'         => $this->description,
            'category_slug'       => $this->category_slug,
            'base_price'          => $this->base_price ?? 0,
            'sale_price'          => $this->sale_price,
            'effective_price'     => $this->effective_price,
            'is_discounted'       => $this->is_discounted,
            'discount_percentage' => $this->discount_percentage,
            'discount_info'       => $this->discount_info,
            'formatted_price'     => $this->formatted_effective_price,
            'stock_quantity'      => $this->stock_quantity ?? 0,
            'low_stock_threshold' => $this->low_stock_threshold ?? 10,
            'unit'                => $this->unit,
            'is_active'           => $this->is_active ?? true,
            'tags'                => $this->tags ?? [],
            'attributes'          => $this->attributes ?? [],
            'images'              => $imageUrls,
            'average_rating'      => $this->average_rating ?? 0,
            'review_count'        => $this->review_count ?? 0,
            'created_at'          => $this->created_at?->toIso8601String(),
            'updated_at'          => $this->updated_at?->toIso8601String(),
        ];
    }
}
