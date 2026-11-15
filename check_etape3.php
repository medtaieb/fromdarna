<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$tests = [
    'GET  /panier'           => ['GET', '/panier'],
    'GET  /commande'         => ['GET', '/commande'],
    'GET  /mes-commandes'    => ['GET', '/mes-commandes'],
    'GET  /restaurants'      => ['GET', '/restaurants'],
    'GET  /restaurants/1'    => ['GET', '/restaurants/1'],
];

echo "=== TESTS HTTP ===\n";
foreach ($tests as $label => [$method, $url]) {
    $request = Illuminate\Http\Request::create($url, $method);
    try {
        $response = $kernel->handle($request);
        $status = $response->getStatusCode();
        $ok = in_array($status, [200, 302]) ? '✅' : '❌';
        echo "{$ok} [{$status}] {$label}\n";
    } catch (\Throwable $e) {
        echo "💥 ERREUR {$label}\n       " . $e->getMessage() . "\n";
    }
}

echo "\n=== TABLES CREEES ===\n";
use Illuminate\Support\Facades\DB;
$tables = DB::select('SHOW TABLES');
foreach ($tables as $t) echo "  - " . array_values((array)$t)[0] . "\n";
