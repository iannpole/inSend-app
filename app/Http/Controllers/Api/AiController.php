<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AiChatRequest;
use App\Models\AiConversation;
use App\Models\Recipe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AiController extends Controller
{
    private string $apiKey;
    private string $model;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey  = config('services.gemini.api_key');
        $this->model   = config('services.gemini.model', 'gemini-1.5-flash');
        $this->baseUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent";
    }

    /**
     * POST /api/ai/chat
     * Terima pesan teks dan/atau gambar, kirim ke Gemini AI
     */
    public function chat(AiChatRequest $request): JsonResponse
    {
        $userId         = (string) $request->user()->_id;
        $conversationId = $request->input('conversation_id');
        $message        = $request->input('message', '');
        $servings       = $request->input('servings', 2);
        $mode           = $request->input('mode', 'chat');

        // Ambil atau buat conversation
        $conversation = $conversationId
            ? AiConversation::where('_id', $conversationId)->where('user_id', $userId)->first()
            : null;

        if (!$conversation) {
            $conversation = AiConversation::create([
                'user_id'  => $userId,
                'title'    => Str::limit($message ?: 'Recipe dari gambar', 50),
                'mode'     => $mode,
                'messages' => [],
            ]);
        }

        // Build parts untuk Gemini
        $parts = [];

        // Handle image upload (Vision)
        $imageUrl     = null;
        $imageBase64  = null;
        $imageMime    = null;

        if ($request->hasFile('image')) {
            $file        = $request->file('image');
            $path        = $file->store('ai-images', 'public');
            $imageUrl    = asset('storage/' . $path);
            $imageBase64 = base64_encode(file_get_contents($file->getRealPath()));
            $imageMime   = $file->getMimeType();

            $parts[] = [
                'inline_data' => [
                    'mime_type' => $imageMime,
                    'data'      => $imageBase64,
                ],
            ];
        }

        // Cari resep relevan dari MongoDB (RAG)
        $recipeContext = $this->getRecipeContext($message, $servings);

        // Build system prompt
        $systemPrompt = $this->buildSystemPrompt($recipeContext, $servings, $mode, $request->hasFile('image'));

        // Gabungkan teks prompt
        $userText = $systemPrompt;
        if (!empty($message)) {
            $userText .= "\n\nPermintaan pengguna: {$message}";
        }
        if ($request->hasFile('image') && empty($message)) {
            $userText .= "\n\nAnalisa gambar yang diupload dan buat resep yang sesuai.";
        }

        $parts[] = ['text' => $userText];

        // Build Gemini API request dengan history conversation
        $contents = $this->buildContents($conversation, $parts);

        // Panggil Gemini API
        $response = $this->callGemini($contents);

        if (!$response['success']) {
            Log::error('Gemini API Error', ['error' => $response['error']]);
            return response()->json([
                'message' => 'Gagal menghubungi AI. Coba lagi.',
                'error'   => $response['error'],
            ], 503);
        }

        $aiReply = $response['text'];

        // Simpan pesan user dan reply AI ke conversation
        $conversation->addMessage('user', $message ?: 'Upload gambar', $imageUrl);
        $conversation->addMessage('model', $aiReply);

        return response()->json([
            'message'         => 'Berhasil',
            'data'            => [
                'conversation_id' => (string) $conversation->_id,
                'reply'           => $aiReply,
                'image_url'       => $imageUrl,
            ],
        ]);
    }

    /**
     * POST /api/ai/generate-recipe
     * Khusus endpoint generate resep (shortcut dari /chat)
     */
    public function generateRecipe(AiChatRequest $request): JsonResponse
    {
        $request->merge(['mode' => 'recipe']);
        return $this->chat($request);
    }

    /**
     * GET /api/ai/conversations
     */
    public function conversations(Request $request): JsonResponse
    {
        $conversations = AiConversation::where('user_id', (string) $request->user()->_id)
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        $data = $conversations->map(function ($conv) {
            return [
                'id'         => (string) $conv->_id,
                'title'      => $conv->title,
                'mode'       => $conv->mode,
                'msg_count'  => count($conv->messages ?? []),
                'updated_at' => $conv->updated_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'total'        => $conversations->total(),
                'current_page' => $conversations->currentPage(),
                'last_page'    => $conversations->lastPage(),
            ],
        ]);
    }

    /**
     * GET /api/ai/conversations/{id}
     */
    public function showConversation(Request $request, string $id): JsonResponse
    {
        $conversation = AiConversation::where('_id', $id)
            ->where('user_id', (string) $request->user()->_id)
            ->firstOrFail();

        return response()->json([
            'data' => [
                'id'         => (string) $conversation->_id,
                'title'      => $conversation->title,
                'mode'       => $conversation->mode,
                'messages'   => $conversation->messages,
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
            ->where('user_id', (string) $request->user()->_id)
            ->firstOrFail();

        $conversation->delete();

        return response()->json(['message' => 'Percakapan dihapus']);
    }

    // ─────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────

    /**
     * Cari resep relevan di MongoDB berdasarkan keyword (RAG)
     */
    private function getRecipeContext(string $message, int $servings): string
    {
        if (empty($message)) {
            // Jika tidak ada pesan (hanya foto), ambil 3 resep populer sebagai inspirasi
            $recipes = Recipe::published()->limit(3)->get();
        } else {
            $recipes = Recipe::published()->search($message)->limit(5)->get();
            if ($recipes->isEmpty()) {
                $recipes = Recipe::published()->limit(3)->get();
            }
        }

        if ($recipes->isEmpty()) {
            return 'Tidak ada resep referensi tersedia.';
        }

        $context = "RESEP REFERENSI DARI DATABASE INSEND:\n";
        foreach ($recipes as $i => $recipe) {
            $context .= "\n--- Resep " . ($i + 1) . ": {$recipe->title} ---\n";
            $context .= "Kategori: {$recipe->category}\n";
            $context .= "Bahan: " . collect($recipe->ingredients)->pluck('name')->join(', ') . "\n";
            if ($recipe->tags) {
                $context .= "Tags: " . implode(', ', $recipe->tags) . "\n";
            }
        }

        return $context;
    }

    /**
     * Build system prompt Insend AI
     */
    private function buildSystemPrompt(string $recipeContext, int $servings, string $mode, bool $hasImage): string
    {
        $basePersonality = "Kamu adalah Insend AI, asisten kuliner cerdas dari aplikasi Insend. Kamu ahli dalam masakan Indonesia dan internasional, fokus pada resep praktis, bumbu yang tepat, dan takaran yang akurat.";

        if ($mode === 'recipe' || $hasImage) {
            return <<<PROMPT
{$basePersonality}

{$recipeContext}

INSTRUKSIMU:
1. Jika ada gambar, identifikasi bahan-bahan yang terlihat.
2. Berdasarkan bahan yang diidentifikasi dan resep referensi di atas, buat resep lengkap untuk {$servings} orang.
3. Format respon SELALU dalam struktur berikut:
   🍽️ **Nama Resep**: [nama resep]
   ⏱️ **Waktu Masak**: [estimasi]
   👥 **Porsi**: {$servings} orang
   
   **Bahan-bahan:**
   - [bahan] [takaran untuk {$servings} orang]
   
   **Cara Masak:**
   1. [langkah pertama]
   2. [langkah berikutnya]
   ...
   
   💡 **Tips**: [tips masak]
4. Gunakan takaran yang spesifik dan praktis (sendok makan, gram, ml, dll).
5. Pastikan bumbu sesuai best practice masakan Indonesia.
PROMPT;
        }

        return <<<PROMPT
{$basePersonality}

{$recipeContext}

Jawab pertanyaan tentang masakan, resep, atau kuliner dengan ramah dan informatif. Jika diminta resep, gunakan referensi di atas dan sesuaikan untuk {$servings} orang.
PROMPT;
    }

    /**
     * Build Gemini contents array dengan history conversation
     */
    private function buildContents(AiConversation $conversation, array $newParts): array
    {
        $contents = [];

        // Ambil 6 pesan terakhir sebagai context history
        $recentMessages = $conversation->getRecentMessages(6);
        foreach ($recentMessages as $msg) {
            $contents[] = [
                'role'  => $msg['role'], // 'user' | 'model'
                'parts' => [['text' => $msg['content']]],
            ];
        }

        // Tambah pesan baru
        $contents[] = [
            'role'  => 'user',
            'parts' => $newParts,
        ];

        return $contents;
    }

    /**
     * Panggil Gemini REST API
     */
    private function callGemini(array $contents): array
    {
        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->timeout(60)
                ->post("{$this->baseUrl}?key={$this->apiKey}", [
                    'contents'         => $contents,
                    'generationConfig' => [
                        'temperature'     => 0.7,
                        'maxOutputTokens' => 2048,
                        'topP'            => 0.9,
                    ],
                    'safetySettings' => [
                        ['category' => 'HARM_CATEGORY_HARASSMENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                        ['category' => 'HARM_CATEGORY_HATE_SPEECH', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                    ],
                ]);

            if (!$response->successful()) {
                return ['success' => false, 'error' => $response->body()];
            }

            $body = $response->json();
            $text = $body['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (!$text) {
                return ['success' => false, 'error' => 'Empty response from Gemini'];
            }

            return ['success' => true, 'text' => $text];

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
