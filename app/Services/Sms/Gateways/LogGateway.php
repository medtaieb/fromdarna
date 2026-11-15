<?php

namespace App\Services\Sms\Gateways;

use App\Services\Sms\SmsGatewayInterface;
use Illuminate\Support\Facades\Log;

class LogGateway implements SmsGatewayInterface
{
    public function send(string $phone, string $message): bool
    {
        Log::info("📱 [SMS MOCK / LOG] Envoi vers {$phone} : {$message}");
        return true;
    }
}
