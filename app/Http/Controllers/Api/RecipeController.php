<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RecipeRequest;
use App\Http\Resources\RecipeResource;
use App\Models\Recipe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecipeController extends Controller
{
    /**
     * GET /api/recipes
     * Query: ?category=xxx&search=yyy&difficulty=easy&per_page=15
     */
    public function index(Request $request): JsonResponse
    {
        $query = Recipe::published();

        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source); // manual | ai_generated
        }

        $perPage = (int) $request->input('per_page', 12);
        $recipes = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'data' => RecipeResource::collection($recipes->items()),
            'meta' => [
                'total'        => $recipes->total(),
                'per_page'     => $recipes->perPage(),
                'current_page' => $recipes->currentPage(),
                'last_page'    => $recipes->lastPage(),
            ],
        ]);
    }

    /**
     * POST /api/recipes
     */
    public function store(RecipeRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Handle image uploads
        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('recipes', 'public');
            }
        }
        $data['images']      = $imagePaths;
        $data['created_by']  = (string) $request->user()->_id;
        $data['source']      = 'manual';

        $recipe = Recipe::create($data);

        return response()->json([
            'message' => 'Resep berhasil dibuat',
            'data'    => new RecipeResource($recipe),
        ], 201);
    }

    /**
     * GET /api/recipes/{id}
     */
    public function show(string $id): JsonResponse
    {
        $recipe = Recipe::findOrFail($id);
        return response()->json(['data' => new RecipeResource($recipe)]);
    }

    /**
     * PUT /api/recipes/{id}
     */
    public function update(RecipeRequest $request, string $id): JsonResponse
    {
        $recipe = Recipe::findOrFail($id);

        // Hanya author atau admin yang bisa edit
        if (!$request->user()->isAdmin() &&
            (string) $recipe->created_by !== (string) $request->user()->_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validated();

        if ($request->hasFile('images')) {
            $existing = $recipe->images ?? [];
            foreach ($request->file('images') as $image) {
                $existing[] = $image->store('recipes', 'public');
            }
            $data['images'] = $existing;
        }

        $recipe->update($data);

        return response()->json([
            'message' => 'Resep berhasil diupdate',
            'data'    => new RecipeResource($recipe),
        ]);
    }

    /**
     * DELETE /api/recipes/{id}
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $recipe = Recipe::findOrFail($id);

        if (!$request->user()->isAdmin() &&
            (string) $recipe->created_by !== (string) $request->user()->_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $recipe->delete();

        return response()->json(['message' => 'Resep berhasil dihapus']);
    }
}
