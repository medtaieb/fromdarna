@extends('layouts.portal')
@section('role', 'ops')
@section('title', 'Restaurants Partenaires')

@section('content')
<x-title title="Restaurants partenaires" sub="Gestion du catalogue, des commissions et des statuts en direct">
    <x-slot name="actions">
        <x-btn :href="route('feed')" variant="ghost">Voir le catalogue client ↗</x-btn>
    </x-slot>
</x-title>

@if(session('success'))
    <div class="al g mb">
        <span>✓ {{ session('success') }}</span>
    </div>
@endif

<x-card>
    <x-table :headers="['Restaurant & Zone', 'Spécialité', 'Commission', 'Délai prép.', 'Menu', 'Statut', 'Actions']">
        @foreach ($stores as $s)
            <tr>
                <td>
                    <b>{{ $s->name }}</b>
                    <div class="mut" style="font-size:0.8rem;">📍 {{ $s->zone?->name ?? 'Grombalia' }} · {{ $s->address }}</div>
                </td>
                <td>
                    <span style="font-size:1.1rem;margin-right:4px;">{{ $s->emoji }}</span> {{ $s->kind }}
                </td>
                <td>
                    <b>{{ ($s->commission_bps ?? 1200) / 100 }} %</b>
                    <div class="mut" style="font-size:0.75rem;">({{ $s->commission_bps }} bps)</div>
                </td>
                <td>
                    ⏱️ ≈ {{ $s->avg_prep_minutes }} min
                </td>
                <td>
                    <span class="bd g">{{ $s->products->count() }} plat(s)</span>
                </td>
                <td>
                    <form method="POST" action="{{ route('ops.stores.toggle', $s->id) }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn sm {{ $s->is_open ? 'olive' : 'ghost' }}" style="padding:4px 8px;font-size:0.8rem;">
                            {{ $s->is_open ? '● Ouvert' : '○ Fermé' }}
                        </button>
                    </form>
                </td>
                <td>
                    <x-btn variant="ghost" size="sm" :href="route('ops.stores.edit', $s->id)">Modifier</x-btn>
                </td>
            </tr>
        @endforeach
    </x-table>
</x-card>
@endsection
