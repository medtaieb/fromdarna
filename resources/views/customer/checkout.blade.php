@extends('layouts.site')
@section('title', 'Confirmer la commande')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #checkout-map {
        height: 280px;
        width: 100%;
        border-radius: 16px;
        border: 1.5px solid var(--line);
        box-shadow: 0 4px 14px rgba(0,0,0,0.06);
        margin: 10px 0;
        z-index: 1;
    }
    .map-controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        margin-top: 6px;
        margin-bottom: 12px;
    }
    .gps-hint {
        font-size: 12px;
        color: var(--mut);
        display: flex;
        align-items: center;
        gap: 4px;
    }
</style>
@endpush

@section('content')
<x-title title="Confirmer la commande" :sub="$store->name . ' · paiement en cash à la livraison'" />

@if($errors->any())
    <div class="flash warn" role="alert">
        <ul style="margin:0;padding-left:18px">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<form method="POST" action="{{ route('orders.place') }}" class="cols">
    @csrf
    <input type="hidden" name="idempotency_key" value="{{ (string) \Illuminate\Support\Str::uuid() }}">

    {{-- Champs cachés envoyés au contrôleur pour la position exacte --}}
    @php
        $initLat = old('delivery_lat', $savedAddress?->latitude ?? $store->latitude ?? 36.6191);
        $initLng = old('delivery_lng', $savedAddress?->longitude ?? $store->longitude ?? 10.5012);
    @endphp
    <input type="hidden" name="delivery_lat" id="delivery_lat" value="{{ $initLat }}">
    <input type="hidden" name="delivery_lng" id="delivery_lng" value="{{ $initLng }}">

    <div class="stack">
        <x-card>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px">
                <h2>Où livrer ?</h2>
                @if($user)
                    <a href="{{ route('account') }}" class="mut" style="font-size:13px;color:var(--rust);text-decoration:none">
                        Mes adresses ({{ $user->addresses()->count() }}) ➔
                    </a>
                @endif
            </div>

            @if($savedAddress)
                <div style="background:var(--bg2);border:1px solid var(--line);border-radius:12px;padding:10px 14px;margin-bottom:10px;font-size:13.5px;display:flex;align-items:center;gap:8px">
                    <span>🏠</span>
                    <span><strong>Adresse principale :</strong> {{ $savedAddress->city }} · {{ $savedAddress->landmark }}</span>
                </div>
            @endif

            {{-- Carte interactive pour sélectionner la position exacte --}}
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <label style="font-weight:600;font-size:14px">📍 Position exacte sur la carte ({{ $store->zone->name }})</label>
                <button type="button" id="btn-geoloc" class="btn sm ghost" style="padding:4px 10px;font-size:12px">
                    📡 Me géolocaliser (GPS)
                </button>
            </div>

            <div id="checkout-map"></div>

            <div class="map-controls">
                <span class="gps-hint">
                    👉 <em>Cliquez ou déplacez le repère rouge exactement sur votre porte.</em>
                </span>
                <span id="coords-display" class="bd x" style="font-size:11px;font-family:monospace">
                    {{ round($initLat, 5) }}, {{ round($initLng, 5) }}
                </span>
            </div>

            <x-field label="Repère indispensable *" name="landmark" placeholder="En face de la pharmacie, portail vert…" :value="old('landmark', $savedAddress?->landmark)" required />
            <div class="grid g3">
                <x-field label="Bâtiment" name="building" :value="old('building', $savedAddress?->building)" />
                <x-field label="Étage" name="floor" :value="old('floor', $savedAddress?->floor)" />
                <x-field label="Porte" name="door" :value="old('door', $savedAddress?->door)" />
            </div>
            <x-field label="Téléphone pour le livreur *" name="phone" type="tel" placeholder="+216 20 123 456" :value="old('phone', $savedAddress?->contact_phone ?? $user?->phone ?? '+216 20 123 456')" required />
            <textarea name="note" class="f" placeholder="Note pour le restaurant ou livreur (sans piment, code interphone…)" style="margin-top:8px">{{ old('note') }}</textarea>
        </x-card>

        <x-card>
            <h2>Paiement</h2>
            <label class="ck"><input type="radio" name="payment" value="cash" checked> Cash à la livraison (Espèces)</label>
            <label class="ck" style="opacity:.55"><input type="radio" name="payment" value="online" disabled> Carte bancaire, D17, Flouci <x-badge type="x">Bientôt</x-badge></label>
        </x-card>
    </div>

    <x-card>
        <h2>Récapitulatif — {{ $store->name }}</h2>
        @foreach($items as $item)
            <div class="sum">
                <span>{{ $item->quantity }} × {{ $item->product->name }}</span>
                <span>{{ \App\Support\Money::tnd($item->line_total) }}</span>
            </div>
        @endforeach
        <div class="sum"><span>Livraison ({{ $store->zone->name }})</span><span>{{ \App\Support\Money::tnd($fee) }}</span></div>
        <div class="sum t"><span>Total à payer</span><span>{{ \App\Support\Money::tnd($total) }}</span></div>
        <button class="btn block glow grad-animate" type="submit">Confirmer la commande (Cash)</button>
        <a href="{{ route('cart') }}" class="btn ghost sm block" style="margin-top:8px">← Modifier le panier</a>
    </x-card>
</form>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const latInput = document.getElementById('delivery_lat');
    const lngInput = document.getElementById('delivery_lng');
    const coordsDisplay = document.getElementById('coords-display');
    const btnGeoloc = document.getElementById('btn-geoloc');

    let curLat = parseFloat(latInput.value) || 36.6191;
    let curLng = parseFloat(lngInput.value) || 10.5012;

    // Initialiser la carte Leaflet
    const map = L.map('checkout-map', {
        scrollWheelZoom: false
    }).setView([curLat, curLng], 15);

    // Tuiles OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap'
    }).addTo(map);

    // Icône personnalisée FromDarna pour le client
    const customerIcon = L.divIcon({
        className: 'custom-pin',
        html: '<div style="font-size:32px;line-height:1;filter:drop-shadow(0 4px 6px rgba(0,0,0,0.3));cursor:grab">📍</div>',
        iconSize: [32, 32],
        iconAnchor: [16, 30]
    });

    // Marqueur déplaçable (draggable)
    const marker = L.marker([curLat, curLng], {
        draggable: true,
        icon: customerIcon
    }).addTo(map);

    marker.bindPopup("<b>Votre porte de livraison</b><br>Déplacez-moi si besoin").openPopup();

    function updatePos(lat, lng) {
        latInput.value = lat.toFixed(7);
        lngInput.value = lng.toFixed(7);
        coordsDisplay.textContent = lat.toFixed(5) + ', ' + lng.toFixed(5);
    }

    // Drag du marqueur
    marker.on('dragend', function (e) {
        const p = e.target.getLatLng();
        updatePos(p.lat, p.lng);
    });

    // Clic n'importe où sur la carte pour déplacer l'épingle
    map.on('click', function (e) {
        marker.setLatLng(e.latlng);
        updatePos(e.latlng.lat, e.latlng.lng);
    });

    // Bouton de géolocalisation GPS du smartphone / navigateur
    if (btnGeoloc && navigator.geolocation) {
        btnGeoloc.addEventListener('click', function () {
            btnGeoloc.disabled = true;
            btnGeoloc.textContent = '⏳ Recherche GPS...';

            navigator.geolocation.getCurrentPosition(
                function (pos) {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    map.flyTo([lat, lng], 17, { duration: 1.2 });
                    marker.setLatLng([lat, lng]);
                    updatePos(lat, lng);
                    btnGeoloc.disabled = false;
                    btnGeoloc.textContent = '✅ Position détectée';
                    setTimeout(() => { btnGeoloc.textContent = '📡 Me géolocaliser (GPS)'; }, 3000);
                },
                function (err) {
                    btnGeoloc.disabled = false;
                    btnGeoloc.textContent = '❌ GPS refusé';
                    alert("Impossible d'accéder au GPS. Veuillez glisser l'épingle manuellement sur la carte.");
                    setTimeout(() => { btnGeoloc.textContent = '📡 Me géolocaliser (GPS)'; }, 3000);
                },
                { enableHighAccuracy: true, timeout: 8000 }
            );
        });
    }
});
</script>
@endsection
