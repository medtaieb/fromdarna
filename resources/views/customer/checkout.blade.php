@extends('layouts.site')
@section('title', 'Confirmer la commande')

@section('content')
<x-title title="Confirmer la commande" sub="Dar Zitouna · paiement en cash à la livraison" />

<form method="POST" action="{{ route('orders.place') }}" class="cols">
    @csrf
    {{-- Clé d'idempotence : évite les doublons si le client touche deux fois --}}
    <input type="hidden" name="idempotency_key" value="{{ (string) \Illuminate\Support\Str::uuid() }}">

    <div class="stack">
        <x-card>
            <h2>Où livrer ?</h2>
            <div class="map"><span>Déplacez l’épingle sur votre porte</span></div>
            <x-field label="Repère" name="landmark" placeholder="En face de la pharmacie, portail vert…" />
            <div class="grid g3">
                <x-field label="Bâtiment" name="building" />
                <x-field label="Étage" name="floor" />
                <x-field label="Porte" name="door" />
            </div>
            <x-field label="Téléphone pour le livreur" name="phone" type="tel" placeholder="+216 20 123 456" />
        </x-card>

        <x-card>
            <h2>Paiement</h2>
            <label class="ck"><input type="radio" name="payment" value="cash" checked> Cash à la livraison</label>
            <label class="ck" style="opacity:.55"><input type="radio" name="payment" value="online" disabled> Carte, D17, Flouci <x-badge type="x">Bientôt</x-badge></label>
        </x-card>
    </div>

    <x-card>
        <h2>Récapitulatif</h2>
        <div class="sum"><span>1 × Couscous à l’agneau</span><span>@tnd(18000)</span></div>
        <div class="sum"><span>2 × Brik à l’œuf</span><span>@tnd(7000)</span></div>
        <div class="sum"><span>Livraison</span><span>@tnd(3000)</span></div>
        <div class="sum t"><span>Total à payer</span><span>@tnd(28000)</span></div>
        <button class="btn block" type="submit">Confirmer la commande</button>
    </x-card>
</form>
@endsection
