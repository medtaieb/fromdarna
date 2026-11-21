@extends('layouts.portal')
@section('role', 'ops')
@section('title', 'Supervision Commande #' . $order->ref)

@section('content')
<x-title :title="'Commande #' . $order->ref" :sub="$order->store->name . ' → ' . ($order->user?->name ?? 'Client') . ' · ' . ($order->store->zone->name ?? $order->delivery_city)">
    <x-slot name="actions">
        <x-badge :type="$order->statusBadgeType()">{{ $order->statusLabel() }}</x-badge>
        <x-btn variant="ghost" size="sm" :href="route('ops.orders')">← Liste des commandes</x-btn>
    </x-slot>
</x-title>

@if(session('success'))
    <div class="al g mb">
        <span>✓ {{ session('success') }}</span>
    </div>
@endif

<div class="cols">
    {{-- Colonne Gauche : Journal, Articles & Carte --}}
    <div class="stack">
        {{-- Journal des transitions d'états --}}
        <x-card>
            <h2>Chronologie & Horodatages</h2>
            <div style="display:flex;flex-direction:column;gap:12px;margin-top:12px;">
                <div style="display:flex;justify-content:space-between;padding:8px 12px;background:#f9f6f0;border-radius:6px;">
                    <span><b>Création de la commande</b> ({{ strtoupper($order->payment_method) }})</span>
                    <span class="mut">{{ $order->created_at->format('d/m/Y H:i:s') }}</span>
                </div>
                
                <div style="display:flex;justify-content:space-between;padding:8px 12px;background:#f9f6f0;border-radius:6px;">
                    <span><b>Acceptation restaurant</b></span>
                    <span class="{{ $order->accepted_at ? '' : 'mut' }}">
                        {{ $order->accepted_at ? $order->accepted_at->format('d/m/Y H:i:s') : 'En attente' }}
                    </span>
                </div>

                <div style="display:flex;justify-content:space-between;padding:8px 12px;background:#f9f6f0;border-radius:6px;">
                    <span><b>Prête en cuisine</b></span>
                    <span class="{{ $order->ready_at ? '' : 'mut' }}">
                        {{ $order->ready_at ? $order->ready_at->format('d/m/Y H:i:s') : 'En attente' }}
                    </span>
                </div>

                <div style="display:flex;justify-content:space-between;padding:8px 12px;background:#f9f6f0;border-radius:6px;">
                    <span><b>Prise en charge par le livreur</b></span>
                    <span class="{{ $order->picked_up_at ? '' : 'mut' }}">
                        {{ $order->picked_up_at ? $order->picked_up_at->format('d/m/Y H:i:s') : 'En attente' }}
                    </span>
                </div>

                <div style="display:flex;justify-content:space-between;padding:8px 12px;background:{{ $order->delivered_at ? '#edf7ed' : '#f9f6f0' }};border-radius:6px;">
                    <span><b>Livraison effectuée au client</b></span>
                    <span class="{{ $order->delivered_at ? 'font-bold' : 'mut' }}">
                        {{ $order->delivered_at ? $order->delivered_at->format('d/m/Y H:i:s') : 'Non livrée' }}
                    </span>
                </div>

                @if($order->rejection_reason)
                    <div style="padding:10px 12px;background:#fdf2f2;border-left:4px solid #C41317;border-radius:4px;color:#C41317;">
                        <b>Motif d’annulation / refus :</b> {{ $order->rejection_reason }}
                    </div>
                @endif
            </div>
        </x-card>

        {{-- Détail des articles commandés --}}
        <x-card>
            <h2>Articles commandés</h2>
            <div style="display:flex;flex-direction:column;gap:10px;margin-top:12px;">
                @foreach ($order->items as $item)
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;padding-bottom:10px;border-bottom:1px solid #eee;">
                        <div>
                            <div style="font-weight:700;">{{ $item->quantity }}x {{ $item->product_name }}</div>
                            @if(!empty($item->options_json))
                                <div class="mut" style="font-size:0.85rem;">
                                    @foreach($item->options_json as $group => $opt)
                                        <span style="display:inline-block;background:#f0e9df;padding:2px 6px;border-radius:4px;margin-right:4px;">
                                            {{ is_array($opt) ? implode(', ', $opt) : $opt }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div style="font-weight:600;">{{ \App\Support\Money::tnd($item->total_millimes) }}</div>
                    </div>
                @endforeach
            </div>

            <div style="margin-top:16px;padding-top:12px;border-top:2px solid #ddd;">
                <div class="sum"><span>Sous-total plats</span><span>{{ \App\Support\Money::tnd($order->subtotal_millimes) }}</span></div>
                <div class="sum"><span>Frais de livraison</span><span>{{ \App\Support\Money::tnd($order->delivery_fee_millimes) }}</span></div>
                <div class="sum t" style="font-size:1.15rem;color:var(--olive);font-weight:800;margin-top:8px;">
                    <span>Total commande ({{ strtoupper($order->payment_method) }})</span>
                    <span>{{ \App\Support\Money::tnd($order->total_millimes) }}</span>
                </div>
            </div>
        </x-card>

        {{-- Incidents liés à la commande --}}
        @if($order->incidents->isNotEmpty())
            <x-card>
                <h2>⚠️ Incidents signalés sur cette commande</h2>
                <div style="display:flex;flex-direction:column;gap:10px;margin-top:10px;">
                    @foreach($order->incidents as $inc)
                        <div style="padding:12px;border:1px solid #ddd;border-radius:6px;background:{{ $inc->status === 'open' ? '#fff5f5' : '#f9f9f9' }};">
                            <div style="display:flex;justify-content:space-between;align-items:center;">
                                <b>Type : {{ ucfirst($inc->type) }}</b>
                                <x-badge :type="$inc->status === 'open' ? 'r' : 'g'">
                                    {{ $inc->status === 'open' ? 'Ouvert' : 'Résolu' }}
                                </x-badge>
                            </div>
                            <p style="margin:6px 0;font-size:0.9rem;">{{ $inc->description }}</p>
                            <div class="mut" style="font-size:0.8rem;">
                                Signalé par {{ $inc->courier?->name ?? 'Livreur' }} le {{ $inc->created_at->format('d/m/Y H:i') }}
                            </div>
                            @if($inc->resolution_notes)
                                <div style="margin-top:6px;padding:6px 8px;background:#edf7ed;border-radius:4px;font-size:0.85rem;color:#1e4620;">
                                    <b>Résolution Ops :</b> {{ $inc->resolution_notes }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </x-card>
        @endif
    </div>

    {{-- Colonne Droite : Coordonnées, Assignation & Actions d'intervention --}}
    <div class="stack">
        {{-- Coordonnées Client & Livraison --}}
        <x-card>
            <h2>Coordonnées & Destination</h2>
            <div style="font-size:0.95rem;line-height:1.6;margin-top:8px;">
                <div><b>Client :</b> {{ $order->user?->name ?? 'Client invité' }}</div>
                <div><b>Téléphone :</b> <a href="tel:{{ $order->delivery_phone }}" style="color:var(--olive);font-weight:700;">{{ $order->delivery_phone }}</a></div>
                <div><b>Ville :</b> {{ $order->delivery_city }} (Zone {{ $order->store->zone->name ?? '-' }})</div>
                <div><b>Repère / Rue :</b> {{ $order->delivery_landmark ?? 'Non précisé' }}</div>
                @if($order->delivery_building || $order->delivery_floor || $order->delivery_door)
                    <div class="mut">Bât. {{ $order->delivery_building ?? '-' }}, Étage {{ $order->delivery_floor ?? '-' }}, Porte {{ $order->delivery_door ?? '-' }}</div>
                @endif
                @if($order->customer_note)
                    <div style="margin-top:8px;padding:8px;background:#FFF4D2;border-radius:4px;font-size:0.85rem;">
                        <b>Note client :</b> {{ $order->customer_note }}
                    </div>
                @endif
                @if($order->delivery_lat && $order->delivery_lng)
                    <div style="margin-top:12px;">
                        <a class="btn ghost sm block" target="_blank" href="https://www.google.com/maps?q={{ $order->delivery_lat }},{{ $order->delivery_lng }}">
                            📍 Ouvrir position GPS exacte sur Google Maps
                        </a>
                    </div>
                @endif
            </div>
        </x-card>

        {{-- Assignation / Réassignation Livreur --}}
        <x-card>
            <h2>Livreur en charge</h2>
            @if($order->courier)
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;padding:8px 12px;background:#edf7ed;border-radius:6px;">
                    <div style="font-size:1.5rem;">🚴</div>
                    <div>
                        <b>{{ $order->courier->name }}</b>
                        <div class="mut" style="font-size:0.8rem;">
                            📞 {{ $order->courier->phone }} · Cash détenu : {{ \App\Support\Money::tnd($order->courier->getHeldCashMillimes()) }}
                        </div>
                    </div>
                </div>
            @else
                <div class="al r mb">
                    <span>⚠️ Aucun livreur n'est actuellement assigné à cette commande.</span>
                </div>
            @endif

            <form method="POST" action="{{ route('ops.orders.assign', $order->id) }}" class="stack">
                @csrf
                <label class="f">
                    <b>Affecter ou Réassigner :</b>
                    <select name="courier_id" required>
                        <option value="">Sélectionner un livreur...</option>
                        @foreach($couriers as $c)
                            @php
                                $activeCount = $c->assignedOrders->count();
                                $cashExceeded = $c->isCashLimitExceeded();
                            @endphp
                            <option value="{{ $c->id }}" {{ $order->courier_id == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} ({{ $activeCount }} en cours, Cash: {{ \App\Support\Money::tnd($c->getHeldCashMillimes()) }}) {{ $cashExceeded ? '⚠️ PLAFOND DÉPASSÉ' : '' }}
                            </option>
                        @endforeach
                    </select>
                </label>
                <button class="btn block" type="submit">Valider l’affectation</button>
            </form>
        </x-card>

        {{-- Forcer le Statut --}}
        <x-card>
            <h2>Forcer le statut (Intervention Ops)</h2>
            <form method="POST" action="{{ route('ops.orders.status', $order->id) }}" class="stack">
                @csrf
                <label class="f">
                    Nouveau statut :
                    <select name="status" required>
                        @foreach(\App\Models\Order::STATUS_LABELS as $key => $meta)
                            <option value="{{ $key }}" {{ $order->status === $key ? 'selected' : '' }}>
                                {{ $meta[0] }} ({{ $key }})
                            </option>
                        @endforeach
                    </select>
                </label>
                <x-field label="Motif ou note interne" name="reason" placeholder="Ex: Déblocage manuel client joint..." />
                <button class="btn olive block" type="submit">Appliquer le changement de statut</button>
            </form>
        </x-card>

        {{-- Annulation ou Remboursement d'urgence --}}
        <x-card>
            <h2>Annulation d’urgence & Remboursement</h2>
            <form method="POST" action="{{ route('ops.orders.cancel', $order->id) }}" class="stack">
                @csrf
                <label class="f">
                    Motif :
                    <select name="reason">
                        <option value="Client injoignable après 3 tentatives">Client injoignable (après 3 tentatives)</option>
                        <option value="Rupture totale d’ingrédients chez le restaurant">Rupture d’ingrédients restaurant</option>
                        <option value="Retard excessif (>45 min) à la demande du client">Retard excessif à la demande du client</option>
                        <option value="Accident ou incident matériel livreur">Accident / Incident matériel livreur</option>
                        <option value="Adresse de livraison hors zone couverte">Adresse de livraison hors zone</option>
                    </select>
                </label>
                <x-field label="Montant à rembourser (TND, optionnel)" name="amount" placeholder="0.000" inputmode="decimal" />
                <button class="btn red block" type="submit" onclick="return confirm('Confirmer l’annulation définitive de cette commande ?');">
                    Annuler la commande (Tracé dans l'audit)
                </button>
            </form>
        </x-card>
    </div>
</div>
@endsection
