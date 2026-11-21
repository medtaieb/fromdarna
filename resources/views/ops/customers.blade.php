@extends('layouts.portal')
@section('role', 'ops')
@section('title', 'Clients et Incidents')

@section('content')
<x-title title="Clients & Incidents" sub="Traitement des blocages de livraison et suivi de la patientèle">
    <x-slot name="actions">
        <x-btn :href="route('ops.dispatch')">🗺️ Dispatch live</x-btn>
    </x-slot>
</x-title>

@if(session('success'))
    <div class="al g mb">
        <span>✓ {{ session('success') }}</span>
    </div>
@endif

{{-- Section Incidents de terrain --}}
<x-card>
    <div class="row" style="justify-content:space-between;align-items:center;margin-bottom:12px;">
        <h2 style="margin:0;">🚨 Incidents de terrain signalés par les livreurs</h2>
    </div>

    @if($incidents->isEmpty())
        <div style="text-align:center;padding:32px;color:var(--muted);">
            Aucun incident n'a été signalé. Le réseau opère normalement.
        </div>
    @else
        <x-table :headers="['Commande', 'Livreur', 'Type & Motif', 'Description', 'Statut', 'Traitement Ops']">
            @foreach ($incidents as $inc)
                <tr>
                    <td>
                        @if($inc->order)
                            <a href="{{ route('ops.orders.show', $inc->order_id) }}" style="font-weight:700;color:var(--olive);">
                                #{{ $inc->order->ref }}
                            </a>
                            <div class="mut" style="font-size:0.8rem;">
                                {{ $inc->order->store?->name }} → {{ $inc->order->delivery_landmark ?? $inc->order->delivery_city }}
                            </div>
                        @else
                            <span class="mut">Commande #{{ $inc->order_id }}</span>
                        @endif
                    </td>
                    <td>
                        <b>{{ $inc->courier?->name ?? 'Livreur' }}</b>
                        <div class="mut" style="font-size:0.8rem;">📞 {{ $inc->courier?->phone }}</div>
                    </td>
                    <td>
                        <b>{{ ucfirst(str_replace('_', ' ', $inc->type)) }}</b>
                        <div class="mut" style="font-size:0.8rem;">{{ $inc->created_at->format('d/m/Y H:i') }}</div>
                    </td>
                    <td style="max-width:280px;">
                        <div>{{ $inc->description }}</div>
                        @if($inc->photo)
                            <div style="margin-top:4px;">
                                <a href="{{ asset('storage/' . $inc->photo) }}" target="_blank" style="font-size:0.8rem;color:var(--accent);">
                                    📷 Voir photo justificative
                                </a>
                            </div>
                        @endif
                    </td>
                    <td>
                        <x-badge :type="$inc->status === 'open' ? 'r' : 'g'">
                            {{ $inc->status === 'open' ? 'À traiter' : 'Résolu' }}
                        </x-badge>
                    </td>
                    <td style="min-width:220px;">
                        @if($inc->status === 'open')
                            <form method="POST" action="{{ route('ops.incidents.resolve', $inc->id) }}" style="display:flex;gap:6px;flex-direction:column;">
                                @csrf
                                <input 
                                    type="text" 
                                    name="resolution_notes" 
                                    required 
                                    placeholder="Action prise (ex: Client contacté...)" 
                                    style="padding:6px 8px;font-size:0.8rem;border:1px solid #ccc;border-radius:4px;"
                                >
                                <button class="btn sm olive" type="submit">✓ Marquer comme résolu</button>
                            </form>
                        @else
                            <div style="font-size:0.85rem;color:#1e4620;background:#edf7ed;padding:6px 8px;border-radius:4px;">
                                <b>Résolu :</b> {{ $inc->resolution_notes }}
                            </div>
                        @endif
                    </td>
                </tr>
            @endforeach
        </x-table>

        <div style="margin-top:16px;">
            {{ $incidents->links() }}
        </div>
    @endif
</x-card>

{{-- Section Base Clients --}}
<x-card>
    <div class="row" style="justify-content:space-between;align-items:center;margin-bottom:12px;">
        <h2 style="margin:0;">Comptes Clients enregistrés</h2>
    </div>

    @if($customers->isEmpty())
        <div style="text-align:center;padding:24px;color:var(--muted);">
            Aucun client enregistré pour l'instant.
        </div>
    @else
        <x-table :headers="['Client', 'Téléphone', 'Email', 'Commandes passées', 'Inscrit le', 'Paiement cash']">
            @foreach ($customers as $c)
                <tr>
                    <td><b>{{ $c->name }}</b></td>
                    <td>📞 {{ $c->phone }}</td>
                    <td class="mut">{{ $c->email }}</td>
                    <td>
                        <b>{{ $c->orders_count }} commande(s)</b>
                    </td>
                    <td class="mut">{{ $c->created_at ? $c->created_at->format('d/m/Y') : '-' }}</td>
                    <td><x-badge type="g">Autorisé</x-badge></td>
                </tr>
            @endforeach
        </x-table>

        <div style="margin-top:16px;">
            {{ $customers->links() }}
        </div>
    @endif
</x-card>
@endsection
