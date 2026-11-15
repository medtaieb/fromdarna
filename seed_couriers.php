<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\CourierLocation;
use Illuminate\Support\Facades\Hash;

$couriers = [
    [
        'name' => 'Walid T.',
        'phone' => '+21621000002',
        'lat' => 36.6185,
        'lng' => 10.5020,
        'battery' => 84,
    ],
    [
        'name' => 'Marwen A.',
        'phone' => '+21621000010',
        'lat' => 36.6230,
        'lng' => 10.4980,
        'battery' => 92,
    ],
    [
        'name' => 'Oussama R.',
        'phone' => '+21621000011',
        'lat' => 36.6150,
        'lng' => 10.5060,
        'battery' => 65,
    ],
];

foreach ($couriers as $c) {
    $user = User::firstOrCreate(
        ['phone' => $c['phone']],
        [
            'name' => $c['name'],
            'role' => 'courier',
            'status' => 'active',
            'password' => Hash::make('password'),
        ]
    );

    CourierLocation::updateOrCreate(
        ['user_id' => $user->id],
        [
            'latitude' => $c['lat'],
            'longitude' => $c['lng'],
            'heading' => 45.0,
            'speed' => 18.5,
            'battery' => $c['battery'],
            'is_online' => true,
            'last_ping_at' => now(),
        ]
    );
    echo "Courier {$user->name} prêt avec localisation ({$c['lat']}, {$c['lng']})\n";
}
