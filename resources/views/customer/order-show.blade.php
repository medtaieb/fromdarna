@extends('layouts.site')
@section('title', 'Commande ' . $order->ref)

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #customer-tracking-map {
        height: 270px;
        width: 100%;
        border-radius: 16px;
        border: 1.5px solid var(--line);
        box-shadow: 0 4px 14px rgba(0,0,0,0.06);
        margin: 14px 0 6px;
        z-index: 1;
    }
</style>
@endpush

@section('content')
<x-title :title="'Commande ' . $order->ref" :sub="$order->store->name . ' · passée le ' . $order->created_at->format('d/m/Y à H:i')">
    <x-slot name="actions">
        <x-badge :type="$order->statusBadgeType()" id="order-status-badge">{{ $order->statusLabel() }}</x-badge>
    </x-slot>
</x-title>

@if(session('success'))<div class="flash ok" role="alert">{{ session('success') }}</div>@endif

<div class="cols">
    <x-card>
        <h2>Où en est ma commande ?</h2>
        @php
            $statusOrder = ['PLACED', 'ACCEPTED', 'READY', 'PICKED_UP', 'DELIVERED'];
            $currentIdx  = array_search($order->status, $statusOrder);
            if ($currentIdx === false) $currentIdx = 0;
        @endphp
        <x-timeline :current="$currentIdx" :steps="[
            ['Commande reçue', 'Le restaurant a été prévenu.'],
            ['En préparation', 'Prête dans ≈ ' . ($order->store->avg_prep_minutes ?? 15) . ' min.'],
            ['Livreur en route vers vous', 'Vous serez prévenu(e) par SMS.'],
            ['Livrée', 'Donnez le code au livreur et réglez en cash.'],
        ]" />

        {{-- ── Carte de suivi GPS en direct ── --}}
        <div style="margin-top:20px;border-top:1px dashed var(--line);padding-top:16px">
            <div style="display:flex;justify-content:space-between;align-items:center">
                <h3 style="margin:0;font-size:16px">🗺️ Suivi de votre livraison sur la carte</h3>
                <span class="bd g" id="courier-live-pill" style="{{ $order->courier ? '' : 'display:none' }}">
                    🛵 Livreur en direct
                </span>
            </div>

            <div id="customer-tracking-map"></div>

            <div id="courier-info-bar" style="display:flex;justify-content:space-between;align-items:center;padding:8px 12px;background:var(--bg2);border-radius:10px;font-size:12.5px;margin-top:6px;{{ $order->courier ? '' : 'display:none' }}">
                <span>Livreur assigné : <strong id="courier-name">{{ $order->courier?->name ?? 'En attente' }}</strong></span>
                <span class="mut" id="courier-ping">Position mise à jour en direct</span>
            </div>
        </div>

        @if(in_array($order->status, ['CANCELLED', 'REJECTED', 'FAILED']))
            <div class="flash warn" role="alert" style="margin-top:12px">
                Commande {{ $order->statusLabel() }}.
                @if($order->status === 'CANCELLED')Vous pouvez recommander ci-dessous.@endif
            </div>
        @endif
    </x-card>

    <div class="stack">
        @if($order->delivery_code && !in_array($order->status, ['DELIVERED', 'CANCELLED', 'REJECTED']))
        <x-card>
            <h2>Code de livraison</h2>
            <div class="code text-grad">{{ implode(' ', str_split($order->delivery_code)) }}</div>
            <p class="mut">À donner au livreur à la remise de la commande.</p>
        </x-card>
        @endif

        <x-card>
            <h2>Détail de la commande</h2>
            @foreach($order->items as $item)
                <div class="sum">
                    <span>{{ $item->quantity }} × {{ $item->product_name }}</span>
                    <span>@tnd($item->line_total_millimes)</span>
                </div>
            @endforeach
            <div class="sum"><span>Livraison ({{ $order->zone->name }})</span><span>@tnd($order->delivery_fee_millimes)</span></div>
            <div class="sum t"><span>Total payé en cash</span><span>@tnd($order->total_millimes)</span></div>

            @if($order->delivery_landmark)
            <p class="mut" style="margin-top:8px;font-size:13px">
                📍 Livré à : {{ $order->delivery_city }} · {{ $order->delivery_landmark }}
                @if($order->delivery_building) · {{ $order->delivery_building }}@endif
            </p>
            @endif
        </x-card>

        <x-btn variant="ghost" :href="'https://wa.me/' . config('cooked.whatsapp', '21620000000')">Contacter le support WhatsApp</x-btn>
        <x-btn variant="ghost" :href="route('store', $order->store_id)" class="glow">Recommander chez {{ $order->store->name }}</x-btn>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const sLat = {{ (float)($order->store->latitude ?? 36.6175) }};
    const sLng = {{ (float)($order->store->longitude ?? 10.4990) }};
    const cLat = {{ (float)($order->delivery_lat ?? 36.6191) }};
    const cLng = {{ (float)($order->delivery_lng ?? 10.5012) }};

    const map = L.map('customer-tracking-map', { scrollWheelZoom: false }).setView([cLat, cLng], 14);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap'
    }).addTo(map);

    const storeIcon = L.divIcon({
        className: 'tracking-icon',
        html: '<div style="font-size:28px;line-height:1;filter:drop-shadow(0 3px 5px rgba(0,0,0,0.3))">🍽️</div>',
        iconSize: [28, 28], iconAnchor: [14, 26]
    });

    const custIcon = L.divIcon({
        className: 'tracking-icon',
        html: '<div style="font-size:32px;line-height:1;filter:drop-shadow(0 3px 5px rgba(0,0,0,0.3))">🏠</div>',
        iconSize: [32, 32], iconAnchor: [16, 30]
    });

    const courierIcon = L.divIcon({
        className: 'tracking-icon',
        html: '<div style="font-size:32px;line-height:1;filter:drop-shadow(0 4px 6px rgba(196,19,23,0.5))">🛵</div>',
        iconSize: [32, 32], iconAnchor: [16, 28]
    });

    const mStore = L.marker([sLat, sLng], { icon: storeIcon }).addTo(map).bindPopup("<b>{{ addslashes($order->store->name) }}</b>");
    const mCust  = L.marker([cLat, cLng], { icon: custIcon }).addTo(map).bindPopup("<b>Votre adresse de livraison</b>");
    let mCourier = null;
    let trackLine = null;

    // Tracé direct initial
    const boundsGroup = [mStore, mCust];

    // Ajuster vue
    map.fitBounds(L.featureGroup(boundsGroup).getBounds().pad(0.2));

    // Polling en temps réel pour suivre le livreur
    function pollTracking() {
        fetch("{{ route('orders.tracking.live', $order) }}")
            .then(res => res.json())
            .then(data => {
                if (data.status_label) {
                    const badge = document.getElementById('order-status-badge');
                    if (badge) badge.textContent = data.status_label;
                }

                if (data.courier && data.courier.lat && data.courier.lng) {
                    const courierLat = data.courier.lat;
                    const courierLng = data.courier.lng;

                    document.getElementById('courier-live-pill').style.display = 'inline-block';
                    document.getElementById('courier-info-bar').style.display = 'flex';
                    document.getElementById('courier-name').textContent = data.courier.name;
                    document.getElementById('courier-ping').textContent = 'Signal : ' + data.courier.last_ping;

                    if (!mCourier) {
                        mCourier = L.marker([courierLat, courierLng], { icon: courierIcon })
                            .addTo(map)
                            .bindPopup(`<b>🛵 ${data.courier.name}</b><br>Livreur FromDarna en route`);
                    } else {
                        mCourier.setLatLng([courierLat, courierLng]);
                    }

                    // Mettre à jour le tracé pointillé
                    if (trackLine) map.removeLayer(trackLine);
                    trackLine = L.polyline([[courierLat, courierLng], [cLat, cLng]], {
                        color: '#C41317',
                        weight: 4,
                        dashArray: '8, 8',
                        opacity: 0.8
                    }).addTo(map);
                }
            })
            .catch(err => console.log("Polling suivi", err));
    }

    pollTracking();
    setInterval(pollTracking, 5000);
});
</script>
@endsection
