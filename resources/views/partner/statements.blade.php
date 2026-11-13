@extends('layouts.portal')
@section('role', 'partner')
@section('title', 'Relevés et versements')

@php
    // [semaine, commandes, ventes en millimes, statut]
    $weeks = [['Semaine 43', 41, 1012000, 'o:En cours'], ['Semaine 42', 38, 962000, 'g:Versé'], ['Semaine 41', 33, 810500, 'g:Versé']];
    $rows = array_map(function ($w) {
        $commission = intdiv($w[2] * 12, 100);
        $m = fn ($x) => \App\Support\Money::tnd($x);
        return [$w[0], (string) $w[1], $m($w[2]), '− ' . $m($commission), $m($w[2] - $commission), $w[3]];
    }, $weeks);
@endphp

@section('content')
<x-title title="Relevés et versements" sub="Versement chaque semaine par virement, commission déduite.">
    <x-slot name="actions"><x-btn variant="ghost" size="sm">Exporter</x-btn></x-slot>
</x-title>

<x-card>
    <x-table :headers="['Période', 'Commandes', 'Ventes', 'Commission (12 %)', 'Net à verser', 'Statut']" :rows="$rows" />
    <p class="mut">Le taux de commission est celui de votre contrat. Chaque ligne se rapproche du grand livre.</p>
</x-card>
@endsection
