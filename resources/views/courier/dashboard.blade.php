@extends('layouts.portal')
@section('role', 'courier')
@section('title', 'Ma course · ' . ($user->name ?? 'Livreur'))

@section('content')
<x-title :title="'Bonjour ' . ($user->name ?? 'Walid')" :sub="'Grombalia · ' . ($user->isCashLimitExceeded() ? 'Plafond cash atteint' : 'En service')">
    <x-slot name="actions">
        @if($isLimitExceeded)
            <span class="bd r pulse">⚠️ Plafond 150 TND dépassé</span>
        @else
            <span class="bd g"><span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#2E7D32;margin-right:4px"></span>En ligne</span>
        @endif
    </x-slot>
</x-title>

@if(session('success'))<div class="flash ok" role="alert">{{ session('success') }}</div>@endif
@if(session('warning'))<div class="flash warn" role="alert">{{ session('warning') }}</div>@endif

{{-- ── Alerte blocage plafond 150 TND ── --}}
@if($isLimitExceeded)
<div class="flash warn glow" role="alert" style="border-color:#C41317;margin-bottom:16px">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
        <div>
            <strong style="color:var(--tomate);font-size:15px">🛑 Plafond de cash atteint ({{ \App\Support\Money::tnd($heldCash) }} / {{ \App\Support\Money::tnd($cashLimit) }})</strong>
            <p style="margin:4px 0 0;font-size:13px">Pour des raisons de sécurité, vous ne pouvez plus accepter de courses en cash tant que vous n'avez pas déposé vos encaissements au bureau FromDarna de Grombalia.</p>
        </div>
        <x-btn :href="route('courier.cash')" variant="red" size="sm" class="glow">Déposer mon cash au QG</x-btn>
    </div>
</div>
@endif

{{-- ── Proposition de course disponible ── --}}
@if($availableOrder && !$activeOrder)
<div class="al glow border-grad" style="margin-bottom:16px">
    <div>
        <strong>Nouvelle course proposée !</strong> ·
        {{ $availableOrder->store->name }} ➔ {{ $availableOrder->delivery_landmark ?? $availableOrder->delivery_city }} ·
        Cash à encaisser : <strong>{{ \App\Support\Money::tnd($availableOrder->total_millimes) }}</strong>
    </div>
    <span class="acts">
        @if(!$isLimitExceeded)
            <form method="POST" action="{{ route('courier.order.status', $availableOrder) }}" style="display:inline">
                @csrf
                <input type="hidden" name="status" value="ACCEPTED">
                <button class="btn olive sm glow" type="submit">Accepter la course</button>
            </form>
        @else
            <button class="btn sm" disabled title="Plafond dépassé">Bloqué (Remise requise)</button>
        @endif
        <button class="btn ghost sm" type="button">Ignorer</button>
    </span>
</div>
@endif

<div class="cols">
    {{-- Course active --}}
    <x-card>
        @if($activeOrder)
            <div class="row">
                <h2>Course en cours · #{{ $activeOrder->ref }}</h2>
                <x-badge :type="$activeOrder->statusBadgeType()">{{ $activeOrder->statusLabel() }}</x-badge>
            </div>
            <div class="grid g2" style="margin-top:10px">
                <div>
                    <span class="mut">Point de retrait</span><br>
                    <b>🍽️ {{ $activeOrder->store->name }}</b><br>
                    <span class="mut">{{ $activeOrder->store->address ?? 'Centre-ville' }}</span>
                </div>
                <div>
                    <span class="mut">Point de livraison</span><br>
                    <b>🏠 {{ $activeOrder->user->name ?? 'Client' }}</b><br>
                    <span class="mut">{{ $activeOrder->delivery_city }} · {{ $activeOrder->delivery_landmark }}</span>
                </div>
            </div>
            <div class="sum t" style="margin-top:12px">
                <span>Cash à encaisser au client</span>
                <span>{{ \App\Support\Money::tnd($activeOrder->total_millimes) }}</span>
            </div>
            <div class="acts" style="margin-top:14px">
                <x-btn :href="route('courier.delivery', $activeOrder->id)" class="glow grad-animate">
                    🗺️ Ouvrir le tracé & étapes
                </x-btn>
                <x-btn variant="ghost" href="https://www.google.com/maps/dir/?api=1&destination={{ $activeOrder->delivery_lat ?? 36.6191 }},{{ $activeOrder->delivery_lng ?? 10.5012 }}" target="_blank">
                    🧭 Navigation Google Maps
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

    {{-- Caisse Cash en direct --}}
    <x-card>
        <div style="display:flex;justify-content:space-between;align-items:center">
            <h2>Mon cash en poche</h2>
            <span class="bd {{ $isLimitExceeded ? 'r pulse' : ($cashPercent > 75 ? 'o' : 'g') }}">
                {{ $cashPercent }} % du plafond
            </span>
        </div>

        <div class="stat" style="margin-top:8px">
            <b style="font-size:32px;color:{{ $isLimitExceeded ? 'var(--tomate)' : 'inherit' }}">{{ \App\Support\Money::tnd($heldCash) }}</b>
        </div>

        <div class="bar {{ $cashPercent > 80 ? 'warn' : '' }}" style="margin:10px 0">
            <i style="width:{{ $cashPercent }}%"></i>
        </div>

        <div style="display:flex;justify-content:space-between;font-size:12px" class="mut">
            <span>0 TND</span>
            <span>Plafond : <strong>{{ \App\Support\Money::tnd($cashLimit) }}</strong></span>
        </div>

        <p class="mut" style="font-size:12.5px;margin-top:10px">
            Au-delà de {{ \App\Support\Money::tnd($cashLimit) }}, le système bloque automatiquement les nouvelles courses cash jusqu'à la remise au QG.
        </p>

        <div style="margin-top:12px;display:flex;gap:8px">
            <x-btn :href="route('courier.cash')" class="glow sm block" style="flex:1;text-align:center">
                💵 Déposer le cash
            </x-btn>
            <x-btn :href="route('courier.earnings')" variant="ghost" size="sm">
                Historique des gains
            </x-btn>
        </div>
    </x-card>
</div>
@endsection
