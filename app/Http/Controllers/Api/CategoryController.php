<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * GET /api/categories
     * List all active categories (nested with subcategories)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Category::active()->ordered();

        // Optionally filter root-only
        if ($request->boolean('roots_only', false)) {
            $query->roots();
        }

        $categories = $query->get();

        // Build nested tree if roots_only
        if ($request->boolean('roots_only', false)) {
            $categories->each(function ($category) {
                $category->setRelation('children',
                    Category::active()->where('parent_id', (string) $category->_id)->ordered()->get()
                );
            });
        }

        return response()->json([
            'status' => 'success',
            'data'   => CategoryResource::collection($categories),
        ]);
    }

    /**
     * GET /api/categories/{slug}
     */
    public function show(string $slug): JsonResponse
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        // Load children
        $category->setRelation('children',
            Category::active()->where('parent_id', (string) $category->_id)->ordered()->get()
        );

        return response()->json([
            'status' => 'success',
            'data'   => new CategoryResource($category),
        ]);
    }

    /**
     * POST /api/categories — Admin only
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'slug'        => ['required', 'string', 'max:100', 'unique:categories,slug'],
            'icon'        => ['nullable', 'string', 'max:50'],
            'image'       => ['nullable', 'string'],
            'parent_id'   => ['nullable', 'string'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
            'is_active'   => ['boolean'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $category = Category::create($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Kategori berhasil dibuat',
            'data'    => new CategoryResource($category),
        ], 201);
    }

    /**
     * PUT /api/categories/{id} — Admin only
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $this->authorizeAdmin($request);

        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name'        => ['sometimes', 'string', 'max:100'],
            'slug'        => ['sometimes', 'string', 'max:100'],
            'icon'        => ['nullable', 'string', 'max:50'],
            'image'       => ['nullable', 'string'],
            'parent_id'   => ['nullable', 'string'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
            'is_active'   => ['boolean'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $category->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Kategori berhasil diupdate',
            'data'    => new CategoryResource($category),
        ]);
    }

    /**
     * DELETE /api/categories/{id} — Admin only
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $this->authorizeAdmin($request);

        $category = Category::findOrFail($id);

        // Check if has children
        if ($category->hasChildren()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Tidak bisa hapus kategori yang memiliki sub-kategori',
            ], 422);
        }

        $category->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Kategori berhasil dihapus',
        ]);
    }

    private function authorizeAdmin(Request $request): void
    {
        if (!$request->user()->isAdmin()) {
            abort(403, 'Hanya admin yang bisa mengelola kategori');
        }
    }
}
