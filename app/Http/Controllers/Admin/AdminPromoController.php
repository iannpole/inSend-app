<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminPromoController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $query = Promotion::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
        }

        $promotions = $query->orderBy('created_at', 'desc')->paginate(10);
        return view('admin.promotions.index', compact('promotions', 'search'));
    }

    public function create()
    {
        return view('admin.promotions.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'           => ['required', 'string', 'max:30', 'unique:promotions,code'],
            'name'           => ['required', 'string', 'max:100'],
            'description'    => ['nullable', 'string', 'max:500'],
            'type'           => ['required', 'in:percentage,fixed,free_shipping'],
            'value'          => ['required', 'numeric', 'min:0'],
            'min_order'      => ['nullable', 'numeric', 'min:0'],
            'max_discount'   => ['nullable', 'numeric', 'min:0'],
            'usage_limit'    => ['nullable', 'integer', 'min:1'],
            'per_user_limit' => ['nullable', 'integer', 'min:1'],
            'start_date'     => ['nullable', 'date'],
            'end_date'       => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active'      => ['boolean'],
            'image'          => ['nullable', 'image', 'max:2048'],
        ]);

        $validated['code'] = strtoupper($validated['code']);
        $validated['is_active'] = $request->has('is_active');

        if ($request->hasFile('image')) {
            $validated['image_url'] = $request->file('image')->store('promotions', 'public');
        }

        Promotion::create($validated);
        return redirect()->route('admin.promotions.index')->with('success', 'Promosi berhasil ditambahkan!');
    }

    public function edit(string $id)
    {
        $promotion = Promotion::findOrFail($id);
        return view('admin.promotions.form', compact('promotion'));
    }

    public function update(Request $request, string $id)
    {
        $promotion = Promotion::findOrFail($id);

        $validated = $request->validate([
            'code'           => ['required', 'string', 'max:30', 'unique:promotions,code,'.$id.',_id'],
            'name'           => ['required', 'string', 'max:100'],
            'description'    => ['nullable', 'string', 'max:500'],
            'type'           => ['required', 'in:percentage,fixed,free_shipping'],
            'value'          => ['required', 'numeric', 'min:0'],
            'min_order'      => ['nullable', 'numeric', 'min:0'],
            'max_discount'   => ['nullable', 'numeric', 'min:0'],
            'usage_limit'    => ['nullable', 'integer', 'min:1'],
            'per_user_limit' => ['nullable', 'integer', 'min:1'],
            'start_date'     => ['nullable', 'date'],
            'end_date'       => ['nullable', 'date'],
            'is_active'      => ['boolean'],
            'image'          => ['nullable', 'image', 'max:2048'],
        ]);

        $validated['code'] = strtoupper($validated['code']);
        $validated['is_active'] = $request->has('is_active');

        if ($request->hasFile('image')) {
            if ($promotion->image_url) {
                Storage::disk('public')->delete($promotion->image_url);
            }
            $validated['image_url'] = $request->file('image')->store('promotions', 'public');
        }

        $promotion->update($validated);
        return redirect()->route('admin.promotions.index')->with('success', 'Promosi berhasil diupdate!');
    }

    public function destroy(string $id)
    {
        $promotion = Promotion::findOrFail($id);
        if ($promotion->image_url) {
            Storage::disk('public')->delete($promotion->image_url);
        }
        $promotion->delete();
        
        return redirect()->route('admin.promotions.index')->with('success', 'Promosi berhasil dihapus!');
    }
}
