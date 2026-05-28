<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $query = BlogPost::where('is_published', true);

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('tag')) {
            $query->where('tags', $request->tag);
        }

        $posts = $query->orderBy('published_at', 'desc')->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $posts
        ]);
    }

    public function show($id)
    {
        // Try finding by ID first, then by slug
        $post = BlogPost::where('is_published', true)
            ->where(function($q) use ($id) {
                $q->where('_id', $id)
                  ->orWhere('slug', $id);
            })->first();

        if (!$post) {
            return response()->json([
                'status' => 'error',
                'message' => 'Post not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $post
        ]);
    }
    
    public function sidebar()
    {
        // Get unique categories and their counts
        $categories = BlogPost::where('is_published', true)
            ->groupBy('category')
            ->select('category')
            ->get()
            ->map(function($post) {
                return [
                    'name' => $post->category,
                    'count' => BlogPost::where('category', $post->category)->where('is_published', true)->count()
                ];
            });

        // Get latest 5 posts for recent posts widget
        $recentPosts = BlogPost::where('is_published', true)
            ->orderBy('published_at', 'desc')
            ->take(5)
            ->get(['_id', 'title', 'slug', 'image', 'published_at']);

        // Get unique tags
        $allTags = BlogPost::where('is_published', true)->pluck('tags')->flatten()->unique()->values();

        return response()->json([
            'status' => 'success',
            'data' => [
                'categories' => $categories,
                'recent_posts' => $recentPosts,
                'tags' => $allTags
            ]
        ]);
    }
}
