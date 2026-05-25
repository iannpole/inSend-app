<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * GET /api/admin/inventory/low-stock — List products below threshold (admin only)
     */
    public function lowStock(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        // MongoDB doesn't support $expr with eloquent easily,
        // so we use raw query to compare stock_quantity <= low_stock_threshold
        $products = Product::where('is_active', true)
            ->get()
            ->filter(function ($product) {
                return $product->isLowStock();
            })
            ->sortBy('stock_quantity')
            ->values();

        $data = $products->map(function ($product) {
            return [
                'id'                  => (string) $product->_id,
                'name'                => $product->name,
                'category_slug'       => $product->category_slug,
                'stock_quantity'      => $product->stock_quantity ?? 0,
                'low_stock_threshold' => $product->low_stock_threshold ?? 10,
                'is_out_of_stock'     => $product->isOutOfStock(),
                'effective_price'     => $product->effective_price,
                'unit'                => $product->unit,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $data,
            'meta'   => [
                'total_low_stock'  => $products->count(),
                'total_out_of_stock' => $products->filter(fn($p) => $p->isOutOfStock())->count(),
            ],
        ]);
    }

    /**
     * PATCH /api/admin/inventory/{id}/stock — Quick stock update (admin only)
     */
    public function updateStock(Request $request, string $id): JsonResponse
    {
        $this->authorizeAdmin($request);

        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'stock_quantity'      => ['required', 'integer', 'min:0'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
        ]);

        $product->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => "Stok '{$product->name}' berhasil diupdate ke {$validated['stock_quantity']}",
            'data'    => [
                'id'                  => (string) $product->_id,
                'name'                => $product->name,
                'stock_quantity'      => $product->stock_quantity,
                'low_stock_threshold' => $product->low_stock_threshold,
                'is_low_stock'        => $product->isLowStock(),
            ],
        ]);
    }

    /**
     * GET /api/admin/inventory/summary — Inventory overview (admin only)
     */
    public function summary(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        $allProducts = Product::where('is_active', true)->get();

        $totalProducts  = $allProducts->count();
        $outOfStock     = $allProducts->filter(fn($p) => $p->isOutOfStock())->count();
        $lowStock       = $allProducts->filter(fn($p) => $p->isLowStock() && !$p->isOutOfStock())->count();
        $healthyStock   = $totalProducts - $outOfStock - $lowStock;

        return response()->json([
            'status' => 'success',
            'data'   => [
                'total_products'  => $totalProducts,
                'out_of_stock'    => $outOfStock,
                'low_stock'       => $lowStock,
                'healthy_stock'   => $healthyStock,
                'total_stock_value' => $allProducts->sum(fn($p) =>
                    ($p->stock_quantity ?? 0) * ($p->effective_price ?? 0)
                ),
            ],
        ]);
    }

    private function authorizeAdmin(Request $request): void
    {
        if (!$request->user()->isAdmin()) {
            abort(403, 'Hanya admin yang bisa akses inventory management');
        }
    }
}
