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
            "text"   => $message,
            "session"=> $sessionName
        ];

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

        return ($httpCode >= 200 && $httpCode < 300);
    }
}

$pdo = new PDO(
    "mysql:host=localhost;dbname=smartbin1;charset=utf8mb4",
    "root",
    "",
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$whatsapp = new WhatsAppSender(
    "https://beta-waha.txfdw3.easypanel.host",
    "admin"
);

$supervisors = $pdo
    ->query("SELECT phone FROM users WHERE role = 4 AND phone IS NOT NULL")
    ->fetchAll(PDO::FETCH_COLUMN);

if (empty($supervisors)) {
    die("❌ No supervisors found.\n");
}

echo "🚀 SmartBin simulator started...\n";

while (true) {

    $devices = $pdo->query("
        SELECT d.id_device, d.device_name, a.asset_name
        FROM devices d
        JOIN assets a ON d.asset_id = a.id
    ")->fetchAll();

    foreach ($devices as $device) {

        $capacity = rand(0, 100);

        // Insert sensor reading
        $stmt = $pdo->prepare("
            INSERT INTO sensors (device_id, battery, capacity, time, network)
            VALUES (?, ?, ?, NOW(), 'LTE')
        ");
        $stmt->execute([
            $device['id_device'],
            rand(30, 100),
            $capacity
        ]);

        echo date('H:i:s') . " {$device['device_name']} → {$capacity}%\n";

        // FULL = 86–100
        if ($capacity >= 86) {

            // Check previous reading
            $prev = $pdo->prepare("
                SELECT capacity FROM sensors
                WHERE device_id = ?
                ORDER BY time DESC
                LIMIT 1 OFFSET 1
            ");
            $prev->execute([$device['id_device']]);
            $last = $prev->fetch();

            // Only alert on transition
            if (!$last || $last['capacity'] < 86) {

                $message =
                    "🚨 *SMARTBIN ALERT*\n\n" .
                    "Bin: {$device['device_name']}\n" .
                    "Asset: {$device['asset_name']}\n" .
                    "Status: FULL ({$capacity}%)\n\n" .
                    "Please clear immediately.";

                foreach ($supervisors as $phone) {
                    $whatsapp->sendTextMessage($phone, $message);
                    echo "📲 Alert sent to $phone\n";
                }
            }
        }
    }

    sleep(60); // 1 minute
}
