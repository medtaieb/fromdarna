<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use App\Models\Order;
use App\Models\Store;
use App\Models\Product;
use App\Models\OrderItem;
use App\Models\User;

$store = Store::first();
$order = Order::where('store_id', $store->id)->first();

if (!$order) {
    $cust = User::where('role', 'customer')->first() ?? User::first();
    $prod = Product::first();
    $order = Order::create([
        'ref' => Order::generateRef(),
        'user_id' => $cust->id,
        'store_id' => $store->id,
        'zone_id' => $store->zone_id,
        'status' => 'PLACED',
        'payment_method' => 'cash',
        'payment_status' => 'pending',
        'subtotal_millimes' => $prod->price_millimes,
        'delivery_fee_millimes' => 3000,
        'total_millimes' => $prod->price_millimes + 3000,
        'delivery_landmark' => 'Porte test',
        'delivery_city' => 'Grombalia',
        'delivery_phone' => '+21620123456',
        'customer_note' => 'Sans harissa svp',
    ]);
    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $prod->id,
        'product_name' => $prod->name,
        'unit_price_millimes' => $prod->price_millimes,
        'quantity' => 2,
        'line_total_millimes' => $prod->price_millimes * 2,
    ]);
}

echo "=== VÉRIFICATION DU PORTAIL PARTENAIRE (ÉCRANS & ACTIONS) ===\n";

$routes = [
    'GET /partenaire'               => ['GET', '/partenaire'],
    'GET /partenaire/commandes'     => ['GET', '/partenaire/commandes'],
    'GET /partenaire/commandes/' . $order->id => ['GET', '/partenaire/commandes/' . $order->id],
    'GET /partenaire/releves'       => ['GET', '/partenaire/releves'],
    'GET /partenaire/api/live-orders' => ['GET', '/partenaire/api/live-orders'],
    'GET /partenaire/menu'          => ['GET', '/partenaire/menu'],
];

foreach ($routes as $label => [$method, $url]) {
    $req = Illuminate\Http\Request::create($url, $method);
    $resp = $kernel->handle($req);
    $status = $resp->getStatusCode();
    $ok = ($status === 200 || $status === 302) ? '✅' : '❌';
    echo "{$ok} [{$status}] {$label}\n";
    if ($status === 500) {
        echo "   Erreur: " . substr($resp->getContent(), 0, 300) . "\n";
    }
}

// Test de l'action Accepter
echo "\n=== TEST ACTION ACCEPTER COMMANDE ===\n";
$req = Illuminate\Http\Request::create('/partenaire/commandes/' . $order->id . '/accept', 'POST', [
    'prep_minutes' => 20
]);
$resp = $kernel->handle($req);
$order->refresh();
echo ($order->status === 'ACCEPTED' ? '✅' : '❌') . " Commande #{$order->ref} statut: {$order->status} (Prête prévue: " . ($order->ready_at ? $order->ready_at->format('H:i') : 'null') . ")\n";

// Test de l'action Prête
echo "\n=== TEST ACTION MARQUER PRÊTE ===\n";
$req = Illuminate\Http\Request::create('/partenaire/commandes/' . $order->id . '/ready', 'POST');
$resp = $kernel->handle($req);
$order->refresh();
echo ($order->status === 'READY' ? '✅' : '❌') . " Commande #{$order->ref} statut: {$order->status}\n";

// Test de l'API live-orders
echo "\n=== TEST API LIVE-ORDERS ===\n";
$req = Illuminate\Http\Request::create('/partenaire/api/live-orders', 'GET');
$resp = $kernel->handle($req);
echo "   Réponse: " . $resp->getContent() . "\n";
