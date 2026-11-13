@extends('layouts.portal')
@section('role', 'ops')
@section('title', 'Grand livre')

@section('content')
<x-title title="Grand livre" sub="Écritures en partie double. Les soldes sont calculés, jamais modifiés à la main.">
    <x-slot name="actions"><x-btn variant="ghost" size="sm">Exporter</x-btn></x-slot>
</x-title>

<div class="grid g4">
    <x-stat label="Cash détenu par les livreurs" value="138,500 TND" />
    <x-stat label="Caisse QG" value="212,000 TND" />
    <x-stat label="Dû aux restaurants" value="1 012,000 TND" />
    <x-stat label="Dû aux livreurs" value="96,200 TND" />
</div>

<div class="al g"><span>Rapprochement du jour : <strong>aucun écart</strong></span><x-badge type="g">Équilibré</x-badge></div>

<x-card>
    <h2>Exemple : une commande cash de 28,000 TND</h2>
    <x-table :headers="['#', 'Moment', 'Débit', 'Crédit', 'Montant']" :rows="[
        ['1', 'Livraison, cash encaissé', 'Cash détenu par le livreur', 'Compensation commande', '28,000 TND'],
        ['2', 'Répartition', 'Compensation commande', 'Dû au restaurant', '25,000 TND'],
        ['3', 'Répartition', 'Compensation commande', 'Produit : frais de livraison', '3,000 TND'],
        ['4', 'Commission 12 %', 'Dû au restaurant', 'Produit : commission', '3,000 TND'],
        ['5', 'Gain du livreur', 'Charge : rémunération livreurs', 'Dû au livreur', '3,000 TND'],
        ['6', 'Remise du cash au QG', 'Caisse QG + Dû au livreur', 'Cash détenu par le livreur', '28,000 TND'],
        ['7', 'Versement hebdomadaire', 'Dû au restaurant', 'Banque', '22,000 TND'],
    ]" />
</x-card>
@endsection
