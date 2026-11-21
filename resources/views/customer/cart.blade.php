@extends('layouts.site')
@section('title', 'Panier')

@section('content')
<x-title title="Votre panier" :sub="$store ? $store->name . ' · un seul restaurant par commande' : 'Panier vide'" />

@if(session('success'))<div class="flash ok" role="alert">{{ session('success') }}</div>@endif
@if(session('warning'))<div class="flash warn" role="alert">{{ session('warning') }}</div>@endif

@if($items->isEmpty())
    <x-card>
        <div style="text-align:center;padding:36px">
            <span style="font-size:40px">🛒</span>
            <p class="mut" style="margin-top:12px">Votre panier est vide.</p>
            <x-btn :href="route('feed')" style="margin-top:16px">Voir les restaurants</x-btn>
        </div>
    </x-card>
@else
<div class="cols">
    <x-card>
        @foreach($items as $item)
            <div class="mi" data-line>
                <div>
                    <h3>{{ $item->product->name }}</h3>
                    <span class="mut">{{ \App\Support\Money::tnd($item->product->price_millimes) }} l'unité</span>
                </div>
                <div class="row" style="gap:18px">
                    <form method="POST" action="{{ route('cart.update', $item) }}" style="display:contents">
                        @csrf @method('PATCH')
                        <div class="qty" data-qty data-price="{{ $item->product->price_millimes }}">
                            <button type="button" data-step="-" aria-label="Retirer">−</button>
                            <output aria-live="polite">{{ $item->quantity }}</output>
                            <button type="button" data-step="+" aria-label="Ajouter">+</button>
                        </div>
                    </form>
                    <strong data-line-total style="min-width:96px;text-align:right">{{ \App\Support\Money::tnd($item->line_total) }}</strong>
                    <form method="POST" action="{{ route('cart.remove', $item) }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn ghost sm" aria-label="Supprimer">🗑</button>
                    </form>
                </div>
            </div>
        @endforeach

        <label class="f">
            Remarque pour le restaurant (facultatif)
            <textarea name="note" placeholder="Sans piment, bien cuit…"></textarea>
        </label>
    </x-card>

    <x-card>
        <h2>Total</h2>
        <div class="sum"><span>Sous-total</span><span data-total>{{ \App\Support\Money::tnd($subtotal) }}</span></div>
        <div class="sum"><span>Livraison</span><span>{{ \App\Support\Money::tnd($fee) }}</span></div>
        <div class="sum t"><span>Total à payer</span><span data-grand data-fee="{{ $fee }}">{{ \App\Support\Money::tnd($total) }}</span></div>
        <p class="mut">Paiement en cash à la livraison. Ce devis est valable 10 minutes ; le prix est recalculé par nos serveurs à la confirmation.</p>
        @if($subtotal < 10000)
            <p class="mut" style="color:var(--r)">⚠️ Commande minimum : 10,000 TND</p>
        @endif
        <x-btn :href="route('checkout')" block class="glow grad-animate">Continuer vers la livraison</x-btn>
        <form method="POST" action="{{ route('cart.clear') }}" style="margin-top:8px">
            @csrf
            <button type="submit" class="btn ghost sm block">Vider le panier</button>
        </form>
    </x-card>
</div>
@endif
@endsection
