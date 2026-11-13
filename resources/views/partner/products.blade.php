@extends('layouts.portal')
@section('role', 'partner')
@section('title', 'Menu et ruptures')

@section('content')
<x-title title="Menu et ruptures" sub="Un produit indisponible disparaît tout de suite de l’application client.">
    <x-slot name="actions">
        <button class="sw" type="button" aria-pressed="true" data-on="Prend des commandes" data-off="En pause"><i></i><span class="lbl">Prend des commandes</span></button>
    </x-slot>
</x-title>

<x-card>
    @foreach (\App\Support\Sample::menu(1) as $category => $items)
        <h2>{{ $category }}</h2>
        @foreach ($items as $item)
            <div class="mi">
                <div><h3>{{ $item['name'] }}</h3><span class="mut">@tnd($item['price'])</span></div>
                <button class="sw" type="button" aria-pressed="{{ $item['ok'] ? 'true' : 'false' }}" data-on="Disponible" data-off="En rupture"><i></i><span class="lbl">{{ $item['ok'] ? 'Disponible' : 'En rupture' }}</span></button>
            </div>
        @endforeach
    @endforeach
    <p class="mut">Pour changer un prix ou ajouter un plat, contactez l’équipe Cooked by Us : les changements sensibles sont validés par nos soins.</p>
</x-card>
@endsection
