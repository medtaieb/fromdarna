@extends('layouts.portal')
@section('role', 'ops')
@section('title', 'Zone et tarifs')

@section('content')
<x-title title="Zone de livraison et tarifs" sub="Une zone configurée au lancement : le centre urbain de Grombalia.">
    <x-slot name="actions"><button class="sw" type="button" aria-pressed="true" data-on="Zone active" data-off="Zone suspendue"><i></i><span class="lbl">Zone active</span></button></x-slot>
</x-title>

<div class="grid g2">
    <x-card>
        <h2>Grombalia</h2>
        <div class="map"><span>Polygone chargé en configuration</span></div>
        <p class="mut">L’éditeur graphique de zones arrive plus tard ; la zone couvre le centre urbain, pas tout le périmètre municipal.</p>
    </x-card>
    <x-card>
        <h2>Tarifs de départ (à confirmer par le test concierge)</h2>
        <x-table :headers="['Élément', 'Valeur']" :rows="[
            ['Livraison, zone pilote', '3,000 TND'],
            ['Commande minimum', '10,000 TND'],
            ['Frais de petit panier', '1,000 TND'],
            ['Frais de service', '0,000 TND'],
            ['Plafond de cash par livreur', '150,000 TND'],
        ]" />
        <button class="btn" type="button" style="align-self:flex-start">Modifier les tarifs</button>
    </x-card>
</div>
@endsection
