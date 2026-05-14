<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminRecipeController extends Controller
{
    public function index(Request $request)
    {
        $query = Recipe::query();

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $recipes = $query->orderBy('created_at', 'desc')->paginate(10);
        return view('admin.recipes.index', compact('recipes'));
    }

    public function create()
    {
        return view('admin.recipes.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'category'     => 'required|string|max:255',
            'prep_time'    => 'required|integer|min:0',
            'cook_time'    => 'required|integer|min:0',
            'servings'     => 'required|integer|min:1',
            'difficulty'   => 'required|string|in:easy,medium,hard',
            'is_published' => 'nullable|string',
            'images.*'     => 'nullable|image|max:2048',
        ]);

        $validated['is_published'] = $request->has('is_published');
        $validated['source'] = 'manual';
        $validated['created_by'] = Auth::id();

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('recipes', 'public');
            }
        }
        $validated['images'] = $imagePaths;

        Recipe::create($validated);

        return redirect()->route('admin.recipes.index')->with('success', 'Recipe created successfully.');
    }

    public function edit(string $id)
    {
        $recipe = Recipe::findOrFail($id);
        return view('admin.recipes.form', compact('recipe'));
    }

    public function update(Request $request, string $id)
    {
        $recipe = Recipe::findOrFail($id);

        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'category'     => 'required|string|max:255',
            'prep_time'    => 'required|integer|min:0',
            'cook_time'    => 'required|integer|min:0',
            'servings'     => 'required|integer|min:1',
            'difficulty'   => 'required|string|in:easy,medium,hard',
            'is_published' => 'nullable|string',
            'images.*'     => 'nullable|image|max:2048',
        ]);

        $validated['is_published'] = $request->has('is_published');

        if ($request->hasFile('images')) {
            $existingImages = $recipe->images ?? [];
            foreach ($request->file('images') as $image) {
                $existingImages[] = $image->store('recipes', 'public');
            }
            $validated['images'] = $existingImages;
        }

        $recipe->update($validated);

        return redirect()->route('admin.recipes.index')->with('success', 'Recipe updated successfully.');
    }

    public function destroy(string $id)
    {
        $recipe = Recipe::findOrFail($id);
        $recipe->delete();

        return redirect()->route('admin.recipes.index')->with('success', 'Recipe deleted successfully.');
    }
}
