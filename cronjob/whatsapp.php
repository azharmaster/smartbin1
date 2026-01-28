<?php
class WhatsAppSender {
    private $apiUrl;
    private $apiKey;

    public function __construct($apiUrl, $apiKey) {
        $this->apiUrl = rtrim($apiUrl, '/');
        $this->apiKey = $apiKey;
    }

    public function sendTextMessage($phone, $message, $sessionName = "default") {
        $url = $this->apiUrl . '/api/sendText';

        $data = [
            "chatId" => $phone . '@c.us',
            "text" => $message,
            "session" => $sessionName
        ];

        return $this->makeRequest($url, $data);
    }

    private function makeRequest($url, $data) {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-Api-Key: ' . $this->apiKey
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'success' => ($httpCode >= 200 && $httpCode < 300),
            'response' => json_decode($response, true),
            'http_code' => $httpCode
        ];
    }
}