@extends('layouts.portal')
@section('role', 'ops')
@section('title', 'Flotte des livreurs & Cash')

@section('content')
<x-title title="Flotte des livreurs" sub="Supervision des positions, de la charge et du cash détenu">
    <x-slot name="actions">
        <x-btn :href="route('ops.dispatch')">🗺️ Dispatch temps réel</x-btn>
    </x-slot>
</x-title>

@if(session('success'))
    <div class="al g mb">
        <span>✓ {{ session('success') }}</span>
    </div>
@endif

{{-- Section Remises de cash au QG en attente de validation --}}
@if($pendingDrops->isNotEmpty())
    <x-card style="border-left: 4px solid var(--accent, #98551C); background: #FFFDF9;">
        <div class="row" style="justify-content:space-between;align-items:center;margin-bottom:12px;">
            <h2 style="margin:0;color:var(--accent);">💰 Remises d’espèces au QG à valider ({{ $pendingDrops->count() }})</h2>
        </div>
        <p class="mut">Les livreurs suivants ont déposé leurs espèces au QG. Confirmez le comptage physique pour réinitialiser leur plafond.</p>

        <x-table :headers="['Référence', 'Livreur', 'Montant déposé', 'Date / Heure', 'Notes', 'Action']">
            @foreach($pendingDrops as $drop)
                <tr>
                    <td><b>{{ $drop->reference }}</b></td>
                    <td>
                        <b>{{ $drop->courier?->name ?? 'Livreur' }}</b>
                        <div class="mut" style="font-size:0.8rem;">📞 {{ $drop->courier?->phone }}</div>
                    </td>
                    <td>
                        <strong style="font-size:1.05rem;color:var(--olive);">{{ \App\Support\Money::tnd($drop->amount_millimes) }}</strong>
                    </td>
                    <td>{{ $drop->created_at->format('d/m/Y H:i') }}</td>
                    <td class="mut">{{ $drop->notes ?? 'Dépôt standard' }}</td>
                    <td>
                        <form method="POST" action="{{ route('ops.couriers.drop.validate', $drop->id) }}">
                            @csrf
                            <button class="btn olive sm" type="submit" onclick="return confirm('Confirmez-vous avoir reçu physiquement {{ \App\Support\Money::tnd($drop->amount_millimes) }} de {{ $drop->courier?->name }} ?');">
                                ✓ Valider l’encaissement
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </x-table>
    </x-card>
@endif

{{-- Liste des livreurs --}}
<x-card>
    <div class="row" style="justify-content:space-between;align-items:center;margin-bottom:12px;">
        <h2 style="margin:0;">Livreurs actifs sur le réseau</h2>
    </div>

    <x-table :headers="['Livreur & Contact', 'Position / GPS', 'Courses aujourd’hui', 'Cash détenu / Plafond', 'Statut plafond', 'Actions']">
        @foreach ($couriers as $courier)
            @php
                $heldCash = $courier->getHeldCashMillimes();
                $isExceeded = $courier->isCashLimitExceeded();
                $loc = $courier->courierLocation;
                $todayCourses = $courier->assignedOrders()->where('status', 'DELIVERED')->whereDate('delivered_at', today())->count();
                $activeCourses = $courier->assignedOrders()->whereIn('status', ['ACCEPTED', 'READY', 'PICKED_UP'])->count();
            @endphp
            <tr>
                <td>
                    <b>{{ $courier->name }}</b>
                    <div class="mut" style="font-size:0.8rem;">📞 {{ $courier->phone }}</div>
                </td>
                <td>
                    @if($loc && $loc->is_online)
                        <div style="color:var(--olive);font-weight:600;font-size:0.85rem;">
                            🟢 En ligne ({{ $loc->battery ?? 80 }}% 🔋)
                        </div>
                        <div class="mut" style="font-size:0.75rem;">
                            Dernier ping: {{ $loc->last_ping_at?->diffForHumans() ?? 'Récemment' }}
                        </div>
                    @else
                        <div class="mut" style="font-size:0.85rem;">⚪ Hors ligne</div>
                    @endif
                </td>
                <td>
                    <b>{{ $todayCourses }} livrée(s)</b>
                    @if($activeCourses > 0)
                        <div style="color:var(--accent);font-size:0.8rem;font-weight:700;">
                            ⚡ {{ $activeCourses }} en cours
                        </div>
                    @endif
                </td>
                <td>
                    <strong style="color:{{ $isExceeded ? '#C41317' : 'inherit' }};font-size:1rem;">
                        {{ \App\Support\Money::tnd($heldCash) }}
                    </strong>
                    <span class="mut" style="font-size:0.85rem;"> / {{ \App\Support\Money::tnd(150000) }}</span>
                </td>
                <td>
                    @if($isExceeded)
                        <x-badge type="r">Plafond dépassé (Bloqué)</x-badge>
                    @else
                        <x-badge type="g">Autorisé (&lt; 150 TND)</x-badge>
                    @endif
                </td>
                <td>
                    <x-btn variant="ghost" size="sm" :href="route('ops.dispatch')">Suivre sur carte</x-btn>
                </td>
            </tr>
        @endforeach
    </x-table>
</x-card>

{{-- Historique récent des remises de cash validées --}}
@if($validatedDrops->isNotEmpty())
    <x-card>
        <h2>Dernières remises validées au QG</h2>
        <x-table :headers="['Réf', 'Livreur', 'Montant', 'Validée le', 'Par']">
            @foreach($validatedDrops as $vDrop)
                <tr>
                    <td><b>{{ $vDrop->reference }}</b></td>
                    <td>{{ $vDrop->courier?->name ?? 'Livreur' }}</td>
                    <td><b>{{ \App\Support\Money::tnd($vDrop->amount_millimes) }}</b></td>
                    <td class="mut">{{ $vDrop->validated_at?->format('d/m/Y H:i') ?? $vDrop->updated_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $vDrop->validator?->name ?? 'Ops QG' }}</td>
                </tr>
            @endforeach
        </x-table>
    </x-card>
@endif
@endsection
