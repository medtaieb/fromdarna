@extends('layouts.site')
@section('title', 'Panier')

@php
    $lines = [['Couscous à l’agneau', 18000, 1], ['Brik à l’œuf', 3500, 2]];
    $fee = 3000;
@endphp

@section('content')
<x-title title="Votre panier" sub="Dar Zitouna · un seul restaurant par commande" />

<div class="cols">
    <x-card>
        @foreach ($lines as $l)
            <div class="mi" data-line>
                <div><h3>{{ $l[0] }}</h3><span class="mut">@tnd($l[1]) l’unité</span></div>
                <div class="row" style="gap:18px">
                    <div class="qty" data-qty data-price="{{ $l[1] }}">
                        <button type="button" data-step="-" aria-label="Retirer">−</button>
                        <output>{{ $l[2] }}</output>
                        <button type="button" data-step="+" aria-label="Ajouter">+</button>
                    </div>
                    <strong data-line-total style="min-width:96px;text-align:right"></strong>
                </div>
            </div>
        @endforeach
        <label class="f">Remarque pour le restaurant (facultatif)<textarea name="note" placeholder="Sans piment, bien cuit…"></textarea></label>
    </x-card>

    <x-card>
        <h2>Total</h2>
        <div class="sum"><span>Sous-total</span><span data-total></span></div>
        <div class="sum"><span>Livraison</span><span>@tnd($fee)</span></div>
        <div class="sum t"><span>Total à payer</span><span data-grand data-fee="{{ $fee }}"></span></div>
        <p class="mut">Paiement en cash à la livraison. Ce devis est valable 10 minutes ; le prix est recalculé par nos serveurs à la confirmation.</p>
        <p class="mut">Commande minimum : 10,000 TND, sinon 1,000 TND de frais de petit panier.</p>
        <x-btn :href="route('checkout')" block>Continuer</x-btn>
    </x-card>
</div>
@endsection
