@extends('layouts.auth')
@section('title', 'Connexion Client · FromDarna')
@section('headline', 'Bienvenue sur FromDarna')
@section('tagline', 'Commandez les meilleurs plats de Grombalia et du Cap Bon, livrés chauds chez vous.')

@section('form')
<form method="POST" action="{{ route('otp.send') }}">
    @csrf
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
        <h1>Connexion</h1>
        <span class="bd o" style="font-size:12px;font-weight:700">🇹🇳 Tunisie</span>
    </div>
    
    <p class="mut" style="font-size:14.5px;line-height:1.5">
        Entrez votre numéro de téléphone. Vous recevrez instantanément un code de vérification à 6 chiffres par SMS.
    </p>

    @if(session('info'))
        <div class="al g" role="status" style="padding:10px 14px;font-size:13.5px">
            <span>{{ session('info') }}</span>
        </div>
    @endif

    @if(session('warning'))
        <div class="al" role="alert" style="padding:10px 14px;font-size:13.5px">
            <span>{{ session('warning') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="al r" role="alert" style="padding:10px 14px;font-size:13.5px">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div style="display:flex;flex-direction:column;gap:6px">
        <label for="phoneInput" style="font-weight:700;font-size:14px;color:var(--tx)">Numéro de téléphone</label>
        <div style="display:flex;gap:8px;align-items:center">
            <span style="padding:10px 14px;background:var(--bg2);border:1.5px solid var(--line);border-radius:14px;font-weight:700;color:var(--tx);font-size:15px;display:flex;align-items:center;gap:6px">
                🇹🇳 +216
            </span>
            <input 
                id="phoneInput"
                type="tel" 
                name="phone" 
                placeholder="20 123 456" 
                value="{{ old('phone', session('auth_phone') ? Str::replaceFirst('+216', '', session('auth_phone')) : '') }}" 
                required 
                inputmode="tel" 
                autocomplete="tel"
                style="flex:1"
            >
        </div>
        @error('phone')
            <div style="color:var(--red);font-size:13px;font-weight:600;margin-top:2px" role="alert">{{ $message }}</div>
        @enderror
    </div>

    {{-- Raccourci de test démo --}}
    <button type="button" onclick="document.getElementById('phoneInput').value='20123456'" style="background:none;border:1px dashed var(--rust);padding:8px 12px;border-radius:10px;color:var(--rust);font-size:13px;cursor:pointer;text-align:left;line-height:1.4">
        ⚡ <strong>Numéro de test rapide :</strong> cliquer ici pour remplir avec <code>20 123 456</code>
    </button>

    <button class="btn block" type="submit" style="margin-top:4px">
        Recevoir le code par SMS ➔
    </button>

    <div style="border-top:1px solid var(--line);padding-top:14px;margin-top:6px;display:flex;flex-direction:column;gap:8px;text-align:center">
        <a class="mut" href="{{ route('staff.login') }}" style="font-size:13.5px;color:var(--rust);font-weight:600;text-decoration:none">
            💼 Vous êtes restaurateur, livreur ou membre d'équipe ? <u>Connexion Pro</u>
        </a>
        <a class="mut" href="{{ route('home') }}" style="font-size:13px">
            ← Retour à l’accueil
        </a>
    </div>
</form>
@endsection
