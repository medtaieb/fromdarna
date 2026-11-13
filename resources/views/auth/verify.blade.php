@extends('layouts.auth')
@section('title', 'Code de vérification · FromDarna')
@section('headline', 'Vérification de sécurité')
@section('tagline', 'Nous protégeons votre compte et vos commandes avec une authentification simple et sécurisée.')

@section('form')
<form method="POST" action="{{ route('otp.check') }}" id="verifyForm">
    @csrf
    <h1>Entrez le code reçu</h1>
    
    <p class="mut" style="font-size:14.5px;line-height:1.5">
        Un code SMS à 6 chiffres a été envoyé au <strong style="color:var(--tx)">{{ $phone ?? session('auth_phone', '+216 XX XXX XXX') }}</strong>.
    </p>

    @if(session('info'))
        <div class="al g" role="status" style="padding:10px 14px;font-size:13.5px">
            <span>{{ session('info') }}</span>
        </div>
    @endif

    @error('otp')
        <div class="al r" role="alert" style="padding:10px 14px;font-size:13.5px">
            <span>{{ $message }}</span>
        </div>
    @enderror

    {{-- Cases OTP --}}
    <div style="display:flex;flex-direction:column;gap:8px">
        <label style="font-weight:700;font-size:14px;color:var(--tx)">Code de confirmation</label>
        <div class="otp" role="group" aria-label="Code à 6 chiffres" id="otpGroup">
            @for ($i = 1; $i <= 6; $i++)
                <input 
                    type="text" 
                    name="otp[]" 
                    inputmode="numeric" 
                    maxlength="1" 
                    aria-label="Chiffre {{ $i }}" 
                    autocomplete="one-time-code"
                    required
                    style="font-size:24px;font-weight:800;height:54px;border-radius:12px;text-align:center"
                >
            @endfor
        </div>
    </div>

    {{-- Raccourci Démo --}}
    <button type="button" id="fillDemoOtp" style="background:none;border:1px dashed var(--olive);padding:8px 12px;border-radius:10px;color:var(--olive);font-size:13px;cursor:pointer;text-align:left;line-height:1.4">
        ⚡ <strong>Environnement de test :</strong> cliquer ici pour insérer le code <code>123456</code>
    </button>

    <div style="display:flex;justify-content:space-between;align-items:center;font-size:13px;color:var(--mut)">
        <span>Valable encore : <b id="otpCountdown" style="color:var(--rust)">09:59</b></span>
        <a href="{{ route('login') }}" style="color:var(--rust);text-decoration:none;font-weight:600">Renvoyer</a>
    </div>

    <button class="btn block" type="submit" style="margin-top:4px">
        Valider et continuer ➔
    </button>

    <div style="border-top:1px solid var(--line);padding-top:14px;margin-top:6px;text-align:center">
        <a class="mut" href="{{ route('login') }}" style="font-size:13.5px">
            ← Changer de numéro de téléphone
        </a>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const inputs = document.querySelectorAll('#otpGroup input');
    if (!inputs.length) return;

    // Focus automatique sur le premier champ
    inputs[0].focus();

    // Remplissage rapide démo
    const fillBtn = document.getElementById('fillDemoOtp');
    if (fillBtn) {
        fillBtn.addEventListener('click', () => {
            const demoCode = '123456';
            inputs.forEach((input, idx) => {
                input.value = demoCode[idx] || '';
            });
            inputs[inputs.length - 1].focus();
        });
    }

    // Gestion du copier/coller d'un code complet
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

    // Compte à rebours 10 min
    let secondsLeft = 599;
    const countdownEl = document.getElementById('otpCountdown');
    if (countdownEl) {
        const timer = setInterval(() => {
            secondsLeft--;
            if (secondsLeft <= 0) {
                clearInterval(timer);
                countdownEl.textContent = 'Expiré';
                return;
            }
            const mins = String(Math.floor(secondsLeft / 60)).padStart(2, '0');
            const secs = String(secondsLeft % 60).padStart(2, '0');
            countdownEl.textContent = `${mins}:${secs}`;
        }, 1000);
    }
});
</script>
@endpush
@endsection
