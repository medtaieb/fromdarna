<?php

namespace App\Services\Sms;

use App\Services\Sms\Gateways\LogGateway;
use App\Services\Sms\Gateways\TunisieSmsGateway;
use App\Services\Sms\Gateways\TwilioGateway;
use App\Services\Sms\Gateways\WinSmsGateway;
use Illuminate\Support\Facades\RateLimiter;
use InvalidArgumentException;

class SmsManager
{
    protected array $config;
    protected array $gateways = [];

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Obtenir l'instance de la passerelle configurée
     */
    public function gateway(?string $name = null): SmsGatewayInterface
    {
        $name = $name ?: ($this->config['default'] ?? 'log');

        if (isset($this->gateways[$name])) {
            return $this->gateways[$name];
        }

        $providerConfig = $this->config['providers'][$name] ?? ['driver' => 'log'];
        $driver = $providerConfig['driver'] ?? 'log';

        $gateway = match ($driver) {
            'log' => new LogGateway(),
            'winsms' => new WinSmsGateway($providerConfig),
            'tunisiesms' => new TunisieSmsGateway($providerConfig),
            'twilio' => new TwilioGateway($providerConfig),
            default => throw new InvalidArgumentException("Passerelle SMS non supportée : {$driver}"),
        };

        return $this->gateways[$name] = $gateway;
    }

    /**
     * Génère un code OTP aléatoire à 6 chiffres
     */
    public function generateOtp(): string
    {
        $digits = $this->config['otp']['digits'] ?? 6;
        $max = (10 ** $digits) - 1;
        return str_pad((string) random_int(0, $max), $digits, '0', STR_PAD_LEFT);
    }

    /**
     * Vérifie si le numéro a dépassé la limite d'envoi (anti SMS-pumping)
     */
    public function isTooManyAttempts(string $phone): bool
    {
        $maxPerHour = $this->config['otp']['max_per_hour'] ?? 5;
        $key = 'sms_otp_rate:' . md5($phone);
        return RateLimiter::tooManyAttempts($key, $maxPerHour);
    }

    /**
     * Incrémente le compteur de tentatives pour ce numéro
     */
    public function recordAttempt(string $phone): void
    {
        $key = 'sms_otp_rate:' . md5($phone);
        RateLimiter::hit($key, 3600); // Bloque/compte pendant 1 heure
    }

    /**
     * Réinitialise le limiteur après une connexion réussie
     */
    public function clearRateLimit(string $phone): void
    {
        $key = 'sms_otp_rate:' . md5($phone);
        RateLimiter::clear($key);
    }

    /**
     * Envoie le code OTP par SMS
     */
    public function sendOtp(string $phone, string $code): bool
    {
        $ttl = $this->config['otp']['ttl_minutes'] ?? 10;
        
        // Message bilingue conforme aux standards de livraison en Tunisie
        $message = "FromDarna : Votre code de vérification est {$code}. Il expire dans {$ttl} min. من دارنا : رمز التحقق الخاص بك هو {$code}";

        return $this->gateway()->send($phone, $message);
    }

    /**
     * Envoi d'un message direct quelconque
     */
    public function send(string $phone, string $message): bool
    {
        return $this->gateway()->send($phone, $message);
    }
}
