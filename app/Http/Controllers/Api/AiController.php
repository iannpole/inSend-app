<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AiChatRequest;
use App\Models\AiConversation;
use App\Services\AI\AiServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AiController extends Controller
{
    public function __construct(
        private AiServiceInterface $aiService
    ) {}

    /**
     * POST /api/ai/chat
     */
    public function chat(AiChatRequest $request): JsonResponse
    {
        $userId  = $request->user()->id;
        $message = trim($request->message ?? '');
        $mode    = $request->mode ?? 'chat';

        // Cari atau buat conversation
        $conversation = $request->conversation_id
            ? AiConversation::where('_id', $request->conversation_id)
                ->where('user_id', $userId)
                ->first()
            : null;

        if (!$conversation) {
            $conversation = AiConversation::create([
                'user_id' => $userId,
                'title'   => Str::limit($message ?: 'Sesi baru', 50),
                'mode'    => $mode,
            ]);
        }

        // Handle image upload (disimpan URL-nya, tapi bot tidak analisis gambar)
        $imageUrl  = null;
        $imagePath = null;

        if ($request->hasFile('image')) {
            $file      = $request->file('image');
            $imagePath = $file->getPathname();
            $imageUrl  = $file->store('ai-images', 'public');
        }

        // Simpan pesan user ke conversation
        $conversation->addMessage('user', $message, $imageUrl);

        // Ambil history percakapan untuk konteks
        $history = $conversation->getRecentMessages(10);

        // Jalankan RecipeBotService
        try {
            $rawResponse = $imagePath
                ? $this->aiService->chatWithImage($message, $imagePath, $history)
                : $this->aiService->chat($message, $history);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal memproses permintaan.',
                'error'   => $e->getMessage(),
            ], 500);
        }

        // Simpan respons bot ke conversation
        $conversation->addMessage('model', $rawResponse);

        // Decode JSON dari bot supaya frontend dapat struktur terpisah
        $parsed = json_decode($rawResponse, true);

        return response()->json([
            'status'          => 'success',
            'conversation_id' => (string) $conversation->_id,
            'mode'            => $mode,
            'reply'           => $parsed['message'] ?? $rawResponse,
            'type'            => $parsed['type'] ?? 'chat',
            'recipes'         => $parsed['recipes'] ?? [],
            'corrections'     => $parsed['corrections'] ?? [],
            'detected_intent' => $parsed['detected_intent'] ?? [],
            'total_found'     => $parsed['total_found'] ?? 0,
        ]);
    }

    /**
     * POST /api/ai/generate-recipe
     */
    public function generateRecipe(AiChatRequest $request): JsonResponse
    {
        $message = trim($request->message ?? '');

        if (empty($message)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Pesan tidak boleh kosong untuk generate resep.',
            ], 422);
        }

        try {
            $rawResponse = $this->aiService->chat($message, []);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal generate resep.',
                'error'   => $e->getMessage(),
            ], 500);
        }

        $parsed = json_decode($rawResponse, true);

        return response()->json([
            'status'  => 'success',
            'reply'   => $parsed['message'] ?? $rawResponse,
            'type'    => $parsed['type'] ?? 'recipe_suggestion',
            'recipes' => $parsed['recipes'] ?? [],
        ]);
    }

    /**
     * GET /api/ai/conversations
     */
    public function conversations(Request $request): JsonResponse
    {
        $conversations = AiConversation::where('user_id', $request->user()->id)
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function ($conv) {
                $messages = $conv->messages ?? [];
                $lastMessage = !empty($messages) ? end($messages) : null;

                return [
                    'id'           => (string) $conv->_id,
                    'title'        => $conv->title,
                    'mode'         => $conv->mode,
                    'message_count'=> count($messages),
                    'last_message' => $lastMessage ? Str::limit($lastMessage['content'] ?? '', 100) : null,
                    'created_at'   => $conv->created_at?->toIso8601String(),
                    'updated_at'   => $conv->updated_at?->toIso8601String(),
                ];
            });

        return response()->json([
            'status' => 'success',
            'data'   => $conversations,
        ]);
    }

    /**
     * GET /api/ai/conversations/{id}
     */
    public function showConversation(Request $request, string $id): JsonResponse
    {
        $conversation = AiConversation::where('_id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$conversation) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Percakapan tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'id'         => (string) $conversation->_id,
                'title'      => $conversation->title,
                'mode'       => $conversation->mode,
                'messages'   => $conversation->messages ?? [],
                'context'    => $conversation->context,
                'created_at' => $conversation->created_at?->toIso8601String(),
                'updated_at' => $conversation->updated_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * DELETE /api/ai/conversations/{id}
     */
    public function deleteConversation(Request $request, string $id): JsonResponse
    {
        $conversation = AiConversation::where('_id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$conversation) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Percakapan tidak ditemukan',
            ], 404);
        }

        $conversation->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Percakapan berhasil dihapus',
        ]);
    }
}