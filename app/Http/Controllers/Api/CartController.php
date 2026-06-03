<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * GET /api/cart — Get user's cart with calculations
     */
    public function index(Request $request): JsonResponse
    {
        $userId = (string) $request->user()->_id;
        $cart   = Cart::where('user_id', $userId)->first();

        if (!$cart || empty($cart->items)) {
            return response()->json([
                'status' => 'success',
                'data'   => [
                    'items'      => [],
                    'item_count' => 0,
                    'subtotal'   => 0,
                ],
            ]);
        }

        // Enrich items with current product data
        $enrichedItems = [];
        foreach ($cart->items as $item) {
            $product = Product::find($item['product_id']);
            $enrichedItems[] = [
                'product_id'     => $item['product_id'],
                'name'           => $item['name'] ?? ($product->name ?? 'Produk tidak ditemukan'),
                'unit'           => $item['unit'] ?? ($product->unit ?? null),
                'image'          => $item['image'] ?? (isset($product->images[0]) ? $product->images[0] : null),
                'qty'            => $item['qty'],
                'price_snapshot' => $item['price_snapshot'],
                'current_price'  => $product ? $product->effective_price : null,
                'subtotal'       => ($item['price_snapshot'] ?? 0) * ($item['qty'] ?? 0),
                'is_available'   => $product ? ($product->is_active && ($product->stock_quantity ?? 0) >= $item['qty']) : false,
                'stock_quantity' => $product->stock_quantity ?? 0,
                'added_at'       => $item['added_at'] ?? null,
            ];
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'items'      => $enrichedItems,
                'item_count' => $cart->getItemCount(),
                'subtotal'   => $cart->getSubtotal(),
            ],
        ]);
    }

    /**
     * POST /api/cart/items — Add item to cart
     */
    public function addItem(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'string'],
            'qty'        => ['required', 'integer', 'min:1'],
        ]);

        $product = Product::find($validated['product_id']);

        if (!$product) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Produk tidak ditemukan',
            ], 404);
        }

        if (!$product->is_active) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Produk sedang tidak tersedia',
            ], 422);
        }

        if (($product->stock_quantity ?? 0) < $validated['qty']) {
            return response()->json([
                'status'  => 'error',
                'message' => "Stok tidak cukup. Tersedia: {$product->stock_quantity}",
            ], 422);
        }

        $userId = (string) $request->user()->_id;
        $cart = Cart::firstOrCreate(
            ['user_id' => $userId],
            ['items' => []]
        );

        $image = isset($product->images[0]) ? $product->images[0] : null;

        $cart->addItem(
            (string) $product->_id,
            $validated['qty'],
            $product->effective_price,
            $product->name,
            $product->unit,
            $image
        );

        return response()->json([
            'status'  => 'success',
            'message' => "'{$product->name}' ditambahkan ke keranjang",
            'data'    => [
                'item_count' => $cart->getItemCount(),
                'subtotal'   => $cart->getSubtotal(),
            ],
        ]);
    }

    /**
     * PATCH /api/cart/items/{product_id} — Update qty
     */
    public function updateItem(Request $request, string $productId): JsonResponse
    {
        $validated = $request->validate([
            'qty' => ['required', 'integer', 'min:1'],
        ]);

        // Check stock
        $product = Product::find($productId);
        if ($product && ($product->stock_quantity ?? 0) < $validated['qty']) {
            return response()->json([
                'status'  => 'error',
                'message' => "Stok tidak cukup. Tersedia: {$product->stock_quantity}",
            ], 422);
        }

        $userId = (string) $request->user()->_id;
        $cart   = Cart::where('user_id', $userId)->first();

        if (!$cart || !$cart->updateItemQty($productId, $validated['qty'])) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Item tidak ditemukan di keranjang',
            ], 404);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Jumlah item diupdate',
            'data'    => [
                'item_count' => $cart->getItemCount(),
                'subtotal'   => $cart->getSubtotal(),
            ],
        ]);
    }

    /**
     * DELETE /api/cart/items/{product_id} — Remove item
     */
    public function removeItem(Request $request, string $productId): JsonResponse
    {
        $userId = (string) $request->user()->_id;
        $cart   = Cart::where('user_id', $userId)->first();

        if (!$cart || !$cart->removeItem($productId)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Item tidak ditemukan di keranjang',
            ], 404);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Item dihapus dari keranjang',
            'data'    => [
                'item_count' => $cart->getItemCount(),
                'subtotal'   => $cart->getSubtotal(),
            ],
        ]);
    }

    /**
     * DELETE /api/cart — Clear entire cart
     */
    public function clear(Request $request): JsonResponse
    {
        $userId = (string) $request->user()->_id;
        $cart   = Cart::where('user_id', $userId)->first();

        if ($cart) {
            $cart->clearItems();
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Keranjang dikosongkan',
        ]);
    }

    /**
     * POST /api/cart/checkout — Convert cart to order
     */
    public function checkout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'address_id'      => ['nullable', 'string'],
            'shipping_address' => ['required_without:address_id', 'nullable', 'array'],
            'shipping_address.recipient_name' => ['required_with:shipping_address', 'string'],
            'shipping_address.phone'          => ['required_with:shipping_address', 'string'],
            'shipping_address.street'         => ['required_with:shipping_address', 'string'],
            'shipping_address.city'           => ['required_with:shipping_address', 'string'],
            'shipping_address.province'       => ['required_with:shipping_address', 'string'],
            'shipping_address.postal_code'    => ['required_with:shipping_address', 'string'],
            'shipping_address.lat'            => ['nullable', 'numeric'],
            'shipping_address.lng'            => ['nullable', 'numeric'],
            'payment_method'  => ['nullable', 'string', 'in:cod,bank_transfer,qris,gopay,ovo'],
            'notes'           => ['nullable', 'string', 'max:500'],
            'delivery_fee'    => ['nullable', 'numeric', 'min:0'],
            'promo_code'      => ['nullable', 'string', 'max:50'],
        ]);

        $userId = (string) $request->user()->_id;
        $cart   = Cart::where('user_id', $userId)->first();

        if (!$cart || empty($cart->items)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Keranjang kosong',
            ], 422);
        }

        // Resolve address
        $shippingAddress = $validated['shipping_address'] ?? null;
        if (!empty($validated['address_id'])) {
            $address = \App\Models\Address::where('_id', $validated['address_id'])
                ->where('user_id', $userId)
                ->first();
            if ($address) {
                $shippingAddress = $address->toShippingAddress();
            }
        }

        if (!$shippingAddress) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Alamat pengiriman harus diisi',
            ], 422);
        }

        // Validate stock & build order items
        $orderItems        = [];
        $subtotal          = 0;
        $validatedProducts = []; // cache produk agar tidak double-query

        foreach ($cart->items as $item) {
            $product = Product::find($item['product_id']);

            if (!$product || !$product->is_active) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "Produk '{$item['name']}' tidak tersedia lagi",
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

        // Decrement stok — gunakan produk yang sudah di-cache (tanpa query ulang)
        foreach ($validatedProducts as $entry) {
            $entry['model']->decrementStock($entry['qty']);
        }

        // Calculate totals
        $deliveryFee    = (float) ($validated['delivery_fee'] ?? 0);
        $discountAmount = 0;

        // Validate promo code if provided
        $promoId   = null;
        $promoCode = $validated['promo_code'] ?? null;
        if ($promoCode) {
            $promo = \App\Models\Promotion::where('code', strtoupper($promoCode))
                ->where('is_active', true)
                ->first();

            if ($promo && $promo->isValid($subtotal)) {
                $discountAmount = $promo->calculateDiscount($subtotal, $deliveryFee);
                $promoId = (string) $promo->_id;
                $promo->increment('used_count');
            }
        }

        $totalPrice = max(0, $subtotal + $deliveryFee - $discountAmount);

        // Create order
        $paymentMethod = $validated['payment_method'] ?? 'cod';
        $order = Order::create([
            'user_id'          => $userId,
            'order_number'     => Order::generateOrderNumber(),
            'items'            => $orderItems,
            'subtotal'         => $subtotal,
            'delivery_fee'     => $deliveryFee,
            'discount_amount'  => $discountAmount,
            'total_price'      => $totalPrice,
            'status'           => $paymentMethod === 'cod' ? Order::STATUS_PENDING : Order::STATUS_AWAITING_PAYMENT,
            'payment_status'   => $paymentMethod === 'cod' ? Order::PAYMENT_PENDING : Order::PAYMENT_PENDING,
            'shipping_address' => $shippingAddress,
            'address_id'       => $validated['address_id'] ?? null,
            'notes'            => $validated['notes'] ?? null,
            'payment_method'   => $paymentMethod,
            'promo_id'         => $promoId,
            'promo_code'       => $promoCode,
        ]);

        // If online payment, create Midtrans transaction
        $paymentUrl   = null;
        $paymentToken = null;
        if ($paymentMethod !== 'cod') {
            try {
                $paymentService = app(\App\Services\Payment\PaymentService::class);
                $paymentResult  = $paymentService->createTransaction($order, $request->user());

                $paymentUrl   = $paymentResult['redirect_url'] ?? null;
                $paymentToken = $paymentResult['token'] ?? null;

                $order->update([
                    'payment_url'   => $paymentUrl,
                    'payment_token' => $paymentToken,
                    'payment_id'    => $paymentResult['order_id'] ?? null,
                ]);
            } catch (\Exception $e) {
                // Payment service not configured, fallback
                \Illuminate\Support\Facades\Log::warning('Midtrans not configured: ' . $e->getMessage());
            }
        }

        // Clear cart
        $cart->clearItems();

        $orderData = (new \App\Http\Resources\OrderResource($order))->resolve();

        return response()->json([
            'status'        => 'success',
            'message'       => 'Checkout berhasil! Order dibuat.',
            'data'          => $orderData,
            'payment_url'   => $paymentUrl,
            'payment_token' => $paymentToken,
        ], 201);
    }
}
