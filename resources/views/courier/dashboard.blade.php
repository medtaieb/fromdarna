@extends('layouts.portal')
@section('role', 'courier')
@section('title', 'Ma course')

@php $cash = 46500; $limit = 150000; @endphp

@section('content')
<x-title title="Bonjour Walid" sub="Grombalia · scooter">
    <x-slot name="actions">
        <button class="sw" type="button" aria-pressed="true" data-on="Disponible" data-off="Indisponible"><i></i><span class="lbl">Disponible</span></button>
    </x-slot>
</x-title>

<div class="al">
    <span><strong>Nouvelle course proposée</strong> · Dar Zitouna → Grombalia centre · à répondre dans <b data-countdown="30">30 s</b></span>
    <span class="acts"><button class="btn olive sm" type="button">Accepter</button><button class="btn ghost sm" type="button">Refuser</button></span>
</div>

<div class="cols">
    <x-card>
        <div class="row"><h2>Course en cours · #FD-1041</h2><x-badge type="g">Prête au restaurant</x-badge></div>
        <div class="grid g2">
            <div><span class="mut">Retrait</span><br><b>Dar Zitouna</b><br><span class="mut">Grombalia centre</span></div>
            <div><span class="mut">Livraison</span><br><b>Rim K.</b><br><span class="mut">En face de la pharmacie · bât. B, 2e étage</span></div>
        </div>
        <div class="sum t"><span>Cash à encaisser</span><span>@tnd(31000)</span></div>
        <div class="acts">
            <x-btn :href="route('courier.delivery', 1041)">Étapes de livraison</x-btn>
            <x-btn variant="ghost" href="https://www.google.com/maps/dir/?api=1&destination=36.6191,10.5012">Ouvrir la navigation</x-btn>
        </div>
    </x-card>

    <x-card>
        <h2>Mon cash</h2>
        <div class="stat"><b>@tnd($cash)</b></div>
        <div class="bar {{ $cash / $limit > .8 ? 'warn' : '' }}"><i style="width:{{ round($cash / $limit * 100) }}%"></i></div>
        <span class="mut">Plafond : @tnd($limit). Au-delà, plus de commande cash avant remise.</span>
    </x-card>
</div>
@endsection
