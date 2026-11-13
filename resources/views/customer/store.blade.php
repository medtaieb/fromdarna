@extends('layouts.site')

@php
    $store = collect(\App\Support\Sample::stores())->firstWhere('id', (int) ($id ?? 1)) ?? \App\Support\Sample::stores()[0];
@endphp

@section('title', $store['name'])

@section('content')
<x-title :title="$store['name']" :sub="$store['kind'] . ' · prêt en ≈ ' . $store['eta'] . ' min'">
    <x-slot name="actions">
        @if ($store['open'])<x-badge type="g">Ouvert</x-badge>@else<x-badge type="x">Fermé pour le moment</x-badge>@endif
    </x-slot>
</x-title>

<x-card>
    @foreach (\App\Support\Sample::menu($store['id']) as $category => $items)
        <h2>{{ $category }}</h2>
        @foreach ($items as $item)
            <div class="mi">
                <div>
                    <h3>{{ $item['name'] }}</h3>
                    @if ($item['desc'])<div class="mut">{{ $item['desc'] }}</div>@endif
                    <strong>@tnd($item['price'])</strong>
                </div>
                @if ($item['ok'] && $store['open'])
                    <div class="qty" data-qty data-price="{{ $item['price'] }}">
                        <button type="button" data-step="-" aria-label="Retirer {{ $item['name'] }}">−</button>
                        <output aria-live="polite">0</output>
                        <button type="button" data-step="+" aria-label="Ajouter {{ $item['name'] }}">+</button>
                    </div>
                @else
                    <x-badge type="x">Indisponible</x-badge>
                @endif
            </div>
        @endforeach
    @endforeach
</x-card>

<a class="cbar" href="{{ route('cart') }}">
    <span><span data-count>0</span> article(s)</span>
    <span>Voir le panier · <span data-total>0,000 TND</span></span>
</a>
@endsection
