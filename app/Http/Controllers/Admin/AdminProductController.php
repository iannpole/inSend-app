<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('category_slug', 'like', "%{$keyword}%")
                  ->orWhere('tags', 'like', "%{$keyword}%");
            });
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(10);
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        return view('admin.products.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'description'         => 'nullable|string',
            'base_price'          => 'required|numeric|min:0',
            'stock_quantity'      => 'required|integer|min:0',
            'category_slug'       => 'required|string|max:255',
            'unit'                => 'required|string|max:50',
            'is_active'           => 'nullable|string',
            'images.*'            => 'nullable|image|max:2048',
            // Discount fields
            'discount_active'     => 'nullable|string',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_fixed'      => 'nullable|numeric|min:0',
            'discount_start'      => 'nullable|date',
            'discount_end'        => 'nullable|date|after_or_equal:discount_start',
            'campaign_name'       => 'nullable|string|max:255',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['slug'] = Str::slug($validated['name']);

        // Build discount_info
        $discountActive = $request->has('discount_active');
        $percentage = (float) ($request->discount_percentage ?? 0);
        $fixedAmount = (float) ($request->discount_fixed ?? 0);

        // Auto-calculate sale_price
        $basePrice = (float) $validated['base_price'];
        $salePrice = $basePrice;

        if ($discountActive && $percentage > 0) {
            $fixedAmount = round($basePrice * ($percentage / 100));
            $salePrice = $basePrice - $fixedAmount;
        } elseif ($discountActive && $fixedAmount > 0) {
            $percentage = round(($fixedAmount / $basePrice) * 100);
            $salePrice = $basePrice - $fixedAmount;
        }

        $validated['sale_price'] = max(0, $salePrice);
        $validated['discount_info'] = [
            'is_active'     => $discountActive,
            'percentage'    => (int) $percentage,
            'fixed_amount'  => (int) $fixedAmount,
            'start_date'    => $request->discount_start ?? null,
            'end_date'      => $request->discount_end ?? null,
            'campaign_name' => $request->campaign_name ?? '',
        ];

        // Handle images
        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('products', 'public');
            }
        }
        $validated['images'] = $imagePaths;

        // Remove non-model fields
        unset($validated['discount_active'], $validated['discount_percentage'], $validated['discount_fixed'], $validated['discount_start'], $validated['discount_end'], $validated['campaign_name']);

        Product::create($validated);

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    public function edit(string $id)
    {
        $product = Product::findOrFail($id);
        return view('admin.products.form', compact('product'));
    }

    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'description'         => 'nullable|string',
            'base_price'          => 'required|numeric|min:0',
            'stock_quantity'      => 'required|integer|min:0',
            'category_slug'       => 'required|string|max:255',
            'unit'                => 'required|string|max:50',
            'is_active'           => 'nullable|string',
            'images.*'            => 'nullable|image|max:2048',
            // Discount fields
            'discount_active'     => 'nullable|string',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_fixed'      => 'nullable|numeric|min:0',
            'discount_start'      => 'nullable|date',
            'discount_end'        => 'nullable|date|after_or_equal:discount_start',
            'campaign_name'       => 'nullable|string|max:255',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['slug'] = Str::slug($validated['name']);

        // Build discount_info
        $discountActive = $request->has('discount_active');
        $percentage = (float) ($request->discount_percentage ?? 0);
        $fixedAmount = (float) ($request->discount_fixed ?? 0);

        // Auto-calculate sale_price
        $basePrice = (float) $validated['base_price'];
        $salePrice = $basePrice;

        if ($discountActive && $percentage > 0) {
            $fixedAmount = round($basePrice * ($percentage / 100));
            $salePrice = $basePrice - $fixedAmount;
        } elseif ($discountActive && $fixedAmount > 0) {
            $percentage = round(($fixedAmount / $basePrice) * 100);
            $salePrice = $basePrice - $fixedAmount;
        }

        $validated['sale_price'] = max(0, $salePrice);
        $validated['discount_info'] = [
            'is_active'     => $discountActive,
            'percentage'    => (int) $percentage,
            'fixed_amount'  => (int) $fixedAmount,
            'start_date'    => $request->discount_start ?? null,
            'end_date'      => $request->discount_end ?? null,
            'campaign_name' => $request->campaign_name ?? '',
        ];

        // Handle images
        if ($request->hasFile('images')) {
            $existingImages = $product->images ?? [];
            foreach ($request->file('images') as $image) {
                $existingImages[] = $image->store('products', 'public');
            }
            $validated['images'] = $existingImages;
        }

        // Remove non-model fields
        unset($validated['discount_active'], $validated['discount_percentage'], $validated['discount_fixed'], $validated['discount_start'], $validated['discount_end'], $validated['campaign_name']);

        $product->update($validated);

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
    }
}
