<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminProductController extends Controller
{
    private function findProduct(string $id): Product
    {
        $product = Product::find($id);

        if (!$product) {
            abort(404);
        }

        return $product;
    }

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
            'discount_active'     => 'nullable|string',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_fixed'      => 'nullable|numeric|min:0',
            'discount_start'      => 'nullable|date',
            'discount_end'        => 'nullable|date|after_or_equal:discount_start',
            'campaign_name'       => 'nullable|string|max:255',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['slug'] = Str::slug($validated['name']);

        $discountActive = $request->has('discount_active');
        $percentage = (float) ($request->discount_percentage ?? 0);
        $fixedAmount = (float) ($request->discount_fixed ?? 0);

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
            'start_date'    => $request->filled('discount_start') ? $request->discount_start : null,
            'end_date'      => $request->filled('discount_end') ? $request->discount_end : null,
            'campaign_name' => $request->campaign_name ?? '',
        ];

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('products', 'public');
            }
        }
        $validated['images'] = $imagePaths;

        unset($validated['discount_active'], $validated['discount_percentage'], $validated['discount_fixed'], $validated['discount_start'], $validated['discount_end'], $validated['campaign_name']);

        Product::create($validated);

        ActivityLog::log('create', 'Product', $validated['name']);

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    public function edit(string $id)
    {
        $product = $this->findProduct($id);
        return view('admin.products.form', compact('product'));
    }

    public function update(Request $request, string $id)
    {
        $product = $this->findProduct($id);

        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'description'         => 'nullable|string',
            'base_price'          => 'required|numeric|min:0',
            'stock_quantity'      => 'required|integer|min:0',
            'category_slug'       => 'required|string|max:255',
            'unit'                => 'required|string|max:50',
            'is_active'           => 'nullable|string',
            'images.*'            => 'nullable|image|max:2048',
            'discount_active'     => 'nullable|string',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_fixed'      => 'nullable|numeric|min:0',
            'discount_start'      => 'nullable|date',
            'discount_end'        => 'nullable|date|after_or_equal:discount_start',
            'campaign_name'       => 'nullable|string|max:255',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['slug'] = Str::slug($validated['name']);

        $discountActive = $request->has('discount_active');
        $percentage = (float) ($request->discount_percentage ?? 0);
        $fixedAmount = (float) ($request->discount_fixed ?? 0);

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
            'start_date'    => $request->filled('discount_start') ? $request->discount_start : null,
            'end_date'      => $request->filled('discount_end') ? $request->discount_end : null,
            'campaign_name' => $request->campaign_name ?? '',
        ];

        $existingImages = is_array($product->images) ? $product->images : [];
        
        if ($request->has('deleted_images')) {
            $deletedImages = $request->deleted_images;
            foreach ($deletedImages as $delImg) {
                // Delete file from storage if it's local
                if (!Str::startsWith($delImg, 'http')) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($delImg);
                }
                // Remove from existing images array
                if (($key = array_search($delImg, $existingImages)) !== false) {
                    unset($existingImages[$key]);
                }
            }
            // Re-index array
            $existingImages = array_values($existingImages);
            $validated['images'] = $existingImages;
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $existingImages[] = $image->store('products', 'public');
            }
            $validated['images'] = $existingImages;
        }

        unset($validated['discount_active'], $validated['discount_percentage'], $validated['discount_fixed'], $validated['discount_start'], $validated['discount_end'], $validated['campaign_name'], $validated['deleted_images']);

        $product->update($validated);

        ActivityLog::log('update', 'Product', $product->name);

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(string $id)
    {
        $product = $this->findProduct($id);
        $name = $product->name;
        $product->delete();

        ActivityLog::log('delete', 'Product', $name);

        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
    }

   

}