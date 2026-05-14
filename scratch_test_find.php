<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$id = "66421a1b8f1b2c8b1f8e0003";

$p1 = App\Models\Product::where('_id', $id)->first();
echo "Find string: " . ($p1 ? "Found" : "Not Found") . "\n";

$p2 = App\Models\Product::where('_id', new \MongoDB\BSON\ObjectId($id))->first();
echo "Find ObjectId: " . ($p2 ? "Found" : "Not Found") . "\n";

// Try raw query
$collection = \Illuminate\Support\Facades\DB::connection('mongodb')->collection('products');
$p3 = $collection->where('_id', $id)->first();
echo "Raw Find string: " . ($p3 ? "Found" : "Not Found") . "\n";

$p4 = $collection->where('_id', new \MongoDB\BSON\ObjectId($id))->first();
echo "Raw Find ObjectId: " . ($p4 ? "Found" : "Not Found") . "\n";
