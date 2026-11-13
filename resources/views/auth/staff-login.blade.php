@extends('layouts.auth')
@section('title', 'Espace équipe')
@section('headline', 'Espace équipe')
@section('tagline', 'Partenaires, livreurs et équipe Ops : connectez-vous pour gérer les commandes.')

@section('form')
<form method="POST" action="{{ route('staff.attempt') }}">
    @csrf
    <h1>Connexion équipe</h1>
    <x-field label="Email" name="email" type="email" placeholder="nom@exemple.tn" autocomplete="email" />
    <x-field label="Mot de passe" name="password" type="password" autocomplete="current-password" />
    @error('email')<div class="mut" role="alert">{{ $message }}</div>@enderror
    <button class="btn block" type="submit">Se connecter</button>
    <div class="mut">Rôles : partenaire, livreur, Ops, finance, admin. Les accès sont limités à votre rôle.</div>
</form>
@endsection
