<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Recipe;
use App\Models\Product;
use Illuminate\Support\Str;

class RecipeProductSyncSeeder extends Seeder
{
    public function run(): void
    {
        $recipes = Recipe::all();
        $productsCreated = 0;
        $existingCount = 0;

        foreach ($recipes as $recipe) {
            if (!empty($recipe->ingredients)) {
                foreach ($recipe->ingredients as $ingredient) {
                    $name = $ingredient['name'] ?? null;
                    if (!$name) continue;

                    $slug = Str::slug($name);

                    // Check if product exists
                    $existing = Product::where('slug', $slug)->first();

                    if (!$existing) {
                        // Create product
                        Product::create([
                            'name' => ucfirst($name),
                            'slug' => $slug,
                            'category_slug' => 'bumbu-bahan-masakan',
                            'base_price' => rand(5000, 50000), // Random base price for simulation
                            'sale_price' => null,
                            'discount_info' => [],
                            'stock_quantity' => 100,
                            'low_stock_threshold' => 10,
                            'unit' => $ingredient['unit'] ?? 'pcs',
                            'description' => 'Bahan masakan segar untuk resep ' . $recipe->title,
                            'images' => [
                                $ingredient['image'] ?? "https://source.unsplash.com/400x400/?ingredient," . urlencode($name)
                            ],
                            'attributes' => [],
                            'tags' => ['bahan', 'segar', strtolower($name)],
                            'is_active' => true,
                            'average_rating' => 0,
                            'review_count' => 0,
                        ]);
                        $productsCreated++;
                    } else {
                        $existingCount++;
                    }
                }
            }
        }

        echo "Created $productsCreated new products from recipes. Skipped $existingCount existing products.\n";
    }
}
