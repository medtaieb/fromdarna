@extends('layouts.portal')
@section('role', 'courier')
@section('title', 'Course ' . ($order?->ref ?? '#FD-1041'))

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #courier-map {
        height: 320px;
        width: 100%;
        border-radius: 16px;
        border: 1.5px solid var(--line);
        box-shadow: 0 4px 14px rgba(0,0,0,0.06);
        margin: 12px 0;
        z-index: 1;
    }
    .nav-buttons {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 8px;
    }
</style>
@endpush

@section('content')
@php
    $storeLat = (float)($order?->store->latitude ?? 36.6175);
    $storeLng = (float)($order?->store->longitude ?? 10.4990);
    $custLat  = (float)($order?->delivery_lat ?? 36.6191);
    $custLng  = (float)($order?->delivery_lng ?? 10.5012);
    $cLat     = (float)($courierLocation?->latitude ?? 36.6185);
    $cLng     = (float)($courierLocation?->longitude ?? 10.5020);

    // Destination actuelle selon l'étape :
    // Si la commande n'est pas encore récupérée au restaurant -> destination = restaurant
    // Dès qu'elle est récupérée (PICKED_UP) -> destination = client
    $isPickedUp = in_array($order?->status, ['PICKED_UP', 'DELIVERED']);
    $destLat = $isPickedUp ? $custLat : $storeLat;
    $destLng = $isPickedUp ? $custLng : $storeLng;
    $destLabel = $isPickedUp ? 'Client : ' . ($order?->delivery_landmark ?? 'Domicile') : 'Restaurant : ' . ($order?->store->name ?? 'Point de retrait');
@endphp

<x-title :title="'Course ' . ($order?->ref ?? '#FD-1041')" :sub="($order?->store->name ?? 'Dar Zitouna') . ' ➔ ' . ($order?->user->name ?? $order?->delivery_landmark ?? 'Client')">
    <x-slot name="actions">
        <x-badge :type="$order?->statusBadgeType() ?? 'g'">{{ $order?->statusLabel() ?? 'En cours' }}</x-badge>
        <x-btn variant="ghost" size="sm" :href="route('courier.incident')">Signaler un incident</x-btn>
    </x-slot>
</x-title>

@if(session('success'))<div class="flash ok" role="alert">{{ session('success') }}</div>@endif
@if(session('warning'))<div class="flash warn" role="alert">{{ session('warning') }}</div>@endif

<div class="cols">
    <x-card>
        <h2>🗺️ Tracé d'itinéraire en direct</h2>
        <div id="courier-map"></div>

        <div class="nav-buttons">
            <a href="https://www.google.com/maps/dir/?api=1&origin={{ $cLat }},{{ $cLng }}&destination={{ $destLat }},{{ $destLng }}&travelmode=driving"
               target="_blank" rel="noopener" class="btn block glow grad-animate" style="flex:1;text-align:center">
                🧭 Naviguer vers {{ $destLabel }} (Google Maps)
            </a>
            @if($order?->delivery_phone)
                <a href="tel:{{ $order->delivery_phone }}" class="btn ghost sm" style="display:flex;align-items:center;gap:6px">
                    📞 Appeler client ({{ $order->delivery_phone }})
                </a>
            @endif
        </div>

        <div style="margin-top:14px;padding:10px 14px;background:var(--bg2);border-radius:12px;font-size:13px;display:flex;justify-content:space-between">
            <span>🛵 <strong>Ma position GPS :</strong> <span id="courier-coords-text">{{ round($cLat, 5) }}, {{ round($cLng, 5) }}</span></span>
            <span class="bd g" id="gps-status-badge">📡 GPS Actif</span>
        </div>
    </x-card>

    <div class="stack">
        <x-card>
            <h2>Progression de la course</h2>
            @php
                $statusIndex = 0;
                if ($order?->status === 'ACCEPTED')  $statusIndex = 1;
                if ($order?->status === 'READY')     $statusIndex = 2;
                if ($order?->status === 'PICKED_UP') $statusIndex = 3;
                if ($order?->status === 'DELIVERED') $statusIndex = 4;
            @endphp
            <x-timeline :current="$statusIndex" :steps="[
                ['Course reçue', 'Assignée au livreur.'],
                ['En route vers restaurant', $order?->store->name ?? 'Retrait'],
                ['Prête au comptoir', 'Vérifiez le ticket et les sacs scellés.'],
                ['En route vers le client', $order?->delivery_city . ' · ' . $order?->delivery_landmark],
                ['Livrée', 'Code client validé & cash encaissé.'],
            ]" />
        </x-card>

        @if($order && $order->status !== 'DELIVERED')
            @if($order->status !== 'PICKED_UP')
                <x-card>
                    <h2>Étape suivante</h2>
                    <form method="POST" action="{{ route('courier.order.status', $order) }}">
                        @csrf
                        <input type="hidden" name="status" value="PICKED_UP">
                        <button class="btn olive block glow" type="submit">
                            🛍️ J'ai récupéré la commande au restaurant
                        </button>
                    </form>
                </x-card>
            @else
                <x-card>
                    <h2>Remise au client</h2>
                    <form method="POST" action="{{ route('courier.order.status', $order) }}" class="stack">
                        @csrf
                        <input type="hidden" name="status" value="DELIVERED">
                        <x-field label="Code de livraison donné par le client *" name="otp" placeholder="4 chiffres (ex: {{ $order->delivery_code }})" inputmode="numeric" required />
                        <div class="sum t"><span>Cash à encaisser</span><span>{{ \App\Support\Money::tnd($order->total_millimes) }}</span></div>
                        <button class="btn block glow grad-animate" type="submit">
                            ✅ Valider le code et terminer la livraison
                        </button>
                    </form>
                </x-card>
            @endif
        @else
            <x-card>
                <div style="text-align:center;padding:18px">
                    <span style="font-size:36px">🎉</span>
                    <h3>Course terminée et encaissée</h3>
                    <x-btn :href="route('courier.dashboard')" style="margin-top:10px">Retour au tableau de bord</x-btn>
                </div>
            </x-card>
        @endif
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const sLat = {{ $storeLat }};
    const sLng = {{ $storeLng }};
    const cLat = {{ $custLat }};
    const cLng = {{ $custLng }};
    let courierLat = {{ $cLat }};
    let courierLng = {{ $cLng }};

    // 1. Initialiser la carte Leaflet
    const map = L.map('courier-map', { scrollWheelZoom: false }).setView([courierLat, courierLng], 14);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap'
    }).addTo(map);

    // Icônes personnalisées
    const storeIcon = L.divIcon({
        className: 'map-icon-store',
        html: '<div style="font-size:30px;line-height:1;filter:drop-shadow(0 4px 6px rgba(0,0,0,0.3))">🍽️</div>',
        iconSize: [30, 30], iconAnchor: [15, 28]
    });

    const custIcon = L.divIcon({
        className: 'map-icon-cust',
        html: '<div style="font-size:32px;line-height:1;filter:drop-shadow(0 4px 6px rgba(0,0,0,0.3))">🏠</div>',
        iconSize: [32, 32], iconAnchor: [16, 30]
    });

    const courierIcon = L.divIcon({
        className: 'map-icon-courier',
        html: '<div style="font-size:34px;line-height:1;filter:drop-shadow(0 4px 8px rgba(196,19,23,0.5))">🛵</div>',
        iconSize: [34, 34], iconAnchor: [17, 30]
    });

    // Marqueurs
    const mStore = L.marker([sLat, sLng], { icon: storeIcon }).addTo(map).bindPopup("<b>Restaurant :</b> {{ addslashes($order?->store->name ?? 'Restaurant') }}");
    const mCust  = L.marker([cLat, cLng], { icon: custIcon }).addTo(map).bindPopup("<b>Livraison :</b> {{ addslashes($order?->delivery_landmark ?? 'Client') }}");
    const mCourier = L.marker([courierLat, courierLng], { icon: courierIcon }).addTo(map).bindPopup("<b>Ma position</b>");

    // Tracé d'itinéraire (polyline colorée FromDarna)
    const routeLine = L.polyline([
        [courierLat, courierLng],
        [sLat, sLng],
        [cLat, cLng]
    ], {
        color: '#98551C',
        weight: 5,
        opacity: 0.85,
        dashArray: '8, 8',
        lineCap: 'round'
    }).addTo(map);

    // Ajuster le zoom pour englober tous les points
    map.fitBounds(L.featureGroup([mStore, mCust, mCourier]).getBounds().pad(0.15));

    // 2. Diffusion en direct de la position GPS du livreur (Background Tracking)
    if (navigator.geolocation) {
        navigator.geolocation.watchPosition(
            function (pos) {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                const heading = pos.coords.heading || 0;
                const speed = pos.coords.speed ? (pos.coords.speed * 3.6) : 0;

                // Mettre à jour le marqueur sur la carte
                mCourier.setLatLng([lat, lng]);
                routeLine.setLatLngs([[lat, lng], [sLat, sLng], [cLat, cLng]]);

                document.getElementById('courier-coords-text').textContent = lat.toFixed(5) + ', ' + lng.toFixed(5);

                // Envoyer au serveur FromDarna (API ping)
                fetch("{{ route('courier.location.ping') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        latitude: lat,
                        longitude: lng,
                        heading: heading,
                        speed: speed
                    })
                }).catch(err => console.log("Ping GPS silencieux", err));
            },
            function (err) {
                console.log("Erreur GPS livreur:", err);
                const badge = document.getElementById('gps-status-badge');
                if (badge) { badge.className = 'bd o'; badge.textContent = '📡 GPS Simulé'; }
            },
            { enableHighAccuracy: true, maximumAge: 10000, timeout: 8000 }
        );
    }
});
</script>
@endsection
