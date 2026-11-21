@extends('layouts.portal')
@section('role', 'partner')
@section('title', 'Menu et ruptures')

@section('content')
<x-title title="Menu et ruptures" sub="Un produit indisponible disparaît tout de suite de l'application client.">
    <x-slot name="actions">
        <form method="POST" action="{{ route('partner.store.toggle', $store) }}" style="display:inline">
            @csrf
            <button class="sw glow" type="submit"
                    aria-pressed="{{ $store->is_open ? 'true' : 'false' }}"
                    data-on="Prend des commandes" data-off="En pause">
                <i></i><span class="lbl">{{ $store->is_open ? 'Prend des commandes' : 'En pause' }}</span>
            </button>
        </form>
    </x-slot>
</x-title>

@if (session('info'))
    <div class="flash info" role="alert">{{ session('info') }}</div>
@endif

<x-card>
    @forelse ($store->categories as $category)
        <h2>{{ $category->name }}</h2>
        @foreach ($category->products as $item)
            <div class="mi">
                <div>
                    <h3>{{ $item->name }}</h3>
                    <span class="mut">{{ \App\Support\Money::tnd($item->price_millimes) }}</span>
                    @if ($item->description)
                        <div class="mut" style="font-size:0.85em">{{ $item->description }}</div>
                    @endif

                    {{-- Options du produit --}}
                    @if ($item->options->count())
                        <div class="mut" style="font-size:0.8em;margin-top:2px">
                            Options :
                            @foreach ($item->options as $opt)
                                {{ $opt->name }}{{ $opt->price_millimes > 0 ? ' (+' . \App\Support\Money::tnd($opt->price_millimes) . ')' : '' }}{{ $opt->is_required ? ' ⚙️' : '' }}@if(!$loop->last), @endif
                            @endforeach
                        </div>
                    @endif
                </div>
                <form method="POST" action="{{ route('partner.products.toggle', $item) }}">
                    @csrf
                    <button class="sw glow" type="submit"
                            aria-pressed="{{ $item->is_available ? 'true' : 'false' }}"
                            data-on="Disponible" data-off="En rupture">
                        <i></i><span class="lbl">{{ $item->is_available ? 'Disponible' : 'En rupture' }}</span>
                    </button>
                </form>
            </div>
        @endforeach
    @empty
        <p class="mut">Aucune catégorie de menu définie pour ce restaurant.</p>
    @endforelse
    <p class="mut">Pour changer un prix ou ajouter un plat, contactez l'équipe FromDarna : les changements sensibles sont validés par nos soins.</p>
</x-card>
@endsection
