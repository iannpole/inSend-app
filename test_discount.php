<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$p = App\Models\Product::first();
echo "Before: " . json_encode($p->discount_info) . "\n";

$p->update([
    'discount_info' => [
        'is_active' => true,
        'percentage' => 20,
        'fixed_amount' => 3000,
        'start_date' => null,
        'end_date' => null,
        'campaign_name' => 'Test'
    ],
    'sale_price' => 12000
]);

$p2 = App\Models\Product::first();
echo "After: " . json_encode($p2->discount_info) . "\n";
echo "Sale Price: " . $p2->sale_price . "\n";
