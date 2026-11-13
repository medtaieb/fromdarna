<?php

namespace App\Support;

/** Données d'exemple du prototype (aucune base de données requise). Montants en millimes. */
class Demo
{
    public static function tnd(int $m): string
    {
        return number_format($m / 1000, 3, ',', ' ') . ' TND';
    }

    public static function stores(): array
    {
        $i = fn ($id, $n, $ar, $p, $c) => ['id' => $id, 'name' => $n, 'ar' => $ar, 'price' => $p, 'cat' => $c];
        return [
            1 => ['id' => 1, 'name' => 'Dar Zitouna', 'cuisine' => 'Cuisine tunisienne maison', 'eta' => '≈ 30 min', 'open' => true, 'c1' => '#98551C', 'c2' => '#B08861', 'items' => [
                $i('brik', 'Brik à l’œuf', 'بريك بالبيض', 2500, 'Entrées'), $i('lablabi', 'Lablabi', 'لبلابي', 5500, 'Entrées'),
                $i('couscous', 'Couscous à l’agneau', 'كسكسي بالعلوش', 14000, 'Plats'), $i('ojja', 'Ojja merguez', 'عجّة مرقاز', 9000, 'Plats'),
                $i('makrouda', 'Makrouda', 'مقروض', 1200, 'Douceurs')]],
            2 => ['id' => 2, 'name' => 'Chez Mama Fatma', 'cuisine' => 'Couscous et plats mijotés', 'eta' => '≈ 35 min', 'open' => true, 'c1' => '#363C14', 'c2' => '#6B7A2A', 'items' => [
                $i('mloukhia', 'Mloukhia', 'ملوخية', 13000, 'Plats'), $i('kamounia', 'Kammounia', 'كمونية', 12000, 'Plats'),
                $i('mechouia', 'Salade méchouia', 'سلاطة مشوية', 4000, 'Entrées')]],
            3 => ['id' => 3, 'name' => 'Pâtisserie El Karam', 'cuisine' => 'Makrouda, baklawa', 'eta' => '≈ 25 min', 'open' => false, 'c1' => '#C41317', 'c2' => '#98551C', 'items' => [
                $i('baklawa', 'Baklawa (6 pièces)', 'بقلاوة', 9000, 'Douceurs'), $i('assida', 'Assida zgougou', 'عصيدة الزقوقو', 4500, 'Douceurs')]],
            4 => ['id' => 4, 'name' => 'Pizzeria Le Vigneron', 'cuisine' => 'Pizzas au feu de bois', 'eta' => '≈ 40 min', 'open' => true, 'c1' => '#1D1916', 'c2' => '#98551C', 'items' => [
                $i('reine', 'Pizza reine', 'بيتزا ريجينا', 11000, 'Pizzas'), $i('thon', 'Pizza thon', 'بيتزا تونة', 10000, 'Pizzas')]],
        ];
    }

    public static function store(int $id): array
    {
        return self::stores()[$id] ?? abort(404);
    }

    public static function partnerOrders(): array
    {
        return [
            ['id' => 'FD-1042', 'col' => 'new', 'at' => '12:41', 'items' => '2 × Brik à l’œuf · 1 × Couscous à l’agneau', 'total' => 19000],
            ['id' => 'FD-1043', 'col' => 'new', 'at' => '12:43', 'items' => '1 × Lablabi · 2 × Makrouda', 'total' => 7900],
            ['id' => 'FD-1040', 'col' => 'prep', 'at' => '12:36', 'items' => '3 × Brik à l’œuf', 'total' => 10500, 'eta' => '12:55'],
            ['id' => 'FD-1038', 'col' => 'ready', 'at' => '12:30', 'items' => '1 × Couscous à l’agneau', 'total' => 17000],
        ];
    }

    public static function history(): array
    {
        return [['FD-1031', '11/11/2026', 'Livrée', 24000, 2880], ['FD-1029', '11/11/2026', 'Livrée', 17000, 2040],
                ['FD-1024', '10/11/2026', 'Annulée', 0, 0], ['FD-1019', '10/11/2026', 'Livrée', 31500, 3780]];
    }

    public static function couriers(): array
    {
        return [
            ['name' => 'Amine B.', 'status' => 'Disponible', 'rides' => 0, 'cash' => 56000],
            ['name' => 'Skander M.', 'status' => 'En course', 'rides' => 1, 'cash' => 112000],
            ['name' => 'Hatem K.', 'status' => 'Disponible', 'rides' => 0, 'cash' => 142000],
            ['name' => 'Wassim T.', 'status' => 'Hors ligne', 'rides' => 0, 'cash' => 0],
        ];
    }

    public static function ledger(): array
    {
        return [
            ['1', 'Livraison, cash encaissé', 'Cash détenu par le livreur', 'Compensation commande', 28000, 'cash'],
            ['2', 'Répartition', 'Compensation commande', 'Dû au restaurant', 25000, 'split'],
            ['3', 'Répartition', 'Compensation commande', 'Produit : frais de livraison', 3000, 'split'],
            ['4', 'Commission 12 %', 'Dû au restaurant', 'Produit : commission', 3000, 'split'],
            ['5', 'Gain du livreur', 'Charge : rémunération livreurs', 'Dû au livreur', 3000, 'courier'],
            ['6', 'Remise du cash au QG', 'Caisse QG', 'Cash détenu par le livreur', 28000, 'cash'],
            ['7', 'Versement hebdomadaire', 'Dû au restaurant', 'Banque', 22000, 'payout'],
        ];
    }
}
