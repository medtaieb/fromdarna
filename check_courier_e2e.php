<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use App\Models\User;
use App\Models\Order;
use App\Models\CourierCashDrop;
use App\Models\OrderIncident;

$courier = User::where('role', 'courier')->first();

echo "=== VÉRIFICATION DES ROUTES LIVREUR & CASH ===\n";

$routes = [
    'GET /livreur'              => ['GET', '/livreur'],
    'GET /livreur/courses'      => ['GET', '/livreur/courses'],
    'GET /livreur/cash-et-gains'=> ['GET', '/livreur/cash-et-gains'],
    'GET /livreur/remise-cash'  => ['GET', '/livreur/remise-cash'],
    'GET /livreur/incident'     => ['GET', '/livreur/incident'],
];

foreach ($routes as $label => [$method, $url]) {
    $req = Illuminate\Http\Request::create($url, $method);
    $resp = $kernel->handle($req);
    $status = $resp->getStatusCode();
    $ok = in_array($status, [200, 302]) ? '✅' : '❌';
    echo "{$ok} [{$status}] {$label}\n";
    if ($status === 500) {
        echo "   Erreur: " . substr($resp->getContent(), 0, 300) . "\n";
    }
}

// Test du calcul de cash
echo "\n=== TEST CALCUL CASH ET PLAFOND 150 TND ===\n";
$heldCash = $courier->getHeldCashMillimes();
echo "   Cash détenu actuel: " . \App\Support\Money::tnd($heldCash) . "\n";
echo "   Plafond dépassé (>= 150 TND): " . ($courier->isCashLimitExceeded() ? 'OUI (BLOQUÉ)' : 'NON (AUTORISÉ)') . "\n";

// Test de remise de cash
echo "\n=== TEST REMISE DE CASH (DROP) ===\n";
$drop = CourierCashDrop::create([
    'user_id' => $courier->id,
    'amount_millimes' => 10000,
    'status' => 'validated',
    'reference' => CourierCashDrop::generateRef(),
    'notes' => 'Test remise cash',
    'validated_at' => now(),
]);
echo "✅ Reçu de remise généré: #{$drop->reference} pour " . \App\Support\Money::tnd($drop->amount_millimes) . "\n";

// Test de signalement d'incident
echo "\n=== TEST SIGNALEMENT D'INCIDENT ===\n";
$incident = OrderIncident::create([
    'courier_id' => $courier->id,
    'type' => 'Client absent / Injoignable',
    'description' => 'Test signalement incident automatique',
    'status' => 'open',
]);
echo "✅ Incident enregistré: #{$incident->id} - Type: {$incident->type}\n";
