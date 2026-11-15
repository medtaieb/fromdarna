<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
foreach(User::all() as $u) {
    echo $u->id . ' - ' . $u->name . ' (' . $u->phone . ') role:' . ($u->role ?? 'none') . "\n";
}
