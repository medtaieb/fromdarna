@extends('layouts.phone')
@section('title', 'Accueil')
@section('tab', 'home')
@section('screen')
    <div class="row between">@include('partials.brand')
        <div class="seg"><button class="on" data-lang="fr">FR</button><button data-lang="ar">ع</button></div></div>
    <p class="mut">Livraison à Grombalia · centre-ville</p>
    <div class="hero g-warm">
        <span class="steam"><i></i><i></i><i></i></span>
        <h1 style="font-size:28px;line-height:1.15;max-width:230px">Cuisine de chez nous, livrée chez vous</h1>
        <p class="ar" style="margin-top:8px;color:var(--soft)">أكل من دارنا لباب دارك</p>
        <div class="row" style="margin-top:16px;flex-wrap:wrap"><span class="bd" style="background:var(--cream);color:#6B3A12">Cash à la livraison</span><span class="bd" style="background:var(--red);color:var(--cream)">≈ 35 min</span></div>
    </div>
    <div class="chips"><button class="chip on">Restaurants</button><button class="chip">Pâtisseries</button><button class="chip">Cafés</button></div>
    <div class="row between"><h2 style="font-size:20px" data-ar="مفتوح الآن">Ouverts maintenant</h2></div>
    @foreach ($stores as $s)
        <a href="{{ route('c.store', $s['id']) }}" class="card row up" style="--i:{{ $loop->index }};padding:14px">
            <div class="tile" style="background:linear-gradient(135deg,{{ $s['c1'] }},{{ $s['c2'] }})">{{ mb_substr($s['name'], 0, 1) }}</div>
            <div class="grow col" style="gap:4px"><b>{{ $s['name'] }}</b><span class="mut">{{ $s['cuisine'] }}</span><span class="mut">{{ $s['eta'] }} · Livraison {{ App\Support\Demo::tnd(3000) }}</span></div>
            <span class="bd {{ $s['open'] ? 'ok' : 'nt' }}">{{ $s['open'] ? 'Ouvert' : 'Occupé' }}</span>
        </a>
    @endforeach
@endsection
