<?php

namespace App\Services\Sms\Gateways;

use App\Services\Sms\SmsGatewayInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TunisieSmsGateway implements SmsGatewayInterface
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function send(string $phone, string $message): bool
    {
        $appId = $this->config['app_id'] ?? '';
        $appSecret = $this->config['app_secret'] ?? '';
        $endpoint = $this->config['endpoint'] ?? 'https://www.tunisiesms.tn/api/send_sms';
        $sender = $this->config['sender'] ?? 'FromDarna';

        if (empty($appId) || empty($appSecret)) {
            Log::warning("⚠️ [TunisieSMS] Identifiants manquants dans .env. Passage en mode simulation.");
            Log::info("📱 [TunisieSMS Simulation] vers {$phone} : {$message}");
            return true;
        }

        $cleanPhone = ltrim($phone, '+');

        try {
            $response = Http::timeout(10)->post($endpoint, [
                'app_id' => $appId,
                'app_secret' => $appSecret,
                'sender' => $sender,
                'mobile' => $cleanPhone,
                'sms' => $message,
            ]);

            if ($response->successful()) {
                Log::info("✅ [TunisieSMS] SMS envoyé avec succès vers {$phone}");
                return true;
            }

            Log::error("❌ [TunisieSMS] Échec SMS vers {$phone} : HTTP {$response->status()} - {$response->body()}");
            return false;
        } catch (\Throwable $e) {
            Log::error("❌ [TunisieSMS] Erreur lors de l'envoi vers {$phone} : " . $e->getMessage());
            return false;
        }
    }
}
