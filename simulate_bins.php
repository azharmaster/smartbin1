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

// WAHA Config
$apiUrl = "https://beta-waha.txfdw3.easypanel.host";
$apiKey = "admin";
$whatsapp = new WhatsAppSender($apiUrl, $apiKey);
$phone = "60198036196"; // WhatsApp recipient

// Run continuously
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
                // Send WhatsApp alert
                $message = "⚠️ ALERT! {$device['device_name']} ({$device['asset_name']}) is FULL ({$capacity}%). Please clear it ASAP!";
                $result = $whatsapp->sendTextMessage($phone, $message);

                if ($result['success']) {
                    echo "✅ Alert sent for {$device['device_name']}\n";
                } else {
                    echo "❌ Failed to send alert for {$device['device_name']}. HTTP code: {$result['http_code']}\n";
                }
            } else {
                echo "⏱ Already full before, no new alert.\n";
            }
        }
    }

    // Wait before next simulation
    sleep(60); // 60 seconds
}
