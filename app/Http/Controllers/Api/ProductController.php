<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * GET /api/products
     * Query params: ?category=xxx&search=yyy&active=true&per_page=15
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::query();

        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        if ($request->boolean('active', true)) {
            $query->active();
        }

        $perPage = (int) $request->input('per_page', 15);
        $products = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'data' => ProductResource::collection($products->items()),
            'meta' => [
                'total'        => $products->total(),
                'per_page'     => $products->perPage(),
                'current_page' => $products->currentPage(),
                'last_page'    => $products->lastPage(),
            ],
        ]);
    }

    /**
     * POST /api/products — Admin only
     */
    public function store(ProductRequest $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        $data = $request->validated();

        // Handle multiple image uploads
        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('products', 'public');
            }
        }
        $data['images'] = $imagePaths;

        $product = Product::create($data);

        return response()->json([
            'message' => 'Produk berhasil dibuat',
            'data'    => new ProductResource($product),
        ], 201);
    }

    /**
     * GET /api/products/{id}
     */
    public function show(string $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        return response()->json(['data' => new ProductResource($product)]);
    }

    /**
     * PUT /api/products/{id} — Admin only
     */
    public function update(ProductRequest $request, string $id): JsonResponse
    {
        $this->authorizeAdmin($request);

        $product = Product::findOrFail($id);
        $data    = $request->validated();

        // Handle image uploads - append to existing
        if ($request->hasFile('images')) {
            $existingImages = $product->images ?? [];
            foreach ($request->file('images') as $image) {
                $existingImages[] = $image->store('products', 'public');
            }
            $data['images'] = $existingImages;
        }

        $product->update($data);

        return response()->json([
            'message' => 'Produk berhasil diupdate',
            'data'    => new ProductResource($product),
        ]);
    }

    /**
     * DELETE /api/products/{id} — Admin only
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $this->authorizeAdmin($request);

        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json(['message' => 'Produk berhasil dihapus']);
    }

    private function authorizeAdmin(Request $request): void
    {
        if (!$request->user()->isAdmin()) {
            abort(403, 'Hanya admin yang bisa mengelola produk');
        }
    }
}
