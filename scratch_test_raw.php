<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$collection = \Illuminate\Support\Facades\DB::connection('mongodb')->collection('products');
$docs = $collection->take(2)->get();
foreach($docs as $doc) {
    echo json_encode($doc, JSON_PRETTY_PRINT) . "\n";
    echo "------------------\n";
}
