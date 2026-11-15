<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Zone;
use App\Models\Store;
use App\Models\Category;
use App\Models\Product;

echo "=== ZONES ===\n";
foreach (Zone::all() as $z) {
    $count = Store::where('zone_id', $z->id)->count();
    echo "  [{$z->id}] {$z->name} (slug: {$z->slug}) — {$count} store(s)\n";
}

echo "\n=== STORES (10 premiers) ===\n";
foreach (Store::with('zone')->orderByDesc('is_open')->get() as $s) {
    $zone = $s->zone ? $s->zone->name : 'SANS ZONE';
    echo "  [{$s->id}] {$s->name} | {$s->kind} | zone: {$zone} | " . ($s->is_open ? 'OUVERT' : 'FERMÉ') . "\n";
}

echo "\n=== CATEGORIES & PRODUITS ===\n";
foreach (Category::with('products')->get() as $cat) {
    echo "  [{$cat->id}] {$cat->name} (store #{$cat->store_id}) — {$cat->products->count()} produit(s)\n";
    foreach ($cat->products as $p) {
        $prix = number_format($p->price_millimes / 1000, 3, ',', ' ') . ' TND';
        echo "       └ {$p->name} · {$prix} · " . ($p->is_available ? '✓' : '✗ indisponible') . "\n";
    }
}

echo "\n=== product_options ===\n";
$total = \Illuminate\Support\Facades\DB::table('product_options')->count();
echo "  {$total} option(s) enregistrée(s)\n";
