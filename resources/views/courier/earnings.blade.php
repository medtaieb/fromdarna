@extends('layouts.portal')
@section('role', 'courier')
@section('title', 'Cash & Gains · ' . ($user->name ?? 'Livreur'))

@section('content')
<x-title title="Cash et gains" sub="Suivi de vos courses livrées, commissions perçues et remises de cash au QG." />

@if(session('success'))<div class="flash ok" role="alert">{{ session('success') }}</div>@endif

<div class="grid g4">
    <x-card class="glow">
        <div class="mut" style="font-size:13px">Cash détenu en main</div>
        <div style="font-size:26px;font-weight:800;color:{{ $isLimitExceeded ? 'var(--tomate)' : 'var(--rust)' }};margin-top:4px">
            {{ \App\Support\Money::tnd($heldCash) }}
        </div>
        <div class="mut" style="font-size:11.5px;margin-top:2px">Plafond : {{ \App\Support\Money::tnd($cashLimit) }}</div>
    </x-card>

    <x-card class="glow">
        <div class="mut" style="font-size:13px">Gains du jour</div>
        <div style="font-size:26px;font-weight:800;color:var(--olive);margin-top:4px">
            {{ \App\Support\Money::tnd($todayEarnings) }}
        </div>
        <div class="mut" style="font-size:11.5px;margin-top:2px">{{ $todayDeliveries->count() }} course(s) aujourd'hui</div>
    </x-card>

    <x-card>
        <div class="mut" style="font-size:13px">Gains de la semaine</div>
        <div style="font-size:24px;font-weight:800;color:#2E7D32;margin-top:4px">
            {{ \App\Support\Money::tnd($weekEarnings) }}
        </div>
        <div class="mut" style="font-size:11.5px;margin-top:2px">Semaine en cours</div>
    </x-card>

    <x-card>
        <div class="mut" style="font-size:13px">Courses totales livrées</div>
        <div style="font-size:26px;font-weight:800;margin-top:4px">
            {{ $deliveredOrders->count() }}
        </div>
        <div class="mut" style="font-size:11.5px;margin-top:2px">Depuis votre inscription</div>
    </x-card>
</div>

{{-- ── 1. Table des courses livrées ── --}}
<x-card style="margin-top:16px">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
        <h2>📦 Historique de vos courses livrées</h2>
        <span class="mut" style="font-size:13px">Grille : 2,500 TND fixe par course livrée</span>
    </div>

    @if($deliveredOrders->count())
        <table style="width:100%;border-collapse:collapse;font-size:13.5px">
            <thead>
                <tr style="border-bottom:1.5px solid var(--line);text-align:left">
                    <th style="padding:10px 8px">Course</th>
                    <th style="padding:10px 8px">Restaurant</th>
                    <th style="padding:10px 8px">Client & Destination</th>
                    <th style="padding:10px 8px">Cash encaissé</th>
                    <th style="padding:10px 8px">Votre gain</th>
                    <th style="padding:10px 8px">Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($deliveredOrders as $ord)
                    <tr style="border-bottom:1px solid var(--line)">
                        <td style="padding:12px 8px"><strong>#{{ $ord->ref }}</strong></td>
                        <td style="padding:12px 8px">🍽️ {{ $ord->store->name }}</td>
                        <td style="padding:12px 8px">
                            <b>{{ $ord->user->name ?? 'Client' }}</b>
                            <div class="mut" style="font-size:11.5px">{{ $ord->delivery_landmark ?? $ord->delivery_city }}</div>
                        </td>
                        <td style="padding:12px 8px">
                            <span class="bd o">{{ \App\Support\Money::tnd($ord->total_millimes) }}</span>
                        </td>
                        <td style="padding:12px 8px;font-weight:700;color:var(--olive)">
                            + 2,500 TND
                        </td>
                        <td style="padding:12px 8px" class="mut">
                            {{ $ord->delivered_at ? $ord->delivered_at->format('d/m/Y H:i') : $ord->created_at->format('d/m/Y H:i') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="mut" style="padding:24px;text-align:center">Aucune course terminée enregistrée pour l'instant.</p>
    @endif
</x-card>

{{-- ── 2. Historique des remises de cash au QG ── --}}
<x-card style="margin-top:16px">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
        <h2>💵 Remises de cash effectuées au QG</h2>
        <x-btn :href="route('courier.cash')" size="sm" class="glow">Déposer du cash</x-btn>
    </div>

    @if($cashDrops->count())
        <table style="width:100%;border-collapse:collapse;font-size:13.5px">
            <thead>
                <tr style="border-bottom:1.5px solid var(--line);text-align:left">
                    <th style="padding:8px">Référence reçu</th>
                    <th style="padding:8px">Montant remis</th>
                    <th style="padding:8px">Statut</th>
                    <th style="padding:8px">Date de remise</th>
                    <th style="padding:8px">Note</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cashDrops as $cd)
                    <tr style="border-bottom:1px solid var(--line)">
                        <td style="padding:10px 8px"><strong>#{{ $cd->reference }}</strong></td>
                        <td style="padding:10px 8px;font-weight:700;color:#2E7D32">{{ \App\Support\Money::tnd($cd->amount_millimes) }}</td>
                        <td style="padding:10px 8px">
                            <span class="bd g">Validée au QG</span>
                        </td>
                        <td style="padding:10px 8px" class="mut">{{ $cd->created_at->format('d/m/Y H:i') }}</td>
                        <td style="padding:10px 8px" class="mut">{{ $cd->notes }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="mut" style="padding:18px;text-align:center">Aucune remise de cash enregistrée jusqu'à présent.</p>
    @endif
</x-card>
@endsection
