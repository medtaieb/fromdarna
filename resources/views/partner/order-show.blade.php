@extends('layouts.portal')
@section('role', 'partner')
@section('title', 'Ticket Commande #' . $order->ref)

@section('content')
<x-title :title="'Commande #' . $order->ref" :sub="($order->user->name ?? 'Client') . ' · passée le ' . $order->created_at->format('d/m/Y à H:i')">
    <x-slot name="actions">
        <x-badge :type="$order->statusBadgeType()">{{ $order->statusLabel() }}</x-badge>
        <x-btn variant="ghost" size="sm" :href="route('partner.orders')">← Toutes les commandes</x-btn>
    </x-slot>
</x-title>

@if(session('success'))<div class="flash ok" role="alert">{{ session('success') }}</div>@endif
@if(session('info'))<div class="flash info" role="alert">{{ session('info') }}</div>@endif

<div class="cols">
    <div class="stack">
        <x-card>
            <h2>📋 Contenu du ticket de cuisine</h2>
            <div style="margin:12px 0;display:flex;flex-direction:column;gap:8px">
                @foreach($order->items as $it)
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--line)">
                        <div>
                            <strong style="font-size:16px">{{ $it->quantity }} × {{ $it->product_name }}</strong>
                            @if($it->product && $it->product->description)
                                <div class="mut" style="font-size:12px">{{ $it->product->description }}</div>
                            @endif
                        </div>
                        <strong style="font-size:15px">@tnd($it->line_total_millimes)</strong>
                    </div>
                @endforeach
            </div>

            <div class="sum t" style="margin-top:10px">
                <span>Total plats restaurant</span>
                <span>@tnd($order->subtotal_millimes)</span>
            </div>

            @if($order->customer_note)
                <div class="al" style="margin-top:14px;padding:10px 14px;font-size:13.5px">
                    📝 <strong>Remarque du client :</strong> « {{ $order->customer_note }} »
                </div>
            @endif

            @if($order->rejection_reason)
                <div class="flash warn" style="margin-top:14px" role="alert">
                    ❌ <strong>Commande refusée :</strong> {{ $order->rejection_reason }}
                </div>
            @endif
        </x-card>

        <x-card>
            <h2>Cycle de préparation</h2>
            @php
                $statusList = ['PLACED', 'ACCEPTED', 'READY', 'PICKED_UP', 'DELIVERED'];
                $currentIdx = array_search($order->status, $statusList);
                if ($currentIdx === false) $currentIdx = 0;
            @endphp
            <x-timeline :current="$currentIdx" :steps="[
                ['Reçue', $order->created_at->format('H:i')],
                ['En préparation', $order->accepted_at ? $order->accepted_at->format('H:i') : 'À accepter'],
                ['Prête au comptoir', $order->ready_at ? 'Prévue à ' . $order->ready_at->format('H:i') : 'En attente'],
                ['Récupérée par le coursier', $order->picked_up_at ? $order->picked_up_at->format('H:i') : 'En attente'],
                ['Livrée au client', $order->delivered_at ? $order->delivered_at->format('H:i') : 'En attente'],
            ]" />
        </x-card>
    </div>

    <div class="stack">
        {{-- ── Formulaires d'actions de cuisine ── --}}
        <x-card>
            <h2>Actions cuisine</h2>

            @if($order->status === 'PLACED')
                <div class="al" style="margin-bottom:12px">
                    <span>🚨 <strong>Nouvelle commande :</strong> Répondez dans les 60 secondes pour garantir un service rapide.</span>
                </div>

                {{-- Formulaire d'acceptation --}}
                <form method="POST" action="{{ route('partner.orders.accept', $order) }}" class="stack" style="margin-bottom:18px">
                    @csrf
                    <label class="f">
                        Temps estimé de préparation
                        <select name="prep_minutes">
                            <option value="10">10 minutes (Rapide)</option>
                            <option value="15" selected>15 minutes (Standard)</option>
                            <option value="20">20 minutes</option>
                            <option value="30">30 minutes (Mijotage)</option>
                            <option value="45">45 minutes</option>
                        </select>
                    </label>
                    <button class="btn olive block glow" type="submit">
                        ✓ Accepter la commande
                    </button>
                </form>

                <hr style="border:none;border-top:1px solid var(--line);margin:16px 0">

                {{-- Formulaire de refus --}}
                <form method="POST" action="{{ route('partner.orders.reject', $order) }}" class="stack">
                    @csrf
                    <label class="f">
                        Motif en cas de refus
                        <select name="reason">
                            <option value="Produit en rupture de stock">Produit en rupture de stock</option>
                            <option value="Cuisine surchargée">Cuisine surchargée</option>
                            <option value="Fermeture exceptionnelle">Fermeture exceptionnelle</option>
                            <option value="Autre motif opérationnel">Autre motif opérationnel</option>
                        </select>
                    </label>
                    <button class="btn red block" type="submit" onclick="return confirm('Êtes-vous sûr de vouloir refuser cette commande ?')">
                        Refuser la commande
                    </button>
                </form>

            @elseif($order->status === 'ACCEPTED')
                <div style="margin-bottom:14px">
                    <span class="bd o pulse" style="font-size:14px;padding:6px 14px">
                        🍳 En préparation (prévue à {{ $order->ready_at ? $order->ready_at->format('H:i') : '' }})
                    </span>
                </div>

                <form method="POST" action="{{ route('partner.orders.ready', $order) }}">
                    @csrf
                    <button class="btn olive block glow grad-animate" type="submit" style="padding:14px;font-size:15px">
                        🔔 Les plats sont prêts (Alerter le coursier)
                    </button>
                </form>

            @elseif($order->status === 'READY')
                <div class="al g" style="margin-bottom:12px">
                    <span>✅ <strong>Plats prêts au comptoir.</strong> Le coursier est en route vers votre établissement.</span>
                </div>
                @if($order->courier)
                    <div style="background:var(--bg2);padding:10px 14px;border-radius:12px;font-size:13.5px">
                        🛵 <strong>Livreur :</strong> {{ $order->courier->name }}<br>
                        📞 <a href="tel:{{ $order->courier->phone }}">{{ $order->courier->phone }}</a>
                    </div>
                @endif

            @elseif($order->status === 'PICKED_UP')
                <div class="al g" style="margin-bottom:12px">
                    <span>🛵 <strong>Commande récupérée</strong> par {{ $order->courier->name ?? 'le livreur' }}. En route vers le client.</span>
                </div>

            @elseif($order->status === 'DELIVERED')
                <div class="al g" style="margin-bottom:12px">
                    <span>🎉 <strong>Commande livrée avec succès !</strong> Encaissement cash validé.</span>
                </div>

            @else
                <p class="mut">Statut : {{ $order->statusLabel() }}</p>
            @endif
        </x-card>

        {{-- ── Informations Client ── --}}
        <x-card>
            <h2>Informations de livraison</h2>
            <div style="font-size:13.5px;display:flex;flex-direction:column;gap:6px">
                <div><strong>Client :</strong> {{ $order->user->name ?? 'Client' }}</div>
                @if($order->delivery_phone)
                    <div><strong>Téléphone :</strong> <a href="tel:{{ $order->delivery_phone }}">{{ $order->delivery_phone }}</a></div>
                @endif
                <div><strong>Zone :</strong> {{ $order->delivery_city }}</div>
                @if($order->delivery_landmark)
                    <div><strong>Repère :</strong> {{ $order->delivery_landmark }}</div>
                @endif
            </div>
        </x-card>
    </div>
</div>
@endsection
