<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\Order;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('q');
        
        if (!$query) {
            return response()->json([]);
        }

        $results = [];

        // Search Products
        $products = Product::where('name', 'like', "%{$query}%")->take(3)->get();
        foreach ($products as $product) {
            $results[] = [
                'type' => 'Product',
                'title' => $product->name,
                'url' => route('admin.products.edit', $product->_id ?? $product->id),
                'icon' => '📦'
            ];
        }

        // Search Recipes
        $recipes = Recipe::where('title', 'like', "%{$query}%")->take(3)->get();
        foreach ($recipes as $recipe) {
            $results[] = [
                'type' => 'Recipe',
                'title' => $recipe->title,
                'url' => route('admin.recipes.edit', $recipe->_id ?? $recipe->id),
                'icon' => '🍳'
            ];
        }

        // Search Orders
        // Try searching by ID if it's alphanumeric
        $orders = Order::where('_id', 'like', "%{$query}%")->take(3)->get();
        foreach ($orders as $order) {
            $results[] = [
                'type' => 'Order',
                'title' => 'Order #' . substr($order->_id ?? $order->id, -6),
                'url' => route('admin.orders.show', $order->_id ?? $order->id),
                'icon' => '🛍️'
            ];
        }

        return response()->json($results);
    }
}
