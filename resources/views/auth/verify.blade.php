@extends('layouts.auth')
@section('title', 'Code de vérification')

@section('form')
<form method="POST" action="{{ route('otp.check') }}">
    @csrf
    <h1>Entrez le code reçu</h1>
    <p class="mut">Code à 6 chiffres envoyé par SMS. Il est valable quelques minutes, avec 5 essais maximum.</p>
    <div class="otp" role="group" aria-label="Code à 6 chiffres">
        @for ($i = 1; $i <= 6; $i++)
            <input type="text" name="otp[]" inputmode="numeric" maxlength="1" aria-label="Chiffre {{ $i }}" autocomplete="one-time-code">
        @endfor
    </div>
    <button class="btn block" type="submit">Valider</button>
    <a class="mut" href="{{ route('login') }}">Changer de numéro ou renvoyer le code</a>
</form>
@endsection
