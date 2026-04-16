<?php
require __DIR__ . '/db.php';
require __DIR__ . '/whatsapp.php';
require __DIR__ . '/../vendor/autoload.php'; // Composer autoload

use Carbon\Carbon;

date_default_timezone_set('Asia/Kuala_Lumpur');

function isWithinCollectionWindow(Carbon $timestamp): bool
{
    $minutes = ($timestamp->hour * 60) + $timestamp->minute;
    $startMinutes = 7 * 60;
    $endMinutes = 19 * 60;

    return $minutes >= $startMinutes && $minutes <= $endMinutes;
}

function isCollectionCapacity(float $capacity): bool
{
    return $capacity <= 0.0 || abs($capacity) < 0.00001;
}

function isDailyReportTime(Carbon $timestamp): bool
{
    return $timestamp->format('H:i') === '18:58';
}

function formatAssetLabel(array $asset): string
{
    $assetName = trim((string) ($asset['asset_name'] ?? ''));
    $location = trim((string) ($asset['location'] ?? ''));

    return $assetName !== '' ? $assetName : $location;
}

function getDailyCollectionTrips(PDO $db, Carbon $date): array
{
    $dayStart = $date->copy()->startOfDay();
    $dayEnd = $date->copy()->endOfDay();

    $assetStmt = $db->query("
        SELECT a.id, a.asset_name, a.location
        FROM assets a
        WHERE a.is_active = 1
        ORDER BY a.asset_name ASC, a.location ASC
    ");

    $assets = $assetStmt->fetchAll(PDO::FETCH_ASSOC);
    $tripRows = [];

    $sensorStmt = $db->prepare("
        SELECT d.id_device, d.device_name, s.capacity, s.created_at
        FROM devices d
        JOIN sensors s ON s.device_id = d.id_device
        WHERE d.asset_id = ?
          AND d.is_active = 1
          AND s.created_at BETWEEN ? AND ?
        ORDER BY s.created_at ASC, d.id_device ASC
    ");

    foreach ($assets as $asset) {
        $sensorStmt->execute([
            $asset['id'],
            $dayStart->format('Y-m-d H:i:s'),
            $dayEnd->format('Y-m-d H:i:s'),
        ]);

        $readings = $sensorStmt->fetchAll(PDO::FETCH_ASSOC);
        $previousCapacities = [];
        $binCleared = false;
        $triggeredDeviceId = null;

        foreach ($readings as $reading) {
            if (!is_numeric($reading['capacity'])) {
                continue;
            }

            $deviceId = (string) $reading['id_device'];
            $currentCapacity = (float) $reading['capacity'];
            $readingTime = Carbon::parse($reading['created_at'], 'Asia/Kuala_Lumpur');
            $previousCapacity = $previousCapacities[$deviceId] ?? null;

            if (!$binCleared) {
                if (
                    $previousCapacity !== null &&
                    $previousCapacity > 10 &&
                    isCollectionCapacity($currentCapacity)
                ) {
                    $binCleared = true;
                    $triggeredDeviceId = $deviceId;

                    if (isWithinCollectionWindow($readingTime)) {
                        $tripRows[] = [
                            'asset_id' => $asset['id'],
                            'asset_name' => $asset['asset_name'],
                            'location' => $asset['location'],
                            'id_device' => $reading['id_device'],
                            'device_name' => $reading['device_name'] ?? 'N/A',
                            'capacity' => $currentCapacity,
                            'prev_capacity' => $previousCapacity,
                            'emptied_time' => $reading['created_at'],
                        ];
                    }
                }
            } elseif ($deviceId === $triggeredDeviceId && $currentCapacity > 10) {
                $binCleared = false;
                $triggeredDeviceId = null;
            }

            $previousCapacities[$deviceId] = $currentCapacity;
        }
    }

    usort($tripRows, function ($a, $b) {
        return strcmp($b['emptied_time'], $a['emptied_time']);
    });

    return $tripRows;
}

function getDailyCollectionReport(PDO $db, Carbon $date): array
{
    $assetStmt = $db->query("
        SELECT a.id, a.asset_name, a.location
        FROM assets a
        WHERE a.is_active = 1
        ORDER BY a.asset_name ASC, a.location ASC
    ");

    $assets = $assetStmt->fetchAll(PDO::FETCH_ASSOC);
    $tripRows = getDailyCollectionTrips($db, $date);
    $countsByAsset = [];

    foreach ($tripRows as $trip) {
        $assetId = (string) $trip['asset_id'];
        $countsByAsset[$assetId] = ($countsByAsset[$assetId] ?? 0) + 1;
    }

    $reportRows = [];
    $totalCollections = 0;

    foreach ($assets as $asset) {
        $assetId = (string) $asset['id'];
        $collectionCount = $countsByAsset[$assetId] ?? 0;

        $reportRows[] = [
            'label' => formatAssetLabel($asset),
            'total' => $collectionCount,
        ];
        $totalCollections += $collectionCount;
    }

    return [
        'rows' => $reportRows,
        'total' => $totalCollections,
    ];
}

function buildDailyReportMessage(array $report, Carbon $now): string
{
    $lines = [];
    $lines[] = 'Smart Bin Report (' . $now->format('j/n/Y') . ')';
    $lines[] = '';
    $lines[] = 'Operations ran smoothly with all bins emptied as scheduled.';
    $lines[] = '';
    $lines[] = 'Collection details:';

    foreach ($report['rows'] as $row) {
        $lines[] = '• ' . $row['label'] . ' | ' . $row['total'];
    }

    $lines[] = '';
    $lines[] = 'Total collection: ' . $report['total'];
    $lines[] = '';
    $lines[] = 'Overall status: Good and consistent.';

    return implode("\n", $lines);
}

// Load .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$logFile = __DIR__ . '/cron.log';
$now = Carbon::now('Asia/Kuala_Lumpur'); // Carbon instance in Malaysia time

// WAHA configuration
$apiUrl = "https://waha.pakarai.dpdns.org";  // WAHA server URL
//$apiUrl = "https://mpeg-yoga-bite-elected.trycloudflare.com";  // WAHA server URL
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

    if (!is_numeric($currentSensor['capacity'])) {
        continue;
    }

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

    $currentCapacity = (float) $currentSensor['capacity'];
    $prevCapacity = ($prevSensor && is_numeric($prevSensor['capacity']))
        ? (float) $prevSensor['capacity']
        : null;
    $readingTime = Carbon::parse($currentSensor['created_at'], 'Asia/Kuala_Lumpur');
    $prevReadingTime = $prevSensor
        ? Carbon::parse($prevSensor['created_at'], 'Asia/Kuala_Lumpur')
        : null;

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

}

$emptiedBins = getDailyCollectionTrips($db, $now);

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
        WHERE  phone IS NOT NULL
          AND whatsapp_notify=1
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
//$tenMinAgo = $now->copy()->subMinutes(10)->format('Y-m-d H:i:s');
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
                $assetList .= $asset['location'] . "\n";
                $assetList .= "Location: *" . $asset['asset_name'] . "*\n";
                // $assetList .= "PIC: Amran (TRX DM - Manager, Soft Services) +60133564132\n\n";
                // $assetList .= "Please arrange for immediate clearance to avoid overflow.\n";
                $assetList .= "\n";
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
            $eventKey = sprintf(
                'whatsapp_emptied:%s:%s',
                $device['id_device'],
                Carbon::parse($device['emptied_time'], 'Asia/Kuala_Lumpur')->format('Y-m-d H:i:s')
            );

            $lastLogStmt = $db->prepare("
                SELECT id
                FROM notification_logs
                WHERE device_id = ? AND channel = 'whatsapp_emptied' AND message_preview = ?
                LIMIT 1
            ");
            $lastLogStmt->execute([$device['id_device'], $eventKey]);
            $alreadySent = $lastLogStmt->fetchColumn();

            if (!$alreadySent) {
                $device['event_key'] = $eventKey;
                $sendBins[] = $device;
            }
        }

        if (count($sendBins) > 0) {
            $assetList = '';
            foreach ($sendBins as $asset) {
                $ts = Carbon::parse($asset['emptied_time'], 'Asia/Kuala_Lumpur');
                $assetList .= "*TRX BIN - BIN CLEARED*\n\n";
                $assetList .= "Date: " . $ts->format('d F Y') . "\n";
                $assetList .= "Time Detected: " . $ts->format('g:i A') . "\n\n";
                $assetList .= "" . $asset['asset_name'] . "\n";
                $assetList .= "Location: *" . $asset['location'] . "*\n";
                // $assetList .= "PIC: Amran (TRX DM - Manager, Soft Services) +60133564132\n";
                $assetList .= "\n";
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
                    $logStmt->execute([$device['id_device'], $device['event_key'], $msg]);
                }
            } catch (Exception $e) {
                file_put_contents($logFile, date('Y-m-d H:i')." | DB log ERROR: ".$e->getMessage()."\n", FILE_APPEND);
            }
        }
    }

    // --------- EMPTY BINS NOTIFICATION ---------
    // DISABLED: Jangan hantar notifikasi untuk TONG SAMPAH KOSONG
    // if (count($emptyBins) > 0) { ... }

    // --------- DAILY 3:40 PM SUMMARY REPORT ---------
    if (isDailyReportTime($now)) {
        $reportDateKey = $now->format('Y-m-d');
        $alreadySentStmt = $db->prepare("
            SELECT id
            FROM notification_logs
            WHERE channel = 'whatsapp_daily_report_7pm'
              AND message_preview = ?
            LIMIT 1
        ");
        $alreadySentStmt->execute([$reportDateKey]);
        $alreadySent = $alreadySentStmt->fetchColumn();

        if (!$alreadySent) {
            $report = getDailyCollectionReport($db, $now);
            $msg = buildDailyReportMessage($report, $now);

            foreach ($supervisors as $sup) {
                if (!empty($sup['phone'])) {
                    $formatted = '60'.ltrim(preg_replace('/\D+/', '', $sup['phone']),'0');
                    try {
                        $result = $whatsapp->sendTextMessage($formatted, $msg);
                        $ok = isset($result['success']) ? $result['success'] : false;
                        file_put_contents($logFile, date('Y-m-d H:i')." | DAILY REPORT WA to {$formatted} | ".($ok?'SUCCESS':'FAILED')."\n", FILE_APPEND);
                    } catch (Exception $e) {
                        file_put_contents($logFile, date('Y-m-d H:i')." | DAILY REPORT WA ERROR: ".$e->getMessage()."\n", FILE_APPEND);
                    }
                }
            }

            try {
                $timedate = date('Y-m-d H:i:s');
                $logStmt = $db->prepare("
                    INSERT INTO notification_logs (channel, message_preview, message_full, sent_at)
                    VALUES ('whatsapp_daily_report_7pm', ?, ?, '$timedate')
                ");
                $logStmt->execute([$reportDateKey, $msg]);
            } catch (Exception $e) {
                file_put_contents($logFile, date('Y-m-d H:i')." | DAILY REPORT DB log ERROR: ".$e->getMessage()."\n", FILE_APPEND);
            }
        } else {
            file_put_contents($logFile, date('Y-m-d H:i')." | DAILY REPORT skipped, already sent for {$reportDateKey}\n", FILE_APPEND);
        }
    }
}

file_put_contents($logFile, date('Y-m-d H:i')." | Cron executed\n\n", FILE_APPEND);
