<?php
require __DIR__ . '/db.php';
require __DIR__ . '/whatsapp.php';
require __DIR__ . '/../vendor/autoload.php'; // Composer autoload

use Carbon\Carbon;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

date_default_timezone_set('Asia/Kuala_Lumpur');

// Load .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$logFile = __DIR__ . '/cron.log';
$now = Carbon::now('Asia/Kuala_Lumpur'); // Carbon instance in Malaysia time

// --- Helper: send email via SMTP safely ---
function sendEmailSMTP($to, $subject, $body, $logFile) {
    $mail = new PHPMailer(true);

    // Read env variables with fallback
    $mailHost       = $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com';
    $mailPort       = $_ENV['MAIL_PORT'] ?? 465;
    $mailUsername   = $_ENV['MAIL_USERNAME'] ?? 'smartbin2026@gmail.com';
    $mailPassword   = $_ENV['MAIL_PASSWORD'] ?? '';
    $mailEncryption = $_ENV['MAIL_ENCRYPTION'] ?? 'ssl';
    $mailFrom       = $_ENV['MAIL_FROM_ADDRESS'] ?? 'smartbin2026@gmail.com';
    $mailFromName   = $_ENV['MAIL_FROM_NAME'] ?? 'SmartBin Reports';

    try {
        // SMTP setup
        $mail->isSMTP();
        $mail->Host       = $mailHost;
        $mail->SMTPAuth   = true;
        $mail->Username   = $mailUsername;
        $mail->Password   = $mailPassword;
        $mail->SMTPSecure = $mailEncryption;
        $mail->Port       = $mailPort;

        // Only ONE setFrom!
        $mail->setFrom($mailFrom, $mailFromName);

        // Recipient
        $mail->addAddress($to);

        // Content
        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        file_put_contents(
            $logFile,
            date('Y-m-d H:i') . " | PHPMailer ERROR for {$to}: " . $mail->ErrorInfo . "\n",
            FILE_APPEND
        );
        return false;
    }
}

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
   4️⃣ Scan Devices
---------------------------- */
$stmt = $db->query("
    SELECT d.id_device, d.device_name, a.asset_name, a.id AS asset_id, a.location
    FROM devices d
    JOIN assets a ON a.id = d.asset_id
    WHERE d.is_active = 1 AND a.is_active = 1
");

$fullBins = [];

while ($device = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // Get latest sensor reading
    $sensorStmt = $db->prepare("
        SELECT capacity, created_at
        FROM sensors
        WHERE device_id = ?
        ORDER BY created_at DESC
        LIMIT 1
    ");
    $sensorStmt->execute([$device['id_device']]);
    $sensor = $sensorStmt->fetch(PDO::FETCH_ASSOC);

    if (!$sensor) continue;

    // Get capacity settings for this asset
    $capStmt = $db->prepare("
        SELECT empty_to, half_to
        FROM capacity_settings
        WHERE asset_id = ?
        LIMIT 1
    ");
    $capStmt->execute([$device['asset_id']]);
    $cap = $capStmt->fetch(PDO::FETCH_ASSOC);

    if (!$cap) continue;

    // FULL condition
    if ($sensor['capacity'] > $cap['half_to']) {
        $device['capacity'] = $sensor['capacity'];
        $device['full_time'] = $sensor['created_at'];
        $fullBins[] = $device;
    }
}

/* -------------------------
   Debug Logging
---------------------------- */
file_put_contents($logFile, date('Y-m-d H:i')." | canSend: ".($canSend ? 'YES':'NO')." | Reasons: ".implode(', ',$reason)."\n", FILE_APPEND);
file_put_contents($logFile, date('Y-m-d H:i')." | Full bins detected: ".count($fullBins)."\n".print_r($fullBins,true)."\n", FILE_APPEND);

/* -------------------------
   5️⃣ Send Notification if Allowed
---------------------------- */
if ($canSend && count($fullBins)) {
    $supervisors = $db->query("
        SELECT DISTINCT phone, email
FROM users
WHERE role = 4 
 AND phone IS NOT NULL
 AND email IS NOT NULL
 AND whatsapp_notify=1
    ")->fetchAll(PDO::FETCH_ASSOC);

    if (empty($supervisors)) {
        file_put_contents($logFile, date('Y-m-d H:i')." | No supervisor contacts found\n", FILE_APPEND);
    } else {

        // Apply 10-minute throttle per device
        /** @var Carbon $now */
        $sendBins = [];
        $tenMinAgo = $now->copy()->subMinutes(10)->format('Y-m-d H:i:s');

        foreach ($fullBins as $device) {
            $lastLogStmt = $db->prepare("
                SELECT sent_at
                FROM notification_logs
                WHERE device_id = ?
                ORDER BY sent_at DESC
                LIMIT 1
            ");
            $lastLogStmt->execute([$device['id_device']]);
            $lastSent = $lastLogStmt->fetchColumn();

            if (!$lastSent || $lastSent <= $tenMinAgo) {
                $sendBins[] = $device; // eligible for notification
            }
        }

        if (count($sendBins)) {
            // Build combined message
            // Group by asset_id (so same bin only appears once)
            $groupedAssets = [];

            foreach ($sendBins as $device) {
                $assetId = $device['asset_id'];

                if (!isset($groupedAssets[$assetId])) {
                    $groupedAssets[$assetId] = [
                        'asset_name' => $device['asset_name'],
                        'location'   => $device['location'],
                        'full_time'  => $device['full_time']
                    ];
                }
            }

            // Build message using unique assets only
            $assetList = '';
            foreach ($groupedAssets as $asset) {
                $ts = Carbon::parse($asset['full_time']);
                $assetList .= "{$asset['asset_name']}\nLocation: {$asset['location']}\nDate: ".$ts->format('d-m-Y')."\nTime: ".$ts->format('H:i A')."\n\n";
            }

            $msg = "*".count($groupedAssets)."* *FULL BINS*\n\n".$assetList;

            file_put_contents($logFile, date('Y-m-d H:i')." | Message prepared:\n".$msg."\n", FILE_APPEND);

            // Send to supervisors
            foreach ($supervisors as $sup) {

                file_put_contents($logFile, date('Y-m-d H:i')." | Processing supervisor: ".json_encode($sup)."\n", FILE_APPEND);

                // WhatsApp
                if (!empty($sup['phone'])) {
                    $formatted = '60'.ltrim(preg_replace('/\D+/', '', $sup['phone']),'0');
                    try {
                        $result = $whatsapp->sendTextMessage($formatted, $msg);
                        $ok = isset($result['success']) ? $result['success'] : false;
                    } catch (Exception $e) {
                        $ok = false;
                        file_put_contents($logFile, date('Y-m-d H:i')." | WhatsApp ERROR: ".$e->getMessage()."\n", FILE_APPEND);
                    }
                    file_put_contents($logFile, date('Y-m-d H:i')." | WhatsApp sent to {$formatted} | ".($ok?'SUCCESS':'FAILED')."\n", FILE_APPEND);
                }

                // Email via PHPMailer
                if (!empty($sup['email'])) {
                    file_put_contents($logFile, date('Y-m-d H:i')." | Sending email to {$sup['email']}\n", FILE_APPEND);
                    $result = sendEmailSMTP($sup['email'], "FULL BINS ALERT", $msg, $logFile);
                    file_put_contents($logFile, date('Y-m-d H:i')." | Email sent to {$sup['email']} | ".($result===true?'SUCCESS':'FAILED')."\n", FILE_APPEND);
                }
            }

            // -------------------------
            // Log to DB (one row per device)
            // -------------------------
            try {
                $timedate=date('Y-m-d H:i:s');
                $logStmt = $db->prepare("
                    INSERT INTO notification_logs (device_id, channel, message_preview, message_full, sent_at)
                    VALUES (?, 'whatsapp+email', ?, ?, '$timedate')
                ");
                foreach ($sendBins as $device) {
                    $logStmt->execute([
                        $device['id_device'],
                        substr($assetList,0,300),
                        $msg
                    ]);
                }
            } catch (Exception $e) {
                file_put_contents($logFile, date('Y-m-d H:i')." | DB log ERROR: ".$e->getMessage()."\n", FILE_APPEND);
            }
        } else {
            file_put_contents($logFile, date('Y-m-d H:i')." | No bins eligible for notification (throttle 10min)\n", FILE_APPEND);
        }
    }
}

file_put_contents($logFile, date('Y-m-d H:i')." | Cron executed\n\n", FILE_APPEND);