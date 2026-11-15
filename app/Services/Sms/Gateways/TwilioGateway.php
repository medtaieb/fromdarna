<?php

namespace App\Services\Sms\Gateways;

use App\Services\Sms\SmsGatewayInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TwilioGateway implements SmsGatewayInterface
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function send(string $phone, string $message): bool
    {
        $sid = $this->config['sid'] ?? '';
        $token = $this->config['token'] ?? '';
        $from = $this->config['from'] ?? 'FromDarna';

        if (empty($sid) || empty($token)) {
            Log::warning("⚠️ [Twilio] Identifiants TWILIO_SID ou TWILIO_TOKEN manquants. Passage en mode simulation.");
            Log::info("📱 [Twilio Simulation] vers {$phone} : {$message}");
            return true;
        }

        try {
            $url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";

            $response = Http::withBasicAuth($sid, $token)
                ->asForm()
                ->timeout(10)
                ->post($url, [
                    'To' => $phone,
                    'From' => $from,
                    'Body' => $message,
                ]);

            if ($response->successful()) {
                Log::info("✅ [Twilio] SMS envoyé avec succès vers {$phone}");
                return true;
            }

            Log::error("❌ [Twilio] Échec SMS vers {$phone} : HTTP {$response->status()} - {$response->body()}");
            return false;
        } catch (\Throwable $e) {
            Log::error("❌ [Twilio] Erreur d'envoi vers {$phone} : " . $e->getMessage());
            return false;
        }
    }
}
