<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * GET /api/products/{product_id}/reviews — List reviews for a product
     */
    public function productReviews(string $productId): JsonResponse
    {
        $product = Product::find($productId);
        if (!$product) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Produk tidak ditemukan',
            ], 404);
        }

        $reviews = Review::byProduct($productId)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($review) {
                $user = \App\Models\User::find($review->user_id);
                return [
                    'id'         => (string) $review->_id,
                    'user_name'  => $user ? $user->name : 'Anonymous',
                    'user_avatar'=> $user ? $user->avatar : null,
                    'rating'     => $review->rating,
                    'comment'    => $review->comment,
                    'images'     => $review->images ?? [],
                    'created_at' => $review->created_at?->toIso8601String(),
                ];
            });

        return response()->json([
            'status' => 'success',
            'data'   => $reviews,
            'meta'   => [
                'total'          => $reviews->count(),
                'average_rating' => $product->average_rating ?? 0,
            ],
        ]);
    }

    /**
     * POST /api/orders/{order_id}/review — Create review for an order's product
     */
    public function store(Request $request, string $orderId): JsonResponse
    {
        $order = Order::findOrFail($orderId);
        $userId = (string) $request->user()->_id;

        // Verify ownership
        if ((string) $order->user_id !== $userId) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Forbidden',
            ], 403);
        }

        // Only after delivered
        if (!$order->isReviewable()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Review hanya bisa dibuat setelah order berstatus "delivered"',
            ], 422);
        }

        // Check if already reviewed this order
        $existingReview = Review::where('order_id', $orderId)
            ->where('user_id', $userId)
            ->first();

        if ($existingReview) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Anda sudah memberikan review untuk order ini',
            ], 422);
        }

        $validated = $request->validate([
            'reviews'              => ['required', 'array', 'min:1'],
            'reviews.*.product_id' => ['required', 'string'],
            'reviews.*.rating'     => ['required', 'integer', 'min:1', 'max:5'],
            'reviews.*.comment'    => ['nullable', 'string', 'max:1000'],
        ]);

        $createdReviews = [];

        foreach ($validated['reviews'] as $reviewData) {
            // Verify product is in this order
            $inOrder = collect($order->items)->contains(fn($item) =>
                $item['product_id'] === $reviewData['product_id']
            );

            if (!$inOrder) {
                continue; // Skip products not in this order
            }

            $review = Review::create([
                'user_id'    => $userId,
                'order_id'   => $orderId,
                'product_id' => $reviewData['product_id'],
                'rating'     => $reviewData['rating'],
                'comment'    => $reviewData['comment'] ?? null,
                'images'     => [],
            ]);

            $createdReviews[] = $review;

            // Update product average rating
            $this->updateProductRating($reviewData['product_id']);
        }

        return response()->json([
            'status'  => 'success',
            'message' => count($createdReviews) . ' review berhasil dibuat',
            'data'    => collect($createdReviews)->map(fn($r) => [
                'id'         => (string) $r->_id,
                'product_id' => $r->product_id,
                'rating'     => $r->rating,
            ]),
        ], 201);
    }

    /**
     * GET /api/reviews/my — List user's own reviews
     */
    public function myReviews(Request $request): JsonResponse
    {
        $userId = (string) $request->user()->_id;

        $reviews = Review::byUser($userId)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($review) {
                $product = Product::find($review->product_id);
                return [
                    'id'           => (string) $review->_id,
                    'product_id'   => $review->product_id,
                    'product_name' => $product ? $product->name : 'Produk tidak ditemukan',
                    'order_id'     => $review->order_id,
                    'rating'       => $review->rating,
                    'comment'      => $review->comment,
                    'images'       => $review->images ?? [],
                    'created_at'   => $review->created_at?->toIso8601String(),
                ];
            });

        return response()->json([
            'status' => 'success',
            'data'   => $reviews,
        ]);
    }

    /**
     * Recalculate and update product average rating
     */
    private function updateProductRating(string $productId): void
    {
        $reviews = Review::byProduct($productId)->get();

        if ($reviews->isEmpty()) return;

        $avgRating   = round($reviews->avg('rating'), 1);
        $reviewCount = $reviews->count();

        Product::where('_id', $productId)->update([
            'average_rating' => $avgRating,
            'review_count'   => $reviewCount,
        ]);
    }

    /**
     * GET /api/reviews/featured — Featured reviews for home page (changes weekly)
     */
    public function featured(): JsonResponse
    {
        // Use week of the year as a seed to rotate featured reviews per week
        $weekSeed = (int) date('W') + (int) date('Y');
        
        // Fetch 5 star reviews
        $reviews = Review::where('rating', '>=', 4)
            ->whereNotNull('comment')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();
            
        if ($reviews->isEmpty()) {
            return response()->json(['status' => 'success', 'data' => []]);
        }

        // Shuffle with seed based on the week
        srand($weekSeed);
        $shuffled = $reviews->shuffle()->take(5);
        srand(); // reset seed

        $formatted = $shuffled->map(function ($review) {
            $user = \App\Models\User::find($review->user_id);
            $product = \App\Models\Product::find($review->product_id);
            return [
                'id'           => (string) $review->_id,
                'user_name'    => $user ? $user->name : 'Anonymous',
                'user_avatar'  => $user ? $user->avatar : null,
                'product_name' => $product ? $product->name : 'Produk',
                'rating'       => $review->rating,
                'comment'      => $review->comment,
                'created_at'   => $review->created_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $formatted->values(),
        ]);
    }
}
