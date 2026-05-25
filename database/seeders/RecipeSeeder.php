<?php

namespace Database\Seeders;

use App\Models\Recipe;
use Illuminate\Database\Seeder;

class RecipeSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = database_path('seeders/recipes_sample.json');
        $recipes  = json_decode(file_get_contents($jsonPath), true);

        $this->command->info('Seeding ' . count($recipes) . ' recipes...');

        foreach ($recipes as $data) {
            Recipe::updateOrCreate(
                ['title' => $data['title']],
                $data
            );
        }

        $this->command->info('✅ Recipe seeding complete!');
    }
}
