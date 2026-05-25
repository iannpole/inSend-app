<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class AiConversation extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'ai_conversations';

    protected $fillable = [
        'user_id',
        'title',
        'mode',
        'messages',
        'context',
    ];

    // Tidak pakai cast 'array' untuk messages — MongoDB handle native
    protected $casts = [
        'context' => 'array',
    ];

    protected $attributes = [
        'mode' => 'chat',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function addMessage(string $role, string $content, ?string $imageUrl = null): void
    {
        $messages   = $this->messages ?? [];
        $messages[] = [
            'role'      => $role,        // 'user' | 'model'
            'content'   => $content,
            'image_url' => $imageUrl,
            'timestamp' => now()->toIso8601String(),
        ];
        $this->messages = $messages;
        $this->save();
    }

    /**
     * Get the most recent N messages for bot context.
     */
    public function getRecentMessages(int $limit = 10): array
    {
        $messages = $this->messages ?? [];
        return array_slice($messages, -$limit);
    }
}