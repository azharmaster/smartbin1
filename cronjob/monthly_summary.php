<?php

declare(strict_types=1);

require __DIR__ . '/db.php';
require __DIR__ . '/../vendor/autoload.php';

use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

date_default_timezone_set('Asia/Kuala_Lumpur');

$logFile = __DIR__ . '/cron.log';
$isCli = PHP_SAPI === 'cli';

function writeCronLog(string $logFile, string $message): void
{
    file_put_contents($logFile, date('Y-m-d H:i:s') . " | {$message}\n", FILE_APPEND);
}

function renderResponse(string $message, int $statusCode = 200): void
{
    if (PHP_SAPI !== 'cli') {
        http_response_code($statusCode);
        header('Content-Type: text/plain; charset=utf-8');
    }

    echo $message;
}

function isWithinCollectionWindow(Carbon $timestamp): bool
{
    $minutes = ($timestamp->hour * 60) + $timestamp->minute;

    return $minutes >= 420 && $minutes <= 1140;
}

function isCollectionCapacity(float $capacity): bool
{
    return $capacity <= 0.0 || abs($capacity) < 0.00001;
}

function fetchTargetUsers(PDO $db): array
{
    $stmt = $db->prepare("
        SELECT id, name, email
        FROM users
        WHERE id = 6
          AND email IS NOT NULL
          AND email <> ''
    ");
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchDevicesByFloor(PDO $db): array
{
    $stmt = $db->query("
        SELECT f.floor_name, COUNT(d.id_device) AS total
        FROM assets a
        JOIN floor f ON f.id = a.floor_id
        JOIN devices d ON d.asset_id = a.id
        WHERE a.is_active = 1
          AND d.is_active = 1
        GROUP BY f.floor_name
        ORDER BY f.floor_name ASC
    ");

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchActiveAssets(PDO $db): array
{
    $stmt = $db->query("
        SELECT
            a.id,
            a.asset_name,
            a.location,
            cs.empty_to,
            cs.half_to
        FROM assets a
        LEFT JOIN capacity_settings cs ON cs.asset_id = a.id
        WHERE a.is_active = 1
        ORDER BY a.asset_name ASC, a.location ASC
    ");

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchAssetDevices(PDO $db, int $assetId): array
{
    $stmt = $db->prepare("
        SELECT id, id_device, device_name
        FROM devices
        WHERE asset_id = ?
          AND is_active = 1
        ORDER BY id_device ASC
    ");
    $stmt->execute([$assetId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchLatestSensorInRange(PDO $db, string $deviceId, string $start, string $end): ?array
{
    $stmt = $db->prepare("
        SELECT capacity, created_at
        FROM sensors
        WHERE device_id = ?
          AND created_at BETWEEN ? AND ?
        ORDER BY created_at DESC
        LIMIT 1
    ");
    $stmt->execute([$deviceId, $start, $end]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ?: null;
}

function fetchSensorHistory(PDO $db, string $deviceId): array
{
    $stmt = $db->prepare("
        SELECT capacity, created_at
        FROM sensors
        WHERE device_id = ?
        ORDER BY created_at ASC
    ");
    $stmt->execute([$deviceId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function buildAssetReadings(PDO $db, array $asset): array
{
    $devices = fetchAssetDevices($db, (int) $asset['id']);
    $readings = [];

    foreach ($devices as $device) {
        $sensorRows = fetchSensorHistory($db, (string) $device['id_device']);

        foreach ($sensorRows as $sensorRow) {
            if (!is_numeric($sensorRow['capacity'])) {
                continue;
            }

            $readings[] = [
                'device_id' => (string) $device['id_device'],
                'device_name' => $device['device_name'] ?: (string) $device['id_device'],
                'capacity' => (float) $sensorRow['capacity'],
                'created_at' => Carbon::parse($sensorRow['created_at'], 'Asia/Kuala_Lumpur'),
            ];
        }
    }

    usort($readings, static fn(array $a, array $b) => $a['created_at']->getTimestamp() <=> $b['created_at']->getTimestamp());

    return $readings;
}

function computeCapacityStats(PDO $db, Carbon $start, Carbon $end): array
{
    $assets = fetchActiveAssets($db);
    $stats = [
        'empty_count' => 0,
        'half_count' => 0,
        'full_count' => 0,
    ];

    foreach ($assets as $asset) {
        if ($asset['empty_to'] === null || $asset['half_to'] === null) {
            continue;
        }

        $devices = fetchAssetDevices($db, (int) $asset['id']);

        foreach ($devices as $device) {
            $sensor = fetchLatestSensorInRange(
                $db,
                (string) $device['id_device'],
                $start->format('Y-m-d H:i:s'),
                $end->format('Y-m-d H:i:s')
            );

            if (!$sensor || !is_numeric($sensor['capacity'])) {
                continue;
            }

            $capacity = (float) $sensor['capacity'];

            if ($capacity <= (float) $asset['empty_to']) {
                $stats['empty_count']++;
            } elseif ($capacity <= (float) $asset['half_to']) {
                $stats['half_count']++;
            } else {
                $stats['full_count']++;
            }
        }
    }

    return $stats;
}

function computeBinAnalytics(PDO $db, Carbon $start, Carbon $end): array
{
    $assets = fetchActiveAssets($db);
    $results = [];

    foreach ($assets as $asset) {
        if ($asset['half_to'] === null) {
            $results[] = [
                'asset_name' => $asset['asset_name'],
                'times_full' => 0,
                'avg_fill_time' => 0,
                'avg_clear_time' => 0,
            ];
            continue;
        }

        $allReadings = buildAssetReadings($db, $asset);
        $previousCapacities = [];
        $binCleared = false;
        $triggeredDeviceId = null;
        $currentDay = null;
        $timesFull = 0;
        $fillDurations = [];
        $clearDurations = [];
        $lastClearAt = null;
        $lastFullAt = null;
        $assetWasFull = false;

        foreach ($allReadings as $reading) {
            $deviceId = $reading['device_id'];
            $currentCap = $reading['capacity'];
            $previousCap = $previousCapacities[$deviceId] ?? null;
            $readingTime = $reading['created_at'];
            $readingDay = $readingTime->format('Y-m-d');

            if ($currentDay !== null && $currentDay !== $readingDay) {
                $binCleared = false;
                $triggeredDeviceId = null;
                $previousCapacities = [];
            }
            $currentDay = $readingDay;

            if ($previousCap !== null && $previousCap <= (float) $asset['half_to'] && $currentCap > (float) $asset['half_to']) {
                if (!$assetWasFull) {
                    $assetWasFull = true;

                    if ($readingTime->betweenIncluded($start, $end)) {
                        $timesFull++;
                        $lastFullAt = $readingTime->copy();

                        if ($lastClearAt !== null) {
                            $fillDurations[] = $lastClearAt->diffInMinutes($readingTime) / 60;
                        }
                    }
                }
            }

            if (!$binCleared) {
                if ($previousCap !== null && $previousCap > 10 && isCollectionCapacity($currentCap)) {
                    $binCleared = true;
                    $triggeredDeviceId = $deviceId;
                    $assetWasFull = false;

                    if (isWithinCollectionWindow($readingTime) && $readingTime->betweenIncluded($start, $end)) {
                        $lastClearAt = $readingTime->copy();

                        if ($lastFullAt !== null) {
                            $clearDurations[] = $lastFullAt->diffInMinutes($readingTime) / 60;
                            $lastFullAt = null;
                        }
                    }
                }
            } elseif ($deviceId === $triggeredDeviceId && $currentCap > 10) {
                $binCleared = false;
                $triggeredDeviceId = null;
            }

            $previousCapacities[$deviceId] = $currentCap;
        }

        $results[] = [
            'asset_name' => $asset['asset_name'],
            'times_full' => $timesFull,
            'avg_fill_time' => count($fillDurations) > 0 ? round(array_sum($fillDurations) / count($fillDurations), 2) : 0,
            'avg_clear_time' => count($clearDurations) > 0 ? round(array_sum($clearDurations) / count($clearDurations), 2) : 0,
        ];
    }

    return $results;
}

function getCleaningLogs(PDO $db, Carbon $start, Carbon $end): array
{
    $assets = fetchActiveAssets($db);
    $logs = [];

    foreach ($assets as $asset) {
        if ($asset['half_to'] === null) {
            continue;
        }

        $allReadings = buildAssetReadings($db, $asset);
        $previousCapacities = [];
        $binCleared = false;
        $triggeredDeviceId = null;
        $currentDay = null;

        foreach ($allReadings as $reading) {
            $deviceId = $reading['device_id'];
            $currentCap = $reading['capacity'];
            $previousCap = $previousCapacities[$deviceId] ?? null;
            $readingTime = $reading['created_at'];
            $readingDay = $readingTime->format('Y-m-d');

            if ($currentDay !== null && $currentDay !== $readingDay) {
                $binCleared = false;
                $triggeredDeviceId = null;
                $previousCapacities = [];
            }
            $currentDay = $readingDay;

            if (!$binCleared) {
                if ($previousCap !== null && $previousCap > 10 && isCollectionCapacity($currentCap)) {
                    $binCleared = true;
                    $triggeredDeviceId = $deviceId;

                    if (isWithinCollectionWindow($readingTime) && $readingTime->betweenIncluded($start, $end)) {
                        $logs[] = [
                            'asset_name' => $asset['asset_name'],
                            'device_name' => $reading['device_name'],
                            'cleaned_at' => $readingTime->copy(),
                        ];
                    }
                }
            } elseif ($deviceId === $triggeredDeviceId && $currentCap > 10) {
                $binCleared = false;
                $triggeredDeviceId = null;
            }

            $previousCapacities[$deviceId] = $currentCap;
        }
    }

    usort($logs, static fn(array $a, array $b) => $b['cleaned_at']->getTimestamp() <=> $a['cleaned_at']->getTimestamp());

    return $logs;
}

function computeSummaryMetrics(PDO $db, Carbon $start, Carbon $end, array $binAnalytics, array $cleaningLogs): array
{
    $totalFullEvents = array_sum(array_column($binAnalytics, 'times_full'));

    $fillTimes = array_values(array_filter($binAnalytics, static fn(array $item) => (float) $item['avg_fill_time'] > 0));
    $clearTimes = array_values(array_filter($binAnalytics, static fn(array $item) => (float) $item['avg_clear_time'] > 0));

    $activeBinsStmt = $db->prepare("
        SELECT COUNT(DISTINCT a.id) AS total_active_bins
        FROM devices d
        JOIN assets a ON a.id = d.asset_id
        JOIN sensors s ON s.device_id = d.id_device
        WHERE a.is_active = 1
          AND d.is_active = 1
          AND s.created_at BETWEEN ? AND ?
    ");
    $activeBinsStmt->execute([
        $start->format('Y-m-d H:i:s'),
        $end->format('Y-m-d H:i:s'),
    ]);
    $activeBins = (int) $activeBinsStmt->fetchColumn();

    return [
        'total_full_events' => $totalFullEvents,
        'avg_fill_time' => count($fillTimes) > 0 ? round(array_sum(array_column($fillTimes, 'avg_fill_time')) / count($fillTimes), 2) : 0,
        'avg_clear_time' => count($clearTimes) > 0 ? round(array_sum(array_column($clearTimes, 'avg_clear_time')) / count($clearTimes), 2) : 0,
        'total_cleaning' => count($cleaningLogs),
        'total_active_bins' => $activeBins,
    ];
}

function fetchQuickChartBase64(string $type, array $labels, string $label, array $data, string $borderColor, string $backgroundColor, string $logFile): ?string
{
    $chartUrl = 'https://quickchart.io/chart?c=' . urlencode((string) json_encode([
        'type' => $type,
        'data' => [
            'labels' => array_values($labels),
            'datasets' => [[
                'label' => $label,
                'data' => array_values($data),
                'borderColor' => $borderColor,
                'backgroundColor' => $backgroundColor,
                'fill' => true,
                'tension' => 0.3,
            ]],
        ],
    ]));

    $image = @file_get_contents($chartUrl);

    if ($image === false) {
        writeCronLog($logFile, "QuickChart fetch failed for {$label}");
        return null;
    }

    return 'data:image/png;base64,' . base64_encode($image);
}

function generatePdfReport(array $reportData): string
{
    $reportTitle = htmlspecialchars($reportData['report_title'], ENT_QUOTES, 'UTF-8');
    $summaryMetrics = $reportData['summary_metrics'];
    $binAnalytics = $reportData['bin_analytics'];
    $cleaningLogs = $reportData['cleaning_logs'];
    $devicesByFloor = $reportData['devices_by_floor'];
    $capacityStats = $reportData['capacity_stats'];
    $timesFullChartData = $reportData['times_full_chart'];
    $avgFillChartData = $reportData['avg_fill_chart'];
    $avgClearChartData = $reportData['avg_clear_chart'];

    $analyticsRows = '';
    foreach ($binAnalytics as $row) {
        $analyticsRows .= '<tr>'
            . '<td>' . htmlspecialchars((string) $row['asset_name'], ENT_QUOTES, 'UTF-8') . '</td>'
            . '<td>' . (int) $row['times_full'] . '</td>'
            . '<td>' . number_format((float) $row['avg_fill_time'], 2) . '</td>'
            . '<td>' . number_format((float) $row['avg_clear_time'], 2) . '</td>'
            . '</tr>';
    }

    $cleaningRows = '';
    if (count($cleaningLogs) === 0) {
        $cleaningRows = '<tr><td colspan="2">No collection trip records found for this period.</td></tr>';
    } else {
        foreach ($cleaningLogs as $log) {
            $cleaningRows .= '<tr>'
                . '<td>' . htmlspecialchars((string) $log['asset_name'], ENT_QUOTES, 'UTF-8') . '</td>'
                . '<td>' . htmlspecialchars($log['cleaned_at']->format('d M Y, h:i A'), ENT_QUOTES, 'UTF-8') . '</td>'
                . '</tr>';
        }
    }

    $floorRows = '';
    foreach ($devicesByFloor as $floor) {
        $floorRows .= '<tr>'
            . '<td>' . htmlspecialchars((string) $floor['floor_name'], ENT_QUOTES, 'UTF-8') . '</td>'
            . '<td>' . (int) $floor['total'] . '</td>'
            . '</tr>';
    }

    $chartOne = $timesFullChartData ? '<img src="' . $timesFullChartData . '" class="chart">' : '<p class="muted">Chart unavailable.</p>';
    $chartTwo = $avgFillChartData ? '<img src="' . $avgFillChartData . '" class="chart">' : '<p class="muted">Chart unavailable.</p>';
    $chartThree = $avgClearChartData ? '<img src="' . $avgClearChartData . '" class="chart">' : '<p class="muted">Chart unavailable.</p>';

    $html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #222; font-size: 12px; }
        h1, h2, h3 { margin: 0 0 10px; }
        .muted { color: #666; }
        .section { margin-bottom: 24px; }
        .summary-grid { width: 100%; border-collapse: separate; border-spacing: 8px; margin-bottom: 12px; }
        .summary-grid td { width: 20%; background: #f3f6fb; border: 1px solid #d9e2f1; padding: 12px; text-align: center; }
        .summary-grid .value { display: block; font-size: 18px; font-weight: bold; margin-top: 6px; }
        table.report { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.report th, table.report td { border: 1px solid #d4d4d4; padding: 8px; text-align: left; }
        table.report th { background: #f2f2f2; }
        .chart { width: 100%; max-width: 700px; margin-top: 8px; }
    </style>
</head>
<body>
    <div class="section">
        <h1>SmartBin Summary</h1>
        <p><strong>' . $reportTitle . '</strong></p>
    </div>

    <div class="section">
        <table class="summary-grid">
            <tr>
                <td>Total Full Events<span class="value">' . (int) $summaryMetrics['total_full_events'] . '</span></td>
                <td>Avg Fill Time<span class="value">' . number_format((float) $summaryMetrics['avg_fill_time'], 2) . ' hrs</span></td>
                <td>Avg Clear Time<span class="value">' . number_format((float) $summaryMetrics['avg_clear_time'], 2) . ' hrs</span></td>
                <td>Total Cleaning<span class="value">' . (int) $summaryMetrics['total_cleaning'] . '</span></td>
                <td>Active Bins<span class="value">' . (int) $summaryMetrics['total_active_bins'] . '</span></td>
            </tr>
        </table>
        <p class="muted">Current capacity snapshot: Empty ' . (int) $capacityStats['empty_count'] . ', Half ' . (int) $capacityStats['half_count'] . ', Full ' . (int) $capacityStats['full_count'] . '</p>
    </div>

    <div class="section">
        <h3>Number of Times Each Bin Became Full</h3>
        ' . $chartOne . '
    </div>

    <div class="section">
        <h3>Average Time for Bin to Become Full (Hours)</h3>
        ' . $chartTwo . '
    </div>

    <div class="section">
        <h3>Average Bin Clear Time (Hours)</h3>
        ' . $chartThree . '
    </div>

    <div class="section">
        <h3>Devices By Floor</h3>
        <table class="report">
            <tr><th>Floor</th><th>Total Devices</th></tr>
            ' . $floorRows . '
        </table>
    </div>

    <div class="section">
        <h3>Bin Analytics (Per Asset)</h3>
        <table class="report">
            <tr><th>Asset</th><th>Times Full</th><th>Avg Fill Time (hrs)</th><th>Avg Clear Time (hrs)</th></tr>
            ' . $analyticsRows . '
        </table>
    </div>

    <div class="section">
        <h3>Collection Trip</h3>
        <table class="report">
            <tr><th>Asset</th><th>Collected At</th></tr>
            ' . $cleaningRows . '
        </table>
    </div>
</body>
</html>';

    $options = new Options();
    $options->set('isRemoteEnabled', false);
    $options->set('isHtml5ParserEnabled', true);

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    return $dompdf->output();
}

function sendEmailSMTP(string $to, string $subject, string $body, string $pdfOutput, string $logFile): bool
{
    $mailHost = $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com';
    $mailPort = (int) ($_ENV['MAIL_PORT'] ?? 465);
    $mailUsername = $_ENV['MAIL_USERNAME'] ?? 'smartbin2026@gmail.com';
    $mailPassword = $_ENV['MAIL_PASSWORD'] ?? '';
    $mailEncryption = $_ENV['MAIL_ENCRYPTION'] ?? 'ssl';
    $mailFrom = $_ENV['MAIL_FROM_ADDRESS'] ?? 'smartbin2026@gmail.com';
    $mailFromName = $_ENV['MAIL_FROM_NAME'] ?? 'SmartBin Reports';

    try {
        $scheme = strtolower($mailEncryption) === 'tls' ? 'smtp' : 'smtps';
        $dsn = sprintf(
            '%s://%s:%s@%s:%d',
            $scheme,
            rawurlencode($mailUsername),
            rawurlencode($mailPassword),
            $mailHost,
            $mailPort
        );

        $transport = Transport::fromDsn($dsn);
        $mailer = new Mailer($transport);

        $email = (new Email())
            ->from(new Address($mailFrom, $mailFromName))
            ->to($to)
            ->subject($subject)
            ->text($body)
            ->attach($pdfOutput, 'summary-report.pdf', 'application/pdf');

        $mailer->send($email);

        return true;
    } catch (TransportExceptionInterface | Throwable $e) {
        writeCronLog($logFile, "Mailer ERROR for {$to}: " . $e->getMessage());
        return false;
    }
}

writeCronLog($logFile, 'Monthly summary cron started via ' . ($isCli ? 'CLI' : 'WEB'));

try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->safeLoad();

    $now = Carbon::now('Asia/Kuala_Lumpur');
    $baseDate = $now->copy()->startOfMonth();
    $start = $baseDate->copy()->startOfMonth();
    $end = $baseDate->copy()->endOfMonth();

    $reportTitle = 'Monthly Report - '
        . $baseDate->format('F Y')
        . ' (' . $start->format('d M')
        . ' - ' . $end->format('d M Y') . ')';

    $users = fetchTargetUsers($db);

    if (count($users) === 0) {
        writeCronLog($logFile, 'User id=6 with email was not found. Skipping email sending.');
        renderResponse('User id=6 with email was not found.', 404);
        exit(1);
    }

    $capacityStats = computeCapacityStats($db, $start, $end);
    $devicesByFloor = fetchDevicesByFloor($db);
    $binAnalytics = computeBinAnalytics($db, $start, $end);
    $cleaningLogs = getCleaningLogs($db, $start, $end);
    $summaryMetrics = computeSummaryMetrics($db, $start, $end, $binAnalytics, $cleaningLogs);

    $labels = array_column($binAnalytics, 'asset_name');
    $timesFullChart = fetchQuickChartBase64(
        'bar',
        $labels,
        'Times Became Full',
        array_map('intval', array_column($binAnalytics, 'times_full')),
        '#8e44ad',
        'rgba(142,68,173,0.8)',
        $logFile
    );
    $avgFillChart = fetchQuickChartBase64(
        'bar',
        $labels,
        'Average Fill Time (Hours)',
        array_map('floatval', array_column($binAnalytics, 'avg_fill_time')),
        '#2ecc71',
        'rgba(46,204,113,0.8)',
        $logFile
    );
    $avgClearChart = fetchQuickChartBase64(
        'bar',
        $labels,
        'Average Clear Time (Hours)',
        array_map('floatval', array_column($binAnalytics, 'avg_clear_time')),
        '#e74c3c',
        'rgba(231,76,60,0.8)',
        $logFile
    );

    $pdfOutput = generatePdfReport([
        'report_title' => $reportTitle,
        'capacity_stats' => $capacityStats,
        'devices_by_floor' => $devicesByFloor,
        'bin_analytics' => $binAnalytics,
        'cleaning_logs' => $cleaningLogs,
        'summary_metrics' => $summaryMetrics,
        'times_full_chart' => $timesFullChart,
        'avg_fill_chart' => $avgFillChart,
        'avg_clear_chart' => $avgClearChart,
    ]);

    $successCount = 0;

    foreach ($users as $user) {
        $email = trim((string) $user['email']);
        writeCronLog($logFile, "Sending monthly summary to {$email}");

        $sent = sendEmailSMTP(
            $email,
            'SmartBin ' . $reportTitle,
            'Please find the attached monthly summary report (PDF).',
            $pdfOutput,
            $logFile
        );

        writeCronLog($logFile, 'Email sent to ' . $email . ' | ' . ($sent ? 'SUCCESS' : 'FAILED'));

        if ($sent) {
            $successCount++;
        }
    }

    $message = "Monthly summary finished. Success: {$successCount}/" . count($users);
    writeCronLog($logFile, $message);
    renderResponse($message, $successCount > 0 ? 200 : 500);
    exit($successCount > 0 ? 0 : 1);
} catch (Throwable $e) {
    writeCronLog($logFile, 'Monthly summary cron failed: ' . $e->getMessage());
    writeCronLog($logFile, 'Failure location: ' . $e->getFile() . ':' . $e->getLine());
    renderResponse('Monthly summary job failed: ' . $e->getMessage(), 500);
    exit(1);
}
