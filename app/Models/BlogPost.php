<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class BlogPost extends Model
{
    protected $collection = 'blog_posts';

    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'category',
        'image',
        'tags',
        'is_published',
        'published_at',
    ];
}
