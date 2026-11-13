@extends('layouts.base')
@section('title', 'Prototype')
@section('content')
    <header class="hub-hero reveal">
        <div style="display:flex;justify-content:center;margin-bottom:22px">@include('partials.brand')</div>
        <h1>Cuisine de chez nous, livrée chez vous</h1>
        <p class="ar" style="font-size:20px;color:var(--brown);margin-top:10px">أكل من دارنا لباب دارك</p>
        <p class="mut" style="margin-top:12px;font-size:15px">Prototype Laravel Blade · Lot 1 (MVP) · Grombalia · données d’exemple</p>
    </header>
    @php
        $spaces = [
            ['App client', 'Android, FR/AR, OTP, panier, suivi', 'c.login', 'g-warm'],
            ['Partenaire', 'Commandes, ruptures, relevés', 'partner.orders', 'g-olive'],
            ['Livreur', 'Course, cash, gains', 'courier.ride', 'g-tomato'],
            ['Ops', 'Dispatch, cash, grand livre', 'ops.dispatch', 'g-olive'],
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
