@extends('layouts.portal')
@section('role', 'ops')
@section('title', 'Versements')

@section('content')
<x-title title="Versements et remises de cash" />

<x-card>
    <h2>Restaurants · semaine 43</h2>
    <x-table :headers="['Restaurant', 'Ventes', 'Commission', 'Net à verser', 'Statut', '']">
        <tr><td><b>Dar Zitouna</b></td><td>@tnd(1012000)</td><td>− @tnd(121440)</td><td>@tnd(890560)</td><td><x-badge type="o">À verser</x-badge></td><td><button class="btn olive sm" type="button" data-confirm="Valider ce versement ?">Valider</button></td></tr>
        <tr><td><b>Sultan Grill</b></td><td>@tnd(745000)</td><td>− @tnd(89400)</td><td>@tnd(655600)</td><td><x-badge type="g">Versé</x-badge></td><td></td></tr>
    </x-table>
</x-card>

<x-card>
    <h2>Remises de cash des livreurs · aujourd’hui</h2>
    <x-table :headers="['Livreur', 'Cash attendu', 'Cash remis', 'Écart', 'Statut']" :rows="[
        ['Walid T.', '46,500 TND', '—', '—', 'o:En attente'],
        ['Marwen A.', '92,000 TND', '92,000 TND', '0,000 TND', 'g:Rapproché'],
    ]" />
</x-card>
@endsection
