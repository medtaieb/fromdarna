<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$tests = [
    '/restaurants'               => 'Feed toutes zones',
    '/restaurants?zone=grombalia' => 'Feed zone Grombalia',
    '/restaurants?zone=soliman'  => 'Feed zone Soliman',
    '/restaurants?zone=beni-khalled' => 'Feed zone Béni Khalled',
    '/restaurants?zone=bou-argoub'   => 'Feed zone Bou Argoub',
    '/restaurants?category=restaurants' => 'Feed catégorie restaurants',
    '/restaurants?zone=grombalia&category=restaurants' => 'Feed zone+catégorie',
    '/restaurants/1'  => 'Store #1 (Dar Zitouna)',
    '/restaurants/5'  => 'Store #5 (Restaurant de la Plage)',
    '/restaurants/99' => 'Store #99 (inexistant → fallback)',
];

foreach ($tests as $url => $label) {
    $request = Illuminate\Http\Request::create($url, 'GET');
    try {
        $response = $kernel->handle($request);
        $status = $response->getStatusCode();
        $ok = $status === 200 ? '✅' : '❌';
        echo "{$ok} [{$status}] {$label}\n       → {$url}\n";
    } catch (\Throwable $e) {
        echo "💥 ERREUR {$label}\n       {$e->getMessage()}\n";
    }
}

echo "\n=== Vérification données par zone ===\n";
foreach (\App\Models\Zone::all() as $zone) {
    $count = \App\Models\Store::where('zone_id', $zone->id)->count();
    echo "  {$zone->name} ({$zone->slug}) : {$count} restaurant(s)\n";
}
