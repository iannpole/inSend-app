<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecipeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $images = $this->images ?? [];
        $imageUrls = array_map(fn($img) => str_starts_with($img, 'http') ? $img : asset('storage/' . $img), $images);

        return [
            'id'           => (string) $this->_id,
            'title'        => $this->title,
            'description'  => $this->description,
            'ingredients'  => $this->ingredients ?? [],
            'steps'        => $this->steps ?? [],
            'category'     => $this->category,
            'prep_time'    => $this->prep_time,
            'cook_time'    => $this->cook_time,
            'total_time'   => ($this->prep_time ?? 0) + ($this->cook_time ?? 0),
            'servings'     => $this->servings,
            'difficulty'   => $this->difficulty,
            'tags'         => $this->tags ?? [],
            'images'       => $imageUrls,
            'is_published' => $this->is_published,
            'source'       => $this->source,
            'nutrition'    => $this->nutrition,
            'created_by'   => (string) $this->created_by,
            'created_at'   => $this->created_at?->toIso8601String(),
            'updated_at'   => $this->updated_at?->toIso8601String(),
        ];
    }
}
