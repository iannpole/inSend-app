<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * GET /api/orders
     * User hanya lihat order sendiri. Admin lihat semua.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Order::query();

        if (!$request->user()->isAdmin()) {
            $query->where('user_id', (string) $request->user()->_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'data' => OrderResource::collection($orders->items()),
            'meta' => [
                'total'        => $orders->total(),
                'per_page'     => $orders->perPage(),
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
            ],
        ]);
    }

    /**
     * POST /api/orders — Buat order baru
     */
    public function store(OrderRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $items     = $validated['items'];
        $orderItems = [];
        $totalPrice = 0;

        // Validasi stok dan kalkulasi harga
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);

            if (!$product) {
                return response()->json([
                    'message' => "Produk dengan ID {$item['product_id']} tidak ditemukan",
                ], 422);
            }

            if (!$product->is_active) {
                return response()->json([
                    'message' => "Produk '{$product->name}' sedang tidak tersedia",
                ], 422);
            }

            if ($product->stock < $item['qty']) {
                return response()->json([
                    'message' => "Stok '{$product->name}' tidak cukup. Tersedia: {$product->stock}",
                ], 422);
            }

            $subtotal     = $product->price * $item['qty'];
            $totalPrice  += $subtotal;

            $orderItems[] = [
                'product_id' => $item['product_id'],
                'name'       => $product->name,
                'unit'       => $product->unit,
                'qty'        => $item['qty'],
                'price'      => $product->price,
                'subtotal'   => $subtotal,
            ];
        }

        // Kurangi stok dan buat order
        foreach ($items as $item) {
            Product::where('_id', $item['product_id'])
                   ->decrement('stock', $item['qty']);
        }

        $order = Order::create([
            'user_id'          => (string) $request->user()->_id,
            'items'            => $orderItems,
            'total_price'      => $totalPrice,
            'status'           => Order::STATUS_PENDING,
            'shipping_address' => $validated['shipping_address'],
            'notes'            => $validated['notes'] ?? null,
            'payment_method'   => $validated['payment_method'] ?? 'cod',
        ]);

        return response()->json([
            'message' => 'Order berhasil dibuat',
            'data'    => new OrderResource($order),
        ], 201);
    }

    /**
     * GET /api/orders/{id}
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $order = Order::findOrFail($id);

        if (!$request->user()->isAdmin() &&
            (string) $order->user_id !== (string) $request->user()->_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json(['data' => new OrderResource($order)]);
    }

    /**
     * PUT /api/orders/{id} — Admin update status
     */
    public function update(Request $request, string $id): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Hanya admin yang bisa update status order'], 403);
        }

        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', 'in:pending,processing,shipped,delivered,cancelled'],
            'notes'  => ['nullable', 'string'],
        ]);

        $order->update($validated);

        return response()->json([
            'message' => 'Status order diupdate',
            'data'    => new OrderResource($order),
        ]);
    }

    /**
     * DELETE /api/orders/{id} — Cancel order (hanya pending)
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $order = Order::findOrFail($id);

        if (!$request->user()->isAdmin() &&
            (string) $order->user_id !== (string) $request->user()->_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if (!$order->isCancellable()) {
            return response()->json([
                'message' => 'Order tidak bisa dibatalkan karena sudah diproses lebih lanjut',
            ], 422);
        }

        // Kembalikan stok
        foreach ($order->items as $item) {
            Product::where('_id', $item['product_id'])
                   ->increment('stock', $item['qty']);
        }

        $order->update(['status' => Order::STATUS_CANCELLED]);

        return response()->json(['message' => 'Order berhasil dibatalkan']);
    }
}
