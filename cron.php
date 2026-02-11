<?php
// =============================================================================
// smartbin-cron-single.php
// Gabungan config + db + whatsapp + cron logic dalam satu fail
// Jalankan fail ini melalui cron job
// =============================================================================

// =============================================================================
// 1. CONFIGURATION
// =============================================================================
$config = [
    'db' => [
        'host'    => 'localhost',
        'name'    => 'u311595433_smartbin1',
        'user'    => 'u311595433_smartbin1',
        'pass'    => '3b3^Msah:=$R',
        'charset' => 'utf8mb4',
    ],
    'work_hours' => [
        'start' => '07:00',
        'end'   => '19:00',
    ],
    // WAHA WhatsApp API settings
    'waha' => [
        'api_url' => 'https://beta-waha.txfdw3.easypanel.host',
        'api_key' => 'admin',
        'session' => 'default',           // nama session WAHA
    ],
];

$logFile = __DIR__ . '/cron-single.log';

// =============================================================================
// 2. DATABASE CONNECTION
// =============================================================================
try {
    $db = new PDO(
        "mysql:host={$config['db']['host']};dbname={$config['db']['name']};charset={$config['db']['charset']}",
        $config['db']['user'],
        $config['db']['pass'],
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    file_put_contents($logFile, date('Y-m-d H:i:s') . " | DB ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
    die("Database connection failed. Check log.");
}

// =============================================================================
// 3. WHATSAPP SENDER CLASS (mini version)
// =============================================================================
class WhatsAppSender {
    private $apiUrl;
    private $apiKey;
    private $session;

    public function __construct($apiUrl, $apiKey, $session = 'default') {
        $this->apiUrl  = rtrim($apiUrl, '/');
        $this->apiKey  = $apiKey;
        $this->session = $session;
    }

    public function sendText($phone, $message) {
        $url = $this->apiUrl . '/api/sendText';
        $payload = [
            'chatId'  => $phone . '@c.us',
            'text'    => $message,
            'session' => $this->session,
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'X-Api-Key: ' . $this->apiKey,
            ],
            CURLOPT_TIMEOUT        => 15,
        ]);

        $response   = curl_exec($ch);
        $httpCode   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError  = curl_error($ch);
        curl_close($ch);

        $success = ($httpCode >= 200 && $httpCode < 300) && empty($curlError);

        return [
            'success'    => $success,
            'http_code'  => $httpCode,
            'response'   => json_decode($response, true),
            'curl_error' => $curlError,
        ];
    }
}

$whatsapp = new WhatsAppSender(
    $config['waha']['api_url'],
    $config['waha']['api_key'],
    $config['waha']['session']
);

// =============================================================================
// 4. MAIN LOGIC
// =============================================================================
$now      = new DateTime();
$today    = $now->format('Y-m-d');
$timeNow  = $now->format('H:i');
$canSend  = true;
$reasons  = [];

// 4.1 WhatsApp notification window (jika ada table whatsapp_notifications)
$notif = $db->query("SELECT * FROM whatsapp_notifications LIMIT 1")->fetch();
if (!$notif) {
    $canSend = false;
    $reasons[] = "Tiada konfigurasi whatsapp_notifications";
} elseif (!$notif['is_active']) {
    $canSend = false;
    $reasons[] = "Notifikasi WhatsApp dimatikan";
} elseif ($timeNow < $notif['start_time'] || $timeNow > $notif['end_time']) {
    $canSend = false;
    $reasons[] = "Di luar waktu notifikasi WA ({$notif['start_time']}–{$notif['end_time']})";
}

// 4.2 Work hours
if ($timeNow < $config['work_hours']['start'] || $timeNow > $config['work_hours']['end']) {
    $canSend = false;
    $reasons[] = "Di luar waktu kerja ({$config['work_hours']['start']}–{$config['work_hours']['end']})";
}

// 4.3 Holiday check
$stmt = $db->prepare("
    SELECT 1 FROM holidays
    WHERE is_active = 1
      AND (:today BETWEEN start_date AND end_date OR (end_date IS NULL AND start_date = :today))
    LIMIT 1
");
$stmt->execute(['today' => $today]);
if ($stmt->fetchColumn()) {
    $canSend = false;
    $reasons[] = "Hari ini cuti";
}

// 4.4 Event check
$stmt = $db->prepare("
    SELECT 1 FROM events
    WHERE is_active = 1
      AND (:today BETWEEN start_date AND end_date OR (end_date IS NULL AND start_date = :today))
    LIMIT 1
");
$stmt->execute(['today' => $today]);
if ($stmt->fetchColumn()) {
    $canSend = false;
    $reasons[] = "Ada acara aktif hari ini";
}

// 4.5 Get full bin threshold
$cap = $db->query("SELECT half_to FROM capacity_settings LIMIT 1")->fetch();
$fullMin = ($cap && isset($cap['half_to'])) ? (int)$cap['half_to'] + 1 : 80; // default 80 jika tiada setting

// 4.6 Cari tong penuh
$stmt = $db->query("
    SELECT d.id_device, d.device_name, a.location
    FROM devices d
    JOIN assets a ON a.id = d.asset_id
    WHERE d.is_active = 1 AND a.is_active = 1
");
$fullBins = [];

while ($device = $stmt->fetch()) {
    $sensorStmt = $db->prepare("
        SELECT capacity FROM sensors
        WHERE device_id = ?
        ORDER BY time DESC
        LIMIT 1
    ");
    $sensorStmt->execute([$device['id_device']]);
    $sensor = $sensorStmt->fetch();

    if ($sensor && $sensor['capacity'] >= $fullMin) {
        $device['capacity'] = $sensor['capacity'];
        $fullBins[] = $device;
    }
}

// =============================================================================
// 5. LOGGING – status sebelum hantar
// =============================================================================
$log = date('Y-m-d H:i:s') . " | canSend: " . ($canSend ? 'YA' : 'TIDAK');
$log .= " | Sebab: " . (empty($reasons) ? '-' : implode(', ', $reasons)) . "\n";
$log .= date('Y-m-d H:i:s') . " | Tong penuh dikesan: " . count($fullBins) . " unit\n";
file_put_contents($logFile, $log, FILE_APPEND);

// =============================================================================
// 6. HANTAR NOTIFIKASI JIKA BOLEH
// =============================================================================
$status = ['success' => false, 'message' => '', 'sent_count' => 0];

if ($canSend && !empty($fullBins)) {
    // Dapatkan nombor telefon penyelia (role = 4)
    $phones = $db->query("
        SELECT DISTINCT phone
        FROM users
        WHERE role = 4 AND phone IS NOT NULL AND phone != ''
    ")->fetchAll(PDO::FETCH_COLUMN);

    if (empty($phones)) {
        file_put_contents($logFile, date('Y-m-d H:i:s') . " | Tiada nombor penyelia ditemui\n", FILE_APPEND);
        $status['message'] = "Tiada nombor telefon penyelia";
    } else {
        // Bina mesej
        $deviceList = '';
        foreach ($fullBins as $bin) {
            $deviceList .= "🆔 : {$bin['device_name']}\n📍 Lokasi: {$bin['location']}\n📊 Kapasiti: {$bin['capacity']}%\n\n";
        }

        $msg = "🚨 *" . count($fullBins) . "* TONG SAMPAH PENUH 🚨\n\n" .
               "Senarai tong yang penuh:\n\n" . $deviceList .
               "📅 Tarikh: " . $now->format('d-m-Y') . "\n" .
               "⏰ Masa: " . $now->format('H:i') . "\n\n" .
               "⚠️ Tindakan Segera Diperlukan:\n" .
               "1. Kosongkan tong\n2. Bersihkan kawasan\n3. Letak tong semula\n\n" .
               "Terima kasih!";

        $sentCount = 0;
        foreach ($phones as $phone) {
            $formatted = '60' . ltrim(preg_replace('/\D+/', '', $phone), '0');
            $result = $whatsapp->sendText($formatted, $msg);

            $ok = $result['success'];
            $sentCount += $ok ? 1 : 0;

            file_put_contents(
                $logFile,
                date('Y-m-d H:i:s') . " | WA → $formatted | " . ($ok ? 'BERJAYA' : 'GAGAL') .
                " (HTTP {$result['http_code']})\n",
                FILE_APPEND
            );
        }

        $status = [
            'success'    => ($sentCount > 0),
            'message'    => "Berjaya hantar ke $sentCount / " . count($phones) . " nombor",
            'sent_count' => $sentCount,
        ];

        // Log ke database (jika table wujud)
        try {
            $logStmt = $db->prepare("
                INSERT INTO notification_logs
                (channel, message_preview, message_full, sent_at)
                VALUES ('whatsapp', ?, ?, ?)
            ");
            $logStmt->execute([
                substr($deviceList, 0, 300), // preview: first 300 chars of device list
                $msg,                         // full message sent
                $now->format('Y-m-d H:i:s')  // consistent Malaysia time
            ]);
        } catch (Exception $e) {
            // senyap jika table tak wujud
        }
    }
} else if (!$canSend) {
    $status['message'] = "Tidak dihantar → " . implode(', ', $reasons);
} else {
    $status['message'] = "Tiada tong penuh pada masa ini";
}

// =============================================================================
// 7. OUTPUT STATUS (untuk debugging atau panggilan manual)
// =============================================================================
header('Content-Type: text/plain; charset=utf-8');

echo "╔════════════════════════════════════════════╗\n";
echo "║          SMARTBIN CRON STATUS              ║\n";
echo "╚════════════════════════════════════════════╝\n\n";

echo "Tarikh & Masa   : " . $now->format('d-m-Y H:i:s') . "\n";
echo "Boleh hantar    : " . ($canSend ? 'YA' : 'TIDAK') . "\n";
if (!$canSend && !empty($reasons)) {
    echo "Sebab           : " . implode("\n                  ", $reasons) . "\n";
}
echo "Tong penuh      : " . count($fullBins) . " unit\n";
echo "Status notifikasi : " . ($status['success'] ? 'BERJAYA' : 'TIDAK DIHANTAR / GAGAL') . "\n";
echo "Butiran         : " . $status['message'] . "\n\n";

echo "Log disimpan di : " . basename($logFile) . "\n";
echo "Selesai dijalankan pada " . date('H:i:s') . "\n";

file_put_contents($logFile, date('Y-m-d H:i:s') . " | Cron selesai | " . $status['message'] . "\n\n", FILE_APPEND);