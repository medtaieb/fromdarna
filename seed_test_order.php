<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Store;
use App\Models\User;
use App\Models\Product;

if (Order::count() === 0) {
    $store = Store::first();
    $customer = User::where('role', 'customer')->first() ?? User::first();
    $courier = User::where('role', 'courier')->first();
    $product = Product::first();

    $order = Order::create([
        'ref' => Order::generateRef(),
        'user_id' => $customer->id,
        'store_id' => $store->id,
        'courier_id' => $courier?->id,
        'zone_id' => $store->zone_id,
        'status' => 'PICKED_UP',
        'payment_method' => 'cash',
        'payment_status' => 'pending',
        'subtotal_millimes' => $product->price_millimes,
        'delivery_fee_millimes' => 3000,
        'total_millimes' => $product->price_millimes + 3000,
        'delivery_landmark' => 'En face de la pharmacie centrale',
        'delivery_city' => 'Grombalia',
        'delivery_building' => 'Bâtiment B',
        'delivery_floor' => '2ème étage',
        'delivery_door' => 'Appartement 4',
        'delivery_phone' => '+21620123456',
        'delivery_lat' => 36.6215,
        'delivery_lng' => 10.5040,
        'delivery_code' => '4821',
        'customer_note' => 'Sans harissa s.v.p.',
        'accepted_at' => now()->subMinutes(15),
        'ready_at' => now()->subMinutes(8),
        'picked_up_at' => now()->subMinutes(3),
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'unit_price_millimes' => $product->price_millimes,
        'quantity' => 1,
        'line_total_millimes' => $product->price_millimes,
    ]);

    echo "Commande test créée: #{$order->ref} (ID: {$order->id})\n";
} else {
    $order = Order::first();
    echo "Commande existante trouvée: #{$order->ref} (ID: {$order->id})\n";
}
