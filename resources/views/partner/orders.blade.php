@extends('layouts.portal')
@section('role', 'partner')
@section('title', 'Commandes')

@section('content')
<x-title title="Commandes" />
<div class="chips"><a class="chip on" href="#">Toutes</a><a class="chip" href="#">À accepter</a><a class="chip" href="#">En préparation</a><a class="chip" href="#">Prêtes</a><a class="chip" href="#">Terminées</a></div>
<x-card>
    <x-table :headers="['Commande', 'Client', 'Total', 'Statut', '']">
        @foreach (\App\Support\Sample::orders() as $o)
            @if ($o['store'] === 'Dar Zitouna')
                <tr>
                    <td><b>{{ $o['id'] }}</b><div class="mut">{{ $o['time'] }}</div></td>
                    <td>{{ $o['customer'] }}</td>
                    <td>@tnd($o['total'])</td>
                    <td><x-badge :type="\App\Support\Sample::STATUS[$o['status']][1]">{{ \App\Support\Sample::STATUS[$o['status']][0] }}</x-badge></td>
                    <td><x-btn variant="ghost" size="sm" :href="route('partner.orders.show', substr($o['id'], 4))">Ouvrir</x-btn></td>
                </tr>
            @endif
        @endforeach
    </x-table>
</x-card>
@endsection
