<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$products = App\Models\Product::take(5)->get();
foreach($products as $p) {
    echo "ID: " . $p->_id . "\n";
    echo "Type: " . gettype($p->_id) . "\n";
    if (is_object($p->_id)) {
        echo "Class: " . get_class($p->_id) . "\n";
    }
    echo "------------------\n";
}
