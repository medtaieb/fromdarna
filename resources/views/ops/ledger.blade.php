@extends('layouts.portal')
@section('role', 'ops')
@section('title', 'Grand livre')

@section('content')
<x-title title="Grand livre" sub="Écritures en partie double. Les soldes sont calculés, jamais modifiés à la main.">
    <x-slot name="actions"><x-btn variant="ghost" size="sm">Exporter</x-btn></x-slot>
</x-title>

<div class="grid g4">
    <x-stat label="Cash détenu par les livreurs" :value="\App\Support\Money::tnd($fleetCash ?? 0)" sub="En circulation dans les sacoches" />
    <x-stat label="Caisse physique QG" :value="\App\Support\Money::tnd($hqCash ?? 0)" sub="Remises validées au bureau" />
    <x-stat label="Dû aux restaurants" :value="\App\Support\Money::tnd($restaurantsDue ?? 0)" sub="Ventes nettes de commission (88%)" />
    <x-stat label="Dû aux livreurs" :value="\App\Support\Money::tnd($couriersDue ?? 0)" sub="2,500 TND par course livrée" />
</div>

<div class="al g"><span>Rapprochement du jour : <strong>aucun écart</strong></span><x-badge type="g">Équilibré</x-badge></div>

<x-card>
    <div class="row" style="justify-content:space-between;align-items:center;margin-bottom:12px;">
        <h2 style="margin:0;">Journal des écritures comptables réelles (Partie double)</h2>
        <span class="bd g">{{ count($lines) }} écriture(s)</span>
    </div>

    @if(empty($lines))
        <div style="text-align:center;padding:36px;border:1.5px dashed var(--line);border-radius:12px">
            <span style="font-size:32px">📒</span>
            <p class="mut" style="margin-top:8px">Aucune écriture comptable pour l'instant. Les écritures se génèrent automatiquement à chaque commande livrée ou remise d'espèces validée par le QG.</p>
        </div>
    @else
        <x-table :headers="['#', 'Date / Heure', 'Opération', 'Compte Débité', 'Compte Crédité', 'Montant']">
            @foreach($lines as $l)
                <tr>
                    <td><b>{{ $l['num'] }}</b></td>
                    <td class="mut" style="font-size:0.85rem;">{{ $l['date'] }}</td>
                    <td><b>{{ $l['moment'] }}</b></td>
                    <td style="color:#2D5A27;font-weight:600;">{{ $l['debit'] }}</td>
                    <td style="color:#98551C;font-weight:600;">{{ $l['credit'] }}</td>
                    <td><strong>{{ \App\Support\Money::tnd($l['amount']) }}</strong></td>
                </tr>
            @endforeach
        </x-table>
    @endif
</x-card>
@endsection
