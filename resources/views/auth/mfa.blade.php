@extends('layouts.auth')
@section('title', 'Double authentification · FromDarna Ops')
@section('headline', 'Console Exploitation & Ops')
@section('tagline', 'Accès hautement sécurisé pour la gestion du dispatch, des finances et des litiges.')

@section('form')
<form method="POST" action="{{ route('staff.mfa.check') }}" id="mfaForm">
    @csrf
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
        <h1>Code de sécurité</h1>
        <span class="bd r" style="font-size:12px;font-weight:700">🔒 MFA Obligatoire</span>
    </div>

    <p class="mut" style="font-size:14px;line-height:1.5">
        Ouvrez votre application d’authentification (Google Authenticator) et saisissez votre code temporaire à 6 chiffres.
    </p>

    @error('totp')
        <div class="al r" role="alert" style="padding:10px 14px;font-size:13.5px">
            <span>{{ $message }}</span>
        </div>
    @enderror

    <div style="display:flex;flex-direction:column;gap:8px">
        <label style="font-weight:700;font-size:14px;color:var(--tx)">Code TOTP à 6 chiffres</label>
        <div class="otp" role="group" aria-label="Code à 6 chiffres" id="totpGroup">
            @for ($i = 1; $i <= 6; $i++)
                <input 
                    type="text" 
                    name="totp[]" 
                    inputmode="numeric" 
                    maxlength="1" 
                    aria-label="Chiffre {{ $i }}" 
                    required
                    style="font-size:24px;font-weight:800;height:54px;border-radius:12px;text-align:center"
                >
            @endfor
        </div>
    </div>

    {{-- Raccourci Démo --}}
    <button type="button" id="fillDemoMfa" style="background:none;border:1px dashed var(--rust);padding:8px 12px;border-radius:10px;color:var(--rust);font-size:13px;cursor:pointer;text-align:left;line-height:1.4">
        ⚡ <strong>Code test Ops :</strong> cliquer ici pour remplir avec <code>123456</code>
    </button>

    <button class="btn block" type="submit" style="margin-top:6px">
        Valider et accéder à la console Ops ➔
    </button>

    <div style="border-top:1px solid var(--line);padding-top:14px;margin-top:6px;text-align:center">
        <a class="mut" href="{{ route('staff.login') }}" style="font-size:13.5px">
            ← Revenir à la connexion
        </a>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const inputs = document.querySelectorAll('#totpGroup input');
    if (!inputs.length) return;

    inputs[0].focus();

    const fillBtn = document.getElementById('fillDemoMfa');
    if (fillBtn) {
        fillBtn.addEventListener('click', () => {
            const code = '123456';
            inputs.forEach((input, idx) => {
                input.value = code[idx] || '';
            });
            inputs[inputs.length - 1].focus();
        });
    }

    inputs[0].addEventListener('paste', (e) => {
        const text = (e.clipboardData || window.clipboardData).getData('text').trim();
        if (/^\d{6}$/.test(text)) {
            e.preventDefault();
            inputs.forEach((input, idx) => {
                input.value = text[idx];
            });
            inputs[inputs.length - 1].focus();
        }
    });
});
</script>
@endpush
@endsection
