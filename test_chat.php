<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\User;
use App\Http\Controllers\Api\AiController;
use App\Http\Requests\AiChatRequest;

echo "Membuat dummy user...\n";
$user = User::firstOrCreate(
    ['email' => 'testbot@example.com'],
    [
        'name' => 'Tester',
        'password' => bcrypt('password123'),
        'email_verified_at' => now(),
        'role' => 'user'
    ]
);

// Skenario Test 1: Cuaca Panas
testDirectly($user, "Cuaca lagi panas banget nih, enaknya minum apa ya?");

// Skenario Test 2: Makanan Sehat
testDirectly($user, "Aku mau diet, butuh rekomendasi sayur yang rendah kalori.");

// Skenario Test 3: Typo Tolerance
testDirectly($user, "ada ide buat srapan praktis?");

// Skenario Test 4: Dynamic Porsi
testDirectly($user, "Tolong carikan resep ayam buat 8 orang dong");

function testDirectly($user, $message) {
    echo "============================================\n";
    echo "USER: $message\n";
    echo "--------------------------------------------\n";
    
    $request = AiChatRequest::create('/api/ai/chat', 'POST', [
        'message' => $message,
    ]);
    $request->setUserResolver(fn() => $user);

    $controller = app(AiController::class);
    $response = $controller->chat($request);
    
    $data = json_decode($response->getContent(), true);
    
    if (isset($data['reply'])) {
        echo "BOT : " . $data['reply'] . "\n\n";
        
        if (!empty($data['corrections'])) {
            echo "💡 Saran Typo:\n";
            foreach($data['corrections'] as $c) {
                echo "   - " . $c['meaning'] . "\n";
            }
            echo "\n";
        }

        if (!empty($data['recipes'])) {
            echo "🍽️ Resep Ditemukan (" . $data['total_found'] . "):\n";
            foreach ($data['recipes'] as $idx => $r) {
                echo "   " . ($idx + 1) . ". " . $r['title'] . " (" . $r['requested_servings'] . " porsi)\n";
                // Tampilkan beberapa bahan pertama untuk bukti scaling
                if (!empty($r['ingredients'])) {
                    echo "      🛒 Bahan-bahan (Auto Cart):\n";
                    foreach(array_slice($r['ingredients'], 0, 3) as $ing) {
                        echo "         - " . $ing['name'] . ": " . $ing['amount'] . " " . ($ing['unit'] ?? '') . "\n";
                    }
                    if (count($r['ingredients']) > 3) echo "         - ...dan lainnya\n";
                }
            }
        } else {
            echo "   (Tidak ada resep yang spesifik ditemukan)\n";
        }
    } else {
        echo "ERROR:\n" . $response->getContent() . "\n";
    }
    echo "============================================\n\n";
}
