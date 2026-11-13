@extends('layouts.site')
@section('title', 'Suivi de commande')

@section('content')
<x-title :title="'Commande #FD-' . ($id ?? 1043)" sub="Dar Zitouna · passée à 12:44">
    <x-slot name="actions"><x-badge type="x">En préparation</x-badge></x-slot>
</x-title>

<div class="cols">
    <x-card>
        <h2>Où en est ma commande ?</h2>
        <x-timeline :current="1" :steps="[
            ['Commande reçue', 'Le restaurant a été prévenu.'],
            ['Acceptée, en préparation', 'Prête dans ≈ 15 min.'],
            ['Livreur en route vers vous', 'Vous serez prévenu(e) par notification.'],
            ['Livrée', 'Donnez le code au livreur et réglez en cash.'],
        ]" />
    </x-card>

    <div class="stack">
        <x-card>
            <h2>Code de livraison</h2>
            <div class="code">4 8 2 1</div>
            <p class="mut">À donner au livreur à la remise de la commande.</p>
        </x-card>
        <x-card>
            <h2>Détail</h2>
            <div class="sum"><span>1 × Couscous à l’agneau</span><span>@tnd(18000)</span></div>
            <div class="sum"><span>2 × Brik à l’œuf</span><span>@tnd(7000)</span></div>
            <div class="sum"><span>Livraison</span><span>@tnd(3000)</span></div>
            <div class="sum t"><span>À payer en cash</span><span>@tnd(28000)</span></div>
        </x-card>
        <x-btn variant="ghost" :href="'https://wa.me/' . config('cooked.whatsapp')">Contacter le support WhatsApp</x-btn>
    </div>
</div>
@endsection
