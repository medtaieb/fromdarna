@extends('layouts.portal')
@section('role', 'courier')
@section('title', 'Étapes de livraison')

@section('content')
<x-title :title="'Course #FD-' . ($id ?? 1041)" sub="Dar Zitouna → Rim K.">
    <x-slot name="actions"><x-btn variant="ghost" size="sm" :href="route('courier.incident')">Signaler un incident</x-btn></x-slot>
</x-title>

<div class="cols">
    <x-card>
        <h2>Progression</h2>
        <x-timeline :current="2" :steps="[
            ['Course acceptée', 'Confirmée à 12:35'],
            ['Arrivé au restaurant', 'Confirmée à 12:41'],
            ['Commande récupérée', 'Vérifiez le contenu et le sac scellé.'],
            ['Arrivé chez le client', 'Appelez si besoin avant de monter.'],
            ['Livrée', 'Saisissez le code du client et le cash reçu.'],
        ]" />
    </x-card>

    <div class="stack">
        <x-card>
            <h2>Étape suivante</h2>
            <button class="btn olive block" type="button">J’ai récupéré la commande</button>
        </x-card>
        <x-card>
            <h2>Remise au client</h2>
            <form method="POST" action="#" class="stack">
                @csrf
                <x-field label="Code donné par le client" name="otp" placeholder="4 chiffres" inputmode="numeric" />
                <x-field label="Cash encaissé (TND)" name="cash" placeholder="31,000" inputmode="decimal" />
                <div class="sum"><span>À encaisser</span><b>@tnd(31000)</b></div>
                <button class="btn block" type="submit">Confirmer la livraison</button>
            </form>
        </x-card>
    </div>
</div>
@endsection
