@extends('layouts.portal')
@section('role', 'courier')
@section('title', 'Ma course')

@php
    $cash = 46500;
    $limit = 150000;
@endphp

@section('content')
<x-title :title="'Bonjour ' . ($user->name ?? 'Walid')" :sub="($activeOrder?->store?->zone?->name ?? 'Grombalia') . ' · En service'">
    <x-slot name="actions">
        <button class="sw" type="button" aria-pressed="true" data-on="Disponible" data-off="Indisponible"><i></i><span class="lbl">Disponible</span></button>
    </x-slot>
</x-title>

@if(session('success'))<div class="flash ok" role="alert">{{ session('success') }}</div>@endif

@if($availableOrder && !$activeOrder)
<div class="al">
    <span>
        <strong>Nouvelle course proposée !</strong> ·
        {{ $availableOrder->store->name }} ➔ {{ $availableOrder->delivery_landmark ?? 'Client' }} ·
        à répondre dans <b data-countdown="30">30 s</b>
    </span>
    <span class="acts">
        <form method="POST" action="{{ route('courier.order.status', $availableOrder) }}" style="display:inline">
            @csrf
            <input type="hidden" name="status" value="ACCEPTED">
            <button class="btn olive sm glow" type="submit">Accepter la course</button>
        </form>
        <button class="btn ghost sm" type="button">Ignorer</button>
    </span>
</div>
@endif

<div class="cols">
    <x-card>
        @if($activeOrder)
            <div class="row">
                <h2>Course en cours · #{{ $activeOrder->ref }}</h2>
                <x-badge :type="$activeOrder->statusBadgeType()">{{ $activeOrder->statusLabel() }}</x-badge>
            </div>
            <div class="grid g2" style="margin-top:10px">
                <div>
                    <span class="mut">Point de retrait</span><br>
                    <b>{{ $activeOrder->store->name }}</b><br>
                    <span class="mut">{{ $activeOrder->store->address ?? 'Centre-ville' }}</span>
                </div>
                <div>
                    <span class="mut">Point de livraison</span><br>
                    <b>{{ $activeOrder->user->name ?? 'Client' }}</b><br>
                    <span class="mut">{{ $activeOrder->delivery_city }} · {{ $activeOrder->delivery_landmark }}</span>
                </div>
            </div>
            <div class="sum t" style="margin-top:12px">
                <span>Cash à encaisser</span>
                <span>@tnd($activeOrder->total_millimes)</span>
            </div>
            <div class="acts" style="margin-top:14px">
                <x-btn :href="route('courier.delivery', $activeOrder->id)" class="glow grad-animate">
                    🗺️ Voir le tracé & étapes
                </x-btn>
                <x-btn variant="ghost" href="https://www.google.com/maps/dir/?api=1&destination={{ $activeOrder->delivery_lat ?? 36.6191 }},{{ $activeOrder->delivery_lng ?? 10.5012 }}" target="_blank">
                    🧭 Ouvrir Google Maps
                </x-btn>
            </div>
        @else
            <h2>Aucune course en cours</h2>
            <p class="mut">Vous êtes en ligne. Les nouvelles commandes à livrer apparaîtront ici dès qu'un restaurant valide un panier.</p>
            <x-btn :href="route('courier.delivery')" variant="ghost" size="sm" style="margin-top:8px">
                Voir la dernière course archivée
            </x-btn>
        @endif
    </x-card>

    <x-card>
        <h2>Mon cash & recettes</h2>
        <div class="stat"><b>@tnd($cash)</b></div>
        <div class="bar {{ $cash / $limit > .8 ? 'warn' : '' }}"><i style="width:{{ round($cash / $limit * 100) }}%"></i></div>
        <span class="mut">Plafond autorisé : @tnd($limit). Au-delà, versement obligatoire auprès de l'agence.</span>
    </x-card>
</div>
@endsection
