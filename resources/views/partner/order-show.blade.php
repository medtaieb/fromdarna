@extends('layouts.portal')
@section('role', 'partner')
@section('title', 'Commande')

@section('content')
<x-title :title="'Commande #FD-' . ($id ?? 1043)" sub="Sana M. · passée à 12:44">
    <x-slot name="actions"><x-badge type="o">À accepter</x-badge></x-slot>
</x-title>

<div class="cols">
    <div class="stack">
        <x-card>
            <h2>Contenu</h2>
            <div class="sum"><span>1 × Couscous à l’agneau</span><span>@tnd(18000)</span></div>
            <div class="sum"><span>2 × Brik à l’œuf</span><span>@tnd(7000)</span></div>
            <div class="sum t"><span>Total plats</span><span>@tnd(25000)</span></div>
            <div class="al"><span>Remarque du client : « Sans piment, s’il vous plaît. »</span></div>
        </x-card>

        <x-card>
            <h2>Cycle de préparation</h2>
            <x-timeline :current="0" :steps="[['Reçue', 'Acceptez ou refusez dans le délai.'], ['Acceptée', 'Indiquez le temps de préparation.'], ['Prête', 'Le livreur peut venir la récupérer.']]" />
        </x-card>
    </div>

    <x-card>
        <h2>Votre réponse</h2>
        <form method="POST" action="#" class="stack">
            @csrf
            <label class="f">Temps de préparation
                <select name="prep"><option>10 min</option><option selected>15 min</option><option>20 min</option><option>30 min</option></select>
            </label>
            <button class="btn olive block" type="submit" name="action" value="accept">Accepter la commande</button>
            <label class="f">Motif en cas de refus
                <select name="reason"><option>Produit en rupture</option><option>Trop de commandes</option><option>Fermeture exceptionnelle</option></select>
            </label>
            <button class="btn red block" type="submit" name="action" value="reject" data-confirm="Refuser cette commande ?">Refuser</button>
            <button class="btn ghost block" type="submit" name="action" value="ready">Marquer comme prête</button>
        </form>
    </x-card>
</div>
@endsection
