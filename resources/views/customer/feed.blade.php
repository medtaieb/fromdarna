@extends('layouts.site')
@section('title', 'Restaurants')

@section('content')
<x-title title="Ouverts maintenant à Grombalia" sub="Choisissez un commerce de votre ville.">
    <x-slot name="actions"><x-badge type="g">Zone de livraison active</x-badge></x-slot>
</x-title>

<div class="chips" role="group" aria-label="Catégories">
    <a class="chip on" href="#">Tout</a><a class="chip" href="#">Restaurants</a><a class="chip" href="#">Pâtisseries</a><a class="chip" href="#">Cafés</a>
</div>

<div class="grid g3">
    @foreach (\App\Support\Sample::stores() as $store)
        <x-store-card :store="$store" />
    @endforeach
</div>

<x-card class="flat">
    <div class="row">
        <span class="mut">Vous êtes dans une autre ville ? Nous ouvrons une ville à la fois.</span>
        <x-btn variant="ghost" size="sm" :href="route('out-of-zone')">Vérifier mon adresse</x-btn>
    </div>
</x-card>
@endsection
