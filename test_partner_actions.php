<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use App\Models\Order;

$order = Order::first();
echo "Order status before: " . $order->status . "\n";

// Créons une commande de test au statut PLACED
$order->update(['status' => 'PLACED', 'accepted_at' => null, 'ready_at' => null]);
echo "Order reset to PLACED: " . $order->status . "\n";

// Appel direct au contrôleur pour tester la logique
$controller = new \App\Http\Controllers\PartnerController();
$req = new \Illuminate\Http\Request(['prep_minutes' => 20]);
$res = $controller->acceptOrder($req, $order);
$order->refresh();
echo ($order->status === 'ACCEPTED' ? '✅' : '❌') . " Après acceptOrder -> Statut: " . $order->status . " (Prête à: " . ($order->ready_at ? $order->ready_at->format('H:i') : '') . ")\n";

$res = $controller->readyOrder(new \Illuminate\Http\Request(), $order);
$order->refresh();
echo ($order->status === 'READY' ? '✅' : '❌') . " Après readyOrder -> Statut: " . $order->status . "\n";

$res = $controller->rejectOrder(new \Illuminate\Http\Request(['reason' => 'Produit en rupture']), $order);
$order->refresh();
echo ($order->status === 'REJECTED' ? '✅' : '❌') . " Après rejectOrder -> Statut: " . $order->status . " (Motif: " . $order->rejection_reason . ")\n";

// Remettons la commande au statut READY pour le livreur
$order->update(['status' => 'READY']);
echo "Order restored to READY.\n";
