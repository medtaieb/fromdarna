@extends('layouts.portal')
@section('role', 'ops')
@section('title', 'Commandes')

@section('content')
<x-title title="Commandes" />
<x-card>
    <form method="GET" class="row" style="flex-wrap:wrap">
        <input name="q" placeholder="N° de commande, client, téléphone…" aria-label="Recherche" style="flex:2;min-width:240px">
        <select name="status" aria-label="Statut" style="flex:1;min-width:160px"><option value="">Tous les statuts</option>@foreach (\App\Support\Sample::STATUS as $k => $s)<option value="{{ $k }}">{{ $s[0] }}</option>@endforeach</select>
        <select name="store" aria-label="Restaurant" style="flex:1;min-width:160px"><option value="">Tous les restaurants</option>@foreach (\App\Support\Sample::stores() as $s)<option>{{ $s['name'] }}</option>@endforeach</select>
        <button class="btn" type="submit">Filtrer</button>
    </form>
</x-card>
<x-card>
    <x-table :headers="['Commande', 'Restaurant', 'Client', 'Total', 'Statut', 'Livreur', '']">
        @foreach (\App\Support\Sample::orders() as $o)
            <tr>
                <td><b>{{ $o['id'] }}</b><div class="mut">{{ $o['time'] }}</div></td>
                <td>{{ $o['store'] }}</td><td>{{ $o['customer'] }}</td><td>@tnd($o['total'])</td>
                <td><x-badge :type="\App\Support\Sample::STATUS[$o['status']][1]">{{ \App\Support\Sample::STATUS[$o['status']][0] }}</x-badge></td>
                <td>{{ $o['courier'] }}</td>
                <td><x-btn variant="ghost" size="sm" :href="route('ops.orders.show', substr($o['id'], 4))">Détail</x-btn></td>
            </tr>
        @endforeach
    </x-table>
</x-card>
@endsection
