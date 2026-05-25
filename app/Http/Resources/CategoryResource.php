<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => (string) $this->_id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'icon'        => $this->icon,
            'image'       => $this->image,
            'description' => $this->description,
            'parent_id'   => $this->parent_id,
            'sort_order'  => $this->sort_order ?? 0,
            'is_active'   => $this->is_active ?? true,
            'children'    => $this->whenLoaded('children', function () {
                return CategoryResource::collection($this->children);
            }),
            'product_count' => $this->whenCounted('products'),
            'created_at'    => $this->created_at?->toIso8601String(),
            'updated_at'    => $this->updated_at?->toIso8601String(),
        ];
    }
}
