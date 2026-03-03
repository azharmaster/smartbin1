<?php
require __DIR__ . '/db.php';
require __DIR__ . '/whatsapp.php';
require __DIR__ . '/../vendor/autoload.php'; // Composer autoload

use Carbon\Carbon;

date_default_timezone_set('Asia/Kuala_Lumpur');

// Load .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$logFile = __DIR__ . '/cron.log';
$now = Carbon::now('Asia/Kuala_Lumpur'); // Carbon instance in Malaysia time

// WAHA configuration
$apiUrl = "https://beta-waha.txfdw3.easypanel.host";  // WAHA server URL
$apiKey = "admin";                                      // WAHA API key
$whatsapp = new WhatsAppSender($apiUrl, $apiKey);

$now = Carbon::now('Asia/Kuala_Lumpur'); // Carbon instance in Malaysia time
$logFile = __DIR__.'/cron.log';

/* -------------------------
   1️⃣ Check Notification Toggle
---------------------------- */
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

/* -------------------------
   2️⃣ Check Work Hours
---------------------------- */
$cfg = require __DIR__ . '/config.php';

if ($now->format('H:i') < $cfg['work_hours']['start'] || $now->format('H:i') > $cfg['work_hours']['end']) {
    $canSend = false;
    $reason[] = "Outside work hours ({$cfg['work_hours']['start']} - {$cfg['work_hours']['end']})";
}

/* -------------------------
   3️⃣ Check Holidays / Events
---------------------------- */
$today = $now->format('Y-m-d');

// Holiday
$stmt = $db->prepare("
    SELECT 1 FROM holidays
    WHERE is_active = 1
      AND ((:today BETWEEN start_date AND end_date) OR (end_date IS NULL AND start_date = :today))
    LIMIT 1
");
$stmt->execute(['today' => $today]);
if ($stmt->fetchColumn()) {
    $canSend = false;
    $reason[] = "Today is a holiday";
}

// Event
$stmt = $db->prepare("
    SELECT 1 FROM events
    WHERE is_active = 1
      AND ((:today BETWEEN start_date AND end_date) OR (end_date IS NULL AND start_date = :today))
    LIMIT 1
");
$stmt->execute(['today' => $today]);
if ($stmt->fetchColumn()) {
    $canSend = false;
    $reason[] = "There is an active event today";
}

/* -------------------------
   4️⃣ Scan Devices for All Status Types
---------------------------- */
$stmt = $db->query("
    SELECT d.id_device, d.device_name, a.asset_name, a.id AS asset_id, a.location
    FROM devices d
    JOIN assets a ON a.id = d.asset_id
    WHERE d.is_active = 1 AND a.is_active = 1
");

$fullBins = [];
$emptiedBins = [];
$emptyBins = [];

while ($device = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // Get latest 2 sensor readings (current + previous for comparison)
    $sensorStmt = $db->prepare("
        SELECT capacity, created_at
        FROM sensors
        WHERE device_id = ?
        ORDER BY created_at DESC
        LIMIT 2
    ");
    $sensorStmt->execute([$device['id_device']]);
    $sensors = $sensorStmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($sensors)) continue;

    $currentSensor = $sensors[0];
    $prevSensor = isset($sensors[1]) ? $sensors[1] : null;

    // Get capacity settings for this asset
    $capStmt = $db->prepare("
        SELECT empty_to, half_to
        FROM capacity_settings
        WHERE asset_id = ?
        LIMIT 1
    ");
    $capStmt->execute([$device['asset_id']]);
    $cap = $capStmt->fetch(PDO::FETCH_ASSOC);

    if (!$cap) {
        // Default thresholds if no settings
        $cap = ['empty_to' => 10, 'half_to' => 80];
    }

    $currentCapacity = (int) $currentSensor['capacity'];
    $prevCapacity = $prevSensor ? (int) $prevSensor['capacity'] : null;

    $device['capacity'] = $currentCapacity;
    $device['reading_time'] = $currentSensor['created_at'];

    // FULL condition: capacity > half_to threshold (e.g., >80%)
    if ($currentCapacity > $cap['half_to']) {
        $device['full_time'] = $currentSensor['created_at'];
        $fullBins[] = $device;
    }

    // EMPTY condition: capacity <= empty_to threshold (e.g., <=10%)
    if ($currentCapacity <= $cap['empty_to']) {
        $device['empty_time'] = $currentSensor['created_at'];
        $emptyBins[] = $device;
    }

    // EMPTIED condition: capacity dropped from >half_to to <=empty_to (e.g., was >80%, now <=10%)
    if ($prevCapacity !== null && 
        $prevCapacity > $cap['half_to'] && 
        $currentCapacity <= $cap['empty_to']) {
        $device['emptied_time'] = $currentSensor['created_at'];
        $device['prev_capacity'] = $prevCapacity;
        $emptiedBins[] = $device;
    }
}

/* -------------------------
   Debug Logging
---------------------------- */
file_put_contents($logFile, date('Y-m-d H:i')." | canSend: ".($canSend ? 'YES':'NO')." | Reasons: ".implode(', ',$reason)."\n", FILE_APPEND);
file_put_contents($logFile, date('Y-m-d H:i')." | Full bins: ".count($fullBins)." | Emptied bins: ".count($emptiedBins)." | Empty bins: ".count($emptyBins)."\n", FILE_APPEND);

/* -------------------------
   5️⃣ Get Supervisors
---------------------------- */
$supervisors = $db->query("
    SELECT DISTINCT phone
    FROM users
    WHERE id=6
")->fetchAll(PDO::FETCH_ASSOC);

if (empty($supervisors)) {
    file_put_contents($logFile, date('Y-m-d H:i')." | No supervisor contacts found\n", FILE_APPEND);
    $canSend = false;
}

/* -------------------------
   6️⃣ Send Notifications if Allowed
---------------------------- */
if ($canSend) {
    $tenMinAgo = $now->copy()->subMinutes(10)->format('Y-m-d H:i:s');

    // --------- FULL BINS NOTIFICATION ---------
    if (count($fullBins) > 0) {
        $sendBins = [];
        foreach ($fullBins as $device) {
            $lastLogStmt = $db->prepare("
                SELECT sent_at
                FROM notification_logs
                WHERE device_id = ? AND channel = 'whatsapp_full'
                ORDER BY sent_at DESC
                LIMIT 1
            ");
            $lastLogStmt->execute([$device['id_device']]);
            $lastSent = $lastLogStmt->fetchColumn();

            if (!$lastSent || $lastSent <= $tenMinAgo) {
                $sendBins[] = $device;
            }
        }

        if (count($sendBins) > 0) {
            $groupedAssets = [];
            foreach ($sendBins as $device) {
                $assetId = $device['asset_id'];
                if (!isset($groupedAssets[$assetId])) {
                    $groupedAssets[$assetId] = [
                        'asset_name' => $device['asset_name'],
                        'location'   => $device['location'],
                        'capacity'   => $device['capacity'],
                        'full_time'  => $device['full_time']
                    ];
                }
            }

            $assetList = '';
            foreach ($groupedAssets as $asset) {
                $ts = Carbon::parse($asset['full_time']);
                $assetList .= "*TRX BIN - IMMEDIATE CLEARANCE REQUIRED*\n\n";
                $assetList .= "Date: " . $ts->format('d F Y') . "\n";
                $assetList .= "Time Detected: " . $ts->format('g:i A') . "\n\n";
                $assetList .= "Location: *" . $asset['asset_name'] . "*\n";
                $assetList .= $asset['location'] . "\n\n";
                $assetList .= "PIC: Amran (TRX DM - Manager, Soft Services) +60133564132\n\n";
                $assetList .= "Please arrange for immediate clearance to avoid overflow.\n";
                $assetList .= "\n───────────\n\n";
            }

            $msg = $assetList;
            // $msg = "*TRX BIN - IMMEDIATE CLEARANCE REQUIRED*\n\n" . $assetList;

            foreach ($supervisors as $sup) {
                if (!empty($sup['phone'])) {
                    $formatted = '60'.ltrim(preg_replace('/\D+/', '', $sup['phone']),'0');
                    try {
                        $result = $whatsapp->sendTextMessage($formatted, $msg);
                        $ok = isset($result['success']) ? $result['success'] : false;
                        file_put_contents($logFile, date('Y-m-d H:i')." | FULL WA to {$formatted} | ".($ok?'SUCCESS':'FAILED')."\n", FILE_APPEND);
                    } catch (Exception $e) {
                        file_put_contents($logFile, date('Y-m-d H:i')." | FULL WA ERROR: ".$e->getMessage()."\n", FILE_APPEND);
                    }
                }
            }

            // Log to DB
            try {
                $timedate = date('Y-m-d H:i:s');
                $logStmt = $db->prepare("
                    INSERT INTO notification_logs (device_id, channel, message_preview, message_full, sent_at)
                    VALUES (?, 'whatsapp_full', ?, ?, '$timedate')
                ");
                foreach ($sendBins as $device) {
                    $logStmt->execute([$device['id_device'], substr($assetList, 0, 300), $msg]);
                }
            } catch (Exception $e) {
                file_put_contents($logFile, date('Y-m-d H:i')." | DB log ERROR: ".$e->getMessage()."\n", FILE_APPEND);
            }
        }
    }

    // --------- EMPTIED BINS NOTIFICATION ---------
    if (count($emptiedBins) > 0) {
        $sendBins = [];
        foreach ($emptiedBins as $device) {
            $lastLogStmt = $db->prepare("
                SELECT sent_at
                FROM notification_logs
                WHERE device_id = ? AND channel = 'whatsapp_emptied'
                ORDER BY sent_at DESC
                LIMIT 1
            ");
            $lastLogStmt->execute([$device['id_device']]);
            $lastSent = $lastLogStmt->fetchColumn();

            if (!$lastSent || $lastSent <= $tenMinAgo) {
                $sendBins[] = $device;
            }
        }

        if (count($sendBins) > 0) {
            $groupedAssets = [];
            foreach ($sendBins as $device) {
                $assetId = $device['asset_id'];
                if (!isset($groupedAssets[$assetId])) {
                    $groupedAssets[$assetId] = [
                        'asset_name' => $device['asset_name'],
                        'location'   => $device['location'],
                        'prev_capacity' => $device['prev_capacity'],
                        'capacity'   => $device['capacity'],
                        'emptied_time'  => $device['emptied_time']
                    ];
                }
            }

            $assetList = '';
            foreach ($groupedAssets as $asset) {
                $ts = Carbon::parse($asset['emptied_time']);
                $assetList .= "*TRX BIN - BIN CLEARED*\n\n";
                $assetList .= "Date: " . $ts->format('d F Y') . "\n";
                $assetList .= "Time Detected: " . $ts->format('g:i A') . "\n\n";
                $assetList .= "Location: *" . $asset['location'] . "*\n";
                $assetList .= "" . $asset['asset_name'] . "\n\n";
                $assetList .= "PIC: Amran (TRX DM - Manager, Soft Services) +60133564132\n";
                $assetList .= "\n───────────\n\n";
            }

            $msg =$assetList;
            //$msg = "*TRX BIN - BIN CLEARED*\n\n" . $assetList;

            foreach ($supervisors as $sup) {
                if (!empty($sup['phone'])) {
                    $formatted = '60'.ltrim(preg_replace('/\D+/', '', $sup['phone']),'0');
                    try {
                        $result = $whatsapp->sendTextMessage($formatted, $msg);
                        $ok = isset($result['success']) ? $result['success'] : false;
                        file_put_contents($logFile, date('Y-m-d H:i')." | EMPTIED WA to {$formatted} | ".($ok?'SUCCESS':'FAILED')."\n", FILE_APPEND);
                    } catch (Exception $e) {
                        file_put_contents($logFile, date('Y-m-d H:i')." | EMPTIED WA ERROR: ".$e->getMessage()."\n", FILE_APPEND);
                    }
                }
            }

            // Log to DB
            try {
                $timedate = date('Y-m-d H:i:s');
                $logStmt = $db->prepare("
                    INSERT INTO notification_logs (device_id, channel, message_preview, message_full, sent_at)
                    VALUES (?, 'whatsapp_emptied', ?, ?, '$timedate')
                ");
                foreach ($sendBins as $device) {
                    $logStmt->execute([$device['id_device'], substr($assetList, 0, 300), $msg]);
                }
            } catch (Exception $e) {
                file_put_contents($logFile, date('Y-m-d H:i')." | DB log ERROR: ".$e->getMessage()."\n", FILE_APPEND);
            }
        }
    }

    // --------- EMPTY BINS NOTIFICATION ---------
    // DISABLED: Jangan hantar notifikasi untuk TONG SAMPAH KOSONG
    // if (count($emptyBins) > 0) { ... }
}

file_put_contents($logFile, date('Y-m-d H:i')." | Cron executed\n\n", FILE_APPEND);