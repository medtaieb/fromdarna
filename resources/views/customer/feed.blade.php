@extends('layouts.site')
@section('title', 'Restaurants · FromDarna' . ($activeZone ? ' ' . $activeZone->name : ''))

@section('content')
<x-title :title="$activeZone ? 'Ouverts maintenant à ' . $activeZone->name : 'Tous les restaurants'" sub="Plats du terroir, grillades et douceurs préparés par les commerces de votre ville.">
    <x-slot name="actions">
        @if($activeZone)
            <span class="bd g">📍 {{ $activeZone->name }} · Zone active</span>
        @else
            <span class="bd g">📍 Toutes les zones</span>
        @endif
    </x-slot>
</x-title>

{{-- ── Sélection de zone ────────────────────────────── --}}
<div class="chips" role="group" aria-label="Zones de livraison">
    <a class="chip {{ !$zoneSlug ? 'on' : '' }}" href="{{ route('feed', array_filter(['category' => $category !== 'all' ? $category : null])) }}">
        🗺️ Toutes les zones
    </a>
    @foreach($zones as $zone)
        <a class="chip {{ $zoneSlug === $zone->slug ? 'on' : '' }}" href="{{ route('feed', array_filter(['zone' => $zone->slug, 'category' => $category !== 'all' ? $category : null])) }}">
            {{ $zone->name }}
        </a>
    @endforeach
</div>

{{-- ── Catégories de commerces ──────────────────────── --}}
<div class="chips" role="group" aria-label="Catégories de commerces" style="margin-top:6px">
    <a class="chip {{ ($category ?? 'all') === 'all' ? 'on' : '' }}" href="{{ route('feed', array_filter(['zone' => $zoneSlug])) }}">
        Tous ({{ $stores->count() }})
    </a>
    <a class="chip {{ ($category ?? '') === 'restaurants' ? 'on' : '' }}" href="{{ route('feed', array_filter(['category' => 'restaurants', 'zone' => $zoneSlug])) }}">
        🍲 Restaurants & Grillades
    </a>
    <a class="chip {{ ($category ?? '') === 'patisseries' ? 'on' : '' }}" href="{{ route('feed', array_filter(['category' => 'patisseries', 'zone' => $zoneSlug])) }}">
        🥐 Pâtisseries & Gâteaux
    </a>
    <a class="chip {{ ($category ?? '') === 'cafes' ? 'on' : '' }}" href="{{ route('feed', array_filter(['category' => 'cafes', 'zone' => $zoneSlug])) }}">
        ☕ Cafés & Petits-déjeuners
    </a>
</div>

{{-- ── Grille de restaurants ────────────────────────── --}}
<div class="grid g3">
    @forelse ($stores as $store)
        <x-store-card :store="$store" />
    @empty
        <div style="grid-column:1/-1;text-align:center;padding:36px;border:1.5px dashed var(--line);border-radius:20px">
            <span style="font-size:32px">🔍</span>
            <p class="mut" style="margin-top:8px">Aucun établissement trouvé dans cette catégorie pour le moment.</p>
            <a href="{{ route('feed') }}" class="btn ghost sm">Afficher tous les restaurants</a>
        </div>
    @endforelse
</div>
@endsection
