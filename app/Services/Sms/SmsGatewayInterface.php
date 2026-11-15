<?php

namespace App\Services\Sms;

interface SmsGatewayInterface
{
    /**
     * Envoyer un SMS vers un numéro de téléphone
     *
     * @param string $phone Numéro au format international E.164 (ex: +21620123456)
     * @param string $message Contenu du texte
     * @return bool Vrai si l'envoi a été accepté par la passerelle
     */
    public function send(string $phone, string $message): bool;
}
