<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminRecipeController extends Controller
{
    private function findRecipe(string $id): Recipe
    {
        // First try finding by exact raw string (for JSON imported data)
        $recipe = Recipe::whereRaw(['_id' => $id])->first();

        if (!$recipe) {
            $recipe = Recipe::find($id);
        }

        if (!$recipe && strlen($id) === 24 && ctype_xdigit($id)) {
            try {
                $recipe = Recipe::where('_id', new \MongoDB\BSON\ObjectId($id))->first();
            } catch (\Exception $e) {
                // Ignore exception and continue
            }
        }

        if (!$recipe) {
            abort(404);
        }

        return $recipe;
    }
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
            'instructions' => 'nullable|array',
            'instructions.*'=> 'required|string',
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

        if (!isset($validated['instructions']) || !is_array($validated['instructions'])) {
            $validated['instructions'] = [];
        } else {
            $validated['instructions'] = array_values(array_filter($validated['instructions'], fn($s) => !empty(trim($s))));
        }

        Recipe::create($validated);

        ActivityLog::log('create', 'Recipe', $validated['title']);

        return redirect()->route('admin.recipes.index')->with('success', 'Recipe created successfully.');
    }

    public function edit(string $id)
    {
        $recipe = $this->findRecipe($id);
        return view('admin.recipes.form', compact('recipe'));
    }

    public function update(Request $request, string $id)
    {
        $recipe = $this->findRecipe($id);

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
            'instructions' => 'nullable|array',
            'instructions.*'=> 'required|string',
        ]);

        $validated['is_published'] = $request->has('is_published');

        $existingImages = is_array($recipe->images) ? $recipe->images : [];

        if ($request->has('deleted_images')) {
            $deletedImages = $request->deleted_images;
            foreach ($deletedImages as $delImg) {
                // Delete file from storage if it's local
                if (!\Illuminate\Support\Str::startsWith($delImg, 'http')) {
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
                $existingImages[] = $image->store('recipes', 'public');
            }
            $validated['images'] = $existingImages;
        }

        unset($validated['deleted_images']);

        if (!isset($validated['instructions']) || !is_array($validated['instructions'])) {
            $validated['instructions'] = [];
        } else {
            // Remove any empty steps
            $validated['instructions'] = array_values(array_filter($validated['instructions'], fn($s) => !empty(trim($s))));
        }

        $recipe->update($validated);

        ActivityLog::log('update', 'Recipe', $recipe->title);

        return redirect()->route('admin.recipes.index')->with('success', 'Recipe updated successfully.');
    }

    public function destroy(string $id)
    {
        $recipe = $this->findRecipe($id);
        $title = $recipe->title;
        $recipe->delete();

        ActivityLog::log('delete', 'Recipe', $title);

        return redirect()->route('admin.recipes.index')->with('success', 'Recipe deleted successfully.');
    }
}
