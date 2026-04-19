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
        'mode',       // 'chat' | 'recipe'
        'messages',   // array: [{ role, content, image_url, timestamp }]
        'context',    // extra context / metadata
    ];

    protected $casts = [
        'messages' => 'array',
        'context'  => 'array',
    ];

    protected $attributes = [
        'messages' => [],
        'mode'     => 'chat',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Tambah pesan ke conversation
     */
    public function addMessage(string $role, string $content, ?string $imageUrl = null): void
    {
        $messages = $this->messages ?? [];
        $messages[] = [
            'role'      => $role, // 'user' | 'model'
            'content'   => $content,
            'image_url' => $imageUrl,
            'timestamp' => now()->toIso8601String(),
        ];
        $this->messages = $messages;
        $this->save();
    }

    /**
     * Ambil pesan terakhir untuk context Gemini
     */
    public function getRecentMessages(int $limit = 10): array
    {
        $messages = $this->messages ?? [];
        return array_slice($messages, -$limit);
    }
}
