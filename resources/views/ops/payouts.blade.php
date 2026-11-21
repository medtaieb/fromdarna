@extends('layouts.portal')
@section('role', 'ops')
@section('title', 'Versements & Rapprochements')

@section('content')
<x-title title="Versements & Rapprochements Financiers" sub="Décompte des commissions partenaires et suivi des flux d’espèces">
    <x-slot name="actions">
        <x-btn :href="route('ops.couriers')" variant="ghost">Gestion du cash flotte ↗</x-btn>
    </x-slot>
</x-title>

@if(session('success'))
    <div class="al g mb">
        <span>✓ {{ session('success') }}</span>
    </div>
@endif

<x-card>
    <div class="row" style="justify-content:space-between;align-items:center;margin-bottom:12px;">
        <h2 style="margin:0;">Versements dus aux restaurants partenaires</h2>
    </div>

    <x-table :headers="['Restaurant & Zone', 'Commandes livrées', 'Ventes brutes (plats)', 'Commission FromDarna', 'Net à verser au partenaire', 'Statut']">
        @foreach ($stores as $p)
            <tr>
                <td>
                    <b>{{ $p['store']->name }}</b>
                    <div class="mut" style="font-size:0.8rem;">📍 {{ $p['store']->zone?->name ?? 'Grombalia' }} · {{ ($p['store']->commission_bps ?? 1200) / 100 }} %</div>
                </td>
                <td>
                    <b>{{ $p['count'] }} commande(s)</b>
                </td>
                <td>
                    <strong>{{ \App\Support\Money::tnd($p['sales']) }}</strong>
                </td>
                <td style="color:#C41317;">
                    − {{ \App\Support\Money::tnd($p['commission']) }}
                </td>
                <td>
                    <strong style="color:var(--olive);font-size:1.05rem;">{{ \App\Support\Money::tnd($p['net']) }}</strong>
                </td>
                <td>
                    <x-badge :type="$p['net'] > 0 ? 'o' : 'g'">
                        {{ $p['status'] }}
                    </x-badge>
                </td>
            </tr>
        @endforeach
    </x-table>
</x-card>

<x-card style="margin-top:20px;">
    <div class="row" style="justify-content:space-between;align-items:center;margin-bottom:12px;">
        <h2 style="margin:0;">Journal des remises de cash au QG ({{ $cashDrops->count() }})</h2>
        <x-btn :href="route('ops.couriers')" size="sm">Valider des remises →</x-btn>
    </div>

    @if($cashDrops->isEmpty())
        <div style="text-align:center;padding:24px;color:var(--muted);">
            Aucune remise d'espèces enregistrée pour le moment.
        </div>
    @else
        <x-table :headers="['Référence', 'Livreur', 'Montant déposé', 'Horodatage', 'Notes', 'Statut', 'Validé par']">
            @foreach ($cashDrops as $drop)
                <tr>
                    <td><b>{{ $drop->reference }}</b></td>
                    <td>
                        <b>{{ $drop->courier?->name ?? 'Livreur' }}</b>
                        <div class="mut" style="font-size:0.8rem;">📞 {{ $drop->courier?->phone }}</div>
                    </td>
                    <td>
                        <strong style="color:var(--olive);">{{ \App\Support\Money::tnd($drop->amount_millimes) }}</strong>
                    </td>
                    <td class="mut">{{ $drop->created_at->format('d/m/Y H:i') }}</td>
                    <td class="mut">{{ $drop->notes ?? '—' }}</td>
                    <td>
                        <x-badge :type="$drop->status === 'validated' ? 'g' : 'o'">
                            {{ $drop->status === 'validated' ? 'Validé au QG' : 'En attente' }}
                        </x-badge>
                    </td>
                    <td>
                        {{ $drop->validator?->name ?? ($drop->status === 'validated' ? 'Ops QG' : '—') }}
                    </td>
                </tr>
            @endforeach
        </x-table>
    @endif
</x-card>
@endsection
