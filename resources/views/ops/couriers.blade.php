@extends('layouts.portal')
@section('role', 'ops')
@section('title', 'Livreurs')

@section('content')
<x-title title="Livreurs">
    <x-slot name="actions"><x-btn>Ajouter un livreur</x-btn></x-slot>
</x-title>

<x-card>
    @php
        $rows = array_map(fn ($c) => [$c['name'], $c['vehicle'], $c['status'], \App\Support\Money::tnd($c['cash']) . ' / ' . \App\Support\Money::tnd($c['limit'])], \App\Support\Sample::couriers());
    @endphp
    <x-table :headers="['Livreur', 'Véhicule', 'Statut', 'Cash détenu / plafond']" :rows="$rows" />
</x-card>

<x-card>
    <h2>Dossier à valider · Fares D.</h2>
    @php $docs = [['CIN', 'g', 'Reçu'], ['Permis', 'g', 'Reçu'], ['Carte grise', 'o', 'À vérifier'], ['Assurance', 'r', 'Manquante']]; @endphp
    <div class="grid g4">
        @foreach ($docs as $d)
            <div class="card flat"><span class="mut">{{ $d[0] }}</span><div><x-badge :type="$d[1]">{{ $d[2] }}</x-badge></div></div>
        @endforeach
    </div>
    <div class="acts"><button class="btn olive" type="button">Valider le dossier</button><button class="btn ghost" type="button">Demander un document</button></div>
</x-card>
@endsection
