<?php

namespace App\Services\Sms\Gateways;

use App\Services\Sms\SmsGatewayInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WinSmsGateway implements SmsGatewayInterface
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function send(string $phone, string $message): bool
    {
        $apiKey = $this->config['api_key'] ?? '';
        $endpoint = $this->config['endpoint'] ?? 'https://www.winsms.tn/api/sms/send';
        $sender = $this->config['sender'] ?? 'FromDarna';

        if (empty($apiKey)) {
            Log::warning("⚠️ [WinSMS] Clé API manquante dans .env (WINSMS_KEY). Passage en mode simulation.");
            Log::info("📱 [WinSMS Simulation] vers {$phone} : {$message}");
            return true;
        }

        // Nettoyer le numéro (216XXXXXXXX sans le symbole + pour les passerelles tunisiennes)
        $cleanPhone = ltrim($phone, '+');

        try {
            $response = Http::timeout(10)->post($endpoint, [
                'api_key' => $apiKey,
                'sender' => $sender,
                'receiver' => $cleanPhone,
                'message' => $message,
            ]);

            if ($response->successful()) {
                Log::info("✅ [WinSMS] SMS envoyé avec succès vers {$phone}");
                return true;
            }

            Log::error("❌ [WinSMS] Échec de l'envoi SMS vers {$phone} : HTTP {$response->status()} - {$response->body()}");
            return false;
        } catch (\Throwable $e) {
            Log::error("❌ [WinSMS] Exception lors de l'envoi vers {$phone} : " . $e->getMessage());
            return false;
        }
    }
}
