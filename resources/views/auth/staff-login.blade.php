@extends('layouts.auth')
@section('title', 'Espace Équipe · FromDarna')
@section('headline', 'Espace Professionnel')
@section('tagline', 'Accès réservé aux restaurants partenaires, coursiers et à l’équipe logistique de FromDarna.')

@section('form')
<form method="POST" action="{{ route('staff.attempt') }}" id="staffForm">
    @csrf
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
        <h1>Connexion équipe</h1>
        <span class="bd x" style="font-size:12px;font-weight:700">Portail Pro</span>
    </div>
    
    <p class="mut" style="font-size:14px;line-height:1.5">
        Connectez-vous avec vos identifiants pour accéder à votre console dédiée.
    </p>

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

    @error('email')
        <div class="al r" role="alert" style="padding:10px 14px;font-size:13.5px">
            <span>{{ $message }}</span>
        </div>
    @enderror

    {{-- Sélecteur de compte démo rapide pour tester --}}
    <div style="background:var(--card);border:1px solid var(--line);border-radius:14px;padding:12px;display:flex;flex-direction:column;gap:8px">
        <span style="font-size:12.5px;font-weight:700;color:var(--mut);text-transform:uppercase;letter-spacing:.03em">
            ⚡ Remplissage rapide (comptes démo) :
        </span>
        <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:6px">
            <button type="button" class="btn ghost sm" onclick="fillStaff('partenaire@fromdarna.tn', 'password')" style="padding:0 8px;font-size:12.5px;height:34px;justify-content:center" title="Accéder au portail restaurant">
                🍲 Partenaire
            </button>
            <button type="button" class="btn ghost sm" onclick="fillStaff('livreur@fromdarna.tn', 'password')" style="padding:0 8px;font-size:12.5px;height:34px;justify-content:center" title="Accéder au portail coursier">
                🛵 Livreur
            </button>
            <button type="button" class="btn ghost sm" onclick="fillStaff('ops@fromdarna.tn', 'password')" style="padding:0 8px;font-size:12.5px;height:34px;justify-content:center" title="Accéder à la console Ops (+ MFA)">
                ⚙️ Ops / Admin
            </button>
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:6px">
        <label for="staffEmail" style="font-weight:700;font-size:14px;color:var(--tx)">Email professionnel</label>
        <input 
            id="staffEmail"
            type="email" 
            name="email" 
            value="{{ old('email', 'partenaire@fromdarna.tn') }}" 
            placeholder="nom@fromdarna.tn" 
            required 
            autocomplete="email"
        >
    </div>

    <div style="display:flex;flex-direction:column;gap:6px">
        <div style="display:flex;justify-content:space-between;align-items:center">
            <label for="staffPassword" style="font-weight:700;font-size:14px;color:var(--tx)">Mot de passe</label>
            <span class="mut" style="font-size:12px">Démo : <code>password</code></span>
        </div>
        <input 
            id="staffPassword"
            type="password" 
            name="password" 
            value="password"
            placeholder="••••••••" 
            required 
            autocomplete="current-password"
        >
    </div>

    <button class="btn block" type="submit" style="margin-top:6px">
        Se connecter à mon espace ➔
    </button>

    <div style="border-top:1px solid var(--line);padding-top:14px;margin-top:6px;display:flex;flex-direction:column;gap:8px;text-align:center">
        <a class="mut" href="{{ route('login') }}" style="font-size:13.5px;color:var(--rust);font-weight:600;text-decoration:none">
            👤 Vous êtes un client particulier ? <u>Connexion par SMS</u>
        </a>
        <a class="mut" href="{{ route('home') }}" style="font-size:13px">
            ← Retour à l’accueil
        </a>
    </div>
</form>

@push('scripts')
<script>
function fillStaff(email, pwd) {
    document.getElementById('staffEmail').value = email;
    document.getElementById('staffPassword').value = pwd;
}
</script>
@endpush
@endsection
