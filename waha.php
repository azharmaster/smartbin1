<?php
class WhatsAppSender {
    private $apiUrl;
    private $apiKey;

    public function __construct($apiUrl, $apiKey) {
        $this->apiUrl = rtrim($apiUrl, '/');
        $this->apiKey = $apiKey;
    }

    // Hantar mesej teks
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
                'X-Api-Key: ' . $this->apiKey // WAHA guna X-Api-Key, bukan Bearer
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

// Konfigurasi WAHA anda
$apiUrl = "https://beta-waha.txfdw3.easypanel.host";  // Ganti dengan URL server WAHA anda
$apiKey = "admin";                                    // Ganti dengan API key yang anda set di server (WAHA_API_KEY)

// Inisialisasi penghantar
$whatsapp = new WhatsAppSender($apiUrl, $apiKey);

// Contoh mesej promosi, ganti phone number dan message ikut keperluan
$phone = ""; // Nombor WhatsApp dengan country code (tanpa +, ganti ikut anda)
$message = "🚀 *SPECIAL OFFER!* 🚀\n\n" .
            "Get 50% OFF on all products this weekend!\n\n" .
            "✨ Features:\n" .
            "• Premium Quality\n" .
            "• Fast Delivery\n" .
            "• 24/7 Support\n\n" .
            "🛒 Shop now: https://example.com\n" .
            "📞 Contact: +1234567890\n\n" .
            "#SpecialOffer #Discount #Sale";

// Hantar mesej
$result = $whatsapp->sendTextMessage($phone, $message);

if ($result['success']) {
    echo "Message sent successfully!";
} else {
    echo "Failed to send message. Error: ";
    if (isset($result['response']['error'])) {
        echo $result['response']['error'];
    } else {
        echo "HTTP Code: " . $result['http_code'];
    }
}
?>
