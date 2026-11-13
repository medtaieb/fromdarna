@props(['store'])
<a class="sc {{ $store['open'] ? '' : 'off' }}" href="{{ route('store', $store['id']) }}" style="--c1:{{ $store['c1'] }};--c2:{{ $store['c2'] }}">
    <div class="sc-i" aria-hidden="true">{{ $store['emoji'] }}</div>
    <div class="sc-b">
        <h3>{{ $store['name'] }}</h3>
        <span class="mut">{{ $store['kind'] }}</span>
        <div class="row">
            @if ($store['open'])<x-badge type="g">Ouvert</x-badge>@else<x-badge type="x">Fermé</x-badge>@endif
            <span class="mut">≈ {{ $store['eta'] }} min · min. @tnd($store['min'])</span>
        </div>
    </div>
</a>
