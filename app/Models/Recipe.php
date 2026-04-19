<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Recipe extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'recipes';

    protected $fillable = [
        'title',
        'description',
        'ingredients',  // array: [{ name, amount, unit }]
        'steps',        // array: [{ order, instruction, duration_minutes }]
        'images',       // array of image paths/urls
        'category',     // misal: 'masakan_indonesia', 'minuman', 'kue', dll
        'prep_time',    // minutes
        'cook_time',    // minutes
        'servings',     // jumlah porsi default
        'difficulty',   // 'easy' | 'medium' | 'hard'
        'tags',         // array of strings untuk RAG search
        'created_by',   // user_id yang buat
        'is_published', // publik atau draft
        'source',       // 'manual' | 'ai_generated'
        'nutrition',    // { calories, protein, carbs, fat } per serving
    ];

    protected $casts = [
        'ingredients'  => 'array',
        'steps'        => 'array',
        'images'       => 'array',
        'tags'         => 'array',
        'nutrition'    => 'array',
        'prep_time'    => 'integer',
        'cook_time'    => 'integer',
        'servings'     => 'integer',
        'is_published' => 'boolean',
    ];

    protected $attributes = [
        'is_published' => true,
        'source'       => 'manual',
        'images'       => [],
        'tags'         => [],
    ];

    // Scope by category
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    // Scope published only
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    // Search by keyword (title, description, tags, ingredients)
    public function scopeSearch($query, string $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('title', 'like', "%{$keyword}%")
              ->orWhere('description', 'like', "%{$keyword}%")
              ->orWhere('tags', 'elemMatch', ['$regex' => $keyword, '$options' => 'i']);
        });
    }
}
