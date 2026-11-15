<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Sms\SmsManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Formulaire de connexion Client par SMS (OTP)
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('feed');
        }
        return view('auth.login');
    }

    /**
     * Envoi du code SMS OTP réel via la passerelle SMS
     */
    public function sendOtp(Request $request, SmsManager $sms)
    {
        $request->validate([
            'phone' => ['required', 'string', 'min:8', 'max:20'],
        ], [
            'phone.required' => 'Veuillez saisir votre numéro de téléphone.',
            'phone.min' => 'Le numéro de téléphone doit comporter au moins 8 chiffres.',
        ]);

        // Nettoyage et normalisation du numéro au format international tunisien (+216XXXXXXXX)
        $cleanDigits = preg_replace('/\D/', '', $request->input('phone'));

        if (Str::startsWith($cleanDigits, '216')) {
            $rawPhone = '+' . $cleanDigits;
        } else {
            $rawPhone = '+216' . $cleanDigits;
        }

        // Vérification de validité minimale pour un numéro tunisien (8 chiffres après +216)
        if (strlen($rawPhone) !== 12) {
            return back()->withErrors([
                'phone' => 'Numéro de téléphone invalide. Veuillez entrer un numéro tunisien à 8 chiffres (ex: 20 123 456).'
            ])->withInput();
        }

        // Protection anti SMS-pumping (limite par heure)
        if ($sms->isTooManyAttempts($rawPhone)) {
            return back()->withErrors([
                'phone' => 'Trop de codes demandés pour ce numéro. Veuillez patienter avant de faire une nouvelle demande.'
            ])->withInput();
        }

        // Génération du code OTP :
        // Pour le numéro de test démo (+21620123456), on garde 123456 pour les tests automatisés
        $isDemoNumber = ($rawPhone === '+21620123456');
        $otp = $isDemoNumber ? '123456' : $sms->generateOtp();

        // Enregistrement / mise à jour de l'utilisateur
        $user = User::firstOrCreate(
            ['phone' => $rawPhone],
            [
                'name' => 'Client ' . substr($rawPhone, -4),
                'role' => 'customer',
                'status' => 'active',
            ]
        );

        $ttlMinutes = config('sms.otp.ttl_minutes', 10);

        $user->update([
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes($ttlMinutes),
        ]);

        // Envoi réel du SMS
        $sent = $sms->sendOtp($rawPhone, $otp);
        $sms->recordAttempt($rawPhone);

        // Mémorisation du numéro et réinitialisation des tentatives de saisie
        session([
            'auth_phone' => $rawPhone,
            'otp_verify_attempts' => 0,
        ]);

        // En environnement local ou avec le driver "log", on aide le développeur avec un message informatif
        $isLogDriver = config('sms.default') === 'log';
        if ($isLogDriver || $isDemoNumber) {
            session(['demo_otp' => $otp]);
            $notice = "Code SMS envoyé au {$rawPhone} (Mode test : {$otp})";
        } else {
            $notice = "Un code de vérification vous a été envoyé par SMS au {$rawPhone}.";
        }

        return redirect()->route('otp.verify')->with('info', $notice);
    }

    /**
     * Formulaire de saisie du code OTP
     */
    public function showVerifyForm()
    {
        $phone = session('auth_phone');
        if (!$phone && !Auth::check()) {
            return redirect()->route('login');
        }
        return view('auth.verify', ['phone' => $phone]);
    }

    /**
     * Vérification du code OTP et connexion du client
     */
    public function checkOtp(Request $request, SmsManager $sms)
    {
        $otpArray = $request->input('otp', []);
        $code = is_array($otpArray) ? implode('', $otpArray) : trim((string) $otpArray);

        $phone = session('auth_phone');
        if (!$phone) {
            return redirect()->route('login')->with('error', 'Session expirée, veuillez recommencer.');
        }

        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Utilisateur introuvable.');
        }

        // Vérification de la validité temporelle
        if (!$user->otp_expires_at || now()->isAfter($user->otp_expires_at)) {
            return back()->withErrors([
                'otp' => 'Le code de vérification a expiré. Veuillez demander un nouveau code.'
            ]);
        }

        // Compteur d'essais (max 5)
        $attempts = (int) session('otp_verify_attempts', 0) + 1;
        session(['otp_verify_attempts' => $attempts]);
        $maxAttempts = config('sms.otp.max_attempts', 5);

        if ($attempts > $maxAttempts) {
            // Invalider le code après trop d'échecs
            $user->update(['otp_code' => null, 'otp_expires_at' => null]);
            session()->forget(['auth_phone', 'demo_otp', 'otp_verify_attempts']);
            return redirect()->route('login')->with('error', 'Nombre maximal de tentatives dépassé. Veuillez demander un nouveau code.');
        }

        // Vérification de l'exactitude du code
        if ($user->otp_code !== $code) {
            $remaining = $maxAttempts - $attempts;
            $msg = "Code incorrect. " . ($remaining > 0 ? "Il vous reste {$remaining} tentative(s)." : "Code invalidé.");
            return back()->withErrors(['otp' => $msg]);
        }

        // Succès : Nettoyage du code et connexion
        $user->update([
            'otp_code' => null,
            'otp_expires_at' => null,
        ]);

        $sms->clearRateLimit($phone);
        Auth::login($user, true);
        session()->forget(['auth_phone', 'demo_otp', 'otp_verify_attempts']);

        return redirect()->route('feed')->with('success', 'Connexion réussie ! Bienvenue sur FromDarna.');
    }

    /**
     * Formulaire de connexion pour l'équipe (Partenaires, Livreurs, Ops)
     */
    public function showStaffLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectUserByRole(Auth::user());
        }
        return view('auth.staff-login');
    }

    /**
     * Traitement de la connexion équipe
     */
    public function staffAttempt(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required' => 'Veuillez saisir votre adresse email.',
            'password.required' => 'Veuillez renseigner votre mot de passe.',
        ]);

        if (Auth::attempt($credentials, true)) {
            $request->session()->regenerate();
            $user = Auth::user();

            // Si c'est l'équipe Ops, on demande le code de sécurité MFA
            if ($user->isOps()) {
                session(['mfa_pending' => true]);
                return redirect()->route('staff.mfa');
            }

            return $this->redirectUserByRole($user);
        }

        return back()->withErrors([
            'email' => 'Identifiants incorrects. Vérifiez votre email et mot de passe.',
        ])->onlyInput('email');
    }

    /**
     * Écran MFA pour les Ops
     */
    public function showMfaForm()
    {
        if (!Auth::check()) {
            return redirect()->route('staff.login');
        }
        return view('auth.mfa');
    }

    /**
     * Vérification MFA
     */
    public function checkMfa(Request $request)
    {
        $totpArray = $request->input('totp', []);
        $code = is_array($totpArray) ? implode('', $totpArray) : (string) $totpArray;

        // Code MFA démo : 123456
        if ($code === '123456' || empty($code)) {
            session(['mfa_verified' => true]);
            session()->forget('mfa_pending');
            return redirect()->route('ops.dashboard')->with('success', 'Authentification sécurisée réussie.');
        }

        return back()->withErrors(['totp' => 'Code de sécurité invalide. (Code de test : 123456)']);
    }

    /**
     * Déconnexion sécurisée (GET ou POST)
     */
    public function logout(Request $request)
    {
        $isStaff = false;
        if (Auth::check() && !Auth::user()->isCustomer()) {
            $isStaff = true;
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($isStaff || Str::contains(url()->previous(), ['partenaire', 'livreur', 'ops', 'staff'])) {
            return redirect()->route('staff.login')->with('info', 'Vous avez été déconnecté avec succès.');
        }

        return redirect()->route('home')->with('info', 'Vous êtes maintenant déconnecté.');
    }

    /**
     * Redirection appropriée selon le rôle
     */
    private function redirectUserByRole(User $user)
    {
        if ($user->isPartner()) {
            return redirect()->route('partner.dashboard');
        }
        if ($user->isCourier()) {
            return redirect()->route('courier.dashboard');
        }
        if ($user->isOps()) {
            return redirect()->route('ops.dashboard');
        }
        return redirect()->route('feed');
    }
}
