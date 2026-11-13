{{-- Écran scindé : visuel de marque + formulaire (@section('form')) --}}
@extends('layouts.base')

@section('bodyclass', 'app')

@section('body')
<div class="auth">
    <div class="auth-art">
        <a href="{{ route('home') }}" aria-label="Accueil"><span class="logo" role="img" aria-label="Logo Cooked by Us"></span></a>
        <h1>@yield('headline', 'Les plats de chez nous')</h1>
        <p class="ar" style="font-size:20px;color:var(--beige)">من دارنا</p>
        <p>@yield('tagline', 'Restaurants, pâtisseries et cafés du Cap Bon ouest, livrés jusqu’à votre porte.')</p>
    </div>
    <div class="auth-form">@yield('form')</div>
</div>
@endsection
