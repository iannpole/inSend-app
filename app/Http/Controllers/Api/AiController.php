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

        // Build parts untuk user message (TANPA system prompt)
        $parts = [];

        // Handle image upload (Vision)
        $imageUrl = null;

        if ($request->hasFile('image')) {
            $file      = $request->file('image');
            $path      = $file->store('ai-images', 'public');
            $imageUrl  = asset('storage/' . $path);

            $parts[] = [
                'inline_data' => [
                    'mime_type' => $file->getMimeType(),
                    'data'      => base64_encode(file_get_contents($file->getRealPath())),
                ],
            ];
        }

        // RAG context masuk ke user message (dinamis per request)
        $recipeContext = $this->getRecipeContext($message, $servings);

        // User text = RAG context + pesan user (system prompt TERPISAH)
        $userText = '';
        if ($recipeContext !== '') {
            $userText .= "[REFERENSI PRODUK & RESEP INSEND]\n{$recipeContext}\n\n";
        }
        if (!empty($message)) {
            $userText .= $message;
        }
        if ($request->hasFile('image') && empty($message)) {
            $userText .= 'Analisa gambar yang diupload dan buat resep yang sesuai.';
        }

        $parts[] = ['text' => $userText];

        // Build system prompt (statis, personality + aturan)
        $systemPrompt = $this->buildSystemPrompt($servings, $mode, $request->hasFile('image'));

        // Build Gemini contents dengan history
        $contents = $this->buildContents($conversation, $parts);

        // Tentukan max tokens berdasarkan mode
        $maxTokens = $mode === 'recipe' || $request->hasFile('image') ? 1500 : 1024;

        // Panggil Gemini API dengan system_instruction terpisah
        $response = $this->callGemini($contents, $systemPrompt, $maxTokens, $mode);

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
            'message' => 'Berhasil',
            'data'    => [
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
     * Hanya kirim field yang diperlukan untuk menghemat token
     */
    private function getRecipeContext(string $message, int $servings): string
    {
        if (empty($message)) {
            $recipes = Recipe::published()->limit(3)->get(['title', 'category', 'ingredients', 'tags']);
        } else {
            $recipes = Recipe::published()->search($message)->limit(5)->get(['title', 'category', 'ingredients', 'tags']);
            if ($recipes->isEmpty()) {
                $recipes = Recipe::published()->limit(3)->get(['title', 'category', 'ingredients', 'tags']);
            }
        }

        if ($recipes->isEmpty()) {
            return '';
        }

        $context = '';
        foreach ($recipes as $i => $recipe) {
            $ingredients = collect($recipe->ingredients)->pluck('name')->join(', ');
            $tags = $recipe->tags ? implode(', ', $recipe->tags) : '';
            $context .= ($i + 1) . ". {$recipe->title} [{$recipe->category}] — Bahan: {$ingredients}";
            if ($tags) {
                $context .= " | Tags: {$tags}";
            }
            $context .= "\n";
        }

        return $context;
    }

    /**
     * Build system prompt (STATIS — personality + aturan + format)
     * Ini masuk ke system_instruction, BUKAN ke user message
     */
    private function buildSystemPrompt(int $servings, string $mode, bool $hasImage): string
    {
        $base = 'Kamu adalah Insend AI, asisten kuliner eksklusif untuk aplikasi e-grocery Insend. Tugasmu HANYA memberikan resep dan merekomendasikan produk bahan makanan.';

        if ($mode === 'recipe' || $hasImage) {
            return <<<PROMPT
{$base}

ATURAN:
1. JANGAN menjawab di luar topik makanan, resep, atau bahan masakan.
2. Jika ada gambar, identifikasi bahan yang terlihat lalu buat resep.
3. Gunakan referensi resep dari [REFERENSI PRODUK & RESEP INSEND] jika relevan.
4. Sesuaikan takaran untuk {$servings} porsi.
5. Selalu kembalikan respon dalam format JSON berikut:

{"konteks":"Penjelasan singkat kenapa resep ini cocok","nama_resep":"...","waktu_masak":"...","porsi":{$servings},"bahan":[{"nama":"...","takaran":"...","keyword_insend":"kata kunci cari di Insend"}],"langkah":["Langkah 1","Langkah 2"],"tips":"..."}
PROMPT;
        }

        return <<<PROMPT
{$base}

ATURAN:
1. JANGAN menjawab di luar topik makanan, resep, atau bahan masakan.
2. Jawab dengan ramah, informatif, dan ringkas.
3. Jika diminta resep, sesuaikan untuk {$servings} porsi dan kembalikan dalam JSON:

{"konteks":"...","nama_resep":"...","waktu_masak":"...","porsi":{$servings},"bahan":[{"nama":"...","takaran":"...","keyword_insend":"..."}],"langkah":["..."],"tips":"..."}

4. Jika hanya ditanya tips/info umum kuliner, jawab dalam JSON:
{"tipe":"info","jawaban":"..."}
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
     * Panggil Gemini REST API dengan system_instruction terpisah
     */
    private function callGemini(array $contents, string $systemPrompt, int $maxTokens, string $mode): array
    {
        try {
            $payload = [
                // System instruction TERPISAH dari contents — hemat token, lebih patuh
                'system_instruction' => [
                    'parts' => [['text' => $systemPrompt]],
                ],
                'contents'         => $contents,
                'generationConfig' => [
                    'temperature'      => 0.7,
                    'maxOutputTokens'  => $maxTokens,
                    'topP'             => 0.9,
                    // Paksa output JSON untuk mode recipe, biarkan bebas untuk chat info
                    'responseMimeType' => 'application/json',
                ],
                'safetySettings' => [
                    ['category' => 'HARM_CATEGORY_HARASSMENT',  'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                    ['category' => 'HARM_CATEGORY_HATE_SPEECH', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                ],
            ];

            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->timeout(60)
                ->post("{$this->baseUrl}?key={$this->apiKey}", $payload);

            if (!$response->successful()) {
                Log::warning('Gemini API non-200', [
                    'status' => $response->status(),
                    'body'   => Str::limit($response->body(), 500),
                ]);
                return ['success' => false, 'error' => $response->body()];
            }

            $body = $response->json();
            $text = $body['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (!$text) {
                return ['success' => false, 'error' => 'Empty response from Gemini'];
            }

            // Validasi JSON
            json_decode($text);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::warning('Gemini returned non-JSON', ['response' => Str::limit($text, 300)]);
            }

            return ['success' => true, 'text' => $text];

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
