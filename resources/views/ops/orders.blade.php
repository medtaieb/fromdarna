@extends('layouts.portal')
@section('role', 'ops')
@section('title', 'Gestion des commandes')

@section('content')
<x-title title="Commandes & Courses" sub="Historique et suivi opérationnel complet">
    <x-slot name="actions">
        <x-btn :href="route('ops.dispatch')">🗺️ Dispatch en direct</x-btn>
    </x-slot>
</x-title>

@if(session('success'))
    <div class="al g mb">
        <span>✓ {{ session('success') }}</span>
    </div>
@endif

<x-card>
    <form method="GET" action="{{ route('ops.orders') }}" class="row" style="flex-wrap:wrap;gap:12px;align-items:center;">
        <input 
            type="search" 
            name="q" 
            value="{{ request('q') }}" 
            placeholder="N° réf (FD-...), nom client, tél..." 
            aria-label="Recherche" 
            style="flex:2;min-width:220px"
        >
        
        <select name="status" aria-label="Statut" style="flex:1;min-width:140px">
            <option value="">Tous les statuts</option>
            @foreach (\App\Models\Order::STATUS_LABELS as $key => $meta)
                <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>
                    {{ $meta[0] }}
                </option>
            @endforeach
        </select>

        <select name="store_id" aria-label="Restaurant" style="flex:1;min-width:160px">
            <option value="">Tous les restaurants</option>
            @foreach ($stores as $s)
                <option value="{{ $s->id }}" {{ request('store_id') == $s->id ? 'selected' : '' }}>
                    {{ $s->name }}
                </option>
            @endforeach
        </select>

        <select name="zone_id" aria-label="Zone" style="flex:1;min-width:140px">
            <option value="">Toutes les zones</option>
            @foreach ($zones as $z)
                <option value="{{ $z->id }}" {{ request('zone_id') == $z->id ? 'selected' : '' }}>
                    {{ $z->name }}
                </option>
            @endforeach
        </select>

        <button class="btn" type="submit">Filtrer</button>
        @if(request()->hasAny(['q', 'status', 'store_id', 'zone_id']))
            <a class="btn ghost" href="{{ route('ops.orders') }}">Réinitialiser</a>
        @endif
    </form>
</x-card>

<x-card>
    @if($orders->isEmpty())
        <div style="text-align:center;padding:40px;color:var(--muted);">
            <div style="font-size:2rem;margin-bottom:8px;">🔍</div>
            <p>Aucune commande ne correspond aux critères de recherche.</p>
        </div>
    @else
        <x-table :headers="['Commande', 'Restaurant', 'Client', 'Zone & Adresse', 'Total', 'Statut', 'Livreur', '']">
            @foreach ($orders as $order)
                <tr>
                    <td>
                        <b>#{{ $order->ref }}</b>
                        <div class="mut" style="font-size:0.8rem;">
                            {{ $order->created_at->format('d/m/Y H:i') }}
                        </div>
                    </td>
                    <td>
                        <b>{{ $order->store->name }}</b>
                    </td>
                    <td>
                        <div><b>{{ $order->user?->name ?? 'Client direct' }}</b></div>
                        <div class="mut" style="font-size:0.8rem;">📞 {{ $order->delivery_phone }}</div>
                    </td>
                    <td>
                        <div>{{ $order->store->zone->name ?? $order->delivery_city }}</div>
                        <div class="mut" style="font-size:0.8rem;">📍 {{ $order->delivery_landmark ?? 'Centre' }}</div>
                    </td>
                    <td>
                        <strong style="color:var(--olive);">@tnd($order->total_millimes)</strong>
                        <div class="mut" style="font-size:0.75rem;">{{ strtoupper($order->payment_method) }}</div>
                    </td>
                    <td>
                        <x-badge :type="$order->statusBadgeType()">{{ $order->statusLabel() }}</x-badge>
                    </td>
                    <td>
                        @if($order->courier)
                            <span>🚴 {{ $order->courier->name }}</span>
                        @else
                            <span class="mut" style="color:#C41317;font-weight:600;">—</span>
                        @endif
                    </td>
                    <td>
                        <x-btn variant="ghost" size="sm" :href="route('ops.orders.show', $order->id)">Détail</x-btn>
                    </td>
                </tr>
            @endforeach
        </x-table>

        <div style="margin-top:20px;">
            {{ $orders->links() }}
        </div>
    @endif
</x-card>
@endsection
