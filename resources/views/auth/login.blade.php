@extends('layouts.auth')
@section('title', 'Connexion')

@section('form')
<form method="POST" action="{{ route('otp.send') }}">
    @csrf
    <h1>Connexion</h1>
    <p class="mut">Entrez votre numéro de téléphone. Nous vous envoyons un code par SMS.</p>
    <x-field label="Numéro de téléphone" name="phone" type="tel" placeholder="+216 20 123 456" inputmode="tel" autocomplete="tel" />
    @error('phone')<div class="mut" role="alert">{{ $message }}</div>@enderror
    <button class="btn block" type="submit">Recevoir le code</button>
    <a class="mut" href="{{ route('staff.login') }}">Vous travaillez avec nous ? Espace équipe</a>
</form>
@endsection
