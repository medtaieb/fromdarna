@extends('layouts.portal')
@section('role', 'courier')
@section('title', 'Cash et gains')

@section('content')
<x-title title="Cash et gains" />

<div class="grid g4">
    <x-stat label="Cash détenu" value="46,500 TND" sub="Plafond 150,000 TND" />
    <x-stat label="Gains du jour" value="18,400 TND" sub="6 courses" />
    <x-stat label="Gains de la semaine" value="96,200 TND" />
    <x-stat label="Pourboires" value="7,000 TND" sub="100 % pour vous" />
</div>

<x-card>
    <h2>Courses du jour</h2>
    <x-table :headers="['Course', 'Base', 'Distance', 'Attente', 'Bonus', 'Gain']" :rows="[
        ['#FD-1041|12:35', '2,000 TND', '+ 0,300 TND', '+ 0,200 TND', '—', '2,500 TND'],
        ['#FD-1036|11:58', '2,000 TND', '+ 0,600 TND', '—', '+ 0,500 TND', '3,100 TND'],
        ['#FD-1030|11:20', '2,000 TND', '+ 0,300 TND', '—', '—', '2,300 TND'],
    ]" />
    <p class="mut">Grille : 2,000 TND par course, 0,300 TND par km au-delà de 1 km, 0,100 TND par minute d’attente après 5 min.</p>
</x-card>

<x-card>
    <h2>Remise du cash</h2>
    <p class="mut">Déposez votre cash chaque jour au point de dépôt de Grombalia. L’équipe Ops enregistre la remise et rapproche tout écart avec vous.</p>
    <x-badge type="o">Remise du jour : en attente</x-badge>
</x-card>
@endsection
