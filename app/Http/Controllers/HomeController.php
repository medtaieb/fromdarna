<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function __invoke()
    {
        return view("welcome", [
            "hub" => [
                "name" => "Grombalia",
                "ar" => "قرمبالية",
                "text" => "La ville de la vigne, notre première ville et notre cuisine centrale d’organisation. Tout part d’ici.",
                "tag" => "Ville pilote",
            ],
            "towns" => [
                ["name" => "Soliman", "km" => 9, "text" => "Entre ville et plage, avec un été plein de monde à nourrir."],
                ["name" => "Béni Khalled", "km" => 9, "text" => "La cité des agrumes et de l’orange d’or, compacte et dense : les livraisons y sont courtes."],
                ["name" => "Bou Argoub", "km" => 10, "text" => "Au bord de la route nationale, avec des déjeuners à servir entre deux zones industrielles."],
            ],
            "steps" => [
                ["Choisissez", "Parcourez les menus des commerces de votre ville, en arabe ou en français, et composez votre panier."],
                ["Confirmez", "Le total, livraison comprise, s’affiche avant la confirmation. Placez votre épingle et un repère pour guider le livreur."],
                ["Recevez et payez", "Suivez les étapes de votre commande, puis réglez en cash à la livraison."],
            ],
            "promises" => [
                ["Un prix clair", "Plats, livraison et total détaillés avant de confirmer."],
                ["Le cash, simplement", "Vous payez à la livraison, sans carte ni compte bancaire."],
                ["Arabe et français", "L’application se lit dans les deux langues, de droite à gauche comme de gauche à droite."],
                ["Une vraie personne au bout du fil", "Un souci avec une commande ? Écrivez-nous sur WhatsApp ou appelez."],
                ["Des commerces de chez vous", "Chaque commande fait travailler les restaurants et les livreurs de votre ville."],
                ["Une ville à la fois", "Nous ouvrons quand les partenaires et les livreurs sont prêts, pas avant."],
            ],
        ]);
    }
}
