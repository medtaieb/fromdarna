@extends('layouts.base')
@section('title', 'Portails FromDarna')
@section('content')
    <header class="hub-hero reveal">
        <div style="display:flex;justify-content:center;margin-bottom:22px">@include('partials.brand')</div>
        <h1>Cuisine de chez nous, livrée chez vous</h1>
        <p class="ar" style="font-size:20px;color:var(--brown);margin-top:10px">أكل من دارنا لباب دارك</p>
        <p class="mut" style="margin-top:12px;font-size:15px">Plateforme intégrée de commande et livraison alimentaire · Cap Bon</p>
    </header>
    @php
        $spaces = [
            ['App client', 'Menus, panier, géolocalisation GPS, suivi direct', 'feed', 'g-warm'],
            ['Espace Partenaire', 'Commandes cuisine, alerte sonore, menu & ruptures, relevés', 'partner.dashboard', 'g-olive'],
            ['Espace Livreur', 'Courses live, guidage GPS, calcul cash & plafond 150 TND', 'courier.dashboard', 'g-tomato'],
            ['Console Ops & Dispatch', 'Supervision temps réel, flotte GPS, alertes d’escalade', 'ops.dashboard', 'g-olive'],
        ];
    @endphp
    <div class="hub-grid">
        @foreach ($spaces as $i => [$t, $d, $r, $g])
            <a href="{{ route($r) }}" class="card up" style="--i:{{ $i }};padding:0;overflow:hidden">
                <div class="{{ $g }}" style="height:110px;position:relative"><span class="steam"><i></i><i></i><i></i></span></div>
                <div style="padding:18px"><h2 style="font-size:22px">{{ $t }}</h2><p class="mut" style="margin-top:6px">{{ $d }}</p><span class="btn sm" style="margin-top:14px">Ouvrir</span></div>
            </a>
        @endforeach
    </div>
@endsection
