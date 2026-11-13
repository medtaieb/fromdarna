@extends('layouts.portal')
@section('role', 'ops')
@section('title', 'Restaurants')

@section('content')
<x-title title="Restaurants partenaires">
    <x-slot name="actions"><x-btn>Ajouter un restaurant</x-btn></x-slot>
</x-title>
<x-card>
    <x-table :headers="['Restaurant', 'Type', 'Commission', 'Préparation', 'Statut', '']">
        @foreach (\App\Support\Sample::stores() as $s)
            <tr>
                <td><b>{{ $s['name'] }}</b></td><td>{{ $s['kind'] }}</td><td>12 %</td><td>≈ {{ $s['eta'] - 15 }} min</td>
                <td><x-badge :type="$s['open'] ? 'g' : 'x'">{{ $s['open'] ? 'Ouvert' : 'Fermé' }}</x-badge></td>
                <td><x-btn variant="ghost" size="sm" :href="route('ops.stores.edit', $s['id'])">Modifier</x-btn></td>
            </tr>
        @endforeach
    </x-table>
</x-card>
@endsection
