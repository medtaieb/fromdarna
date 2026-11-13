<?php

namespace App\Support;

/**
 * Données d'exemple (noms fictifs). À remplacer par vos modèles Eloquent.
 * Montants en millimes.
 */
class Sample
{
    const STATUS = [
        'PLACED' => ['Reçue', 'o'], 'ACCEPTED' => ['En préparation', 'x'], 'READY' => ['Prête', 'g'],
        'PICKED_UP' => ['En route', 'g'], 'DELIVERED' => ['Livrée', 'g'],
        'CANCELLED' => ['Annulée', 'r'], 'REJECTED' => ['Refusée', 'r'], 'FAILED' => ['Échec', 'r'],
    ];

    /** "g:Livrée" : syntaxe lue par <x-table> */
    public static function badge(string $status): string
    {
        [$label, $type] = self::STATUS[$status];
        return "$type:$label";
    }

    public static function stores(): array
    {
        return [
            ['id' => 1, 'name' => 'Dar Zitouna', 'kind' => 'Cuisine tunisienne', 'emoji' => '🍲', 'eta' => 35, 'open' => true, 'min' => 10000, 'c1' => '#98551C', 'c2' => '#B08861'],
            ['id' => 2, 'name' => 'Sultan Grill', 'kind' => 'Grillades et sandwichs', 'emoji' => '🥙', 'eta' => 30, 'open' => true, 'min' => 10000, 'c1' => '#7A4214', 'c2' => '#C41317'],
            ['id' => 3, 'name' => 'Pâtisserie El Warda', 'kind' => 'Pâtisseries et gâteaux', 'emoji' => '🥐', 'eta' => 25, 'open' => true, 'min' => 8000, 'c1' => '#B08861', 'c2' => '#D9B77A'],
            ['id' => 4, 'name' => 'Café du Vignoble', 'kind' => 'Café et petit-déjeuner', 'emoji' => '☕', 'eta' => 20, 'open' => false, 'min' => 6000, 'c1' => '#363C14', 'c2' => '#6E7A2C'],
        ];
    }

    public static function menu(int $store = 1): array
    {
        return [
            'Plats' => [
                ['id' => 11, 'name' => 'Couscous à l’agneau', 'desc' => 'Semoule fine, légumes du jour, harissa maison.', 'price' => 18000, 'ok' => true],
                ['id' => 12, 'name' => 'Ojja merguez', 'desc' => 'Œufs, merguez, sauce tomate pimentée.', 'price' => 12000, 'ok' => true],
                ['id' => 13, 'name' => 'Lablabi', 'desc' => 'Pois chiches, pain, œuf, thon, câpres.', 'price' => 6500, 'ok' => false],
            ],
            'Entrées et boissons' => [
                ['id' => 21, 'name' => 'Brik à l’œuf', 'desc' => 'Feuille croustillante, œuf, thon, persil.', 'price' => 3500, 'ok' => true],
                ['id' => 22, 'name' => 'Thé à la menthe', 'desc' => 'Avec pignons sur demande.', 'price' => 2500, 'ok' => true],
                ['id' => 23, 'name' => 'Eau minérale 50 cl', 'desc' => '', 'price' => 1000, 'ok' => true],
            ],
        ];
    }

    public static function orders(): array
    {
        return [
            ['id' => '#FD-1043', 'time' => '12:44', 'customer' => 'Sana M.', 'store' => 'Dar Zitouna', 'status' => 'PLACED', 'total' => 24500, 'courier' => '—'],
            ['id' => '#FD-1042', 'time' => '12:41', 'customer' => 'Amine B.', 'store' => 'Sultan Grill', 'status' => 'ACCEPTED', 'total' => 18000, 'courier' => '—'],
            ['id' => '#FD-1041', 'time' => '12:33', 'customer' => 'Rim K.', 'store' => 'Dar Zitouna', 'status' => 'READY', 'total' => 31000, 'courier' => 'Walid T.'],
            ['id' => '#FD-1040', 'time' => '12:20', 'customer' => 'Hatem S.', 'store' => 'Pâtisserie El Warda', 'status' => 'PICKED_UP', 'total' => 15500, 'courier' => 'Walid T.'],
            ['id' => '#FD-1039', 'time' => '12:02', 'customer' => 'Ines L.', 'store' => 'Dar Zitouna', 'status' => 'DELIVERED', 'total' => 28000, 'courier' => 'Marwen A.'],
            ['id' => '#FD-1038', 'time' => '11:48', 'customer' => 'Nader Z.', 'store' => 'Sultan Grill', 'status' => 'CANCELLED', 'total' => 12000, 'courier' => '—'],
        ];
    }

    public static function couriers(): array
    {
        return [
            ['name' => 'Walid T.', 'vehicle' => 'Scooter', 'status' => 'g:En course', 'cash' => 46500, 'limit' => 150000, 'active' => 2],
            ['name' => 'Marwen A.', 'vehicle' => 'Moto', 'status' => 'g:Disponible', 'cash' => 92000, 'limit' => 150000, 'active' => 0],
            ['name' => 'Oussama R.', 'vehicle' => 'Scooter', 'status' => 'x:Hors service', 'cash' => 0, 'limit' => 150000, 'active' => 0],
            ['name' => 'Fares D.', 'vehicle' => 'Vélo', 'status' => 'o:Dossier à valider', 'cash' => 0, 'limit' => 150000, 'active' => 0],
        ];
    }
}
