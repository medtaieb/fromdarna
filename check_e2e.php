<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Http\Kernel::class)->bootstrap();
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use Illuminate\Support\Facades\DB;
use App\Models\Zone;
use App\Models\Store;
use App\Models\Category;
use App\Models\Product;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;

$ok = 0; $fail = 0;

function test($label, $result, $expected = true) {
    global $ok, $fail;
    if ($result === $expected) {
        echo "  ✅ {$label}\n";
        $ok++;
    } else {
        echo "  ❌ {$label} (got: " . json_encode($result) . ", expected: " . json_encode($expected) . ")\n";
        $fail++;
    }
}

function req($kernel, $url, $method = 'GET') {
    $r = Illuminate\Http\Request::create($url, $method);
    try {
        $resp = $kernel->handle($r);
        return $resp->getStatusCode();
    } catch (\Throwable $e) {
        return "ERR: " . $e->getMessage();
    }
}

// ════════════════════════════════════════════════
echo "\n=== ÉTAPE 2 : BASE DE DONNÉES ═══════════════\n";

test("4 zones en BDD", Zone::count(), 4);
test("10 stores en BDD", Store::count(), 10);
test("12 catégories en BDD", Category::count(), 12);
test("31 produits en BDD", Product::count(), 31);

$zones = Zone::all()->pluck('slug')->toArray();
test("Zone grombalia existe", in_array('grombalia', $zones), true);
test("Zone soliman existe", in_array('soliman', $zones), true);
test("Zone beni-khalled existe", in_array('beni-khalled', $zones), true);
test("Zone bou-argoub existe", in_array('bou-argoub', $zones), true);

$grombalia = Zone::where('slug', 'grombalia')->first();
test("Grombalia a 4 restaurants", Store::where('zone_id', $grombalia->id)->count(), 4);
$soliman = Zone::where('slug', 'soliman')->first();
test("Soliman a 2 restaurants", Store::where('zone_id', $soliman->id)->count(), 2);

// Vérifier relations Eloquent
$store = Store::with('zone', 'categories.products')->first();
test("Store->zone fonctionne", $store->zone !== null, true);
test("Store->categories fonctionne", $store->categories->count() > 0, true);
test("Category->products fonctionne", $store->categories->first()->products->count() > 0, true);
test("Product a price_millimes > 0", $store->categories->first()->products->first()->price_millimes > 0, true);

// Accesseurs Store
test("Store accessor 'open'", is_bool($store->open), true);
test("Store accessor 'eta'", is_int($store->eta), true);
test("Store accessor 'min'", is_int($store->min), true);

echo "\n=== ÉTAPE 2 : ROUTES HTTP ══════════════════\n";

test("GET /restaurants → 200", req($kernel, '/restaurants'), 200);
test("GET /restaurants?zone=grombalia → 200", req($kernel, '/restaurants?zone=grombalia'), 200);
test("GET /restaurants?zone=soliman → 200", req($kernel, '/restaurants?zone=soliman'), 200);
test("GET /restaurants?zone=beni-khalled → 200", req($kernel, '/restaurants?zone=beni-khalled'), 200);
test("GET /restaurants?zone=bou-argoub → 200", req($kernel, '/restaurants?zone=bou-argoub'), 200);
test("GET /restaurants?category=restaurants → 200", req($kernel, '/restaurants?category=restaurants'), 200);
test("GET /restaurants?category=patisseries → 200", req($kernel, '/restaurants?category=patisseries'), 200);
test("GET /restaurants?category=cafes → 200", req($kernel, '/restaurants?category=cafes'), 200);
test("GET /restaurants?zone=grombalia&category=restaurants → 200", req($kernel, '/restaurants?zone=grombalia&category=restaurants'), 200);
test("GET /restaurants/1 → 200", req($kernel, '/restaurants/1'), 200);
test("GET /restaurants/2 → 200", req($kernel, '/restaurants/2'), 200);
test("GET /restaurants/5 → 200 (Soliman)", req($kernel, '/restaurants/5'), 200);
test("GET /restaurants/99 → 200 (fallback)", req($kernel, '/restaurants/99'), 200);

echo "\n=== ÉTAPE 2 : PARTENAIRE ════════════════════\n";
test("GET /partenaire → 302 (auth required)", req($kernel, '/partenaire'), 302);

echo "\n=== ÉTAPE 3 : BASE DE DONNÉES ══════════════\n";

$tables = DB::select('SHOW TABLES');
$tableNames = array_map(fn($t) => array_values((array)$t)[0], $tables);
test("Table cart_items existe", in_array('cart_items', $tableNames), true);
test("Table orders existe", in_array('orders', $tableNames), true);
test("Table order_items existe", in_array('order_items', $tableNames), true);

// Modèle Order
$ref = Order::generateRef();
test("Order::generateRef() génère une ref", str_starts_with($ref, 'FD-'), true);
test("Order STATUS_LABELS complet", count(Order::STATUS_LABELS), 8);

// CartItem
$cols = DB::select('SHOW COLUMNS FROM cart_items');
$colNames = array_map(fn($c) => $c->Field, $cols);
test("cart_items.session_id existe", in_array('session_id', $colNames), true);
test("cart_items.product_id existe", in_array('product_id', $colNames), true);
test("cart_items.quantity existe", in_array('quantity', $colNames), true);
test("cart_items.options_json existe", in_array('options_json', $colNames), true);

$ocols = DB::select('SHOW COLUMNS FROM orders');
$oNames = array_map(fn($c) => $c->Field, $ocols);
test("orders.ref existe", in_array('ref', $oNames), true);
test("orders.status existe", in_array('status', $oNames), true);
test("orders.total_millimes existe", in_array('total_millimes', $oNames), true);
test("orders.delivery_code existe", in_array('delivery_code', $oNames), true);
test("orders.idempotency_key existe", in_array('idempotency_key', $oNames), true);

$icols = DB::select('SHOW COLUMNS FROM order_items');
$iNames = array_map(fn($c) => $c->Field, $icols);
test("order_items.product_name existe (snapshot)", in_array('product_name', $iNames), true);
test("order_items.unit_price_millimes existe", in_array('unit_price_millimes', $iNames), true);
test("order_items.line_total_millimes existe", in_array('line_total_millimes', $iNames), true);

echo "\n=== ÉTAPE 3 : ROUTES HTTP ════════════════════\n";

test("GET /panier → 200", req($kernel, '/panier'), 200);
test("GET /commande → 302 (panier vide = redirect)", req($kernel, '/commande'), 302);
test("GET /mes-commandes → 200", req($kernel, '/mes-commandes'), 200);

echo "\n=== ÉTAPE 3 : FORMAT MONÉTAIRE ══════════════\n";
test("Money::tnd(18000) = '18,000 TND'", \App\Support\Money::tnd(18000), '18,000 TND');
test("Money::tnd(3500) = '3,500 TND'", \App\Support\Money::tnd(3500), '3,500 TND');
test("Money::tnd(1000) = '1,000 TND'", \App\Support\Money::tnd(1000), '1,000 TND');
test("Money::tnd(0) = '0,000 TND'", \App\Support\Money::tnd(0), '0,000 TND');

echo "\n══════════════════════════════════════════════\n";
echo "  ✅ {$ok} tests réussis\n";
if ($fail > 0) echo "  ❌ {$fail} tests échoués\n";
echo "══════════════════════════════════════════════\n";
