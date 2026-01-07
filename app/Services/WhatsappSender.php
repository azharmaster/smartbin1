<?php 

namespace App\Services;

class WhatsAppSender
{
    private $apiUrl;
    private $apiKey;

    public function __construct()
    {
        $this->apiUrl = config('services.waha.url');
        $this->apiKey = config('services.waha.key');
    }

    public function send($phone, $message)
    {
        $payload = [
            'chatId' => $phone . '@c.us',
            'text' => $message,
            'session' => 'default'
        ];

        $ch = curl_init($this->apiUrl . '/api/sendText');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-Api-Key: ' . $this->apiKey
            ]
        ]);
        curl_exec($ch);
        curl_close($ch);
    }
}
