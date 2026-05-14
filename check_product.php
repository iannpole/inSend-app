<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$p = App\Models\Product::first();
echo "id: " . $p->id . "\n";
echo "id type: " . gettype($p->id) . "\n";
echo "_id: ";
var_dump($p->_id);
echo "edit URL: " . route('admin.products.edit', $p->id) . "\n";
echo "destroy URL: " . route('admin.products.destroy', $p->id) . "\n";
echo "update URL: " . route('admin.products.update', $p->id) . "\n";
