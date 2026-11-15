<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "=== TABLES ===\n";
$tables = DB::select('SHOW TABLES');
foreach ($tables as $t) {
    $name = array_values((array)$t)[0];
    echo "  - $name\n";
}

echo "\n=== stores columns ===\n";
$cols = DB::select('SHOW COLUMNS FROM stores');
foreach ($cols as $c) {
    echo "  {$c->Field} ({$c->Type}) {$c->Null} {$c->Default}\n";
}

echo "\n=== categories columns ===\n";
$cols = DB::select('SHOW COLUMNS FROM categories');
foreach ($cols as $c) {
    echo "  {$c->Field} ({$c->Type}) {$c->Null} {$c->Default}\n";
}

echo "\n=== products columns ===\n";
$cols = DB::select('SHOW COLUMNS FROM products');
foreach ($cols as $c) {
    echo "  {$c->Field} ({$c->Type}) {$c->Null} {$c->Default}\n";
}

echo "\n=== product_options? ===\n";
try {
    $cols = DB::select('SHOW COLUMNS FROM product_options');
    foreach ($cols as $c) {
        echo "  {$c->Field} ({$c->Type}) {$c->Null} {$c->Default}\n";
    }
} catch (\Exception $e) {
    echo "  TABLE DOES NOT EXIST\n";
}

echo "\n=== COUNTS ===\n";
echo "  zones: " . DB::table('zones')->count() . "\n";
echo "  stores: " . DB::table('stores')->count() . "\n";
echo "  categories: " . DB::table('categories')->count() . "\n";
echo "  products: " . DB::table('products')->count() . "\n";
try {
    echo "  product_options: " . DB::table('product_options')->count() . "\n";
} catch (\Exception $e) {
    echo "  product_options: TABLE MISSING\n";
}
