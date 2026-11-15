@extends('layouts.site')
@section('title', 'Mes commandes')

@section('content')
<x-title title="Mes commandes" />

@if(session('success'))<div class="flash ok" role="alert">{{ session('success') }}</div>@endif

<x-card>
@if(Auth::check() && $orders->count())
    <x-table
        :headers="['Ref.', 'Restaurant', 'Total', 'Statut']"
        :rows="$orders->map(fn($o) => [
            $o->ref . '|' . $o->created_at->format('d/m H:i'),
            $o->store->name,
            \App\Support\Money::tnd($o->total_millimes),
            $o->statusBadgeType() . ':' . $o->statusLabel(),
        ])->toArray()"
    />
    <div style="margin-top:16px">{{ $orders->links() }}</div>
@elseif(!Auth::check())
    <div style="text-align:center;padding:24px">
        <p class="mut">Connectez-vous pour voir vos commandes.</p>
        <x-btn :href="route('login')" style="margin-top:12px">Se connecter</x-btn>
    </div>
@else
    <div style="text-align:center;padding:24px">
        <span style="font-size:36px">📦</span>
        <p class="mut" style="margin-top:8px">Vous n'avez pas encore passé de commande.</p>
        <x-btn :href="route('feed')" style="margin-top:12px" class="glow">Voir les restaurants</x-btn>
    </div>
@endif
</x-card>
@endsection
