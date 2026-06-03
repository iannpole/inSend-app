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
        
        // 3.5 Cari produk langsung (opsi beli bahan)
        $products = $this->searchProducts($detectedTags, $tokens);

        // 4. Format response
        return $this->formatResponse($recipes, $suggestions, null, $detectedTags, $message, $requestedServings, $products);
    }

    public function chatWithImage(string $message, string $imagePath, array $history = [], string $originalFilename = ''): string
    {
        // Step 1: Analyze image via AI Vision to get ingredient keywords
        $imageKeywords = $this->analyzeImageHints($imagePath, $originalFilename);

        // Step 2: Search recipes that actually CONTAIN those ingredients (max 1, best match)
        $imageRecipes = collect([]);
        $imageProducts = collect([]);

        if (!empty($imageKeywords)) {
            $imageRecipes = $this->searchRecipesFromIngredients($imageKeywords);
            $imageProducts = $this->searchProducts($imageKeywords, $imageKeywords);
        }

        // Step 3: Build message
        $imageNote = '📸 Foto berhasil diterima! ';
        if (!empty($imageKeywords)) {
            $imageNote .= 'Saya mendeteksi bahan: *' . implode(', ', $imageKeywords) . '*.';
        } else {
            $imageNote .= 'Saya tidak bisa mendeteksi bahan dari foto ini. Coba foto yang lebih jelas ya!';
        }

        if ($imageRecipes->isNotEmpty()) {
            $imageNote .= '\n\nBerikut resep yang bisa dibuat dari bahan tersebut 🍳';
        } elseif (!empty($imageKeywords)) {
            $imageNote .= '\n\nBahan ini belum ada resepnya di database, tapi kamu bisa langsung beli bahan di bawah!';
        }

        $recipeList = $this->buildRecipeList($imageRecipes, null);
        $productList = $this->buildProductList($imageProducts);

        $response = [
            'type'             => 'recipe_suggestion',
            'message'          => $imageNote,
            'detected_intent'  => $imageKeywords,
            'recipes'          => $recipeList,
            'products'         => $productList,
            'total_found'      => count($recipeList),
            'image_analyzed'   => true,
            'detected_keywords'=> $imageKeywords,
        ];

        return json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    // ---------------------------------------------------------------
    // SEARCH RECIPES by strict ingredient match — for image context
    // ---------------------------------------------------------------

    private function searchRecipesFromIngredients(array $keywords): \Illuminate\Support\Collection
    {
        if (empty($keywords)) return collect([]);

        $query = Recipe::published();
        $query->where(function ($q) use ($keywords) {
            foreach ($keywords as $kw) {
                $q->orWhere('title', 'like', "%{$kw}%")
                  ->orWhere('description', 'like', "%{$kw}%");
            }
        });

        return $query
            ->select(['_id', 'title', 'description', 'category', 'tags',
                       'prep_time', 'cook_time', 'servings', 'difficulty',
                       'ingredients', 'images', 'nutrition'])
            ->limit(1) // max 1 recipe for image context
            ->get();
    }

    // ---------------------------------------------------------------
    // IMAGE ANALYSIS — extract food hints without external AI
    // ---------------------------------------------------------------

    private function analyzeImageHints(string $imagePath, string $originalFilename = ''): array
    {
        // 1. Try OpenRouter AI Vision if API Key is configured
        $openRouterKey = env('OPENROUTER_API_KEY');
        if ($openRouterKey && file_exists($imagePath)) {
            try {
                $base64 = base64_encode(file_get_contents($imagePath));
                $mime = mime_content_type($imagePath) ?: 'image/jpeg';
                
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'Authorization' => 'Bearer ' . $openRouterKey,
                    'HTTP-Referer' => env('APP_URL', 'http://localhost'),
                    'X-Title' => 'InSend App',
                ])->timeout(30)->post('https://openrouter.ai/api/v1/chat/completions', [
                    'model' => env('OPENROUTER_VISION_MODEL', env('OPENROUTER_MODEL', 'google/gemini-2.0-flash-exp:free')),
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => [
                                [
                                    'type' => 'text',
                                    'text' => 'Kamu adalah asisten koki cerdas. Analisis gambar ini. Jika ini adalah gambar bahan makanan mentah, sayuran, buah, daging, atau bumbu masakan, kembalikan HANYA daftar kata kunci bahan utamanya dalam bahasa Indonesia BAKU, dipisahkan koma (contoh: alpukat, timun, tomat, ayam, tahu, tempe, bawang merah, cabai, telur, daging sapi, ikan). Jangan gunakan istilah asing atau bahasa slang (gunakan "alpukat" bukan "awokado"/"avocado"). Jangan halusinasi. Jika gambar bukan makanan atau bahan yang bisa dimasak, kembalikan "tidak_ada". JANGAN beri penjelasan, hanya kata kunci.'
                                ],
                                [
                                    'type' => 'image_url',
                                    'image_url' => [
                                        'url' => "data:{$mime};base64,{$base64}"
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]);

                if ($response->successful()) {
                    $content = $response->json('choices.0.message.content');
                    if ($content) {
                        $content = str_replace(['.', '"', '\''], '', mb_strtolower($content));
                        $aiKeywords = array_map('trim', explode(',', $content));
                        $aiKeywords = array_filter($aiKeywords, fn($k) => $k !== 'tidak_ada' && $k !== 'tidak_ada_makanan' && !empty($k));
                        if (!empty($aiKeywords)) {
                            return array_unique($aiKeywords);
                        }
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('OpenRouter AI Vision Error: ' . $e->getMessage());
                // Silently fallback to heuristic if AI fails
            }
        }

        // 2. Fallback to local heuristic (filename and colors)
        $keywords = [];
        
        // 1. Analyze filename for food-related words
        $filename = mb_strtolower(pathinfo($originalFilename ?: $imagePath, PATHINFO_FILENAME));
        $filename = preg_replace('/[^a-z0-9\s_\-]/', ' ', $filename);
        
        $foodWords = [
            'ayam', 'ikan', 'daging', 'sapi', 'kambing', 'udang', 'cumi',
            'sayur', 'bayam', 'kangkung', 'brokoli', 'wortel', 'tomat',
            'buah', 'apel', 'jeruk', 'pisang', 'mangga', 'alpukat',
            'nasi', 'mie', 'roti', 'telur', 'tahu', 'tempe',
            'bawang', 'cabe', 'cabai', 'jahe', 'kunyit', 'santan',
            'goreng', 'bakar', 'rebus', 'kukus', 'panggang',
            'soto', 'rendang', 'gulai', 'sate', 'bakso', 'rawon',
            'chicken', 'fish', 'meat', 'beef', 'shrimp', 'egg',
            'vegetable', 'fruit', 'rice', 'noodle', 'salad',
            'food', 'meal', 'cook', 'kitchen', 'fridge', 'kulkas',
        ];
        
        foreach ($foodWords as $word) {
            if (str_contains($filename, $word)) {
                $keywords[] = $word;
            }
        }
        
        // 2. Check image EXIF data for time-of-day context
        if (function_exists('exif_read_data') && file_exists($imagePath)) {
            try {
                $exif = @exif_read_data($imagePath);
                if ($exif && isset($exif['DateTimeOriginal'])) {
                    $hour = (int) date('H', strtotime($exif['DateTimeOriginal']));
                    if ($hour >= 5 && $hour < 10) {
                        $keywords[] = 'sarapan';
                    } elseif ($hour >= 11 && $hour < 14) {
                        $keywords[] = 'makan_siang';
                    } elseif ($hour >= 17 && $hour < 21) {
                        $keywords[] = 'makan_malam';
                    }
                }
            } catch (\Exception $e) {
                // EXIF not available, skip
            }
        }
        
        // 3. Image color analysis — detect dominant green/red/brown for food category guess
        if (function_exists('imagecreatefromjpeg') && file_exists($imagePath)) {
            try {
                $ext = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
                $img = null;
                if ($ext === 'jpg' || $ext === 'jpeg') {
                    $img = @imagecreatefromjpeg($imagePath);
                } elseif ($ext === 'png') {
                    $img = @imagecreatefrompng($imagePath);
                }
                
                if ($img) {
                    $width = imagesx($img);
                    $height = imagesy($img);
                    $totalR = $totalG = $totalB = 0;
                    $sampleCount = 0;
                    
                    // Sample pixels in a grid pattern
                    for ($x = 0; $x < $width; $x += max(1, intval($width / 10))) {
                        for ($y = 0; $y < $height; $y += max(1, intval($height / 10))) {
                            $rgb = imagecolorat($img, $x, $y);
                            $totalR += ($rgb >> 16) & 0xFF;
                            $totalG += ($rgb >> 8) & 0xFF;
                            $totalB += $rgb & 0xFF;
                            $sampleCount++;
                        }
                    }
                    
                    if ($sampleCount > 0) {
                        $avgR = $totalR / $sampleCount;
                        $avgG = $totalG / $sampleCount;
                        $avgB = $totalB / $sampleCount;
                        
                        // Green dominant → likely vegetables
                        if ($avgG > $avgR * 1.2 && $avgG > $avgB * 1.2) {
                            $keywords[] = 'sayur';
                        }
                        // Red/orange dominant → likely meat or fruit
                        elseif ($avgR > $avgG * 1.3 && $avgR > $avgB * 1.3) {
                            $keywords[] = 'daging';
                            $keywords[] = 'buah';
                        }
                        // Brown tones → likely cooked food
                        elseif ($avgR > 100 && $avgG > 60 && $avgB < 80) {
                            $keywords[] = 'goreng';
                        }
                    }
                    
                    imagedestroy($img);
                }
            } catch (\Exception $e) {
                // GD not available, skip
            }
        }
        
        // 4. If nothing detected, add default food context
        if (empty($keywords)) {
            $keywords = ['masakan', 'segar'];
        }
        
        return array_unique($keywords);
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

        $filteredWords = array_filter($words, fn($w) => !in_array($w, $stopWords) && strlen($w) > 1);

        // Map sinonim / typo bahasa asing ke bahasa baku
        $synonyms = [
            'awokado'  => 'alpukat',
            'avocado'  => 'alpukat',
            'apokat'   => 'alpukat',
            'telor'    => 'telur',
            'cabe'     => 'cabai',
            'chili'    => 'cabai',
            'bawang'   => 'bawang',
            'onion'    => 'bawang',
            'garlic'   => 'bawang',
            'chicken'  => 'ayam',
            'beef'     => 'daging',
            'meat'     => 'daging',
            'fish'     => 'ikan',
            'egg'      => 'telur',
            'carrot'   => 'wortel',
            'cucumber' => 'timun',
            'mentimun' => 'timun',
            'tomat'    => 'tomat',
            'tomato'   => 'tomat',
            'sayuran'  => 'sayur',
            'vegetable'=> 'sayur',
        ];

        return array_map(fn($w) => $synonyms[$w] ?? $w, $filteredWords);
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
    // SEARCH PRODUCTS — query ke MongoDB berdasarkan tags untuk opsi "Beli Bahan"
    // ---------------------------------------------------------------

    private function searchProducts(array $tags, array $rawTokens): \Illuminate\Support\Collection
    {
        // Jika tidak ada kata kunci spesifik, jangan sarankan produk random
        if (empty($tags) && empty($rawTokens)) {
            return collect([]);
        }

        $query = \App\Models\Product::where('is_active', true);

        $query->where(function ($q) use ($tags, $rawTokens) {
            foreach ($tags as $tag) {
                $q->orWhere('name', 'like', "%{$tag}%")
                  ->orWhere('description', 'like', "%{$tag}%");
            }
            foreach ($rawTokens as $token) {
                $q->orWhere('name', 'like', "%{$token}%")
                  ->orWhere('description', 'like', "%{$token}%");
            }
        });

        return $query
            ->select(['_id', 'name', 'slug', 'base_price', 'sale_price', 'images', 'unit'])
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
        ?int $requestedServings = null,
        $products = null
    ): string {
        if ($customMessage && empty($recipes) && empty($products)) {
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
        $recipeList = $this->buildRecipeList($recipes, $requestedServings);

        // Susun daftar produk
        $productList = $this->buildProductList($products);

        // Susun respons final
        $response = [
            'type'            => 'recipe_suggestion',
            'message'         => $intro,
            'detected_intent' => $detectedTags,
            'recipes'         => $recipeList,
            'products'        => $productList,
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
    // BUILD RECIPE LIST — shared helper for formatResponse & chatWithImage
    // ---------------------------------------------------------------

    private function buildRecipeList($recipes, ?int $requestedServings): array
    {
        $appUrl = rtrim(env('APP_URL', 'http://localhost:8000'), '/');

        return collect($recipes)->map(function ($recipe) use ($requestedServings, $appUrl) {
            $defaultServings = $recipe->servings ?? 1;
            $targetServings  = $requestedServings ?? $defaultServings;
            $scale           = $targetServings / $defaultServings;

            $scaledIngredients = array_map(function ($ing) use ($scale) {
                if (isset($ing['amount']) && is_numeric($ing['amount'])) {
                    $ing['amount'] = round($ing['amount'] * $scale, 2);
                }
                if (isset($ing['name'])) {
                    $product = \App\Models\Product::where('name', 'LIKE', '%' . $ing['name'] . '%')->first();
                    if ($product) {
                        $ing['product_id'] = (string) $product->_id;
                        $ing['price'] = $product->effective_price ?? $product->base_price;
                        if (!empty($product->images) && isset($product->images[0])) {
                            $img = $product->images[0];
                            $ing['imageUrl'] = str_starts_with($img, 'http') ? $img : $appUrl . '/storage/' . $img;
                        }
                    }
                }
                return $ing;
            }, $recipe->ingredients ?? []);

            // Resolve image URL for the recipe itself
            $rawImage = $recipe->images[0] ?? null;
            $resolvedImage = null;
            if ($rawImage) {
                $resolvedImage = str_starts_with($rawImage, 'http') ? $rawImage : $appUrl . '/storage/' . $rawImage;
            }

            return [
                'id'                 => (string) $recipe->_id,
                'title'              => $recipe->title,
                'description'        => $recipe->description,
                'category'           => $recipe->category,
                'difficulty'         => $recipe->difficulty,
                'prep_time'          => $recipe->prep_time,
                'cook_time'          => $recipe->cook_time,
                'total_time'         => ($recipe->prep_time ?? 0) + ($recipe->cook_time ?? 0),
                'default_servings'   => $defaultServings,
                'requested_servings' => $targetServings,
                'ingredients'        => $scaledIngredients,
                'image'              => $resolvedImage,
                'images'             => collect($recipe->images ?? [])->map(fn($img) =>
                    str_starts_with($img, 'http') ? $img : $appUrl . '/storage/' . $img
                )->toArray(),
                'nutrition'          => $recipe->nutrition ?? null,
                'tags'               => $recipe->tags ?? [],
            ];
        })->values()->toArray();
    }

    // ---------------------------------------------------------------
    // BUILD PRODUCT LIST — shared helper
    // ---------------------------------------------------------------

    private function buildProductList($products): array
    {
        if (empty($products)) return [];
        $appUrl = rtrim(env('APP_URL', 'http://localhost:8000'), '/');
        return collect($products)->map(function ($product) use ($appUrl) {
            $rawImage = $product->images[0] ?? null;
            $resolvedImage = null;
            if ($rawImage) {
                $resolvedImage = str_starts_with($rawImage, 'http') ? $rawImage : $appUrl . '/storage/' . $rawImage;
            }
            return [
                'id'    => (string) $product->_id,
                'name'  => $product->name,
                'price' => $product->effective_price ?? $product->base_price,
                'image' => $resolvedImage,
                'unit'  => $product->unit,
            ];
        })->values()->toArray();
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
