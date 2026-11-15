<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Client démo (Sana M.)
        $customer = User::updateOrCreate(
            ['phone' => '+21620123456'],
            [
                'name' => 'Sana M.',
                'email' => 'client@fromdarna.tn',
                'role' => 'customer',
                'status' => 'active',
                'language' => 'fr',
                'notify_orders' => true,
                'notify_marketing' => false,
                'otp_code' => '123456',
                'otp_expires_at' => now()->addDay(),
            ]
        );

        Address::updateOrCreate(
            ['user_id' => $customer->id, 'label' => 'Maison'],
            [
                'city' => 'Grombalia',
                'street_address' => 'Avenue Habib Bourguiba',
                'landmark' => 'En face de la pharmacie centrale, portail vert',
                'building' => 'Résidence Les Orangers',
                'floor' => '2ème',
                'door' => 'Appt 4',
                'latitude' => 36.6191,
                'longitude' => 10.5012,
                'contact_phone' => '+21620123456',
                'is_default' => true,
            ]
        );

        // 2. Partenaire démo (Dar Zitouna)
        User::updateOrCreate(
            ['email' => 'partenaire@fromdarna.tn'],
            [
                'name' => 'Dar Zitouna',
                'phone' => '+21621000001',
                'password' => Hash::make('password'),
                'role' => 'partner_owner',
                'status' => 'active',
            ]
        );

        // 3. Livreur démo (Walid T.)
        User::updateOrCreate(
            ['email' => 'livreur@fromdarna.tn'],
            [
                'name' => 'Walid T.',
                'phone' => '+21621000002',
                'password' => Hash::make('password'),
                'role' => 'courier',
                'status' => 'active',
            ]
        );

        // 4. Ops démo
        User::updateOrCreate(
            ['email' => 'ops@fromdarna.tn'],
            [
                'name' => 'Amine (Ops Grombalia)',
                'phone' => '+21621000003',
                'password' => Hash::make('password'),
                'role' => 'ops',
                'status' => 'active',
            ]
        );
    }
}
