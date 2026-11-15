@extends('layouts.site')
@section('title', 'Commande ' . $order->ref)

@section('content')
<x-title :title="'Commande ' . $order->ref" :sub="$order->store->name . ' · passée le ' . $order->created_at->format('d/m/Y à H:i')">
    <x-slot name="actions">
        <x-badge :type="$order->statusBadgeType()">{{ $order->statusLabel() }}</x-badge>
    </x-slot>
</x-title>

@if(session('success'))<div class="flash ok" role="alert">{{ session('success') }}</div>@endif

<div class="cols">
    <x-card>
        <h2>Où en est ma commande ?</h2>
        @php
            $steps = [
                ['PLACED']              => ['Commande reçue', 'Le restaurant a été prévenu.'],
                ['ACCEPTED']            => ['En préparation', 'Prête dans ≈ ' . ($order->store->avg_prep_minutes ?? 15) . ' min.'],
                ['READY', 'PICKED_UP']  => ['Livreur en route vers vous', 'Vous serez prévenu(e) par SMS.'],
                ['DELIVERED']           => ['Livrée', 'Donnez le code au livreur et réglez en cash.'],
            ];
            $statusOrder = ['PLACED', 'ACCEPTED', 'READY', 'PICKED_UP', 'DELIVERED'];
            $currentIdx  = array_search($order->status, $statusOrder) ?: 0;
        @endphp
        <x-timeline :current="$currentIdx" :steps="[
            ['Commande reçue', 'Le restaurant a été prévenu.'],
            ['En préparation', 'Prête dans ≈ ' . ($order->store->avg_prep_minutes ?? 15) . ' min.'],
            ['Livreur en route vers vous', 'Vous serez prévenu(e) par SMS.'],
            ['Livrée', 'Donnez le code au livreur et réglez en cash.'],
        ]" />

        @if(in_array($order->status, ['CANCELLED', 'REJECTED', 'FAILED']))
            <div class="flash warn" role="alert" style="margin-top:12px">
                Commande {{ $order->statusLabel() }}.
                @if($order->status === 'CANCELLED')Vous pouvez recommander ci-dessous.@endif
            </div>
        @endif
    </x-card>

    <div class="stack">
        @if($order->delivery_code && !in_array($order->status, ['DELIVERED', 'CANCELLED', 'REJECTED']))
        <x-card>
            <h2>Code de livraison</h2>
            <div class="code">{{ implode(' ', str_split($order->delivery_code)) }}</div>
            <p class="mut">À donner au livreur à la remise de la commande.</p>
        </x-card>
        @endif

        <x-card>
            <h2>Détail de la commande</h2>
            @foreach($order->items as $item)
                <div class="sum">
                    <span>{{ $item->quantity }} × {{ $item->product_name }}</span>
                    <span>@tnd($item->line_total_millimes)</span>
                </div>
            @endforeach
            <div class="sum"><span>Livraison ({{ $order->zone->name }})</span><span>@tnd($order->delivery_fee_millimes)</span></div>
            <div class="sum t"><span>Total payé en cash</span><span>@tnd($order->total_millimes)</span></div>

            @if($order->delivery_landmark)
            <p class="mut" style="margin-top:8px;font-size:13px">
                📍 Livré à : {{ $order->delivery_city }} · {{ $order->delivery_landmark }}
                @if($order->delivery_building) · {{ $order->delivery_building }}@endif
            </p>
            @endif
        </x-card>

        <x-btn variant="ghost" :href="'https://wa.me/' . config('cooked.whatsapp', '21620000000')">Contacter le support WhatsApp</x-btn>
        <x-btn variant="ghost" :href="route('store', $order->store_id)">Recommander chez {{ $order->store->name }}</x-btn>
    </div>
</div>
@endsection
