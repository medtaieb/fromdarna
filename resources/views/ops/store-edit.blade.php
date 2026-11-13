@extends('layouts.portal')
@section('role', 'ops')
@section('title', 'Modifier un restaurant')

@section('content')
<x-title title="Dar Zitouna" sub="Fiche, horaires et menu saisis par l’équipe." />

<div class="grid g2">
    <x-card>
        <h2>Informations</h2>
        <x-field label="Nom" name="name" value="Dar Zitouna" />
        <div class="grid g2">
            <x-field label="Commission (points de base)" name="commission_bps" value="1200" />
            <x-field label="Préparation moyenne (min)" name="avg_prep" value="15" />
        </div>
        <div class="map" style="height:130px"><span>Position exacte du point de retrait</span></div>
        <button class="btn" type="button" style="align-self:flex-start">Enregistrer</button>
    </x-card>

    <x-card>
        <h2>Horaires</h2>
        @foreach (['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'] as $d)
            <div class="row" style="flex-wrap:nowrap"><span style="width:90px">{{ $d }}</span><input type="time" value="11:00" aria-label="Ouverture {{ $d }}"><input type="time" value="22:00" aria-label="Fermeture {{ $d }}"></div>
        @endforeach
        <label class="ck"><input type="checkbox"> Activer les horaires « mode Ramadan »</label>
    </x-card>
</div>

<x-card>
    <div class="row"><h2>Menu</h2><x-btn size="sm">Ajouter un produit</x-btn></div>
    @php
        $rows = [];
        foreach (\App\Support\Sample::menu(1) as $cat => $items) {
            foreach ($items as $i) { $rows[] = [$i['name'], $cat, \App\Support\Money::tnd($i['price']), $i['ok'] ? 'g:Disponible' : 'x:En rupture']; }
        }
    @endphp
    <x-table :headers="['Produit', 'Catégorie', 'Prix', 'Disponibilité']" :rows="$rows" />
</x-card>
@endsection
