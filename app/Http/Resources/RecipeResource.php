<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecipeResource extends JsonResource
{
    /**
     * Build a full storage URL from a relative path.
     * Returns null if the path is empty/null.
     */
    private function resolveImageUrl(?string $path): ?string
    {
        if (!$path) return null;
        return str_starts_with($path, 'http') ? $path : asset('storage/' . $path);
    }

    public function toArray(Request $request): array
    {
        $rawImages = $this->images ?? [];

        // Only the first image is resolved eagerly — used as thumbnail in list views.
        // Full images array is resolved lazily only when accessing recipe detail.
        $thumbnailPath = $rawImages[0] ?? null;
        // Deteksi apakah ini detail view (show) atau list view (index)
        $routeAction = $request->route()?->getActionMethod();
        $isDetail    = in_array($routeAction, ['show']) || $request->boolean('with_images');

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
            // thumbnail: satu gambar untuk list view — hemat bandwidth
            'thumbnail'    => $this->resolveImageUrl($thumbnailPath),
            // images: semua gambar, hanya dikirim saat detail view atau ?with_images=1
            'images'       => $isDetail
                ? array_values(array_filter(array_map(
                    fn($img) => $this->resolveImageUrl($img), $rawImages
                )))
                : [],
            'is_published' => $this->is_published,
            'source'       => $this->source,
            'nutrition'    => $this->nutrition,
            'created_by'   => (string) $this->created_by,
            'created_at'   => $this->created_at?->toIso8601String(),
            'updated_at'   => $this->updated_at?->toIso8601String(),
        ];
    }
}
