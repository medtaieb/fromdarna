{{-- Parcours client : barre du haut (bureau) + barre d'onglets (mobile) --}}
@extends('layouts.base')

@section('bodyclass', 'app')

@section('body')
<nav>
    <div class="wrap">
        <a class="brand" href="{{ route('home') }}"><span class="logo" role="img" aria-label="Logo Cooked by Us"></span>Cooked by Us</a>
        <ul>
            <li><a href="{{ route('feed') }}">Restaurants</a></li>
            <li><a href="{{ route('orders.index') }}">Mes commandes</a></li>
            <li><a href="{{ route('account') }}">Compte</a></li>
        </ul>
        <a class="btn" href="{{ route('cart') }}">Panier</a>
    </div>
</nav>

<main class="wrap stack site-main">@yield('content')</main>

<div class="tabbar" role="navigation" aria-label="Navigation">
    @foreach ([['feed', '🍲', 'Restaurants'], ['cart', '🛒', 'Panier'], ['orders.index', '🧾', 'Commandes'], ['account', '👤', 'Compte']] as $t)
        <a href="{{ route($t[0]) }}" class="{{ request()->routeIs($t[0]) ? 'on' : '' }}"><span>{{ $t[1] }}</span>{{ $t[2] }}</a>
    @endforeach
</div>

@include('partials.footer')
@endsection
