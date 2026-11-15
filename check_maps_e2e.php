<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use App\Models\Order;
use App\Models\CourierLocation;
use App\Models\User;

$tests = [
    'GET /ops/dispatch'                => ['GET', '/ops/dispatch'],
    'GET /ops/dispatch/live-data'      => ['GET', '/ops/dispatch/live-data'],
    'GET /livreur'                     => ['GET', '/livreur'],
    'GET /livreur/courses/1'           => ['GET', '/livreur/courses/1'],
    'GET /mes-commandes/1'             => ['GET', '/mes-commandes/1'],
    'GET /commandes/1/live-tracking'   => ['GET', '/commandes/1/live-tracking'],
];

echo "=== VÉRIFICATION DES ROUTES MAPS & TRACKING ===\n";
foreach ($tests as $label => [$method, $url]) {
    $req = Illuminate\Http\Request::create($url, $method);
    $resp = $kernel->handle($req);
    $status = $resp->getStatusCode();
    $ok = in_array($status, [200, 302]) ? '✅' : '❌';
    echo "{$ok} [{$status}] {$label}\n";
    if ($status === 500) {
        echo "   Contenu erreur: " . substr($resp->getContent(), 0, 300) . "\n";
    }
}

// Test du ping GPS livreur
echo "\n=== TEST PING GPS LIVREUR ===\n";
$courier = User::where('role', 'courier')->first();
$req = Illuminate\Http\Request::create('/livreur/location/ping', 'POST', [
    'latitude' => 36.6200,
    'longitude' => 10.5030,
    'heading' => 90,
    'speed' => 25.4,
    'battery' => 78,
]);
$resp = $kernel->handle($req);
echo ($resp->getStatusCode() === 200 ? '✅' : '❌') . " [{$resp->getStatusCode()}] POST /livreur/location/ping\n";
echo "   Réponse: " . $resp->getContent() . "\n";

// Test des données renvoyées par live-data
echo "\n=== TEST DONNÉES DISPATCH LIVE-DATA ===\n";
$req = Illuminate\Http\Request::create('/ops/dispatch/live-data', 'GET');
$resp = $kernel->handle($req);
$data = json_decode($resp->getContent(), true);
echo "   Livreurs reçus: " . count($data['couriers'] ?? []) . "\n";
echo "   Commandes actives reçues: " . count($data['orders'] ?? []) . "\n";
foreach ($data['couriers'] as $c) {
    echo "   - 🛵 {$c['name']} @ ({$c['lat']}, {$c['lng']}) - Batterie: {$c['battery']}%\n";
}
foreach ($data['orders'] as $o) {
    echo "   - 📦 #{$o['ref']} : {$o['store']['name']} -> {$o['customer']['landmark']} (Temps: {$o['elapsed_minutes']} min)\n";
}
