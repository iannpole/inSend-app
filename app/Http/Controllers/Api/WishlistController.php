<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    /**
     * GET /api/wishlist — List user's wishlist
     */
    public function index(Request $request): JsonResponse
    {
        $userId = (string) $request->user()->_id;

        $wishlistItems = Wishlist::byUser($userId)
            ->orderByDesc('created_at')
            ->get();

        // Enrich with product data
        $items = $wishlistItems->map(function ($item) {
            $product = Product::find($item->product_id);
            return [
                'id'         => (string) $item->_id,
                'product_id' => $item->product_id,
                'product'    => $product ? (new ProductResource($product))->resolve() : null,
                'added_at'   => $item->created_at?->toIso8601String(),
            ];
        })->filter(fn($item) => $item['product'] !== null)->values();

        return response()->json([
            'status' => 'success',
            'data'   => $items,
            'meta'   => ['total' => $items->count()],
        ]);
    }

    /**
     * POST /api/wishlist — Add product to wishlist (toggle)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'string'],
        ]);

        $userId    = (string) $request->user()->_id;
        $productId = $validated['product_id'];

        // Check if product exists
        $product = Product::find($productId);
        if (!$product) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Produk tidak ditemukan',
            ], 404);
        }

        // Check if already in wishlist
        $existing = Wishlist::byUser($userId)
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            // Toggle off — remove
            $existing->delete();
            return response()->json([
                'status'    => 'success',
                'message'   => "'{$product->name}' dihapus dari wishlist",
                'wishlisted'=> false,
            ]);
        }

        // Add to wishlist
        Wishlist::create([
            'user_id'    => $userId,
            'product_id' => $productId,
        ]);

        return response()->json([
            'status'    => 'success',
            'message'   => "'{$product->name}' ditambahkan ke wishlist",
            'wishlisted'=> true,
        ], 201);
    }

    /**
     * DELETE /api/wishlist/{product_id} — Remove from wishlist
     */
    public function destroy(Request $request, string $productId): JsonResponse
    {
        $userId = (string) $request->user()->_id;

        $item = Wishlist::byUser($userId)
            ->where('product_id', $productId)
            ->first();

        if (!$item) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Item tidak ditemukan di wishlist',
            ], 404);
        }

        $item->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Item dihapus dari wishlist',
        ]);
    }

    /**
     * GET /api/wishlist/check/{product_id} — Check if product is wishlisted
     */
    public function check(Request $request, string $productId): JsonResponse
    {
        $userId = (string) $request->user()->_id;

        $exists = Wishlist::byUser($userId)
            ->where('product_id', $productId)
            ->exists();

        return response()->json([
            'status'    => 'success',
            'wishlisted'=> $exists,
        ]);
    }
}
