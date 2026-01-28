<?php
require __DIR__ . '/db.php';
require __DIR__ . '/whatsapp.php';

$now = new DateTime();
$logFile = __DIR__.'/cron.log';

/* ===============================
   1️⃣ WhatsApp notification window
================================ */

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

/* ===============================
   2️⃣ Work hours
================================ */

$cfg = require __DIR__ . '/config.php';

if ($now->format('H:i') < $cfg['work_hours']['start'] || $now->format('H:i') > $cfg['work_hours']['end']) {
    $canSend = false;
    $reason[] = "Outside work hours ({$cfg['work_hours']['start']} - {$cfg['work_hours']['end']})";
}

/* ===============================
   3️⃣ Holiday / Event
================================ */

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
$isHoliday = $stmt->fetchColumn(); // 1 if a holiday exists today, false otherwise

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
$hasEvent = $stmt->fetchColumn(); // 1 if an event exists today, false otherwise

if ($hasEvent) {
    $canSend = false;
    $reason[] = "There is an active event today";
}

/* ===============================
   4️⃣ Capacity threshold
================================ */

$cap = $db->query("SELECT half_to FROM capacity_settings LIMIT 1")->fetch();
$fullMin = $cap['half_to'] + 1;

/* ===============================
   5️⃣ Scan devices
================================ */

$stmt = $db->query("
    SELECT d.id_device, d.device_name, a.location
    FROM devices d
    JOIN assets a ON a.id = d.asset_id
    WHERE d.is_active = 1 AND a.is_active = 1
");

$fullBins = [];

while ($device = $stmt->fetch()) {
    $sensor = $db->prepare("
        SELECT capacity FROM sensors
        WHERE device_id = ?
        ORDER BY time DESC
        LIMIT 1
    ");
    $sensor->execute([$device['id_device']]);
    $sensor = $sensor->fetch();

    if (!$sensor) continue;

    if ($sensor['capacity'] >= $fullMin) {
        $device['capacity'] = $sensor['capacity'];
        $fullBins[] = $device;
    }
}

/* ===============================
   6️⃣ Debug logging before sending
================================ */

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

/* ===============================
   7️⃣ Send WhatsApp
================================ */

if ($canSend && count($fullBins)) {

    $phones = $db->query("
        SELECT DISTINCT phone
        FROM users
        WHERE role = 4 AND phone IS NOT NULL
    ")->fetchAll(PDO::FETCH_COLUMN);

    if (empty($phones)) {
        file_put_contents($logFile, date('Y-m-d H:i')." | No supervisor phones found\n", FILE_APPEND);
    } else {

        $deviceList = '';
        foreach ($fullBins as $device) {
            $deviceList .= 
                "🆔 : {$device['device_name']}\n" .
                "📍 Lokasi: {$device['location']}\n" .
                "📊 Kapasiti: {$device['capacity']}%\n\n";
        }

        $msg =
            "🚨 *".count($fullBins)."* *TONG SAMPAH PENUH* 🚨\n\n" .
            "Berikut adalah senarai tong sampah yang telah penuh:\n\n" .
            $deviceList .
            "📅 Tarikh: ".$now->format('d-m-Y')."\n" .
            "⏰ Masa: ".$now->format('H:i')."\n\n" .
            "⚠️ *Tindakan Segera Diperlukan:*\n" .
            "1. Sila kosongkan tong sampah\n" .
            "2. Bersihkan kawasan sekeliling\n" .
            "3. Pastikan tong diletakkan semula\n\n" .
            "Terima kasih atas kerjasama anda.";

        foreach ($phones as $p) {
            $formatted = '60'.ltrim(preg_replace('/\D+/', '', $p), '0');
            $ok = sendWhatsApp($formatted, $msg);

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
        $log->execute([substr($deviceList, 0, 300), $deviceList]);
    }
}

/* ===============================
   Done
================================ */

file_put_contents($logFile, date('Y-m-d H:i')." | Cron executed\n\n", FILE_APPEND);
