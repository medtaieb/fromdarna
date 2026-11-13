@extends('layouts.auth')
@section('title', 'Double authentification')
@section('headline', 'Double authentification')

@section('form')
<form method="POST" action="{{ route('staff.mfa.check') }}">
    @csrf
    <h1>Code de sécurité</h1>
    <p class="mut">Ouvrez votre application d’authentification et saisissez le code à 6 chiffres. Obligatoire pour l’équipe Ops.</p>
    <div class="otp" role="group" aria-label="Code à 6 chiffres">
        @for ($i = 1; $i <= 6; $i++)
            <input type="text" name="totp[]" inputmode="numeric" maxlength="1" aria-label="Chiffre {{ $i }}">
        @endfor
    </div>
    <button class="btn block" type="submit">Valider</button>
</form>
@endsection
