@php
    // $zones is passed from controller
@endphp
<div class="chips" role="group" aria-label="Zones de livraison">
    @foreach($zones as $zone)
        <a class="chip {{ ($zoneSlug ?? '') === $zone->slug ? 'on' : '' }}" href="{{ route('feed', array_merge(request()->except(['zone']), ['zone' => $zone->slug])) }}">
            {{ $zone->name }}
        </a>
    @endforeach
</div>