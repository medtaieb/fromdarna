@extends('layouts.phone')
@section('title', 'Connexion')
@section('screen')
    <div class="row between">@include('partials.brand')
        <div class="seg"><button class="on" data-lang="fr">FR</button><button data-lang="ar">ع</button></div></div>
    <div class="hero g-warm">
        <span class="steam"><i></i><i></i><i></i></span>
        <h1 style="font-size:30px;line-height:1.1;max-width:220px" data-ar="أكل من دارنا">Cuisine de chez nous</h1>
        <p class="ar" style="margin-top:8px;color:var(--soft)">أكل من دارنا لباب دارك</p>
    </div>
    <div class="card col">
        <b data-ar="رقم الهاتف">Votre numéro de téléphone</b>
        <input type="tel" inputmode="numeric" placeholder="+216 22 123 456" aria-label="Numéro de téléphone" value="+216 22 123 456">
        <b data-ar="رمز التحقق">Code reçu par SMS</b>
        <div class="otp"><input inputmode="numeric" maxlength="1" aria-label="Chiffre 1"><input inputmode="numeric" maxlength="1" aria-label="Chiffre 2"><input inputmode="numeric" maxlength="1" aria-label="Chiffre 3"><input inputmode="numeric" maxlength="1" aria-label="Chiffre 4"><input inputmode="numeric" maxlength="1" aria-label="Chiffre 5"><input inputmode="numeric" maxlength="1" aria-label="Chiffre 6"></div>
        <button id="otp-go" class="btn block" data-next="{{ route('c.home') }}" data-ar="دخول">Se connecter</button>
        <span class="mut" style="text-align:center">Prototype : tapez n’importe quel code.</span>
    </div>
@endsection
