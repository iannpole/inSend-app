<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Product;
use Illuminate\Http\Request;

class AdminReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::query()->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('comment', 'like', "%{$search}%");
        }

        $reviews = $query->paginate(10);
        return view('admin.reviews.index', compact('reviews'));
    }

    public function destroy(string $id)
    {
        $review = Review::find($id);
        if (!$review) {
            return back()->with('error', 'Review not found.');
        }

        $productId = $review->product_id;
        $review->delete();

        // Recalculate average rating for product
        $this->updateProductRating($productId);

        return back()->with('success', 'Review deleted successfully.');
    }

    private function updateProductRating(string $productId): void
    {
        $reviews = Review::byProduct($productId)->get();

        if ($reviews->isEmpty()) {
            Product::where('_id', $productId)->update([
                'average_rating' => 0,
                'review_count'   => 0,
            ]);
            return;
        }

        $avgRating   = round($reviews->avg('rating'), 1);
        $reviewCount = $reviews->count();

        Product::where('_id', $productId)->update([
            'average_rating' => $avgRating,
            'review_count'   => $reviewCount,
        ]);
    }
}
