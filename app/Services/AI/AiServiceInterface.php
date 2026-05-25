<?php

namespace App\Services\AI;

interface AiServiceInterface
{
    /**
     * Process a text chat message and return a response string.
     * Response is a JSON-encoded string for recipe bot, or plain text.
     */
    public function chat(string $message, array $history = []): string;

    /**
     * Process a chat message with an image path.
     * Image-aware features are optional; implementations may fallback to text.
     */
    public function chatWithImage(string $message, string $imagePath, array $history = []): string;
}