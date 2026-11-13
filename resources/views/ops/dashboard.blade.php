@extends('layouts.portal')
@section('role', 'ops')
@section('title', 'Tableau de bord Ops')

@section('content')
<x-title title="Tableau de bord" sub="Grombalia · aujourd’hui, 12:46 (données d’exemple)">
    <x-slot name="actions"><x-btn :href="route('ops.dispatch')">Ouvrir le dispatch</x-btn></x-slot>
</x-title>

<div class="grid g4">
    <x-stat label="Livrées à l’heure" value="27" sub="Étoile du Nord · cible ≥ 30/jour" class="hot" />
    <x-stat label="Acceptation restaurant" value="96 %" sub="cible ≥ 95 %" />
    <x-stat label="Délai médian" value="34 min" sub="cible ≤ 35 min" />
    <x-stat label="Écart de cash inexpliqué" value="0,000 TND" sub="cible : 0" />
</div>

<div class="cols">
    <x-card>
        <h2>Alertes</h2>
        <div class="al r"><span><strong>#FD-1043</strong> · restaurant sans réponse depuis 60 s</span><x-btn variant="red" size="sm" :href="route('ops.orders.show', 1043)">Traiter</x-btn></div>
        <div class="al"><span><strong>#FD-1041</strong> · prête depuis 5 min, sans livreur</span><x-btn size="sm" :href="route('ops.dispatch')">Assigner</x-btn></div>
        <div class="al"><span><strong>#FD-1040</strong> · en retard de 15 min sur l’heure annoncée</span><x-btn variant="ghost" size="sm" :href="route('ops.orders.show', 1040)">Prévenir le client</x-btn></div>
        <div class="al g"><span>Tous les portails partenaires sont connectés.</span></div>
    </x-card>

    <x-card>
        <h2>Commandes par heure</h2>
        @foreach ([['11 h', 40], ['12 h', 95], ['13 h', 70], ['14 h', 30]] as $h)
            <div class="row" style="flex-wrap:nowrap"><span style="width:44px">{{ $h[0] }}</span><div class="bar" style="flex:1"><i style="width:{{ $h[1] }}%"></i></div></div>
        @endforeach
    </x-card>
</div>

<x-card>
    <h2>Commandes en cours</h2>
    @php
        $rows = array_map(fn ($o) => [$o['id'] . '|' . $o['time'], $o['store'], $o['customer'], \App\Support\Sample::badge($o['status']), $o['courier']], array_slice(\App\Support\Sample::orders(), 0, 4));
    @endphp
    <x-table :headers="['Commande', 'Restaurant', 'Client', 'Statut', 'Livreur']" :rows="$rows" />
</x-card>
@endsection
