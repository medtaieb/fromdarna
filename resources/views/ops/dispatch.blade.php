@extends('layouts.portal')
@section('role', 'ops')
@section('title', 'Dispatch & Suivi en temps réel')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #dispatch-map {
        height: 440px;
        width: 100%;
        border-radius: 18px;
        border: 1.5px solid var(--line);
        box-shadow: 0 6px 20px rgba(0,0,0,0.07);
        z-index: 1;
    }
    .dispatch-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
        margin-bottom: 16px;
    }
    .live-pulse {
        display: inline-block;
        width: 10px;
        height: 10px;
        background: #28a745;
        border-radius: 50%;
        margin-right: 6px;
        box-shadow: 0 0 0 rgba(40,167,69, 0.6);
        animation: pulse-dot 1.6s infinite;
    }
    @keyframes pulse-dot {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(40,167,69, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(40,167,69, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(40,167,69, 0); }
    }
    .courier-label {
        background: var(--card);
        border: 1px solid var(--line);
        border-radius: 6px;
        padding: 2px 6px;
        font-size: 11px;
        font-weight: 700;
        box-shadow: 0 2px 5px rgba(0,0,0,0.15);
    }
</style>
@endpush

@section('content')
<x-title title="Dispatch & Flotte en direct" sub="Supervision en temps réel des livreurs, tracés GPS et temps des livraisons.">
    <x-slot name="actions">
        <span class="bd g"><span class="live-pulse"></span>Flux GPS Direct (Sync 4s)</span>
        <x-badge type="o">{{ $ordersToAssign->count() }} à affecter</x-badge>
    </x-slot>
</x-title>

@if(session('success'))<div class="flash ok" role="alert">{{ session('success') }}</div>@endif

{{-- ── Statistiques de supervision rapide ── --}}
<div class="dispatch-stats">
    <x-card class="glow">
        <div class="mut" style="font-size:13px">Livreurs en ligne</div>
        <div style="font-size:26px;font-weight:800;color:var(--olive);margin-top:4px" id="stat-couriers-count">
            {{ $couriers->count() }}
        </div>
    </x-card>
    <x-card class="glow">
        <div class="mut" style="font-size:13px">Courses en cours</div>
        <div style="font-size:26px;font-weight:800;color:var(--rust);margin-top:4px" id="stat-active-count">
            {{ $activeDeliveries->count() }}
        </div>
    </x-card>
    <x-card class="glow">
        <div class="mut" style="font-size:13px">À affecter</div>
        <div style="font-size:26px;font-weight:800;color:var(--tomate);margin-top:4px">
            {{ $ordersToAssign->count() }}
        </div>
    </x-card>
    <x-card>
        <div class="mut" style="font-size:13px">Dernière mise à jour</div>
        <div style="font-size:15px;font-weight:700;margin-top:8px" id="stat-last-sync">
            À l'instant
        </div>
    </x-card>
</div>

{{-- ── 1. CARTE EN TEMPS RÉEL (TOUS LES LIVREURS & LIVRAISONS) ── --}}
<x-card style="margin-bottom:18px">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
        <h2>🗺️ Carte de surveillance des livreurs</h2>
        <div style="font-size:13px" class="mut">
            🛵 = Livreur &nbsp;·&nbsp; 🍽️ = Restaurant &nbsp;·&nbsp; 🏠 = Client
        </div>
    </div>
    <div id="dispatch-map"></div>
</x-card>

{{-- ── 2. TABLE DES LIVRAISONS & TEMPS ÉCOULÉ EN DIRECT ── --}}
<div class="cols">
    <x-card>
        <h2>⏱️ Livraisons en cours & Temps en temps réel</h2>
        @if($activeDeliveries->count() || $ordersToAssign->count())
            <table style="width:100%;border-collapse:collapse;margin-top:10px;font-size:13.5px">
                <thead>
                    <tr style="border-bottom:1.5px solid var(--line);text-align:left">
                        <th style="padding:8px 4px">Commande</th>
                        <th style="padding:8px 4px">Itinéraire</th>
                        <th style="padding:8px 4px">Temps écoulé</th>
                        <th style="padding:8px 4px">Livreur</th>
                        <th style="padding:8px 4px">Statut</th>
                    </tr>
                </thead>
                <tbody id="orders-tbody">
                    {{-- Commandes avec livreur --}}
                    @foreach($activeDeliveries as $ord)
                        @php
                            $mins = $ord->created_at->diffInMinutes(now());
                            $urgent = $mins > 25;
                        @endphp
                        <tr style="border-bottom:1px solid var(--line)">
                            <td style="padding:10px 4px">
                                <strong>#{{ $ord->ref }}</strong>
                                <div class="mut" style="font-size:11px">{{ \App\Support\Money::tnd($ord->total_millimes) }}</div>
                            </td>
                            <td style="padding:10px 4px">
                                <div>🍽️ {{ $ord->store->name }}</div>
                                <div class="mut" style="font-size:11.5px">➔ 🏠 {{ $ord->delivery_landmark ?? $ord->delivery_city }}</div>
                            </td>
                            <td style="padding:10px 4px">
                                <span class="bd {{ $urgent ? 'r pulse' : 'o' }}">
                                    ⏱️ {{ $mins }} min
                                </span>
                            </td>
                            <td style="padding:10px 4px">
                                <b>🛵 {{ $ord->courier->name ?? 'Livreur' }}</b>
                                @if($ord->courier?->phone)
                                    <div class="mut" style="font-size:11px">{{ $ord->courier->phone }}</div>
                                @endif
                            </td>
                            <td style="padding:10px 4px">
                                <x-badge :type="$ord->statusBadgeType()">{{ $ord->statusLabel() }}</x-badge>
                            </td>
                        </tr>
                    @endforeach

                    {{-- Commandes à affecter --}}
                    @foreach($ordersToAssign as $ord)
                        @php $mins = $ord->created_at->diffInMinutes(now()); @endphp
                        <tr style="border-bottom:1px solid var(--line);background:rgba(196,19,23,0.03)">
                            <td style="padding:10px 4px">
                                <strong style="color:var(--tomate)">#{{ $ord->ref }}</strong>
                                <div class="mut" style="font-size:11px">{{ \App\Support\Money::tnd($ord->total_millimes) }}</div>
                            </td>
                            <td style="padding:10px 4px">
                                <div>🍽️ {{ $ord->store->name }}</div>
                                <div class="mut" style="font-size:11.5px">➔ 🏠 {{ $ord->delivery_landmark ?? $ord->delivery_city }}</div>
                            </td>
                            <td style="padding:10px 4px">
                                <span class="bd r pulse">⏱️ {{ $mins }} min</span>
                            </td>
                            <td style="padding:10px 4px" colspan="2">
                                <form method="POST" action="{{ route('ops.orders.assign', $ord) }}" style="display:flex;gap:6px">
                                    @csrf
                                    <select name="courier_id" class="f" style="padding:4px 8px;font-size:12px;border-radius:8px" required>
                                        <option value="">Choisir un livreur...</option>
                                        @foreach($couriers as $cr)
                                            <option value="{{ $cr->id }}">{{ $cr->name }} ({{ $cr->assignedOrders->count() }} courses)</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn sm olive" style="white-space:nowrap">Affecter</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="mut" style="padding:24px;text-align:center">Aucune livraison active pour le moment.</p>
        @endif
    </x-card>

    <x-card>
        <h2>🛵 Flotte des Livreurs ({{ $couriers->count() }})</h2>
        <div style="display:flex;flex-direction:column;gap:10px;margin-top:10px">
            @foreach($couriers as $c)
                @php
                    $loc = $c->courierLocation;
                    $activeCount = $c->assignedOrders->count();
                @endphp
                <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px;background:var(--bg2);border:1px solid var(--line);border-radius:12px">
                    <div>
                        <strong>{{ $c->name }}</strong>
                        <div class="mut" style="font-size:12px">
                            📞 {{ $c->phone }} · 🔋 {{ $loc?->battery ?? 85 }}%
                        </div>
                    </div>
                    <div style="text-align:right">
                        <span class="bd {{ $activeCount > 0 ? 'g' : 'x' }}">
                            {{ $activeCount }} course(s)
                        </span>
                        <div class="mut" style="font-size:11px;margin-top:3px">
                            GPS : {{ $loc ? round($loc->latitude, 3) . ', ' . round($loc->longitude, 3) : 'Inactif' }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </x-card>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Initialiser la carte Leaflet sur la région de Grombalia
    const map = L.map('dispatch-map', { scrollWheelZoom: false }).setView([36.6191, 10.5012], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap'
    }).addTo(map);

    // Conteneurs de marqueurs en mémoire pour les mises à jour sans rechargement
    const courierMarkers = {};
    const orderMarkers = {};
    const routeLines = [];

    // Fabrique d'icônes
    function makeIcon(emoji, bg = '#fff') {
        return L.divIcon({
            className: 'custom-dispatch-icon',
            html: `<div style="font-size:28px;line-height:1;filter:drop-shadow(0 3px 6px rgba(0,0,0,0.3));cursor:pointer">${emoji}</div>`,
            iconSize: [28, 28],
            iconAnchor: [14, 26]
        });
    }

    // 2. Fonction de rafraîchissement temps réel (Polling AJAX)
    function fetchLiveData() {
        fetch("{{ route('ops.dispatch.live') }}")
            .then(res => res.json())
            .then(data => {
                document.getElementById('stat-last-sync').textContent = new Date().toLocaleTimeString('fr-FR');
                if (data.couriers) {
                    document.getElementById('stat-couriers-count').textContent = data.couriers.length;
                }
                if (data.orders) {
                    document.getElementById('stat-active-count').textContent = data.orders.length;
                }

                // 2.1 Mettre à jour les livreurs sur la carte
                data.couriers.forEach(c => {
                    const lat = parseFloat(c.lat);
                    const lng = parseFloat(c.lng);
                    const popupContent = `
                        <b>🛵 ${c.name}</b><br>
                        📞 ${c.phone}<br>
                        🔋 Batterie : ${c.battery}%<br>
                        ⚡ En cours : <b>${c.active_orders} livraison(s)</b><br>
                        <small class="mut">Dernier signal : ${c.last_ping}</small>
                    `;

                    if (courierMarkers[c.id]) {
                        // Animation douce vers la nouvelle position
                        courierMarkers[c.id].setLatLng([lat, lng]);
                        courierMarkers[c.id].setPopupContent(popupContent);
                    } else {
                        const m = L.marker([lat, lng], { icon: makeIcon('🛵') })
                            .addTo(map)
                            .bindPopup(popupContent);
                        courierMarkers[c.id] = m;
                    }
                });

                // 2.2 Mettre à jour les restaurants et clients des commandes
                // Nettoyer les tracés précédents
                routeLines.forEach(l => map.removeLayer(l));
                routeLines.length = 0;

                data.orders.forEach(o => {
                    const sLat = o.store.lat;
                    const sLng = o.store.lng;
                    const cLat = o.customer.lat;
                    const cLng = o.customer.lng;

                    // Marqueur resto
                    if (!orderMarkers['s_' + o.id]) {
                        orderMarkers['s_' + o.id] = L.marker([sLat, sLng], { icon: makeIcon('🍽️') })
                            .addTo(map)
                            .bindPopup(`<b>🍽️ ${o.store.name}</b><br>Commande #${o.ref}`);
                    }

                    // Marqueur client
                    if (!orderMarkers['c_' + o.id]) {
                        orderMarkers['c_' + o.id] = L.marker([cLat, cLng], { icon: makeIcon('🏠') })
                            .addTo(map)
                            .bindPopup(`<b>🏠 Livraison #${o.ref}</b><br>${o.customer.landmark || o.customer.city}<br>Temps : ${o.elapsed_minutes} min`);
                    }

                    // Tracé pointillé entre resto et client
                    const line = L.polyline([[sLat, sLng], [cLat, cLng]], {
                        color: '#98551C',
                        weight: 3,
                        dashArray: '6, 6',
                        opacity: 0.7
                    }).addTo(map);
                    routeLines.push(line);
                });
            })
            .catch(err => console.error("Erreur sync live ops", err));
    }

    // Premier chargement immédiat
    fetchLiveData();

    // Rafraîchissement automatique toutes les 4 secondes
    setInterval(fetchLiveData, 4000);
});
</script>
@endsection
