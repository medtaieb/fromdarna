@extends('layouts.site')
@section('title', 'Mes commandes')

@section('content')
<x-title title="Mes commandes" />
<x-card>
    @php
        $rows = array_map(fn ($o) => [$o['id'] . '|' . $o['time'], $o['store'], \App\Support\Money::tnd($o['total']), \App\Support\Sample::badge($o['status'])], array_slice(\App\Support\Sample::orders(), 0, 4));
    @endphp
    <x-table :headers="['Commande', 'Restaurant', 'Total', 'Statut']" :rows="$rows" />
    <x-btn variant="ghost" size="sm" :href="route('orders.show', 1043)">Suivre ma dernière commande</x-btn>
</x-card>
@endsection
