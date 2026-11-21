@extends('layouts.portal')
@section('role', 'ops')
@section('title', 'Gestion des Utilisateurs')

@section('content')
<x-title title="Utilisateurs & Rôles" sub="Annuaire des comptes et gestion des accès aux espaces métiers" />

<x-card>
    <form method="GET" action="{{ route('ops.users') }}" class="row" style="flex-wrap:wrap;gap:12px;align-items:center;">
        <input 
            type="search" 
            name="q" 
            value="{{ request('q') }}" 
            placeholder="Nom, téléphone, email..." 
            aria-label="Recherche utilisateur" 
            style="flex:2;min-width:220px"
        >

        <select name="role" aria-label="Rôle" style="flex:1;min-width:160px">
            <option value="">Tous les rôles</option>
            <option value="customer" {{ request('role') === 'customer' ? 'selected' : '' }}>Client</option>
            <option value="courier" {{ request('role') === 'courier' ? 'selected' : '' }}>Livreur</option>
            <option value="partner_owner" {{ request('role') === 'partner_owner' ? 'selected' : '' }}>Partenaire (Gérant)</option>
            <option value="partner_staff" {{ request('role') === 'partner_staff' ? 'selected' : '' }}>Partenaire (Staff)</option>
            <option value="ops" {{ request('role') === 'ops' ? 'selected' : '' }}>Ops / Dispatch</option>
            <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Administrateur</option>
            <option value="finance" {{ request('role') === 'finance' ? 'selected' : '' }}>Finance</option>
        </select>

        <button class="btn" type="submit">Filtrer</button>
        @if(request()->hasAny(['q', 'role']))
            <a class="btn ghost" href="{{ route('ops.users') }}">Réinitialiser</a>
        @endif
    </form>
</x-card>

<x-card>
    <x-table :headers="['Nom & Contact', 'Email', 'Rôle système', 'Activité', 'Inscrit le']">
        @foreach ($users as $user)
            @php
                $roleBadges = [
                    'admin'         => ['r', 'Administrateur'],
                    'ops'           => ['o', 'Ops / Dispatch'],
                    'finance'       => ['o', 'Finance'],
                    'partner_owner' => ['g', 'Partenaire (Gérant)'],
                    'partner_staff' => ['g', 'Partenaire (Staff)'],
                    'courier'       => ['x', 'Livreur'],
                    'customer'      => ['g', 'Client'],
                ];
                $b = $roleBadges[$user->role] ?? ['x', $user->role];
            @endphp
            <tr>
                <td>
                    <b>{{ $user->name }}</b>
                    <div class="mut" style="font-size:0.8rem;">📞 {{ $user->phone }}</div>
                </td>
                <td class="mut">{{ $user->email ?? '—' }}</td>
                <td>
                    <x-badge :type="$b[0]">{{ $b[1] }}</x-badge>
                </td>
                <td>
                    @if($user->role === 'courier')
                        <span>🚴 {{ $user->assigned_orders_count }} course(s)</span>
                    @elseif($user->role === 'customer')
                        <span>🛍️ {{ $user->orders_count }} commande(s)</span>
                    @else
                        <span class="mut">—</span>
                    @endif
                </td>
                <td class="mut">{{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : '—' }}</td>
            </tr>
        @endforeach
    </x-table>

    <div style="margin-top:20px;">
        {{ $users->links() }}
    </div>
</x-card>
@endsection
