<?php
require __DIR__ . '/db.php';
require __DIR__ . '/whatsapp.php';
require __DIR__ . '/../vendor/autoload.php';  // <-- correct path

use Carbon\Carbon;

// WAHA configuration
$apiUrl = "https://beta-waha.txfdw3.easypanel.host";  // WAHA server URL
$apiKey = "admin";                                      // WAHA API key
$whatsapp = new WhatsAppSender($apiUrl, $apiKey);

$now = new DateTime();
$logFile = __DIR__.'/cron.log';

/* 1️⃣ WhatsApp notification window */
$canSend = true;
$reason = [];

$notif = $db->query("SELECT * FROM whatsapp_notifications LIMIT 1")->fetch();

if (!$notif) {
    $canSend = false;
    $reason[] = "No WhatsApp notification config found";
} elseif (!$notif['is_active']) {
    $canSend = false;
    $reason[] = "WhatsApp notifications inactive";
} elseif ($now->format('H:i') < $notif['start_time'] || $now->format('H:i') > $notif['end_time']) {
    $canSend = false;
    $reason[] = "Outside notification time window";
}

/* Work hours */
$cfg = require __DIR__ . '/config.php';

if ($now->format('H:i') < $cfg['work_hours']['start'] || $now->format('H:i') > $cfg['work_hours']['end']) {
    $canSend = false;
    $reason[] = "Outside work hours ({$cfg['work_hours']['start']} - {$cfg['work_hours']['end']})";
}

/* Holiday / Event */
$today = $now->format('Y-m-d');

// --- Check holidays ---
$stmt = $db->prepare("
    SELECT 1
    FROM holidays
    WHERE is_active = 1
      AND (
          (:today BETWEEN start_date AND end_date)
          OR (end_date IS NULL AND start_date = :today)
      )
    LIMIT 1
");
$stmt->execute(['today' => $today]);
$isHoliday = $stmt->fetchColumn();

if ($isHoliday) {
    $canSend = false;
    $reason[] = "Today is a holiday";
}

$stmt = $db->prepare("
    SELECT 1
    FROM events
    WHERE is_active = 1
      AND (
          (:today BETWEEN start_date AND end_date)
          OR (end_date IS NULL AND start_date = :today)
      )
    LIMIT 1
");
$stmt->execute(['today' => $today]);
$hasEvent = $stmt->fetchColumn();

if ($hasEvent) {
    $canSend = false;
    $reason[] = "There is an active event today";
}

/* Capacity threshold */
$cap = $db->query("SELECT half_to FROM capacity_settings LIMIT 1")->fetch();
$fullMin = $cap['half_to'] + 1;

/* Scan devices */
$stmt = $db->query("
    SELECT d.id_device, d.device_name, a.location, a.asset_name
    FROM devices d
    JOIN assets a ON a.id = d.asset_id
    WHERE d.is_active = 1 AND a.is_active = 1
");

$fullBins = [];

while ($device = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $sensorStmt = $db->prepare("
        SELECT capacity, time
        FROM sensors
        WHERE device_id = ?
        ORDER BY time DESC
        LIMIT 1
    ");
    $sensorStmt->execute([$device['id_device']]);
    $sensor = $sensorStmt->fetch(PDO::FETCH_ASSOC);

    if (!$sensor) continue;

    if ($sensor['capacity'] >= $fullMin) {
        $device['capacity'] = $sensor['capacity'];
        $device['full_time'] = $sensor['time']; // timestamp when full
        $fullBins[] = $device;
    }
}

/*6️⃣ Debug logging before sending */
file_put_contents(
    $logFile,
    date('Y-m-d H:i')." | canSend: ".($canSend ? 'YES' : 'NO')." | Reasons: ".implode(', ', $reason)."\n",
    FILE_APPEND
);

file_put_contents(
    $logFile,
    date('Y-m-d H:i')." | Full bins detected: ".count($fullBins)."\n".print_r($fullBins, true)."\n",
    FILE_APPEND
);

/* Send WhatsApp */
if ($canSend && count($fullBins)) {

    $phones = $db->query("
        SELECT DISTINCT phone
        FROM users
        WHERE role = 4 AND phone IS NOT NULL
    ")->fetchAll(PDO::FETCH_COLUMN);

    if (empty($phones)) {
        file_put_contents($logFile, date('Y-m-d H:i')." | No supervisor phones found\n", FILE_APPEND);
    } else {

        // Build minimal list by unique asset with timestamp
        $uniqueAssets = [];
        foreach ($fullBins as $device) {
            $assetKey = $device['asset_name'];

            // If asset not added yet OR this device has a later timestamp
            if (!isset($uniqueAssets[$assetKey]) || $device['full_time'] > $uniqueAssets[$assetKey]['timestamp']) {
                $uniqueAssets[$assetKey] = [
                    'location'  => $device['location'],
                    'timestamp' => $device['full_time']
                ];
            }
        }

        // Build WhatsApp message
        $assetList = '';
        foreach ($uniqueAssets as $name => $data) {
            $ts = Carbon::parse($data['timestamp']);
            $assetList .= 
                "{$name}\n" .
                "Location: {$data['location']}\n" .
                "Date: ".$ts->format('d-m-Y')."\n" .
                "Time: ".$ts->format('H:i')."\n";
        }

        $msg = "*".count($uniqueAssets)."* *FULL BINS* \n\n" . $assetList;

        foreach ($phones as $p) {
            $formatted = '60'.ltrim(preg_replace('/\D+/', '', $p), '0');
            $result = $whatsapp->sendTextMessage($formatted, $msg);
            $ok = $result['success'];

            file_put_contents(
                $logFile,
                date('Y-m-d H:i')." | WhatsApp sent to {$formatted} | ".($ok ? 'SUCCESS' : 'FAILED')."\n",
                FILE_APPEND
            );
        }

        // Log to DB
        $log = $db->prepare("
            INSERT INTO notification_logs
            (channel, message_preview, message_full, sent_at)
            VALUES ('whatsapp', ?, ?, NOW())
        ");
        $log->execute([substr($assetList, 0, 300), $msg]);
    }
}

file_put_contents($logFile, date('Y-m-d H:i')." | Cron executed\n\n", FILE_APPEND);
