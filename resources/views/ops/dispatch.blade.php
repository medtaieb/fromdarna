@extends('layouts.portal')
@section('role', 'ops')
@section('title', 'Dispatch')

@section('content')
<x-title title="Dispatch manuel" sub="Choisissez selon la zone, la disponibilité, la charge et l’ancienneté.">
    <x-slot name="actions"><x-badge type="o">2 courses à affecter</x-badge></x-slot>
</x-title>

<div class="cols">
    <x-card>
        <h2>Commandes à affecter</h2>
        <div class="al"><span><strong>#FD-1041</strong> · Dar Zitouna → Rim K. · prête depuis 5 min</span><x-badge type="r">Urgent</x-badge></div>
        <div class="al"><span><strong>#FD-1042</strong> · Sultan Grill → Amine B. · prête dans ≈ 8 min</span><x-badge type="o">À venir</x-badge></div>
    </x-card>

    <x-card>
        <h2>Livreurs disponibles</h2>
        <x-table :headers="['Livreur', 'Charge', 'Cash détenu', '']">
            @foreach (array_slice(\App\Support\Sample::couriers(), 0, 3) as $c)
                @if (\Illuminate\Support\Str::startsWith($c['status'], 'g:'))
                    <tr>
                        <td><b>{{ $c['name'] }}</b><div class="mut">{{ $c['vehicle'] }}</div></td>
                        <td>{{ $c['active'] }} en cours</td><td>@tnd($c['cash'])</td>
                        <td><button class="btn olive sm" type="button">Proposer</button></td>
                    </tr>
                @endif
            @endforeach
        </x-table>
        <p class="mut">Un livreur qui dépasse le plafond de cash ne peut plus recevoir de commande cash.</p>
    </x-card>
</div>
@endsection
