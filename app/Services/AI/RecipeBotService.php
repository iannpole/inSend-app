<?php

namespace App\Services\AI;

use App\Models\Recipe;

/**
 * RecipeBotService
 *
 * Pure algorithmic recipe recommendation engine.
 * No external AI API used — all logic runs on MongoDB recipe data.
 *
 * Features:
 *  - Tolerant input: fuzzy matching via Levenshtein distance & similar_text
 *  - Keyword detection: maps user intent to recipe categories/tags
 *  - Typo suggestions: recommends closest known keywords
 *  - Context-aware: e.g., "cuaca panas" → minuman segar, es, salad
 */
class RecipeBotService implements AiServiceInterface
{
    // ---------------------------------------------------------------
    // KAMUS KATA KUNCI → tag/kategori resep
    // ---------------------------------------------------------------
    private array $intentMap = [
        // Cuaca / suasana
        'panas'       => ['es', 'segar', 'dingin', 'minuman', 'jus', 'smoothie', 'salad'],
        'dingin'      => ['hangat', 'sup', 'kuah', 'berkuah', 'jahe', 'wedang'],
        'hujan'       => ['hangat', 'berkuah', 'sup', 'jahe', 'wedang', 'mie'],
        'summer'      => ['es', 'segar', 'jus', 'smoothie', 'buah'],
        'winter'      => ['hangat', 'sup', 'kuah'],

        // Mood / kondisi
        'diet'        => ['rendah_kalori', 'salad', 'panggang', 'rebus', 'sayur'],
        'sehat'       => ['rendah_kalori', 'salad', 'sayur', 'buah', 'rebus'],
        'sakit'       => ['bubur', 'sup', 'rebus', 'jahe', 'kuah'],
        'energi'      => ['protein', 'karbohidrat', 'nasi', 'daging'],
        'keto'        => ['rendah_karbohidrat', 'protein', 'daging', 'telur', 'keju'],

        // Waktu
        'sarapan'     => ['sarapan', 'pagi', 'roti', 'telur', 'bubur', 'oat'],
        'makan_siang' => ['makan_siang', 'nasi', 'lauk', 'sayur'],
        'makan_malam' => ['makan_malam', 'malam', 'nasi', 'lauk'],
        'cemilan'     => ['cemilan', 'snack', 'gorengan', 'kue'],
        'snack'       => ['cemilan', 'snack', 'gorengan', 'kue'],

        // Jenis masakan
        'indonesia'   => ['masakan_indonesia', 'nusantara'],
        'cepat'       => ['cepat', 'mudah', 'praktis', '10_menit', '15_menit'],
        'mudah'       => ['mudah', 'praktis', 'simpel'],
        'mewah'       => ['premium', 'spesial', 'restoran'],
        'vegetarian'  => ['vegetarian', 'vegan', 'sayur', 'tanpa_daging'],
        'vegan'       => ['vegan', 'vegetarian', 'tanpa_susu', 'sayur'],

        // Bahan utama
        'ayam'        => ['ayam', 'unggas'],
        'ikan'        => ['ikan', 'seafood', 'laut'],
        'daging'      => ['daging', 'sapi', 'kambing', 'protein'],
        'telur'       => ['telur'],
        'tahu'        => ['tahu', 'tempe', 'vegetarian'],
        'tempe'       => ['tempe', 'tahu', 'vegetarian'],
        'sayur'       => ['sayur', 'vegetarian'],
        'buah'        => ['buah', 'segar', 'jus'],
        'nasi'        => ['nasi', 'makan_siang', 'makan_malam'],
        'mie'         => ['mie', 'pasta'],
        'roti'        => ['roti', 'sarapan', 'cemilan'],

        // Minuman
        'minuman'     => ['minuman', 'jus', 'es', 'segar'],
        'kopi'        => ['kopi', 'minuman', 'hangat'],
        'teh'         => ['teh', 'minuman', 'hangat'],
        'jus'         => ['jus', 'minuman', 'buah', 'segar'],
        'smoothie'    => ['smoothie', 'jus', 'buah', 'sehat'],

        // Teknik memasak
        'goreng'      => ['goreng', 'crispy'],
        'bakar'       => ['bakar', 'panggang', 'grill'],
        'rebus'       => ['rebus', 'sehat', 'berkuah'],
        'kukus'       => ['kukus', 'sehat'],
        'panggang'    => ['panggang', 'oven', 'bakar'],
    ];

    // Daftar semua kata kunci yang dikenal (untuk saran typo)
    private array $knownKeywords = [];

    public function __construct()
    {
        $this->knownKeywords = array_keys($this->intentMap);
    }

    // ---------------------------------------------------------------
    // Implementasi interface
    // ---------------------------------------------------------------

    public function chat(string $message, array $history = []): string
    {
        if (empty(trim($message))) {
            return $this->formatResponse([], [], 'Halo! Ceritakan ingin masak apa atau bagaimana suasana hari ini? 😊');
        }

        // 1. Tokenisasi & normalisasi pesan
        $tokens = $this->tokenize($message);

        // 1.5 Cek apakah user minta porsi spesifik (misal: "untuk 4 orang", "3 porsi", "porsi 5")
        $requestedServings = null;
        if (preg_match('/(?:(\d+)\s*(?:porsi|orang))|(?:(?:porsi|buat|untuk)\s*(\d+))/i', $message, $matches)) {
            $requestedServings = (int) (!empty($matches[1]) ? $matches[1] : $matches[2]);
        }

        // 2. Detect intent dari token
        $detectedTags = $this->detectTags($tokens);
        $suggestions  = $this->suggestCorrections($tokens);

        // 3. Cari resep dari DB berdasarkan tags terdeteksi
        $recipes = $this->searchRecipes($detectedTags, $tokens);

        // 4. Format response
        return $this->formatResponse($recipes, $suggestions, null, $detectedTags, $message, $requestedServings);
    }

    public function chatWithImage(string $message, string $imagePath, array $history = []): string
    {
        // Tanpa AI, fitur gambar tidak diproses — fallback ke chat text biasa
        $note = '[Fitur analisis gambar tidak tersedia. Menampilkan rekomendasi berdasarkan teks.] ';
        return $note . $this->chat($message, $history);
    }

    // ---------------------------------------------------------------
    // TOKENIZER — normalisasi & pecah kata
    // ---------------------------------------------------------------

    private function tokenize(string $text): array
    {
        $text = mb_strtolower($text);

        // Hapus karakter spesial, pertahankan huruf & angka
        $text = preg_replace('/[^a-z0-9\s]/', ' ', $text);

        // Pecah jadi array kata
        $words = preg_split('/\s+/', trim($text), -1, PREG_SPLIT_NO_EMPTY);

        // Filter stop words bahasa Indonesia
        $stopWords = ['dan', 'atau', 'yang', 'ini', 'itu', 'di', 'ke', 'dari',
                      'ada', 'apa', 'saya', 'mau', 'minta', 'tolong', 'bisa',
                      'boleh', 'dong', 'kah', 'lah', 'yah', 'ya', 'nih', 'aja',
                      'kok', 'sih', 'gimana', 'bagaimana', 'cara', 'buat', 'bikin',
                      'resep', 'masak', 'makanan', 'minuman'];

        return array_filter($words, fn($w) => !in_array($w, $stopWords) && strlen($w) > 1);
    }

    // ---------------------------------------------------------------
    // DETECT TAGS — langsung atau fuzzy dari intent map
    // ---------------------------------------------------------------

    private function detectTags(array $tokens): array
    {
        $tags = [];

        foreach ($tokens as $token) {
            // Exact match
            if (isset($this->intentMap[$token])) {
                $tags = array_merge($tags, $this->intentMap[$token]);
                continue;
            }

            // Fuzzy match — cari keyword terdekat
            $best = $this->findClosestKeyword($token, threshold: 75);
            if ($best) {
                $tags = array_merge($tags, $this->intentMap[$best]);
            }
        }

        return array_unique($tags);
    }

    // ---------------------------------------------------------------
    // FUZZY MATCH — Levenshtein + similar_text hybrid
    // ---------------------------------------------------------------

    private function findClosestKeyword(string $word, int $threshold = 75): ?string
    {
        $bestMatch  = null;
        $bestScore  = 0;

        foreach ($this->knownKeywords as $keyword) {
            // Gunakan similar_text untuk persentase kemiripan
            similar_text($word, $keyword, $percent);

            // Juga cek Levenshtein distance (max 2 karakter berbeda untuk kata pendek)
            $lev     = levenshtein($word, $keyword);
            $maxLen  = max(strlen($word), strlen($keyword));
            $levScore = $maxLen > 0 ? (1 - $lev / $maxLen) * 100 : 0;

            // Ambil rata-rata kedua score
            $score = ($percent + $levScore) / 2;

            if ($score > $bestScore && $score >= $threshold) {
                $bestScore = $score;
                $bestMatch = $keyword;
            }
        }

        return $bestMatch;
    }

    // ---------------------------------------------------------------
    // SUGGEST CORRECTIONS — rekomendasikan kata yang mendekati
    // ---------------------------------------------------------------

    private function suggestCorrections(array $tokens): array
    {
        $suggestions = [];

        foreach ($tokens as $token) {
            if (isset($this->intentMap[$token])) {
                continue; // sudah tepat, tidak perlu saran
            }

            $best = $this->findClosestKeyword($token, threshold: 55);
            if ($best && $best !== $token) {
                $suggestions[] = [
                    'input'      => $token,
                    'suggestion' => $best,
                    'meaning'    => "Apakah maksud Anda \"{$best}\"?",
                ];
            }
        }

        return $suggestions;
    }

    // ---------------------------------------------------------------
    // SEARCH RECIPES — query ke MongoDB berdasarkan tags
    // ---------------------------------------------------------------

    private function searchRecipes(array $tags, array $rawTokens): \Illuminate\Support\Collection
    {
        $query = Recipe::published();

        if (!empty($tags)) {
            // Cari resep yang punya salah satu dari tags terdeteksi
            $query->where(function ($q) use ($tags, $rawTokens) {
                // Match tags array (MongoDB elemMatch atau $in)
                $q->whereIn('tags', $tags);

                // Fallback: cari juga di title & ingredients.name
                foreach ($rawTokens as $token) {
                    $q->orWhere('title', 'like', "%{$token}%")
                      ->orWhere('description', 'like', "%{$token}%");
                }
            });
        } else {
            // Tidak ada tag terdeteksi — kembalikan resep populer/terbaru
            $query->latest();
        }

        return $query
            ->select(['_id', 'title', 'description', 'category', 'tags',
                       'prep_time', 'cook_time', 'servings', 'difficulty',
                       'ingredients', 'images', 'nutrition'])
            ->limit(5)
            ->get();
    }

    // ---------------------------------------------------------------
    // FORMAT RESPONSE — susun reply teks + data JSON
    // ---------------------------------------------------------------

    private function formatResponse(
        $recipes,
        array $suggestions = [],
        ?string $customMessage = null,
        array $detectedTags = [],
        string $originalQuery = '',
        ?int $requestedServings = null
    ): string {
        if ($customMessage && empty($recipes)) {
            return json_encode([
                'type'    => 'greeting',
                'message' => $customMessage,
                'recipes' => [],
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }

        // Buat kalimat pembuka berdasarkan tags
        $intro = $this->generateIntro($detectedTags, $originalQuery);

        if ($requestedServings) {
            $intro .= "\n💡 Porsi disesuaikan secara otomatis untuk $requestedServings orang. Bahan-bahan sudah dikalibrasi dan siap masuk keranjang belanja Anda!";
        }

        // Susun daftar resep
        $recipeList = collect($recipes)->map(function ($recipe) use ($requestedServings) {
            $defaultServings = $recipe->servings ?? 1;
            $targetServings = $requestedServings ?? $defaultServings;
            $scale = $targetServings / $defaultServings;

            // Scale ingredients
            $scaledIngredients = array_map(function($ing) use ($scale) {
                if (isset($ing['amount']) && is_numeric($ing['amount'])) {
                    // Kalikan amount dan bulatkan (misal: 1.5)
                    $ing['amount'] = round($ing['amount'] * $scale, 2);
                }
                return $ing;
            }, $recipe->ingredients ?? []);

            return [
                'id'          => (string) $recipe->_id,
                'title'       => $recipe->title,
                'description' => $recipe->description,
                'category'    => $recipe->category,
                'difficulty'  => $recipe->difficulty,
                'prep_time'   => $recipe->prep_time,
                'cook_time'   => $recipe->cook_time,
                'total_time'  => ($recipe->prep_time ?? 0) + ($recipe->cook_time ?? 0),
                'default_servings'   => $defaultServings,
                'requested_servings' => $targetServings,
                'ingredients' => $scaledIngredients,
                'image'       => isset($recipe->images[0]) ? $recipe->images[0] : null,
                'nutrition'   => $recipe->nutrition ?? null,
                'tags'        => $recipe->tags ?? [],
            ];
        })->values()->toArray();

        // Susun respons final
        $response = [
            'type'            => 'recipe_suggestion',
            'message'         => $intro,
            'detected_intent' => $detectedTags,
            'recipes'         => $recipeList,
            'total_found'     => count($recipeList),
        ];

        // Tambahkan saran koreksi jika ada
        if (!empty($suggestions)) {
            $response['corrections'] = $suggestions;
            $correctionText = implode(', ', array_column($suggestions, 'meaning'));
            $response['message'] .= "\n\n💡 " . $correctionText;
        }

        if (empty($recipeList)) {
            $response['type']    = 'no_result';
            $response['message'] = "Hmm, belum ada resep yang cocok untuk \"" . htmlspecialchars($originalQuery) . "\". "
                . "Coba kata lain seperti: *panas, dingin, sehat, sarapan, ayam, ikan*, dll. 🍽️";
        }

        return json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    // ---------------------------------------------------------------
    // GENERATE INTRO — kalimat kontekstual berdasarkan intent
    // ---------------------------------------------------------------

    private function generateIntro(array $tags, string $query): string
    {
        // Konteks cuaca panas
        if (array_intersect($tags, ['es', 'segar', 'dingin', 'jus', 'smoothie'])) {
            return "Cuaca panas ya? ☀️ Ini resep-resep segar yang cocok buat kamu!";
        }

        // Konteks hangat/hujan
        if (array_intersect($tags, ['hangat', 'sup', 'kuah', 'berkuah', 'jahe'])) {
            return "Butuh yang hangat dan nyaman? 🌧️ Ini rekomendasi resep berkuah untuk kamu!";
        }

        // Konteks diet/sehat
        if (array_intersect($tags, ['rendah_kalori', 'salad', 'vegetarian', 'vegan'])) {
            return "Mau makan sehat? 🥗 Ini pilihan resep bergizi untuk mendukung gaya hidupmu!";
        }

        // Konteks cepat/mudah
        if (array_intersect($tags, ['mudah', 'praktis', 'cepat'])) {
            return "Mau yang simpel dan cepat? ⚡ Ini resep mudah yang bisa kamu coba!";
        }

        // Konteks sarapan
        if (array_intersect($tags, ['sarapan', 'pagi'])) {
            return "Selamat pagi! 🌅 Ini ide sarapan lezat buat memulai harimu!";
        }

        // Konteks protein/daging
        if (array_intersect($tags, ['protein', 'daging', 'ayam', 'ikan'])) {
            return "Butuh asupan protein? 💪 Ini resep dengan bahan utama pilihan untuk kamu!";
        }

        // Default
        return "Ini rekomendasi resep berdasarkan \"" . htmlspecialchars($query) . "\" yang mungkin kamu suka 😊";
    }
}
