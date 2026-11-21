@extends('layouts.site')

@section('title', $store->name . ' · FromDarna')

@section('content')
<x-title :title="$store->name" :sub="$store->kind . ' · prêt en ≈ ' . $store->eta . ' min'">
    <x-slot name="actions">
        @if ($store->is_open)<x-badge type="g">Ouvert</x-badge>@else<x-badge type="x">Fermé pour le moment</x-badge>@endif
    </x-slot>
</x-title>

<x-card>
    @forelse ($store->categories as $category)
        <h2>{{ $category->name }}</h2>
        @foreach ($category->products as $item)
            <div class="mi">
                <div>
                    <h3>{{ $item->name }}</h3>
                    @if ($item->description)<div class="mut">{{ $item->description }}</div>@endif
                    <strong>{{ \App\Support\Money::tnd($item->price_millimes) }}</strong>

                    {{-- Options du produit --}}
                    @if ($item->options && $item->options->count())
                        <div class="mut" style="margin-top:4px;font-size:0.85em">
                            @foreach ($item->options as $opt)
                                <span>
                                    {{ $opt->is_required ? '⚙️' : '+' }}
                                    {{ $opt->name }}
                                    @if ($opt->price_millimes > 0)
                                        (+{{ \App\Support\Money::tnd($opt->price_millimes) }})
                                    @endif
                                    @if (!$opt->is_available)
                                        <em style="color:var(--r)">· indisponible</em>
                                    @endif
                                </span>
                                @if (!$loop->last) · @endif
                            @endforeach
                        </div>
                    @endif
                </div>
                @if ($item->is_available && $store->is_open)
                    <div class="qty glow" data-qty data-price="{{ $item->price_millimes }}">
                        <button type="button" data-step="-" aria-label="Retirer {{ $item->name }}">−</button>
                        <output aria-live="polite">0</output>
                        <button type="button" data-step="+" aria-label="Ajouter {{ $item->name }}">+</button>
                    </div>
                @else
                    <x-badge type="x">Indisponible</x-badge>
                @endif
            </div>
        @endforeach
    @empty
        <p class="mut">Le menu de ce restaurant n'a pas encore été ajouté.</p>
    @endforelse
</x-card>

<a class="cbar glow grad-animate" href="{{ route('cart') }}">
    <span><span data-count>0</span> article(s)</span>
    <span>Voir le panier · <span data-total>0,000 TND</span></span>
</a>
@endsection
