@extends('layouts.portal')
@section('role', 'ops')
@section('title', 'Tableau de bord Ops')

@section('content')
<x-title title="Tableau de bord Ops" sub="Supervision en direct · Cap Bon & Grombalia">
    <x-slot name="actions">
        <x-btn :href="route('ops.dispatch')">🗺️ Ouvrir la carte Dispatch</x-btn>
    </x-slot>
</x-title>

@if(session('success'))
    <div class="al g mb">
        <span>✓ {{ session('success') }}</span>
    </div>
@endif

<div class="grid g4">
    <x-stat label="Livrées aujourd’hui" :value="$deliveredToday" sub="Commandes complétées" class="hot" />
    <x-stat label="Taux d’acceptation" :value="$acceptanceRate . ' %'" sub="Cible standard ≥ 95 %" />
    <x-stat label="Délai médian" :value="$medianDelayMinutes . ' min'" sub="Création → Livraison au client" />
    <x-stat label="Cash flotte non déposé" :value="\App\Support\Money::tnd($uncollectedCashMillimes)" sub="Détenu par les livreurs en tournée" />
</div>

<div class="cols">
    <x-card>
        <div class="row" style="justify-content:space-between;align-items:center;margin-bottom:12px;">
            <h2 style="margin:0;">🚨 File d’escalade & alertes actives</h2>
            <x-btn variant="ghost" size="sm" :href="route('ops.dashboard')">↻ Actualiser</x-btn>
        </div>

        @php
            $hasAlerts = $unresponsiveStoreOrders->isNotEmpty() 
                || $unassignedReadyOrders->isNotEmpty() 
                || $delayedOrders->isNotEmpty() 
                || $cashLimitExceededCouriers->isNotEmpty() 
                || $openIncidents->isNotEmpty();
        @endphp

        @if(!$hasAlerts)
            <div class="al g">
                <span>✓ <strong>Réseau fluide :</strong> Aucune alerte critique, toutes les commandes sont prises en charge.</span>
            </div>
        @else
            {{-- Alertes Restaurant sans réponse > 60s --}}
            @foreach($unresponsiveStoreOrders as $alertOrder)
                <div class="al r">
                    <span>
                        <strong>#{{ $alertOrder->ref }}</strong> · <b>{{ $alertOrder->store->name }}</b> sans réponse depuis {{ $alertOrder->created_at->diffForHumans() }}.
                    </span>
                    <x-btn variant="red" size="sm" :href="route('ops.orders.show', $alertOrder->id)">Relancer / Traiter</x-btn>
                </div>
            @endforeach

            {{-- Alertes Commande prête sans livreur > 5 min --}}
            @foreach($unassignedReadyOrders as $readyOrder)
                <div class="al" style="border-left: 4px solid var(--accent, #98551C); background: #FFF4D2;">
                    <span>
                        <strong>#{{ $readyOrder->ref }}</strong> · Prête chez <b>{{ $readyOrder->store->name }}</b> depuis {{ $readyOrder->ready_at?->diffForHumans() ?? 'plusieurs minutes' }}, sans livreur !
                    </span>
                    <x-btn size="sm" :href="route('ops.orders.show', $readyOrder->id)">Assigner d’urgence</x-btn>
                </div>
            @endforeach

            {{-- Alertes Incidents de terrain --}}
            @foreach($openIncidents as $inc)
                <div class="al r">
                    <span>
                        ⚠️ <b>Incident sur #{{ $inc->order?->ref }}</b> : {{ $inc->description }} (signalé par {{ $inc->courier?->name }})
                    </span>
                    <x-btn variant="ghost" size="sm" :href="route('ops.customers')">Résoudre</x-btn>
                </div>
            @endforeach

            {{-- Alertes Plafond de cash dépassé --}}
            @foreach($cashLimitExceededCouriers as $excCourier)
                <div class="al" style="border-left: 4px solid #C41317; background: #fff1f0;">
                    <span>
                        💰 <b>Plafond Cash Dépassé :</b> {{ $excCourier->name }} détient {{ \App\Support\Money::tnd($excCourier->getHeldCashMillimes()) }} (Plafond: {{ \App\Support\Money::tnd(150000) }}). Bloqué pour nouvelles courses.
                    </span>
                    <x-btn variant="ghost" size="sm" :href="route('ops.couriers')">Valider remise</x-btn>
                </div>
            @endforeach

            {{-- Alertes Retard important --}}
            @foreach($delayedOrders as $delOrder)
                <div class="al">
                    <span>
                        ⏱️ <strong>#{{ $delOrder->ref }}</strong> · En cours depuis {{ $delOrder->created_at->diffInMinutes() }} min ({{ $delOrder->store->name }} → {{ $delOrder->delivery_landmark ?? $delOrder->delivery_city }})
                    </span>
                    <x-btn variant="ghost" size="sm" :href="route('ops.orders.show', $delOrder->id)">Superviser</x-btn>
                </div>
            @endforeach
        @endif
    </x-card>

    <x-card>
        <h2>Flux des commandes par heure (Aujourd’hui)</h2>
        <div style="display:flex;flex-direction:column;gap:8px;margin-top:12px;">
            @foreach ($hourlyStats as $h)
                @php
                    $pct = $maxPerHour > 0 ? round(($h['count'] / $maxPerHour) * 100) : 0;
                @endphp
                <div class="row" style="flex-wrap:nowrap;align-items:center;">
                    <span style="width:48px;font-size:0.85rem;color:var(--muted);font-weight:600;">{{ $h['hour'] }}</span>
                    <div class="bar" style="flex:1;background:#f0e9df;border-radius:6px;height:14px;overflow:hidden;position:relative;">
                        <i style="display:block;height:100%;background:linear-gradient(90deg, #98551C, #B08861);width:{{ max(4, $pct) }}%;border-radius:6px;"></i>
                    </div>
                    <span style="width:32px;text-align:right;font-size:0.85rem;font-weight:700;">{{ $h['count'] }}</span>
                </div>
            @endforeach
        </div>
    </x-card>
</div>

<x-card>
    <div class="row" style="justify-content:space-between;align-items:center;margin-bottom:12px;">
        <h2 style="margin:0;">Commandes en cours d’exécution</h2>
        <x-btn variant="ghost" size="sm" :href="route('ops.orders')">Toutes les commandes →</x-btn>
    </div>

    @if($activeOrders->isEmpty())
        <div style="text-align:center;padding:32px;color:var(--muted);">
            Aucune commande active en ce moment.
        </div>
    @else
        <x-table :headers="['Commande', 'Restaurant', 'Client & Zone', 'Total cash', 'Statut', 'Livreur', '']">
            @foreach ($activeOrders as $order)
                <tr>
                    <td>
                        <b>#{{ $order->ref }}</b>
                        <div class="mut">{{ $order->created_at->format('H:i') }} (il y a {{ $order->created_at->diffInMinutes() }} min)</div>
                    </td>
                    <td>
                        <b>{{ $order->store->name }}</b>
                    </td>
                    <td>
                        <div>{{ $order->user?->name ?? 'Client direct' }}</div>
                        <div class="mut" style="font-size:0.8rem;">📍 {{ $order->delivery_landmark ?? $order->delivery_city }}</div>
                    </td>
                    <td>
                        <strong style="color:var(--olive);">{{ \App\Support\Money::tnd($order->total_millimes) }}</strong>
                    </td>
                    <td>
                        <x-badge :type="$order->statusBadgeType()">{{ $order->statusLabel() }}</x-badge>
                    </td>
                    <td>
                        @if($order->courier)
                            <span>🚴 {{ $order->courier->name }}</span>
                        @else
                            <span class="mut" style="color:#C41317;font-weight:600;">⚠️ Non assigné</span>
                        @endif
                    </td>
                    <td>
                        <x-btn variant="ghost" size="sm" :href="route('ops.orders.show', $order->id)">Intervenir</x-btn>
                    </td>
                </tr>
            @endforeach
        </x-table>
    @endif
</x-card>
@endsection
