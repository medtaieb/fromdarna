@extends('layouts.portal')
@section('role', 'ops')
@section('title', 'Zones et Tarifs')

@section('content')
<x-title title="Zones de livraison & Grille tarifaire" sub="Périmètres opérationnels couverts dans le Cap Bon">
    <x-slot name="actions">
        <x-btn :href="route('feed')" variant="ghost">Filtrer par zone ↗</x-btn>
    </x-slot>
</x-title>

@if(session('success'))
    <div class="al g mb">
        <span>✓ {{ session('success') }}</span>
    </div>
@endif

<x-card>
    <x-table :headers="['Zone', 'Nom Arabe', 'Frais de livraison', 'Délai moyen', 'Restaurants', 'Statut opérationnel', 'Action']">
        @foreach ($zones as $zone)
            <tr>
                <td>
                    <b>{{ $zone->name }}</b>
                    <div class="mut" style="font-size:0.8rem;">slug: {{ $zone->slug }}</div>
                </td>
                <td style="font-family:sans-serif;font-size:1.1rem;direction:rtl;text-align:right;">
                    {{ $zone->ar_name }}
                </td>
                <td>
                    <strong style="color:var(--olive);font-size:1.05rem;">@tnd($zone->delivery_fee_millimes)</strong>
                </td>
                <td>
                    ⏱️ ≈ {{ $zone->estimated_delivery_minutes }} min
                </td>
                <td>
                    <span class="bd g">{{ $zone->stores_count }} restaurant(s)</span>
                </td>
                <td>
                    <x-badge :type="$zone->is_active ? 'g' : 'r'">
                        {{ $zone->is_active ? 'Active' : 'Suspendue' }}
                    </x-badge>
                </td>
                <td>
                    <form method="POST" action="{{ route('ops.zones.toggle', $zone->id) }}">
                        @csrf
                        <button type="submit" class="btn sm {{ $zone->is_active ? 'red' : 'olive' }}" style="padding:4px 8px;font-size:0.8rem;">
                            {{ $zone->is_active ? 'Suspendre zone' : 'Activer zone' }}
                        </button>
                    </form>
                </td>
            </tr>
        @endforeach
    </x-table>
</x-card>

<div class="grid g2" style="margin-top:20px;">
    <x-card>
        <h2>Paramètres globaux de la plateforme</h2>
        <x-table :headers="['Paramètre standard', 'Valeur en vigueur']" :rows="[
            ['Plafond max de cash par livreur', '150,000 TND (150 000 millimes)'],
            ['Rémunération livreur par course', '2,500 TND'],
            ['Taux de commission standard partenaire', '12 % (1200 bps)'],
            ['Fréquence synchronisation GPS', 'Toutes les 4 secondes'],
            ['Délai alerte réponse restaurant', '60 secondes'],
        ]" />
    </x-card>

    <x-card>
        <h2>Couverture territoriale</h2>
        <p class="mut">Le service dessert activement le Cap Bon :</p>
        <ul style="line-height:1.8;padding-left:20px;">
            <li><b>Grombalia (Zone pilote) :</b> Centre-ville, Cité Najeh, Cité El Bassatine, Turki.</li>
            <li><b>Soliman :</b> Centre, Soliman Plage, Cité El Wifak.</li>
            <li><b>Béni Khalled :</b> Avenue Habib Bourguiba, Zaouiet Djedidi.</li>
            <li><b>Bou Argoub :</b> Gare, Cité El Amel, Khanguet.</li>
        </ul>
    </x-card>
</div>
@endsection
