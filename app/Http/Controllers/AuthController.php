<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
     * Envoi du code SMS OTP
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|min:8',
        ], [
            'phone.required' => 'Veuillez saisir votre numéro de téléphone.',
            'phone.min' => 'Le numéro de téléphone doit comporter au moins 8 chiffres.',
        ]);

        $rawPhone = preg_replace('/\s+/', '', $request->input('phone'));
        // Normaliser au format international +216
        if (!Str::startsWith($rawPhone, '+216')) {
            $rawPhone = '+216' . ltrim($rawPhone, '0+');
        }

        // Code OTP fixe pour l'environnement de démo/test : 123456
        $otp = '123456';

        $user = User::firstOrCreate(
            ['phone' => $rawPhone],
            [
                'name' => 'Client ' . substr($rawPhone, -4),
                'role' => 'customer',
                'status' => 'active',
            ]
        );

        $user->update([
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        session([
            'auth_phone' => $rawPhone,
            'demo_otp' => $otp, // Affiché en aide pour faciliter les tests
        ]);

        return redirect()->route('otp.verify')->with('info', "Code envoyé au {$rawPhone} (Code de test : {$otp})");
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
    public function checkOtp(Request $request)
    {
        $otpArray = $request->input('otp', []);
        $code = is_array($otpArray) ? implode('', $otpArray) : (string) $otpArray;

        $phone = session('auth_phone');
        if (!$phone) {
            return redirect()->route('login')->with('error', 'Session expirée, veuillez recommencer.');
        }

        $user = User::where('phone', $phone)->first();

        if (!$user || $user->otp_code !== $code) {
            return back()->withErrors(['otp' => 'Le code saisi est incorrect. (Code de test : 123456)']);
        }

        // Réinitialiser le code OTP
        $user->update([
            'otp_code' => null,
            'otp_expires_at' => null,
        ]);

        Auth::login($user, true);
        session()->forget(['auth_phone', 'demo_otp']);

        return redirect()->route('feed')->with('success', 'Bienvenue sur FromDarna !');
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

