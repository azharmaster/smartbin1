<?php
require_once 'WhatsAppSender.php'; // Your WAHA class

// DB connection
$host = 'localhost';
$db   = 'smartbin1';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}

// Function to check if WAHA number exists
function isWAHANumberValid($whatsapp, $phone, $sessionName = "default") {
    $url = $whatsapp->apiUrl . '/api/checkNumber';
    $data = [
        "chatId" => $phone . '@c.us',
        "session" => $sessionName
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'X-Api-Key: ' . $whatsapp->apiKey
        ]
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode >= 200 && $httpCode < 300) {
        $resp = json_decode($response, true);
        return isset($resp['exists']) && $resp['exists'] === true;
    }

    return false;
}

// WAHA Config
$apiUrl = "https://beta-waha.txfdw3.easypanel.host";
$apiKey = "admin";
$whatsapp = new WhatsAppSender($apiUrl, $apiKey);

// Get all supervisors' phone numbers
$stmtSupervisors = $pdo->query("SELECT phone FROM users WHERE role = 4");
$supervisors = $stmtSupervisors->fetchAll(PDO::FETCH_COLUMN);

if (empty($supervisors)) {
    die("No supervisors found in the database.\n");
}

echo "Starting fake bin simulator...\n";

while (true) {

    // Fetch all devices
    $stmt = $pdo->query("SELECT d.id_device, d.device_name, a.asset_name
                         FROM devices d
                         JOIN assets a ON d.asset_id = a.id");
    $devices = $stmt->fetchAll();

    foreach ($devices as $device) {
        // Generate fake capacity
        $capacity = rand(0, 100);

        // Insert new sensor reading
        $stmtInsert = $pdo->prepare("INSERT INTO sensors (device_id, battery, capacity, time, network) 
                                     VALUES (:device_id, :battery, :capacity, NOW(), :network)");
        $stmtInsert->execute([
            ':device_id' => $device['id_device'],
            ':battery'   => rand(20, 100),
            ':capacity'  => $capacity,
            ':network'   => 'LTE'
        ]);

        echo date('H:i:s') . " - {$device['device_name']} ({$device['asset_name']}) capacity: $capacity%\n";

        // Check if capacity just crossed into full (86-100)
        if ($capacity >= 86) {
            // Get last sensor reading before this one
            $stmtLast = $pdo->prepare("SELECT capacity FROM sensors 
                                       WHERE device_id = :device_id 
                                       ORDER BY time DESC 
                                       LIMIT 1 OFFSET 1"); // OFFSET 1 = previous reading
            $stmtLast->execute([':device_id' => $device['id_device']]);
            $last = $stmtLast->fetch();

            $wasFullBefore = $last && $last['capacity'] >= 86;

            if (!$wasFullBefore) {
                // Send WhatsApp alert to all supervisors
                $message = "⚠️ ALERT! {$device['device_name']} ({$device['asset_name']}) is FULL ({$capacity}%). Please clear it ASAP!";
                
                foreach ($supervisors as $phone) {
                    // Check if the number is valid in WAHA
                    if (isWAHANumberValid($whatsapp, $phone)) {
                        $result = $whatsapp->sendTextMessage($phone, $message);
                        if ($result['success']) {
                            echo "✅ Alert sent to $phone for {$device['device_name']}\n";
                        } else {
                            echo "❌ Failed to send alert to $phone for {$device['device_name']}. HTTP code: {$result['http_code']}\n";
                        }
                    } else {
                        echo "⚠️ Number $phone not valid in WAHA. Skipping alert.\n";
                    }
                }
            } else {
                echo "⏱ Already full before, no new alert.\n";
            }
        }
    }

    // Wait before next simulation
    sleep(60); // 60 seconds
}
