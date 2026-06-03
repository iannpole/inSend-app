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
    
    public function index(Request $request): JsonResponse
    {
        $query = Order::query();

        if (!$request->user()->isAdmin()) {
            $query->where('user_id', (string) $request->user()->_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'status'  => 'success',
            'message' => 'Daftar order berhasil diambil',
            'data'    => OrderResource::collection($orders->items()),
            'meta'    => [
                'total'        => $orders->total(),
                'per_page'     => $orders->perPage(),
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
            ],
        ]);
    }

    /**
     * POST /api/orders — Buat order baru (direct, tanpa cart)
     */
    public function store(OrderRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $items     = $validated['items'];
        $orderItems = [];
        $subtotal   = 0;
        $validatedProducts = []; // cache produk agar tidak double-query

        // Validasi stok dan kalkulasi harga
        foreach ($items as $item) {
            $product = Product::where('_id', $item['product_id'])->first();

            if (!$product) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "Produk dengan ID {$item['product_id']} tidak ditemukan",
                ], 422);
            }

            if (!$product->is_active) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "Produk '{$product->name}' sedang tidak tersedia",
                ], 422);
            }

            $currentStock = $product->stock_quantity ?? 0;
            if ($currentStock < $item['qty']) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "Stok '{$product->name}' tidak cukup. Tersedia: {$currentStock}",
                ], 422);
            }

            $price        = $product->effective_price;
            $itemSubtotal = $price * $item['qty'];
            $subtotal    += $itemSubtotal;

            $orderItems[] = [
                'product_id' => (string) $product->_id,
                'name'       => $product->name,
                'unit'       => $product->unit,
                'qty'        => $item['qty'],
                'price'      => $price,
                'subtotal'   => $itemSubtotal,
            ];

            // Cache produk — hindari double-query saat decrement stok
            $validatedProducts[(string) $product->_id] = [
                'model' => $product,
                'qty'   => $item['qty'],
            ];
        }

        // Kurangi stok — gunakan produk yang sudah di-cache (tanpa query ulang)
        foreach ($validatedProducts as $entry) {
            $entry['model']->decrementStock($entry['qty']);
        }

        // Kalkulasi total
        $deliveryFee    = (float) ($validated['delivery_fee'] ?? 0);
        $discountAmount = (float) ($validated['discount_amount'] ?? 0);
        $totalPrice     = max(0, $subtotal + $deliveryFee - $discountAmount);

        $order = Order::create([
            'user_id'          => (string) $request->user()->_id,
            'order_number'     => Order::generateOrderNumber(),
            'items'            => $orderItems,
            'subtotal'         => $subtotal,
            'delivery_fee'     => $deliveryFee,
            'discount_amount'  => $discountAmount,
            'total_price'      => $totalPrice,
            'status'           => Order::STATUS_PENDING,
            'payment_status'   => Order::PAYMENT_PENDING,
            'shipping_address' => $validated['shipping_address'],
            'address_id'       => $validated['address_id'] ?? null,
            'notes'            => $validated['notes'] ?? null,
            'payment_method'   => $validated['payment_method'] ?? 'cod',
            'promo_code'       => $validated['promo_code'] ?? null,
        ]);

        return response()->json([
            'status'  => 'success',
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
            return response()->json([
                'status'  => 'error',
                'message' => 'Forbidden',
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data'   => new OrderResource($order),
        ]);
    }

    /**
     * PUT /api/orders/{id} — Admin update status
     */
    public function update(Request $request, string $id): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Hanya admin yang bisa update status order',
            ], 403);
        }

        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', 'in:pending,awaiting_payment,paid,processing,shipped,delivered,cancelled,payment_expired'],
            'notes'  => ['nullable', 'string'],
        ]);

        // Track timestamp changes
        $updates = $validated;
        if ($validated['status'] === Order::STATUS_SHIPPED && !$order->shipped_at) {
            $updates['shipped_at'] = now();
        }
        if ($validated['status'] === Order::STATUS_DELIVERED && !$order->delivered_at) {
            $updates['delivered_at'] = now();
        }
        if ($validated['status'] === Order::STATUS_PAID && !$order->paid_at) {
            $updates['paid_at'] = now();
            $updates['payment_status'] = Order::PAYMENT_PAID;
        }

        // Jika cancel, kembalikan stok
        if ($validated['status'] === Order::STATUS_CANCELLED && $order->status !== Order::STATUS_CANCELLED) {
            foreach ($order->items as $item) {
                $product = Product::where('_id', $item['product_id'])->first();
                if ($product) {
                    $product->incrementStock($item['qty']);
                }
            }
        }

        $order->update($updates);

        return response()->json([
            'status'  => 'success',
            'message' => 'Status order diupdate',
            'data'    => new OrderResource($order),
        ]);
    }

    /**
     * DELETE /api/orders/{id} — Cancel order (hanya pending/awaiting_payment)
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $order = Order::findOrFail($id);

        if (!$request->user()->isAdmin() &&
            (string) $order->user_id !== (string) $request->user()->_id) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Forbidden',
            ], 403);
        }

        if (!$order->isCancellable()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Order tidak bisa dibatalkan karena sudah diproses lebih lanjut',
            ], 422);
        }

        // Kembalikan stok
        foreach ($order->items as $item) {
            $product = Product::where('_id', $item['product_id'])->first();
            if ($product) {
                $product->incrementStock($item['qty']);
            }
        }

        $order->update(['status' => Order::STATUS_CANCELLED]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Order berhasil dibatalkan',
        ]);
    }
}
