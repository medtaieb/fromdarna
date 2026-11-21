@extends('layouts.portal')
@section('role', 'partner')
@section('title', 'Toutes les commandes · ' . $store->name)

@section('content')
<x-title :title="'Commandes · ' . $store->name" sub="Historique et gestion des commandes de votre restaurant." />

@if(session('success'))<div class="flash ok" role="alert">{{ session('success') }}</div>@endif
@if(session('info'))<div class="flash info" role="alert">{{ session('info') }}</div>@endif

<div class="chips" role="group" aria-label="Filtres par statut">
    <a class="chip {{ ($filter ?? 'all') === 'all' ? 'on' : '' }}" href="{{ route('partner.orders') }}">
        Toutes
    </a>
    <a class="chip {{ ($filter ?? '') === 'placed' ? 'on' : '' }}" href="{{ route('partner.orders', ['status' => 'placed']) }}">
        🚨 À accepter
    </a>
    <a class="chip {{ ($filter ?? '') === 'accepted' ? 'on' : '' }}" href="{{ route('partner.orders', ['status' => 'accepted']) }}">
        🍳 En préparation
    </a>
    <a class="chip {{ ($filter ?? '') === 'ready' ? 'on' : '' }}" href="{{ route('partner.orders', ['status' => 'ready']) }}">
        📦 Prêtes
    </a>
    <a class="chip {{ ($filter ?? '') === 'delivered' ? 'on' : '' }}" href="{{ route('partner.orders', ['status' => 'delivered']) }}">
        ✅ Terminées
    </a>
</div>

<x-card>
    @if($orders->count())
        <table style="width:100%;border-collapse:collapse;font-size:13.5px">
            <thead>
                <tr style="border-bottom:1.5px solid var(--line);text-align:left">
                    <th style="padding:10px 6px">Commande</th>
                    <th style="padding:10px 6px">Client</th>
                    <th style="padding:10px 6px">Articles</th>
                    <th style="padding:10px 6px">Total plats</th>
                    <th style="padding:10px 6px">Statut</th>
                    <th style="padding:10px 6px;text-align:right">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $o)
                    <tr style="border-bottom:1px solid var(--line)">
                        <td style="padding:12px 6px">
                            <strong>#{{ $o->ref }}</strong>
                            <div class="mut" style="font-size:12px">{{ $o->created_at->format('d/m/Y H:i') }}</div>
                        </td>
                        <td style="padding:12px 6px">
                            <b>{{ $o->user->name ?? 'Client' }}</b>
                            @if($o->delivery_phone)
                                <div class="mut" style="font-size:11.5px">{{ $o->delivery_phone }}</div>
                            @endif
                        </td>
                        <td style="padding:12px 6px">
                            <span>{{ $o->items->sum('quantity') }} article(s)</span>
                            <div class="mut" style="font-size:11.5px">
                                {{ Str::limit($o->items->pluck('product_name')->implode(', '), 30) }}
                            </div>
                        </td>
                        <td style="padding:12px 6px">
                            <strong>@tnd($o->subtotal_millimes)</strong>
                        </td>
                        <td style="padding:12px 6px">
                            <x-badge :type="$o->statusBadgeType()">{{ $o->statusLabel() }}</x-badge>
                        </td>
                        <td style="padding:12px 6px;text-align:right">
                            <x-btn variant="ghost" size="sm" :href="route('partner.orders.show', $o->id)">
                                Ouvrir
                            </x-btn>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top:16px">
            {{ $orders->links() }}
        </div>
    @else
        <div style="text-align:center;padding:32px">
            <span style="font-size:36px">📋</span>
            <p class="mut" style="margin-top:8px">Aucune commande trouvée pour ce filtre.</p>
            <x-btn :href="route('partner.orders')" variant="ghost" size="sm" style="margin-top:8px">Afficher toutes les commandes</x-btn>
        </div>
    @endif
</x-card>
@endsection
